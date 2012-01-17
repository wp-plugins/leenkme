<?php
/*
Plugin Name: leenk.me
Plugin URI: http://leenk.me/
Description: Automatically publish to your Twitter, Facebook Profile/Fan Page/Group, and LinkedIn whenever you publish a new post on your WordPress website with the leenk.me social network connector. You need a <a href="http://leenk.me/">leenk.me API key</a> to use this plugin.
Author: Lew Ayotte @ leenk.me
Version: 2.0.0b3
Author URI: http://leenk.me/about/
Tags: twitter, facebook, face, book, linkedin, linked, in, friendfeed, friend, feed, oauth, profile, fan page, groups, image, images, social network, social media, post, page, custom post type, twitter post, tinyurl, twitter friendly links, admin, author, contributor, exclude, category, categories, retweet, republish, connect, status update, leenk.me, leenk me, leenk, scheduled post, publish, publicize, smo, social media optimization, ssl, secure, facepress, hashtags, hashtag, categories, tags, social tools, bit.ly, j.mp, bitly, jmp, ow.ly, owly, YOURLS, tinyurl
*/

define( 'LEENKME_VERSION' , '2.0.0b3' );

if ( ! class_exists( 'leenkme' ) ) {
	
	class leenkme {
		
		// Class members
		var $adminpages 			= array( 'leenkme', 'leenkme_twitter', 'leenkme_facebook', 'leenkme_linkedin', 'leenkme_friendfeed' );
		
		function leenkme() {
			
			global $wp_version;
			
			$this->wp_version = $wp_version;
			$this->base_url = plugins_url() . '/' . dirname( plugin_basename( __FILE__ ) ) . '/';
			$this->api_url	= 'https://leenk.me/api/1.1/';
			$this->timeout	= '5000';		// in miliseconds
		
			add_action( 'init', array( &$this, 'upgrade' ) );
			add_action( 'admin_enqueue_scripts', 						array( &$this, 'leenkme_admin_enqueue_scripts' ) );
			
			add_action( 'wp_ajax_show_lm_shortener_options', 					array( &$this, 'show_lm_shortener_options' ) );

			add_filter( 'get_shortlink', 'leenkme_get_shortlink_handler', 1, 4 );
	
		}
		
		function get_leenkme_settings() {
			
			$options = array( 	'twitter' 		=> false,
								'facebook' 		=> false,
								'linkedin' 		=> false,
								'friendfeed '	=> false,
								'post_types'	=> array( 'post' ),
								'url_shortener'	=> 'tinyurl' );
		
			$leenkme_settings = get_option( 'leenkme' );
			if ( !empty( $leenkme_settings ) ) {
				
				foreach ( $leenkme_settings as $key => $option ) {
					
					$options[$key] = $option;
					
				}
			
			}
			
			return $options;
			
		}
	
		function get_user_settings( $user_id = false ) {
			
			$options = array( 'leenkme_API' => '' );
	
			$user_settings = get_user_option( 'leenkme', $user_id );
			if ( !empty( $user_settings ) ) {
				
				foreach ( $user_settings as $key => $option ) {
					
					$options[$key] = $option;
					
				}
				
			}
			
			return $options;
			
		}
		
		function leenkme_admin_enqueue_scripts( $hook_suffix ) {
			
			$leenkme_general_pages 		= array( 
											'post.php', 
											'edit.php',
											'post-new.php'
										);
			
			$leenkme_settings_pages 	= array( 
											'toplevel_page_leenkme', 
											'leenk-me_page_leenkme_twitter', 
											'leenk-me_page_leenkme_facebook', 
											'leenk-me_page_leenkme_friendfeed', 
											'leenk-me_page_leenkme_linkedin' 
										);
										
			if ( in_array( $hook_suffix, array_merge( $leenkme_general_pages, $leenkme_settings_pages ) ) ) {
			
				wp_enqueue_script( 'leenkme_js', $this->base_url . 'js/leenkme.js' );
			
			}
			
			if ( in_array( $hook_suffix, $leenkme_settings_pages ) ) {	
				
				wp_enqueue_style( 'global' );
				wp_enqueue_style( 'dashboard' );
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_style( 'wp-admin' );
				wp_enqueue_style( 'leenkme_plugin_css', $this->base_url . 'css/leenkme.css' );
				
				wp_enqueue_script( 'postbox' );
				wp_enqueue_script( 'dashboard' );
				wp_enqueue_script( 'thickbox' );
			
			}
			
			$leenkme_post_pages 		= array( 
											'post.php', 
											'post-new.php'
										);
			
			if ( in_array( $hook_suffix, $leenkme_post_pages ) ) {
				
				wp_enqueue_style( 'leenkme_post_css', $this->base_url . 'css/post.css' );
				
				wp_enqueue_script( 'leenkme_post_js', $this->base_url . 'js/post.js' );
			
				if ( $this->plugin_enabled( 'twitter' ) )
					wp_enqueue_script( 'leenkme_twitter_post_js', $this->base_url . 'js/post-twitter.js' );
				
				if ( $this->plugin_enabled( 'facebook' ) )
					wp_enqueue_script( 'leenkme_facebook_post_js', $this->base_url . 'js/post-facebook.js' );
				
				if ( $this->plugin_enabled( 'linkedin' ) )
					wp_enqueue_script( 'leenkme_linkedin_post_js', $this->base_url . 'js/post-linkedin.js' );
				
				if ( $this->plugin_enabled( 'friendfeed' ) )
					wp_enqueue_script( 'leenkme_friendfeed_post_js', $this->base_url . 'js/post-friendfeed.js' );
					
			}
			
		}
		
		function leenkme_settings_page() {
			
			global $current_user;
			get_currentuserinfo();
			$user_id = $current_user->ID;
			
			// Get the user options
			$user_settings = $this->get_user_settings( $user_id );
			$leenkme_settings = $this->get_leenkme_settings();
			
			if ( isset( $_POST['update_leenkme_settings'] ) ) {
				
				if ( isset( $_POST['leenkme_API'] ) )
					$user_settings['leenkme_API'] = $_POST['leenkme_API'];
					
				update_user_option( $user_id, 'leenkme', $user_settings );
				
				if ( current_user_can( 'leenkme_manage_all_settings' ) ) { //we're dealing with the main Admin options
				
					if ( isset( $_POST['twitter'] ) )
						$leenkme_settings['twitter'] = true;
					else
						$leenkme_settings['twitter'] = false;
					
					if ( isset( $_POST['facebook'] ) )
						$leenkme_settings['facebook'] = true;
					else
						$leenkme_settings['facebook'] = false;
					
					if ( isset( $_POST['linkedin'] ) )
						$leenkme_settings['linkedin'] = true;
					else
						$leenkme_settings['linkedin'] = false;
					
					if ( isset( $_POST['friendfeed'] ) )
						$leenkme_settings['friendfeed'] = true;
					else
						$leenkme_settings['friendfeed'] = false;
					
					if ( isset( $_POST['post_types'] ) )
						$leenkme_settings['post_types'] = $_POST['post_types'];
					
					if ( isset( $_POST['url_shortener'] ) )
						$leenkme_settings['url_shortener'] = $_POST['url_shortener'];
					
					if ( isset( $_POST['supr_shortner_type'] ) )
						$leenkme_settings['supr_shortner_type'] = $_POST['supr_shortner_type'];
					
					if ( isset( $_POST['supr_username'] ) )
						$leenkme_settings['supr_username'] = $_POST['supr_username'];
					
					if ( isset( $_POST['supr_apikey'] ) )
						$leenkme_settings['supr_apikey'] = $_POST['supr_apikey'];
					
					if ( isset( $_POST['bitly_username'] ) )
						$leenkme_settings['bitly_username'] = $_POST['bitly_username'];
					
					if ( isset( $_POST['bitly_apikey'] ) )
						$leenkme_settings['bitly_apikey'] = $_POST['bitly_apikey'];
					
					if ( isset( $_POST['yourls_auth_type'] ) )
						$leenkme_settings['yourls_auth_type'] = $_POST['yourls_auth_type'];
					
					if ( isset( $_POST['yourls_api_url'] ) )
						$leenkme_settings['yourls_api_url'] = $_POST['yourls_api_url'];
					
					if ( isset( $_POST['yourls_username'] ) )
						$leenkme_settings['yourls_username'] = $_POST['yourls_username'];
					
					if ( isset( $_POST['yourls_password'] ) )
						$leenkme_settings['yourls_password'] = $_POST['yourls_password'];
					
					if ( isset( $_POST['yourls_signature'] ) )
						$leenkme_settings['yourls_signature'] = $_POST['yourls_signature'];
					
					update_option( 'leenkme', $leenkme_settings );
					
					// It's not pretty, but the easiest way to get the menu to refresh after save...
					?>
						<script type="text/javascript">
						<!--
						window.location = "<?php echo $_SERVER['PHP_SELF'] .'?page=leenkme&settings_saved'; ?>"
						//-->
						</script>
					<?php
					
				}
				
			}
			
			if ( isset( $_POST['update_leenkme_settings'] ) || isset( $_GET['settings_saved'] ) ) {
				
				// update settings notification ?>
				<div class="updated"><p><strong><?php _e( "leenk.me Settings Updated.", "leenkme" );?></strong></p></div>
				<?php
				
			}
			
			// Display HTML form for the options below
			?>
			<div class=wrap>
            <div style="width:70%;" class="postbox-container">
            <div class="metabox-holder">	
            <div class="meta-box-sortables ui-sortable">
                <form id="leenkme" method="post" action="">
                    <h2 style='margin-bottom: 10px;' ><img src='<?php echo $this->base_url; ?>/images/leenkme-logo-32x32.png' style='vertical-align: top;' /> <?php _e( 'leenk.me General Settings', 'leenkme' ); ?></h2>
                    
                    <div id="api-key" class="postbox">
                    
                        <div class="handlediv" title="Click to toggle"><br /></div>
                        
                        <h3 class="hndle"><span><?php _e( 'leenk.me API Key', 'leenkme' ); ?></span></h3>
                        
                        <div class="inside">
                        <p>
                        <?php _e( 'leenk.me API Key', 'leenkme' ); ?>: <input type="text" id="api" class="regular-text" name="leenkme_API" value="<?php echo htmlspecialchars( stripcslashes( $user_settings['leenkme_API'] ) ); ?>" />
                        <input type="button" class="button" name="verify_leenkme_api" id="verify" value="<?php _e( 'Verify leenk.me API', 'leenkme' ) ?>" />
                        <?php wp_nonce_field( 'verify', 'leenkme_verify_wpnonce' ); ?>
                        </p>
                        
                        <?php if ( empty( $user_settings['leenkme_API'] ) ) { ?>
                        
                            <p>
                            <a href="<?php echo apply_filters( 'leenkme_url', 'http://leenk.me/' ); ?>"><?php _e( 'Click here to subscribe to leenk.me and generate an API key', 'leenkme' ); ?></a>
                            </p>
                        
                        <?php } ?>
                                                  
                        <p class="submit">
                            <input class="button-primary" type="submit" name="update_leenkme_settings" value="<?php _e( 'Save Settings', 'leenkme' ) ?>" />
                        </p>
                        
                        </div>
                        
                    </div>
                    
                    <?php if ( current_user_can( 'leenkme_manage_all_settings' ) ) {?>
                    <div id="modules" class="postbox">
                    
                        <div class="handlediv" title="Click to toggle"><br /></div>
                        
                        <h3 class="hndle"><span><?php _e( 'leenk.me Administrator Options', 'leenkme' ); ?></span></h3>
                        
                        <div class="inside">
                        
                        <table id="leenkme_leenkme_social_network_modules">
                        	<tr>
                                <th rowspan="1"><?php _e( 'Enable Your Social Network Modules', 'leenkme' ); ?></th>
                                <td class="leenkme_plugin_name">Twitter: </td>
                                <td class="leenkme_plugin_button"><input type="checkbox" name="twitter" <?php checked( $leenkme_settings['twitter'] ); ?> /></td>
                                <td class="leenkme_plugin_settings"> <?php if ( $leenkme_settings['twitter'] ) { ?><a href="admin.php?page=leenkme_twitter">Twitter Settings</a><?php } ?></td>
                            </tr>
                            <tr>
                                <td rowspan="4" id="leenkme_social_network_description"><?php  _e( 'Choose which social network modules you want to enable for this site.', 'leenkme' ); ?></td>
                                <td class="leenkme_plugin_name">Facebook: </td>
                                <td class="leenkme_plugin_button"><input type="checkbox" name="facebook" <?php checked( $leenkme_settings['facebook'] ); ?> /></td>
                                <td class="leenkme_plugin_settings"> <?php if ( $leenkme_settings['facebook'] ) { ?><a href="admin.php?page=leenkme_facebook">Facebook Settings</a><?php } ?></td>
                            </tr>
                            <tr>
                                <td class="leenkme_plugin_name">LinkedIn: </td>
                                <td class="leenkme_plugin_button"><input type="checkbox" name="linkedin" <?php checked( $leenkme_settings['linkedin'] ); ?> /></td>
                                <td class="leenkme_plugin_settings"> <?php if ( $leenkme_settings['linkedin'] ) { ?><a href="admin.php?page=leenkme_linkedin">LinkedIn Settings</a><?php } ?></td>
                            </tr>
                            <tr>
                                <td id="leenkme_plugin_name">FriendFeed: </td>
                                <td id="leenkme_plugin_button"><input type="checkbox" name="friendfeed" <?php checked( $leenkme_settings['friendfeed'] ); ?> /></td>
                                <td id="leenkme_plugin_settings"> <?php if ( $leenkme_settings['friendfeed'] ) { ?><a href="admin.php?page=leenkme_friendfeed">Friendfeed Settings</a><?php } ?></td>
                            </tr>
                        </table>
                        
                        <table id="leenkme_leenkme_post_type_to_publish">
                        
                        <tr>
                            <th rowspan="1"><?php _e( 'Select Your Post Types', 'leenkme' ); ?></th>
                            <td class="leenkme_post_type_name"><?php _e( 'Post:', 'leenkme' ); ?></td>
                            <td class="leenkme_module_checkbox">
                                <input type="checkbox" value="post" name="post_types[]" checked="checked" readonly="readonly" disabled="disabled" />
                                <input type="hidden" value="post" name="post_types[]" />
                            </td>
                        </tr>
                        <?php if ( version_compare( $this->wp_version, '2.9', '>' ) ) {
                            
                            $hidden_post_types = array( 'post', 'attachment', 'revision', 'nav_menu_item' );
                            $post_types = get_post_types( array(), 'objects' );
							$post_types_num = count( $post_types );
							$first = true;
							
							echo '<tr>';
							echo '	<td rowspan="' . ( $post_types_num - 4 ) . '">' . __( 'Choose which post types you want leenk.me to automatically publish to your social networks.', 'leenkme' ) . '</td>';
							 
                            foreach ( $post_types as $post_type ) {
                                
                                if ( in_array( $post_type->name, $hidden_post_types ) ) 
                                    continue;
									
								if ( !$first )
									echo "<tr>";
									
								$first = false;
                                ?>
                                
                                <td class="leenkme_post_type_name"><?php echo ucfirst( $post_type->name ); ?>: </td>
                                <td class="post_type_checkbox"><input type="checkbox" value="<?php echo $post_type->name; ?>" name="post_types[]" <?php checked( in_array( $post_type->name, $leenkme_settings['post_types'] ) ); ?> /></td></tr>
                                
                                <?php } ?>
                        </table>
                        
                        <?php } else { ?>
                        
                        </table>
                        <p><?php _e( 'To take advantage of publishing to Pages and Custom Post Types, please upgrade to the latest version of WordPress.', 'leenkme' ); ?></p>
                        
                        <?php } ?>
                        
                        <table id="leenkme_leenkme_url_shortener">
                        
                        <tr>
                        	<th rowspan="1"><?php _e( 'Select Your Default URL Shortner', 'leenkme' ); ?></th>
                            <td class="leenkme_url_shortener">
                            	<select id="leenkme_url_shortener_select" name="url_shortener"> 
                                	<option value="supr" <?php selected( 'supr', $leenkme_settings['url_shortener'] ); ?>>su.pr</option>
                                	<option value="bitly" <?php selected( 'bitly', $leenkme_settings['url_shortener'] ); ?>>bit.ly</option>
                                    <option value="yourls" <?php selected( 'yourls', $leenkme_settings['url_shortener'] ); ?>>YOURLS</option>
                                    <option value="isgd" <?php selected( 'isgd', $leenkme_settings['url_shortener'] ); ?>>is.gd</option>
                                    <option value="wpme" <?php selected( 'wpme', $leenkme_settings['url_shortener'] ); ?>>wp.me</option>
                                    <option value="owly" <?php selected( 'owly', $leenkme_settings['url_shortener'] ); ?>>ow.ly</option>
                                    <option value="tinyurl" <?php selected( 'tinyurl', $leenkme_settings['url_shortener'] ); ?>>TinyURL</option>
                                    <option value="tflp" <?php selected( 'tflp', $leenkme_settings['url_shortener'] ); ?>>Twitter Friendly Links Plugin</option>
                                    <option value="wppostid" <?php selected( 'wppostid', $leenkme_settings['url_shortener'] ); ?>>WordPress Post ID</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                        	<td></td>
                            <td class='url_shortener_options'>
                            	<?php
									switch( $leenkme_settings['url_shortener'] ) {
									
										case 'supr' :
											leenkme_show_supr_options();
											break;
										
										case 'bitly' :
											leenkme_show_bitly_options();
											break;
										
										case 'yourls' :
											leenkme_show_yourls_options();
											break;
										
										case 'wpme' :
											leenkme_show_wpme_options();
											break;
										
										case 'tflp' :
											leenkme_show_tflp_options();
											break;
										
									}
								?>
                            </td>
                        </tr>
                        
                        </table>
                            
                        <?php wp_nonce_field( 'leenkme_general_options', 'leenkme_general_options_nonce' ); ?>
                                                  
                        <p class="submit">
                            <input class="button-primary" type="submit" name="update_leenkme_settings" value="<?php _e( 'Save Settings', 'leenkme' ) ?>" />
                        </p>

                        </div>
                        
                    </div>
                    <?php } ?>
                </form>
            </div>
            </div>
            </div>
			</div>
			<?php
			
		}
		
		function leenkme_add_wpnonce() {
			
			wp_nonce_field( 'leenkme', 'leenkme_wpnonce' );
			
		}
		
		function plugin_enabled( $plugin ) {
			
			$leenkme_settings = $this->get_leenkme_settings();
			return $leenkme_settings[$plugin];
			
		}
		
		function upgrade() {
			
			$leenkme_settings = $this->get_leenkme_settings();
			
			if ( isset( $leenkme_settings['version'] ) )
				$old_version = $leenkme_settings['version'];
			else
				$old_version = 0;
			
			if ( version_compare( $old_version, '1.2.3', '<' ) )
				$this->upgrade_to_1_2_3();
			
			if ( version_compare( $old_version, '1.3.0', '<' ) )
				$this->upgrade_to_1_3_0();
			
			$leenkme_settings['version'] = LEENKME_VERSION;
			update_option( 'leenkme', $leenkme_settings );
			
		}
		
		function upgrade_to_1_2_3() {
			
			$role = get_role('administrator');
			if ($role !== NULL)
				$role->add_cap('leenkme_manage_all_settings');
				$role->add_cap('leenkme_edit_user_settings');
	
			$role = get_role('editor');
			if ($role !== NULL)
				$role->add_cap('leenkme_edit_user_settings');
	
			$role = get_role('author');
			if ($role !== NULL)
				$role->add_cap('leenkme_edit_user_settings');
	
			$role = get_role('contributor');
			if ($role !== NULL)
				$role->add_cap('leenkme_edit_user_settings');
				
		}
		
		function upgrade_to_1_3_0() {
			
			global $wpdb;
			
			$user_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM '. $wpdb->users ) );
			
			foreach ( (array)$user_ids as $user_id ) {
				
				if ( !user_can( $user_id, 'leenkme_edit_user_settings' ) ) {
					
					clean_user_cache( $user_id );
					continue;
					
				}
				
				$tw_user_settings = get_user_option( 'leenkme_twitter', $user_id );
				if ( !empty( $tw_user_settings )
						&& isset( $tw_user_settings['tweetcats'] ) && !empty( $tw_user_settings['tweetcats'] ) ) {
				
					$new_tweetcats = $this->convert_old_categories( $tw_user_settings['tweetcats'] );
					if ( !empty( $new_tweetcats ) ) {
						
						$tw_user_settings['clude'] = array_shift( $new_tweetcats );
						$tw_user_settings['tweetcats'] = $new_tweetcats;
						update_user_option( $user_id, 'leenkme_twitter', $tw_user_settings );
						
					} 
				
				}
				
				$fb_user_settings = get_user_option( 'leenkme_facebook', $user_id );
				if ( !empty( $fb_user_settings ) 
						&& isset( $fb_user_settings['publish_cats'] ) && !empty( $fb_user_settings['publish_cats'] ) ) {
				
					$new_publish_cats = $this->convert_old_categories( $fb_user_settings['publish_cats'] );
					
					if ( !empty( $new_publish_cats ) ) {
						
						$fb_user_settings['clude'] = array_shift( $new_publish_cats );
						$fb_user_settings['publish_cats'] = $new_publish_cats;
						update_user_option( $user_id, 'leenkme_facebook', $fb_user_settings );
						
					} 
				
				}
				
				$li_user_settings = get_user_option( 'leenkme_linkedin', $user_id );
				if ( !empty( $li_user_settings )
						&&isset( $li_user_settings['share_cats'] ) && !empty( $li_user_settings['share_cats'] ) ) {
				
					$new_share_cats = $this->convert_old_categories( $li_user_settings['share_cats'] );
					
					if ( !empty( $new_share_cats ) ) {
						
						$li_user_settings['clude'] = array_shift( $new_share_cats );
						$li_user_settings['share_cats'] = $new_share_cats;
						update_user_option( $user_id, 'leenkme_linkedin', $li_user_settings );
						
					}
					
				}
				
				clean_user_cache( $user_id );
				
			}
			
		}
		
		function convert_old_categories( $categories ) {
	
			$cats = split( ",", $categories );
			
			foreach ( (array)$cats as $cat ) {
				
				if ( preg_match( '/^-\d+/', $cat ) ) {
					
					$exclude[] = (int)preg_replace( '/^-/', '', $cat );
					
				} else if ( preg_match( '/\d+/', $cat ) ) {
					
					$include[] = (int)$cat;
					
				}
				
			}
			
			if ( !empty( $include ) ) {
			
				array_unshift( $include, 'in' );
				return $include;
				
			} else if ( !empty( $exclude ) ) {
			
				array_unshift( $exclude, 'ex' );
				return $exclude;
				
			} else {
	
				return array( 'in', '0' ); // Default to include all categories
				
			}
			
		}
		
		function leenkme_add_meta_tag_options() {
			
			global $dl_pluginleenkme;
			
			$leenkme_settings = $dl_pluginleenkme->get_leenkme_settings();
			foreach ( $leenkme_settings['post_types'] as $post_type ) {
				
				add_meta_box( 
					'leenkme',
					__( 'leenk.me', 'leenkme' ),
					array( $this, 'leenkme_meta_box' ),
					$post_type 
				);
				
			}
			
		}
		
		function leenkme_meta_box() {
			
			global $dl_pluginleenkme, $post, $current_user;
			
			get_currentuserinfo();
			$user_id = $current_user->ID;
	
			echo '<div id="leenkme_meta_box">';
			
				echo '<ul class="leenkme_tabs">';
				
				if ( $dl_pluginleenkme->plugin_enabled( 'twitter' ) ) {
					
					echo '<li><a href="#leenkme_twitter_meta_content"><img src="' . $this->base_url . '/images/twitter-16x16.png" alt="Twitter" /></a></li>';
					
				}
				
				if ( $dl_pluginleenkme->plugin_enabled( 'facebook' ) ) {
					
					echo '<li><a href="#leenkme_facebook_meta_content"><img src="' . $this->base_url . '/images/facebook-16x16.png" alt="Facebook" /></a></li>';
					
				}
				
				if ( $dl_pluginleenkme->plugin_enabled( 'linkedin' ) ) {
					
					echo '<li><a href="#leenkme_linkedin_meta_content"><img src="' . $this->base_url . '/images/linkedin-16x16.png" alt="LinkedIn" /></a></li>';
					
				}
				
				if ( $dl_pluginleenkme->plugin_enabled( 'friendfeed' ) ) {
					
					echo '<li><a href="#leenkme_friendfeed_meta_content"><img src="' . $this->base_url . '/images/friendfeed-16x16.png" alt="FriendFeed" /></a></li>';
					
				}
				
				echo '</ul>';
				
				echo '<div class="leenkme_tab_container">';
				
				if ( $dl_pluginleenkme->plugin_enabled( 'twitter' ) ) {
					
					echo '<div id="leenkme_twitter_meta_content" class="leenkme_tab_content">';
					
					global $dl_pluginleenkmeTwitter;
					echo $dl_pluginleenkmeTwitter->leenkme_twitter_meta_box();
					
					echo '</div>';
					
				}
				
				if ( $dl_pluginleenkme->plugin_enabled( 'facebook' ) ) {
					
					echo '<div id="leenkme_facebook_meta_content" class="leenkme_tab_content">';
					
					global $dl_pluginleenkmeFacebook;
					echo $dl_pluginleenkmeFacebook->leenkme_facebook_meta_box();
					
					echo '</div>';
					
				}
				
				if ( $dl_pluginleenkme->plugin_enabled( 'linkedin' ) ) {
					
					echo '<div id="leenkme_linkedin_meta_content" class="leenkme_tab_content">';
					
					global $dl_pluginleenkmeLinkedIn;
					echo $dl_pluginleenkmeLinkedIn->leenkme_linkedin_meta_box();
					
					echo '</div>';
					
				}
				
				if ( $dl_pluginleenkme->plugin_enabled( 'friendfeed' ) ) {
					
					echo '<div id="leenkme_friendfeed_meta_content" class="leenkme_tab_content">';
					
					global $dl_pluginleenkmeFriendFeed;
					echo $dl_pluginleenkmeFriendFeed->leenkme_friendfeed_meta_box();
					
					echo '</div>';
					
				}
				
				echo '</div>';
				
				echo "<div style='clear: both;'></div>";
				
			echo '</div>';

		}
		
		/**
		 * Save the data via AJAX
		 *
		 * @TODO clean params
		 * @since 0.3
		 */
		function show_lm_shortener_options() {
			
			check_ajax_referer( 'leenkme_general_options' );
			
			if ( isset( $_POST['selected'] ) ) {
				
				switch( $_POST['selected'] ) {
				
					case 'supr' :
						die( leenkme_show_supr_options() );
						break;
					
					case 'bitly' :
						die( leenkme_show_bitly_options() );
						break;
					
					case 'yourls' :
						die( leenkme_show_yourls_options() );
						break;
					
					case 'wpme' :
						die( leenkme_show_wpme_options() );
						break;
					
					case 'tflp' :
						die( leenkme_show_tflp_options() );
						break;
						
					default :
						die();
						break;
						
				}	
				
			} else {
				
				die();	
				
			}
			
		}
		
	}

}

// Instantiate the class
if ( class_exists( 'leenkme' ) ) {
	
	require_once( 'includes/functions.php' );
	require_once( 'includes/url-shortener.php' );
	
	$dl_pluginleenkme = new leenkme();
	
	if ( $dl_pluginleenkme->plugin_enabled( 'twitter' ) )
		require_once( 'twitter.php' );
	
	if ( $dl_pluginleenkme->plugin_enabled( 'facebook' ) )
		require_once( 'facebook.php' );
	
	if ( $dl_pluginleenkme->plugin_enabled( 'linkedin' ) )
		require_once( 'linkedin.php' );
	
	if ( $dl_pluginleenkme->plugin_enabled( 'friendfeed' ) )
		require_once( 'friendfeed.php' );
}

// Initialize the admin panel if the plugin has been activated
function leenkme_ap() {
	
	global $dl_pluginleenkme;
	
	if ( !isset( $dl_pluginleenkme ) )
		return;
	
	add_menu_page( __( 'leenk.me Settings', 'leenkme' ), __( 'leenk.me', 'leenkme' ), 'leenkme_edit_user_settings', 'leenkme', array( &$dl_pluginleenkme, 'leenkme_settings_page' ), $dl_pluginleenkme->base_url . '/images/leenkme-logo-16x16.png' );
	
	if (substr($dl_pluginleenkme->wp_version, 0, 3) >= '2.9')
		add_submenu_page( 'leenkme', __( 'leenk.me Settings', 'leenkme' ), __( 'leenk.me Settings', 'leenkme' ), 'leenkme_edit_user_settings', 'leenkme', array( &$dl_pluginleenkme, 'leenkme_settings_page' ) );
	
	if ( $dl_pluginleenkme->plugin_enabled( 'twitter' ) ) {
		
		global $dl_pluginleenkmeTwitter;
		add_submenu_page( 'leenkme', __( 'Twitter Settings', 'leenkme' ), __( 'Twitter', 'leenkme' ), 'leenkme_edit_user_settings', 'leenkme_twitter', array( &$dl_pluginleenkmeTwitter, 'print_twitter_settings_page' ) );
		
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'facebook' ) ) {
		
		global $dl_pluginleenkmeFacebook;
		add_submenu_page( 'leenkme', __( 'Facebook Settings', 'leenkme' ), __( 'Facebook', 'leenkme' ), 'leenkme_edit_user_settings', 'leenkme_facebook', array( &$dl_pluginleenkmeFacebook, 'print_facebook_settings_page' ) );
		
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'linkedin' ) ) {
		
		global $dl_pluginleenkmeLinkedIn;
		add_submenu_page( 'leenkme', __( 'LinkedIn Settings', 'leenkme' ), __( 'LinkedIn', 'leenkme' ), 'leenkme_edit_user_settings', 'leenkme_linkedin', array( &$dl_pluginleenkmeLinkedIn, 'print_linkedin_settings_page' ) );
		
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'friendfeed' ) ) {
		
		global $dl_pluginleenkmeFriendFeed;
		add_submenu_page( 'leenkme', __( 'FriendFeed Settings', 'leenkme' ), __( 'FriendFeed', 'leenkme' ), 'leenkme_edit_user_settings', 'leenkme_friendfeed', array( &$dl_pluginleenkmeFriendFeed, 'print_friendfeed_settings_page' ) );
		
	}
}

function leenkme_ajax_verify() {

	check_ajax_referer( 'verify' );
	
	if ( isset( $_POST['leenkme_API'] ) ) {

		$api_key = $_POST['leenkme_API'];
		$connect_arr[$api_key]['verify'] = true;
		
		$results = leenkme_ajax_connect( $connect_arr );
	
		if ( isset( $results ) ) {		
			
			foreach( $results as $result ) {	
	
				if ( is_wp_error( $result ) ) {
	
					$out[] = "<p>" . $result->get_error_message() . "</p>";
	
				} else if ( isset( $result['response']['code'] ) ) {
	
					$out[] = "<p>" . $result['body'] . "</p>";
	
				} else {
	
					$out[] = "<p>" . __( 'ERROR: Unknown error, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.', 'leenkme' ) . "</p>";
	
				}
	
			}
			
			die( join( (array)$out ) );
		
		} else {

			die( __( 'ERROR: Unknown error, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.', 'leenkme' ) );

		}

	} else {

		die( __( 'Please fill in your API key.', 'leenkme' ) );

	}

}

function leenkme_ajax_leenkme_row_action() {
	
	global $dl_pluginleenkme;

	if ( !isset( $_POST['id'] ) )
		die( __( 'Unable to determine Post ID.', 'leenkme' ) );

	if ( !isset( $_POST['colspan'] ) )
		die( __( 'Unable to determine column size.', 'leenkme' ) );
		
	$out = '<td colspan="' . $_POST['colspan'] . '">';
	
	$out .= '<h4>' . __( 'Choose the Social Networks that you want to ReLeenk and click the ReLeenk button.', 'leenkme' ) . '</h4>';
	
	if ( $dl_pluginleenkme->plugin_enabled( 'twitter' ) ) {
		
		$out .= '<label><input type="checkbox" class="lm_releenk_networks_' . $_POST['id'] . '" name="lm_releenk[]" value="twitter" /> Twitter</label><br />';
		
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'facebook' ) ) {
		
		$out .= '<label><input type="checkbox" class="lm_releenk_networks_' . $_POST['id'] . '" name="lm_releenk[]" value="facebook" /> Facebook</label><br />';
		
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'linkedin' ) ) {
		
		$out .= '<label><input type="checkbox" class="lm_releenk_networks_' . $_POST['id'] . '" name="lm_releenk[]" value="linkedin" /> LinkedIn</label><br />';
		
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'friendfeed' ) ) {
		
		$out .= '<label><input type="checkbox" class="lm_releenk_networks_' . $_POST['id'] . '" name="lm_releenk[]" value="friendfeed" /> Friendfeed</label><br />';
		
	}
	
	$out .= '<p class="submit inline-leenkme">';
	$out .= '<a class="button-secondary cancel alignleft inline-leenkme-cancel" title="Cancel" post_id="' . $_POST['id'] .'" href="#inline-releenk">' . __( 'Cancel', 'leenkme' ) . '</a>';
	$out .= '<a style="margin-left: 10px;" class="button-primary save alignleft inline-leenkme-releenk" title="ReLeenk" post_id="' . $_POST['id'] .'" href="#inline-releenk">' . __( 'ReLeenk', 'leenkme' ) . '</a>';
	$out .= '</p>';
	
	$out .= '</td>';
		
	die( $out );
	
}

function leenkme_ajax_releenk() {
	
	if ( !isset( $_POST['id'] ) )
		die( __( 'Unable to determine Post ID.', 'leenkme' ) );
	
	if ( !isset( $_POST['networks'] ) )
		die( __( 'No Social Networks selected.', 'leenkme' ) );
		
	$connect_array = array();
				
	if ( in_array( 'twitter', $_POST['networks'] ) ) {
		
		$connect_array = leenkme_publish_to_twitter( $connect_array, $_POST['id'], false, true );
		
	}
		
	if ( in_array( 'facebook', $_POST['networks'] ) ) {
	
		$connect_array = leenkme_publish_to_facebook( $connect_array, $_POST['id'], false, true );
		
	}
		
	if ( in_array( 'linkedin', $_POST['networks'] ) ) {
	
		$connect_array = leenkme_publish_to_linkedin( $connect_array, $_POST['id'], false, true );
		
	}
		
	if ( in_array( 'friendfeed', $_POST['networks'] ) ) {
	
		$connect_array = leenkme_publish_to_friendfeed( $connect_array, $_POST['id'], false, true );
		
	}
	
	$results = leenkme_ajax_connect( $connect_array );
	
	if ( isset( $results ) ) {		
				
		foreach( $results as $result ) {	

			if ( is_wp_error( $result ) ) {

				$out[] = "<p>" . $result->get_error_message() . "</p>";

			} else if ( isset( $result['response']['code'] ) ) {

				$out[] = "<p>" . $result['body'] . "</p>";

			} else {

				$out[] = "<p>" . __( 'Error received! If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' ) . "</p>";

			}

		}
		
		die( join( $out ) );
		
	} else {
		
		die( __( 'ERROR: Unknown error, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' ) );

	}
	
}

function leenkme_connect( $post ) {
	
	global $dl_pluginleenkme;
	$out = "";
	
	if ( leenkme_rate_limit() ) {
	
		$connect_arr = apply_filters( 'leenkme_connect', array(), $post->ID );
	
		if ( !empty( $connect_arr ) ) {
			
			foreach ( $connect_arr as $api_key => $body ) {
				
				$body['host'] = $_SERVER['SERVER_NAME'];
				$body['leenkme_API'] = $api_key;
				$headers = array( 'Authorization' => 'None' );
														
				$result = wp_remote_post( apply_filters( 'leenkme_api_url', $dl_pluginleenkme->api_url ), 
											array( 	'body' => $body, 
													'headers' => $headers,
													'sslverify' => false,
													'httpversion' => '1.1',
													'timeout' => $dl_pluginleenkme->timeout ) );
				
				if ( isset( $result ) ) {
					
					$out[] = $result;
					
				} else {
					
					$out[]=  "<p>" . $api_key . ": " . __( 'Undefined error occurred, for help please contact <a href="http://leenk.me/" target="_blank">leenk.me support</a>.', 'leenkme' ) . "</p>";
					
				}
				
			}
			
		}
	
	} else {
		
		$out = __( 'Error: You have exceeded your rate limit for API calls, only 350 API calls are allowed every hour.', 'leenkme' );
		
	}
	
	return $out;

}

function leenkme_ajax_connect( $connect_arr ) {
	
	global $dl_pluginleenkme;
	
	$out = array();
	
	if ( leenkme_rate_limit() ) {
		
		if ( !empty( $connect_arr ) ) {
			
			foreach ( $connect_arr as $api_key => $body ) {
				
				$body['host'] = $_SERVER['SERVER_NAME'];
				$body['leenkme_API'] = $api_key;
				$headers = array( 'Authorization' => 'None' );
														
				$result = wp_remote_post( apply_filters( 'leenkme_api_url', $dl_pluginleenkme->api_url ), 
											array( 	'body' => $body, 
													'headers' => $headers,
													'sslverify' => false,
													'httpversion' => '1.1',
													'timeout' => $dl_pluginleenkme->timeout ) );
				
				if ( isset( $result ) ) {
					
					$out[] = $result;
					
				} else {
					
					$out[] =  "<p>" . $api_key . ": " . __( 'Undefined error occurred, for help please contact <a href="http://leenk.me/" target="_blank">leenk.me support</a>.', 'leenkme' ) . "</p>";
					
				}
				
			}
			
		} else {
			
				$out[] = __( 'Invalid leenk.me setup, for help please contact <a href="http://leenk.me/" target="_blank">leenk.me support</a>.', 'leenkme' );
		
		}
		
	} else {
		
		$out[] = __( 'Error: You have exceeded your rate limit for API calls, only 350 API calls are allowed every hour.', 'leenkme' );
		
	}
	
	return $out;
	
}

function leenkme_help_list( $contextual_help, $screen ) {
	
	if ( 'leenkme' == $screen->parent_base ) {
		
		$contextual_help[$screen->id] = __( '<p>Need help working with the leenk.me plugin? Try these links for more information:</p>', 'leenkme' ) 
			. '<a href="http://leenk.me/2010/09/04/how-to-use-the-leenk-me-twitter-plugin-for-wordpress/" target="_blank">Twitter</a> | '
			. '<a href="http://leenk.me/2010/09/04/how-to-use-the-leenk-me-facebook-plugin-for-wordpress/" target="_blank">Facebook</a> | '
			. '<a href="http://leenk.me/2010/12/01/how-to-use-the-leenk-me-linkedin-plugin-for-wordpress/" target="_blank">LinkedIn</a> | '
			. '<a href="http://leenk.me/2011/04/08/how-to-use-the-leenk-me-friendfeed-plugin-for-wordpress/" target="_blank">FriendFeed</a>';

	}

	return $contextual_help;

}


function releenk_row_action( $actions, $post ) {
	
	global $dl_pluginleenkme;
	
	$leenkme_options = $dl_pluginleenkme->get_leenkme_settings();
	
	if ( in_array( $post->post_type, $leenkme_options['post_types'] ) ) {
		
		// Only show leenk.me button if the post is "published"
		if ( 'publish' === $post->post_status )
			$actions['leenkme'] = '<a class="releenk_row_action" id="' . $post->ID . '" title="leenk.me" href="#">leenk.me</a>';
		
	}
	

	return $actions;
	
}

// Actions and filters	
if ( isset( $dl_pluginleenkme ) ) {

	/*--------------------------------------------------------------------
	    Actions
	  --------------------------------------------------------------------*/
	
	add_action( 'admin_init', array( $dl_pluginleenkme, 'leenkme_add_meta_tag_options' ), 1 );

	// Add the admin menu
	add_action( 'admin_menu', 'leenkme_ap');
	
	// Whenever you publish a post, connect to leenk.me
	add_action( 'new_to_publish', 'leenkme_connect', 20 );
	add_action( 'draft_to_publish', 'leenkme_connect', 20 );
	add_action( 'pending_to_publish', 'leenkme_connect', 20 );
	add_action( 'future_to_publish', 'leenkme_connect', 20 );
	
	add_action( 'admin_footer', array( $dl_pluginleenkme, 'leenkme_add_wpnonce' ) );
	
	add_action( 'wp_ajax_verify', 				'leenkme_ajax_verify' );
	add_action( 'wp_ajax_plugins', 				'leenkme_ajax_plugins' );
	add_action( 'wp_ajax_leenkme_row_action', 	'leenkme_ajax_leenkme_row_action' );
	add_action( 'wp_ajax_releenk', 				'leenkme_ajax_releenk' );
	
	add_filter( 'contextual_help_list', 'leenkme_help_list', 10, 2);
	
	// edit-post.php post row update
	add_filter( 'post_row_actions', 'releenk_row_action', 10, 2 );
	add_filter( 'page_row_actions', 'releenk_row_action', 10, 2 );
	
	load_plugin_textdomain( 'leenkme', false, basename( dirname( __FILE__ ) ) . '/i18n' );
	
}