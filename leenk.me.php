<?php
/*
Plugin Name: leenk.me
Plugin URI: http://leenk.me/
Description: Automatically publish to your Twitter, Facebook Profile/Fan Page/Group, and LinkedIn whenever you publish a new post on your WordPress website with the leenk.me social network connector. You need a <a href="http://leenk.me/">leenk.me API key</a> to use this plugin.
Author: Lew Ayotte @ leenk.me
Version: 1.3.12
Author URI: http://leenk.me/about/
Tags: twitter, facebook, face, book, linkedin, linked, in, friendfeed, friend, feed, oauth, profile, fan page, groups, image, images, social network, social media, post, page, custom post type, twitter post, tinyurl, twitter friendly links, admin, author, contributor, exclude, category, categories, retweet, republish, connect, status update, leenk.me, leenk me, leenk, scheduled post, publish, publicize, smo, social media optimization, ssl, secure, facepress, hashtags, hashtag, categories, tags, social tools, bit.ly, j.mp
*/

define( 'LEENKME_VERSION' , '1.3.12' );

if ( ! class_exists( 'leenkme' ) ) {
	
	class leenkme {
		
		// Class members	
		var $options_name			= 'leenkme';
		var $leenkme_API			= 'leenkme_API';
		var $version				= 'version';
		var $twitter				= 'twitter';
		var $facebook				= 'facebook';
		var $linkedin				= 'linkedin';
		var $friendfeed				= 'friendfeed';
		var $base_url 				= 'base_url';
		var $api_url				= 'api_url';
		var $timeout				= 'timeout';
		var $post_types				= 'post_types';
		var $adminpages 			= array( 'leenkme', 'leenkme_twitter', 'leenkme_facebook', 'leenkme_linkedin', 'leenkme_friendfeed' );
		
		function leenkme() {
			
			global $wp_version;
			
			$this->wp_version = $wp_version;
			$this->base_url = plugins_url() . '/' . dirname( plugin_basename( __FILE__ ) ) . '/';
			$this->api_url	= 'https://leenk.me/api/1.1/';
			$this->timeout	= '5000';		// in miliseconds
		
			add_action( 'init', array( &$this, 'upgrade' ) );
			add_action( 'admin_print_scripts', array( &$this, 'leenkme_print_scripts' ) );
			add_action( 'admin_print_styles', array( &$this, 'leenkme_print_styles' ) );
	
		}
		
		function get_leenkme_settings() {
			
			$twitter 		= false;
			$facebook		= false;
			$linkedin 		= false;
			$friendfeed		= false;
			$post_types 	= array('post');
			
			$options = array( 	$this->twitter 		=> $twitter,
								$this->facebook 	=> $facebook,
								$this->linkedin 	=> $linkedin,
								$this->friendfeed 	=> $friendfeed,
								$this->post_types	=> $post_types	);
		
			$leenkme_settings = get_option( $this->options_name );
			if ( !empty( $leenkme_settings ) ) {
				
				foreach ( $leenkme_settings as $key => $option ) {
					
					$options[$key] = $option;
					
				}
			
			}
			
			return $options;
			
		}
	
		function get_user_settings( $user_id = false ) {
			
			$leenkme_API = '';
			
			$options = array( $this->leenkme_API => $leenkme_API );
	
			$user_settings = get_user_option( $this->options_name, $user_id );
			if ( !empty( $user_settings ) ) {
				
				foreach ( $user_settings as $key => $option ) {
					
					$options[$key] = $option;
					
				}
				
			}
			
			return $options;
			
		}
		
		function leenkme_print_scripts( $pagenow ) {
			
			global $pagenow;

			if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && in_array( $_GET['page'], $this->adminpages ) ) {
				
				wp_enqueue_script( 'postbox' );
				wp_enqueue_script( 'dashboard' );
				wp_enqueue_script( 'thickbox' );
				
			}
			
		}
		
		function leenkme_print_styles( $pagenow ) {
			
			global $pagenow;

			if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && in_array( $_GET['page'], $this->adminpages ) ) {
				
				wp_enqueue_style('global');
				wp_enqueue_style('dashboard');
				wp_enqueue_style('thickbox');
				wp_enqueue_style('wp-admin');
				
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
					$user_settings[$this->leenkme_API] = $_POST['leenkme_API'];
					
				update_user_option( $user_id, $this->options_name, $user_settings );
				
				if ( current_user_can( 'leenkme_manage_all_settings' ) ) { //we're dealing with the main Admin options
				
					if ( isset( $_POST['twitter'] ) )
						$leenkme_settings[$this->twitter] = true;
					else
						$leenkme_settings[$this->twitter] = false;
					
					if ( isset( $_POST['facebook'] ) )
						$leenkme_settings[$this->facebook] = true;
					else
						$leenkme_settings[$this->facebook] = false;
					
					if ( isset( $_POST['linkedin'] ) )
						$leenkme_settings[$this->linkedin] = true;
					else
						$leenkme_settings[$this->linkedin] = false;
					
					if ( isset( $_POST['friendfeed'] ) )
						$leenkme_settings[$this->friendfeed] = true;
					else
						$leenkme_settings[$this->friendfeed] = false;
					
					if ( isset( $_POST['post_types'] ) )
						$leenkme_settings[$this->post_types] = $_POST['post_types'];
					
					update_option( $this->options_name, $leenkme_settings );
					
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
                    <h2 style='margin-bottom: 10px;' ><img src='<?php echo $this->base_url; ?>/leenkme-logo-32x32.png' style='vertical-align: top;' /> leenk.me General Settings</h2>
                    
                    <div id="api-key" class="postbox">
                    
                        <div class="handlediv" title="Click to toggle"><br /></div>
                        
                        <h3 class="hndle"><span><?php _e( 'leenk.me API Key' ); ?></span></h3>
                        
                        <div class="inside">
                        <p>
                        <?php _e( 'leenk.me API Key' ); ?>: <input id="api" type="text" name="leenkme_API" style="width: 25%;" value="<?php echo htmlspecialchars( stripcslashes( $user_settings[$this->leenkme_API] ) ); ?>" />
                        <input type="button" class="button" name="verify_leenkme_api" id="verify" value="<?php _e( 'Verify leenk.me API', 'leenkme' ) ?>" />
                        <?php wp_nonce_field( 'verify', 'leenkme_verify_wpnonce' ); ?>
                        </p>
                        
                        <?php if ( empty( $user_settings[$this->leenkme_API] ) ) { ?>
                        
                            <p>
                            <a href="<?php echo apply_filters( 'leenkme_url', 'http://leenk.me/' ); ?>">Click here to subscribe to leenk.me and generate an API key</a>
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
                        
                        <h3 class="hndle"><span><?php _e( 'leenk.me Social Networks Modules' ); ?></span></h3>
                        
                        <div class="inside">
                        
                        <table id="leenkme_leenkme_manage_all_settings">
                            <tr><td id="leenkme_plugin_name">Twitter: </td>
                            <td id="leenkme_plugin_button"><input type="checkbox" name="twitter" <?php checked( $leenkme_settings[$this->twitter] ); ?> /></td>
                            <td id="leenkme_plugin_settings"> <?php if ( $leenkme_settings[$this->twitter] ) { ?><a href="admin.php?page=leenkme_twitter">Twitter Settings</a><?php } ?></td></tr>
                            <tr><td id="leenkme_plugin_name">Facebook: </td>
                            <td id="leenkme_plugin_button"><input type="checkbox" name="facebook" <?php checked( $leenkme_settings[$this->facebook] ); ?> /></td>
                            <td id="leenkme_plugin_settings"> <?php if ( $leenkme_settings[$this->facebook] ) { ?><a href="admin.php?page=leenkme_facebook">Facebook Settings</a><?php } ?></td></tr>
                            
                            <tr><td id="leenkme_plugin_name">LinkedIn: </td>
                            <td id="leenkme_plugin_button"><input type="checkbox" name="linkedin" <?php checked( $leenkme_settings[$this->linkedin] ); ?> /></td>
                            <td id="leenkme_plugin_settings"> <?php if ( $leenkme_settings[$this->linkedin] ) { ?><a href="admin.php?page=leenkme_linkedin">LinkedIn Settings</a><?php } ?></td></tr>
                            <tr><td id="leenkme_plugin_name">FriendFeed: </td>
                            <td id="leenkme_plugin_button"><input type="checkbox" name="friendfeed" <?php checked( $leenkme_settings[$this->friendfeed] ); ?> /></td>
                            <td id="leenkme_plugin_settings"> <?php if ( $leenkme_settings[$this->friendfeed] ) { ?><a href="admin.php?page=leenkme_friendfeed">Friendfeed Settings</a><?php } ?></td></tr>
                        </table>
                                                  
                        <p class="submit">
                            <input class="button-primary" type="submit" name="update_leenkme_settings" value="<?php _e( 'Save Settings', 'leenkme' ) ?>" />
                        </p>
                        
                        </div>
                        
                    </div>
                    
                    <div id="post-types" class="postbox">
                    
                        <div class="handlediv" title="Click to toggle"><br /></div>
                        <h3 class="hndle"><span><?php _e( 'Post Types to Publish' ); ?></span></h3>
                        
                        <div class="inside">
                        
                        <table id="leenkme_leenkme_manage_all_settings">
                        
                        <tr>
                            <td id="leenkme_plugin_name">Post: </td>
                            <td id="post_type">
                                <input type="checkbox" value="post" name="post_types[]" checked="checked" readonly="readonly" disabled="disabled" />
                                <input type="hidden" value="post" name="post_types[]" />
                            </td>
                        </tr>
                        
                        <?php if ( version_compare( $this->wp_version, '2.9', '>' ) ) {
                            
                            $hidden_post_types = array( 'post', 'attachment', 'revision', 'nav_menu_item' );
                            
                            foreach ( get_post_types( array(), 'objects' ) as $post_type ) {
                                
                                if ( in_array( $post_type->name, $hidden_post_types ) ) 
                                    continue;
                                ?>
                                
                                <tr><td id="leenkme_plugin_name"><?php echo ucfirst( $post_type->name ); ?>: </td>
                                <td id="post_type"><input type="checkbox" value="<?php echo $post_type->name; ?>" name="post_types[]" <?php checked( in_array( $post_type->name, $leenkme_settings[$this->post_types] ) ); ?> /></td></tr>
                                
                                <?php } ?>
                        </table>
                        
                        <?php } else { ?>
                        
                        </table>
                        <p>To take advantage of publishing to Pages and Custom Post Types, please upgrade to the latest version of WordPress.</p>
                        
                        <?php } ?>  
                                                  
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
			update_option( $this->options_name, $leenkme_settings );
			
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
		
	}

}

// Instantiate the class
if ( class_exists( 'leenkme' ) ) {
	$dl_pluginleenkme = new leenkme();
	
	if ( $dl_pluginleenkme->plugin_enabled( 'twitter' ) ) {
		require_once( 'twitter.php' );
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'facebook' ) ) {
		require_once( 'facebook.php' );
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'linkedin' ) ) {
		require_once( 'linkedin.php' );
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'friendfeed' ) ) {
		require_once( 'friendfeed.php' );
	}
}

// Initialize the admin panel if the plugin has been activated
function leenkme_ap() {
	global $dl_pluginleenkme;
	
	if ( !isset( $dl_pluginleenkme ) ) {
		return;
	}
	
	add_menu_page( __( 'leenk.me Settings', 'leenkme' ), __( 'leenk.me', 'leenkme' ), 'leenkme_edit_user_settings', 'leenkme', array( &$dl_pluginleenkme, 'leenkme_settings_page' ), $dl_pluginleenkme->base_url . '/leenkme-logo-16x16.png' );
	
	if (substr($dl_pluginleenkme->wp_version, 0, 3) >= '2.9') {
		add_submenu_page( 'leenkme', __( 'leenk.me Settings', 'leenkme' ), __( 'leenk.me Settings', 'leenkme' ), 'leenkme_edit_user_settings', 'leenkme', array( &$dl_pluginleenkme, 'leenkme_settings_page' ) );
	}
	
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

// Example followed from http://codex.wordpress.org/AJAX_in_Plugins
function leenkme_js() {
	global $dl_pluginleenkme;
?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('input#api').live('click', function() {
			$('input#api').css('background-color', 'white');
		});
	
		$('input#verify').live('click', function() {
			var leenkme_API = $('input#api').val();
			var error = false;
			if (leenkme_API == "") {
				$('input#api').css('background-color', 'red');
				return false;
			}
		
			var data = {
				action: 	'verify',
				leenkme_API: leenkme_API,
				_wpnonce: 	$('input#leenkme_verify_wpnonce').val()
			};
			
			ajax_response(data);
		});
<?php 
	if ( $dl_pluginleenkme->plugin_enabled( 'twitter' ) ) {
		leenkme_twitter_js();
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'facebook' ) ) {
		leenkme_facebook_js();
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'linkedin' ) ) {
		leenkme_linkedin_js();
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'friendfeed' ) ) {
		leenkme_friendfeed_js();
	}
?>

		function ajax_response(data) {
			var style = "position: fixed; " +
						"display: none; " +
						"z-index: 1000; " +
						"top: 50%; " +
						"left: 50%; " +
						"background-color: #E8E8E8; " +
						"border: 1px solid #555; " +
						"padding: 15px; " +
						"width: 500px; " +
						"min-height: 80px; " +
						"margin-left: -250px; " + 
						"margin-top: -150px;" +
						"text-align: center;" +
						"vertical-align: middle;";
			$('body').append("<div id='results' style='" + style + "'></div>");
			$('#results').html("<p>Sending data to leenk.me</p>" +
									"<p><img src='/wp-includes/js/thickbox/loadingAnimation.gif' /></p>");
			$('#results').show();
			
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, data, function(response) {
				$('#results').html('<p>' + response + '</p>' +
										'<input type="button" class="button" name="results_ok_button" id="results_ok_button" value="OK" />');
				$('#results_ok_button').click(remove_results);
			});
		}
		
		function remove_results() {
			jQuery("#results_ok_button").unbind("click");
			jQuery("#results").remove();
			
			if (typeof document.body.style.maxHeight == "undefined") {//if IE 6
				jQuery("body","html").css({height: "auto", width: "auto"});
				jQuery("html").css("overflow","");
			}
			
			document.onkeydown = "";
			document.onkeyup = "";
			return false;
		}
	});
</script>

<?php
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
	
					$out[] = "<p>" . __( 'ERROR: Unknown error, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' ) . "</p>";
	
				}
	
			}
			
			die( join( $out ) );
		
		} else {

			die( __( 'ERROR: Unknown error, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' ) );

		}

	} else {

		die( __( 'Please fill in your API key.' ) );

	}

}

function leenkme_connect( $post ) {
	
	global $dl_pluginleenkme;
	$out = "";
	
	$connect_arr = apply_filters( 'leenkme_connect', array(), $post );

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
				
				$out[]=  "<p>" . $api_key . ": " . __( 'Undefined error occurred, for help please contact <a href="http://leenk.me/" target="_blank">leenk.me support</a>.' ) . "</p>";
				
			}
			
		}
		
		return $out;
		
	}

}

function leenkme_ajax_connect( $connect_arr ) {
	
	global $dl_pluginleenkme;
	$out = "";
	
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
				
				$out[] =  "<p>" . $api_key . ": " . __( 'Undefined error occurred, for help please contact <a href="http://leenk.me/" target="_blank">leenk.me support</a>.' ) . "</p>";
				
			}
			
		}
		
		return $out;
		
	} else {
		
			return __( 'Invalid leenk.me setup, for help please contact <a href="http://leenk.me/" target="_blank">leenk.me support</a>.' );
	
	}
	
}

function leenkme_help_list( $contextual_help, $screen ) {
	if ( 'leenkme' == $screen->parent_base ) {
		$contextual_help[$screen->id] = __( '<p>Need help working with the leenk.me plugin? Try these links for more information:</p>' .
'<a href="http://leenk.me/2010/09/04/how-to-use-the-leenk-me-twitter-plugin-for-wordpress/" target="_blank">Twitter</a> | ' .
'<a href="http://leenk.me/2010/09/04/how-to-use-the-leenk-me-facebook-plugin-for-wordpress/" target="_blank">Facebook</a> | ' .
'<a href="http://leenk.me/2010/12/01/how-to-use-the-leenk-me-linkedin-plugin-for-wordpress/" target="_blank">LinkedIn</a> | ' .
'<a href="http://leenk.me/2011/04/08/how-to-use-the-leenk-me-friendfeed-plugin-for-wordpress/" target="_blank">FriendFeed</a>' );
	}

	return $contextual_help;

}

// Actions and filters	
if ( isset( $dl_pluginleenkme ) ) {

	/*--------------------------------------------------------------------
	    Actions
	  --------------------------------------------------------------------*/

	// Add the admin menu
	add_action( 'admin_menu', 'leenkme_ap');
	
	// Whenever you publish a post, connect to leenk.me
	add_action( 'new_to_publish', 'leenkme_connect', 20 );
	add_action( 'draft_to_publish', 'leenkme_connect', 20 );
	add_action( 'pending_to_publish', 'leenkme_connect', 20 );
	add_action( 'future_to_publish', 'leenkme_connect', 20 );
	
	add_action( 'admin_footer', array( $dl_pluginleenkme, 'leenkme_add_wpnonce' ) );
	
	add_action( 'admin_head-toplevel_page_leenkme', 'leenkme_js' );
	add_action( 'admin_head-edit.php', 'leenkme_js' );
	add_action( 'admin_head-post.php', 'leenkme_js' );
	add_action( 'admin_head-page.php', 'leenkme_js' ); 			// used for WP2.9.x
	add_action( 'admin_head-edit-pages.php', 'leenkme_js' ); 	// used for WP2.9.x
	add_action( 'wp_ajax_verify', 'leenkme_ajax_verify', 10, 1 );
	add_action( 'wp_ajax_plugins', 'leenkme_ajax_plugins', 10, 1 );
	
	add_filter( 'contextual_help_list', 'leenkme_help_list', 10, 2);
	

} 

// From PHP_Compat-1.6.0a2 Compat/Function/str_ireplace.php for PHP4 Compatibility
if ( !function_exists( 'str_ireplace' ) ) {

    function str_ireplace( $search, $replace, $subject ) {
		// Sanity check
		if ( is_string( $search ) && is_array( $replace ) ) {
			user_error( 'Array to string conversion', E_USER_NOTICE );
			$replace = (string)$replace;
		}
	
		// If search isn't an array, make it one
		$search = (array)$search;
		$length_search = count( $search );
	
		// build the replace array
		$replace = is_array( $replace )
		? array_pad( $replace, $length_search, '' )
		: array_pad( array(), $length_search, $replace );
	
		// If subject is not an array, make it one
		$was_string = false;
		if ( is_string( $subject ) ) {
			$was_string = true;
			$subject = array( $subject );
		}
	
		// Prepare the search array
		foreach ( $search as $search_key => $search_value ) {
			$search[$search_key] = '/' . preg_quote( $search_value, '/' ) . '/i';
		}
		
		// Prepare the replace array (escape backreferences)
		$replace = str_replace( array( '\\', '$' ), array( '\\\\', '\$' ), $replace );
	
		$result = preg_replace( $search, $replace, $subject );
		return $was_string ? $result[0] : $result;
	}

}
// disabled() since 3.0, needed to maintain 2.8, and 2.9 backwards compatability
if ( !function_exists( 'disabled' ) ) {

	/**
	 * Outputs the html disabled attribute.
	 *
	 * Compares the first two arguments and if identical marks as disabled
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $disabled One of the values to compare
	 * @param mixed $current (true) The other value to compare if not just true
	 * @param bool $echo Whether to echo or just return the string
	 * @return string html attribute or empty string
	 */
	function disabled( $disabled, $current = true, $echo = true ) {
		return __checked_selected_helper( $disabled, $current, $echo, 'disabled' );
	}

}

// user_can() since 3.1, needed to maintain 2.8, 2.9, and 3.0 backwards compatability
if ( !function_exists( 'user_can' ) ) {

	/**
	 * Whether a particular user has capability or role.
	 *
	 * @since 3.1.0
	 *
	 * @param int|object $user User ID or object.
	 * @param string $capability Capability or role name.
	 * @return bool
	 */
	function user_can( $user, $capability ) {
		if ( ! is_object( $user ) )
			$user = new WP_User( $user );
	
		if ( ! $user || ! $user->ID )
			return false;
	
		$args = array_slice( func_get_args(), 2 );
		$args = array_merge( array( $capability ), $args );
	
		return call_user_func_array( array( &$user, 'has_cap' ), $args );
	}

}

// user_can() since 3.0, needed to maintain 2.8 and 2.9 backwards compatability
if ( !function_exists( 'clean_user_cache' ) ) {

	/**
	 * Clean all user caches
	 *
	 * @since 3.0.0
	 *
	 * @param int $id User ID
	 */
	function clean_user_cache($id) {
		$user = new WP_User($id);
	
		wp_cache_delete($id, 'users');
		wp_cache_delete($user->user_login, 'userlogins');
		wp_cache_delete($user->user_email, 'useremail');
		wp_cache_delete($user->user_nicename, 'userslugs');
		wp_cache_delete('blogs_of_user-' . $id, 'users');
	}

}

if ( !function_exists( 'wp_strip_all_tags' ) ) {
	/**
	 * Properly strip all HTML tags including script and style
	 *
	 * @since 2.9.0
	 *
	 * @param string $string String containing HTML tags
	 * @param bool $remove_breaks optional Whether to remove left over line breaks and white space chars
	 * @return string The processed string.
	 */
	function wp_strip_all_tags($string, $remove_breaks = false) {
		$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
		$string = strip_tags($string);
	
		if ( $remove_breaks )
			$string = preg_replace('/[\r\n\t ]+/', ' ', $string);
	
		return trim($string);
	}
}

if ( !function_exists( 'leenkme_trim_characters' ) ) {

	/**
	 * Clean all user caches
	 *
	 * @since 1.3.10
	 *
	 * @param int $id User ID
	 */
	function leenkme_trim_words( $string, $maxChar ) {
		
		$num_words = 55;
		$more = "...";
	
		$original_string = $string;
		$string = strip_shortcodes( $string );
		$string = wp_strip_all_tags( $string );
		$words_array = preg_split( "/[\n\r\t ]+/", $string, $num_words + 1, PREG_SPLIT_NO_EMPTY );
		$length = strlen( utf8_decode( $string ) );
		
		while ( $length > $maxChar ) {
		
			array_pop( $words_array );
			$string = implode( ' ', $words_array );
			$string = $string . $more;
			
			$length = strlen( utf8_decode( $string ) );
			
		}
		
		return $string;
		
	}

}