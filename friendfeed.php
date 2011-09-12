<?php

if ( ! class_exists( 'leenkme_FriendFeed' ) ) {

	// Define class
	class leenkme_FriendFeed {
		
		// Class members		
		var $options_name			= 'leenkme_friendfeed';
		var $friendfeed_myfeed		= 'friendfeed_myfeed';
		var $friendfeed_group		= 'friendfeed_group';
		var $default_image			= 'default_image';
		var $feed_cats				= 'feed_cats';
		var $clude					= 'clude';
		var $feed_all_users			= 'feed_all_users';
	
		// Constructor
		function leenkme_FriendFeed() {
			//Not Currently Needed
		}
		
		/*--------------------------------------------------------------------
			Administrative Functions
		  --------------------------------------------------------------------*/
		
		function get_leenkme_friendfeed_settings() {
			
			global $wpdb;
			
			$user_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ID) FROM ' . $wpdb->users ) );
			
			if ( 1 < $user_count )
				$feed_all_users = true;
			else
				$feed_all_users = false;
			
			$options = array( $this->feed_all_users => $feed_all_users );
		
			$leenkme_settings = get_option( $this->options_name );
			if ( !empty( $leenkme_settings ) ) {
				
				foreach ( $leenkme_settings as $key => $option ) {
					
					$options[$key] = $option;
					
				}
				
			}
			
			return $options;
			
		}
	  
		// Option loader function
		function get_user_settings( $user_id ) {
			
			// Default values for the options
			$friendfeed_myfeed		= true;
			$friendfeed_group		= false;
			$default_image			= '';
			$feed_cats				= array( '0' );
			$clude					= 'in';
			
			$options = array(
								 $this->friendfeed_myfeed 	=> $friendfeed_myfeed,
								 $this->friendfeed_group 	=> $friendfeed_group,
								 $this->default_image		=> $default_image,
								 $this->feed_cats 			=> $feed_cats,
								 $this->clude 				=> $clude
							);
							
			// Get values from the WP options table in the database, re-assign if found
			$user_settings = get_user_option( $this->options_name, $user_id );
			if ( !empty( $user_settings ) ) {
				
				foreach ( $user_settings as $key => $option ) {
					
					$options[$key] = $option;
					
				}
				
			}
			
			// Need this for initial INIT, for people who don't save the default settings...
			update_user_option( $user_id, $this->options_name, $user_settings );
			
			return $options;
			
		}
		
		// Print the admin page for the plugin
		function print_friendfeed_settings_page() {
			global $dl_pluginleenkme, $current_user;
			
			get_currentuserinfo();
			$user_id = $current_user->ID;
			
			// Get the user options
			$user_settings = $this->get_user_settings( $user_id );
			$friendfeed_settings = $this->get_leenkme_friendfeed_settings();
			
			if ( isset( $_POST['update_friendfeed_settings'] ) ) {
				
				if ( isset( $_POST['friendfeed_myfeed'] ) )
					$user_settings[$this->friendfeed_myfeed] = true;
				else
					$user_settings[$this->friendfeed_myfeed] = false;
				
				if ( isset( $_POST['friendfeed_group'] ) )
					$user_settings[$this->friendfeed_group] = true;
				else
					$user_settings[$this->friendfeed_group] = false;
				
				if ( isset( $_POST['friendfeed_body'] ) )
					$user_settings[$this->friendfeed_body] = $_POST['friendfeed_body'];
				
				if ( isset( $_POST['default_image'] ) )
					$user_settings[$this->default_image] = $_POST['default_image'];
	
				if ( isset( $_POST['clude'] ) && isset( $_POST['feed_cats'] ) ) {
					
					$user_settings[$this->clude] = $_POST['clude'];
					$user_settings[$this->feed_cats] = $_POST['feed_cats'];
					
				} else {
					
					$user_settings[$this->clude] = 'in';
					$user_settings[$this->feed_cats] = array( '0' );
					
				}
				
				update_user_option($user_id, $this->options_name, $user_settings);
				
				if ( current_user_can( 'leenkme_manage_all_settings' ) ) { //we're dealing with the main Admin options
				
					if ( isset( $_POST['feed_all_users'] ) )
						$friendfeed_settings[$this->feed_all_users] = true;
					else
						$friendfeed_settings[$this->feed_all_users] = false;
					
					update_option( $this->options_name, $friendfeed_settings );
					
				}
				
				// update settings notification ?>
				<div class="updated"><p><strong><?php _e( 'Settings Updated.', 'leenkme_FriendFeed' );?></strong></p></div>
				<?php
			}
			// Display HTML form for the options below
			?>
			<div class=wrap>
			<div style="width:70%;" class="postbox-container">
			<div class="metabox-holder">	
			<div class="meta-box-sortables ui-sortable">
				<form id="leenkme" method="post" action="">
					<h2 style='margin-bottom: 10px;' ><img src='<?php echo $dl_pluginleenkme->base_url; ?>/leenkme-logo-32x32.png' style='vertical-align: top;' /> FriendFeed Settings (<a href="http://leenk.me/2011/04/08/how-to-use-the-leenk-me-friendfeed-plugin-for-wordpress/" target="_blank">help</a>)</h2>
					
					<div id="post-types" class="postbox">
					
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span><?php _e( 'Social Settings' ); ?></span></h3>
						
						<div class="inside">
						<p>Feed to MyFeed? <input type="checkbox" id="friendfeed_myfeed" name="friendfeed_myfeed" <?php checked( $user_settings[$this->friendfeed_myfeed] ); ?> /></p>
						<p>Feed to Group? <input type="checkbox" id="friendfeed_group" name="friendfeed_group" <?php checked( $user_settings[$this->friendfeed_group] ); ?> /></p>

						<p>
                        	<input type="button" class="button" name="verify_friendfeed_connect" id="ff_publish" value="<?php _e( 'Feed a Test Message', 'leenkme_FriendFeed' ) ?>" />
							<?php wp_nonce_field( 'ff_publish', 'ff_publish_wpnonce' ); ?>
                        
                            <input class="button-primary" type="submit" name="update_friendfeed_settings" value="<?php _e( 'Save Settings', 'leenkme_FriendFeed' ) ?>" />
                        </p>
					
						</div>
					
					</div>
					
					<div id="post-types" class="postbox">
					
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span><?php _e( 'Message Settings' ); ?></span></h3>
						
						<div class="inside">
						<p>Default Image URL: <input name="default_image" type="text" style="width: 500px;" value="<?php _e(  $user_settings[$this->default_image], 'leenkme_FriendFeed' ) ?>" /></p>
                        
                        <p>
                            <input class="button-primary" type="submit" name="update_friendfeed_settings" value="<?php _e( 'Save Settings', 'leenkme_FriendFeed' ) ?>" />
                        </p>
                        
						</div>
					
					</div>
					
					<div id="post-types" class="postbox">
					
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span><?php _e( 'Feed Settings' ); ?></span></h3>
						
						<div class="inside">
						<p>Feed Categories: 
					
						<div class="feed-cats" style="margin-left: 50px;">
							<p>
							<input type='radio' name='clude' id='include_cat' value='in' <?php checked( 'in', $user_settings[$this->clude] ); ?> /><label for='include_cat'>Include</label> &nbsp; &nbsp; <input type='radio' name='clude' id='exclude_cat' value='ex' <?php checked( 'ex', $user_settings[$this->clude] ); ?> /><label for='exclude_cat'>Exclude</label> </p>
							<p>
							<select id='categories' name='feed_cats[]' multiple="multiple" size="5" style="height: 70px; width: 150px;">
							<option value="0" <?php selected( in_array( '0', (array)$user_settings[$this->feed_cats] ) ); ?>>All Categories</option>
                            
							<?php 
							$categories = get_categories( array( 'hide_empty' => 0, 'orderby' => 'name' ) );
							foreach ( (array)$categories as $category ) {
								?>
								
								<option value="<?php echo $category->term_id; ?>" <?php selected( in_array( $category->term_id, (array)$user_settings[$this->feed_cats] ) ); ?>><?php echo $category->name; ?></option>
			
			
								<?php
							}
							?>
                            
							</select></p>
							<p style="font-size: 11px; margin-bottom: 0px;">To 'deselect' hold the SHIFT key on your keyboard while you click the category.</p>
						</div>
						
						<?php if ( current_user_can('leenkme_manage_all_settings') ) { //then we're displaying the main Admin options ?>
                        
						<p>Feed All Authors? <input type="checkbox" name="feed_all_users" <?php checked( $friendfeed_settings[$this->feed_all_users] ); ?> /></p>
						<div class="publish-allusers" style="margin-left: 50px;">
						<p style="font-size: 11px; margin-bottom: 0px;">Check this box if you want leenk.me to feed to each available author account.</p>
						</div>
                        
						<?php } ?>
                        
                        <p>
                            <input class="button-primary" type="submit" name="update_friendfeed_settings" value="<?php _e( 'Save Settings', 'leenkme_FriendFeed' ) ?>" />
                        </p>
                        
						</div>
					
					</div>
				</form>
			</div>
            </div>
            </div>
            </div>
			<?php
			
		}
		
		function leenkme_friendfeed_meta_tags( $post_id ) {
			
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return;
				
			if ( isset( $_REQUEST['_inline_edit'] ) )
				return;
	
			if ( isset( $_POST["friendfeed_exclude_myfeed"] ) )
				update_post_meta( $post_id, 'friendfeed_exclude_myfeed', $_POST["friendfeed_exclude_myfeed"] );
			else
				delete_post_meta( $post_id, 'friendfeed_exclude_myfeed' );
	
			if ( isset( $_POST["friendfeed_exclude_group"] ) )
				update_post_meta( $post_id, 'friendfeed_exclude_group', $_POST["friendfeed_exclude_group"] );
			else
				delete_post_meta( $post_id, 'friendfeed_exclude_group' );
			
			if ( isset( $_POST['friendfeed_body'] ) && !empty( $_POST['friendfeed_body'] ) )
				update_post_meta( $post_id, 'friendfeed_body', $_POST['friendfeed_body'] );
			else
				delete_post_meta( $post_id, 'friendfeed_body' );
	
			if ( isset($_POST["friendfeed_image"] ) && !empty( $_POST["friendfeed_image"] ) )
				update_post_meta( $post_id, 'friendfeed_image', $_POST["friendfeed_image"] );
			else
				delete_post_meta( $post_id, 'friendfeed_image' );
			
		}
		
		function leenkme_add_friendfeed_meta_tag_options() { 
		
			global $dl_pluginleenkme;
			
			$leenkme_settings = $dl_pluginleenkme->get_leenkme_settings();
			foreach ( $leenkme_settings['post_types'] as $post_type ) {
				
				add_meta_box( 
					'leenkme-FriendFeed',
					__( 'leenk.me FriendFeed', 'leenkme' ),
					array( $this, 'leenkme_friendfeed_meta_box' ),
					$post_type 
				);
				
			}
			
		}
		
		function leenkme_friendfeed_meta_box() {
			
			global $post, $current_user, $dl_pluginleenkme;
			
			get_currentuserinfo();
			$user_id = $current_user->ID;
			
			$exclude_myfeed = get_post_meta( $post->ID, 'friendfeed_exclude_myfeed', true ); 
			$exclude_group = get_post_meta( $post->ID, 'friendfeed_exclude_group', true ); 
			$friendfeed_body = get_post_meta( $post->ID, 'friendfeed_body', true);
			$friendfeed_image = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, 'friendfeed_image', true ) ) );
			
			$user_settings = $this->get_user_settings( $user_id );
			$friendfeed_settings = $this->get_leenkme_friendfeed_settings(); ?>
		
			<input value="friendfeed_edit" type="hidden" name="friendfeed_edit" />
			<table>
				<tr><td scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;"><?php _e( 'Format Options:', 'leenkme' ) ?></td>
				  <td style="vertical-align:top; width:80px;">
					<p>%TITLE%, %WPSITENAME%, %WPTAGLINE%</p>
				</td></tr>
				  
				<tr><td scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-bottom:5px; padding-right:10px; vertical-align:top;"><?php _e( 'Custom Body:', 'leenkme' ) ?></td>
				  <td><textarea style="margin-top: 5px;" name="friendfeed_body" cols="66" rows="5"><?php echo $friendfeed_body; ?></textarea>
				</td></tr>
				
				<tr><td scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Image URL:', 'leenkme' ) ?></td>
				  <td><input value="<?php echo $friendfeed_image; ?>" type="text" name="friendfeed_image" size="80px" /></td></tr>
				  
				<tr><td scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;"></td>
				  <td style="vertical-align:top; width:80px;">
					<p>Paste the URL to the image or set the "Featured Image" if your theme supports it.</p>
				</td></tr>
				
				<?php if ( $user_settings['friendfeed_myfeed'] ) { ?>
                
				<tr><td scope="row" style="text-align:right; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Exclude from MyFeed:', 'leenkme' ) ?></td>
				  <td><input style="margin-top: 5px;" type="checkbox" name="friendfeed_exclude_myfeed" <?php checked( $exclude_myfeed || 'on' == $exclude_myfeed ); ?> />
				</td></tr>
                
				<?php } ?>
				
				<?php if ( $user_settings['friendfeed_group'] ) { ?>
                
				<tr><td scope="row" style="text-align:right; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Exclude from Group:', 'leenkme' ) ?></td>
				  <td><input style="margin-top: 5px;" type="checkbox" name="friendfeed_exclude_group" <?php checked( $exclude_group || 'on' == $exclude_group ); ?> />
				</td></tr>
                
				<?php } ?>
				
				<?php // Only show ReFeed button if the post is "published"
				if ( 'publish' === $post->post_status ) { ?>
                
				<tr><td colspan="2">
				<input style="float: right;" type="button" class="button" name="refeed_friendfeed" id="refeed_button" value="<?php _e( 'ReFeed', 'leenkme_FriendFeed' ) ?>" />
				</td></tr>
                
				<?php } ?>
                
			</table>
			<?php

		}

	}
	
}

if ( class_exists( 'leenkme_FriendFeed' ) ) {
	$dl_pluginleenkmeFriendFeed = new leenkme_FriendFeed();
}

// Example followed from http://codex.wordpress.org/AJAX_in_Plugins
function leenkme_friendfeed_js() {
?>
		$('input#ff_publish').live('click', function() {
			var friendfeed_myfeed = $('input#friendfeed_myfeed').attr('checked')
			var friendfeed_group = $('input#friendfeed_group').attr('checked')
			
			var data = {
				action:				'ff_publish',
				friendfeed_myfeed:	friendfeed_myfeed,
				friendfeed_group:	friendfeed_group,
				_wpnonce:			$('input#ff_publish_wpnonce').val()
			};
			
			ajax_response(data);
		});

		$('input#refeed_button').live('click', function() {
			var data = {
				action: 			'refeed',
				id:  				$('input#post_ID').val(),
				_wpnonce: 			$('input#leenkme_wpnonce').val()
			};
			
			ajax_response(data);
		});

		$('a.refeed_row_action').live('click', function() {
			var data = {
				action: 			'refeed',
				id:  				$(this).attr('id'),
				_wpnonce: 			$('input#leenkme_wpnonce').val()
			};
            
			ajax_response(data);
		});
<?php
}

function leenkme_ajax_ff() {

	check_ajax_referer( 'ff_publish' );
	global $current_user;
	get_currentuserinfo();
	$user_id = $current_user->ID;
	
	global $dl_pluginleenkme;
	$user_settings = $dl_pluginleenkme->get_user_settings( $user_id );

	if ( $api_key = $user_settings['leenkme_API'] ) {

		$body = "Testing leenk.me's FriendFeed Plugin for WordPress - A webapp that allows you to publicize your WordPress posts automatically.";
		$url = 'http://leenk.me/';
		$picture = 'http://leenk.me/leenkme.png';
		
		$connect_arr[$api_key]['friendfeed_body'] = $body;
		$connect_arr[$api_key]['friendfeed_link'] = $url;
		$connect_arr[$api_key]['friendfeed_picture'] = $picture;
						
		if ( isset( $_POST['friendfeed_myfeed'] ) 
				&& ( 'true' === $_POST['friendfeed_myfeed'] || 'checked' === $_POST['friendfeed_myfeed'] ) )
			$connect_arr[$api_key]['friendfeed_myfeed'] = true;
		
		if ( isset( $_POST['friendfeed_group'] ) 
				&& ( 'true' === $_POST['friendfeed_group'] || 'checked' === $_POST['friendfeed_group'] ) )
			$connect_arr[$api_key]['friendfeed_group'] = true;

		$result = leenkme_ajax_connect($connect_arr);
		
		if ( isset( $result[0] ) ) {
				
			if ( is_wp_error( $result[0] ) ) {
				
				die( $result[0]->get_error_message() );	
				
			} else if ( isset( $result[0]['response']['code'] ) ) {
				
				die( $result[0]['body'] );
				
			} else {
				
				die( __( 'ERROR: Unknown error, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' ) );
			
			}
			
		} else {
			
			die( __( 'ERROR: Unknown error, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' ) );

		}
		
	} else {
		
		die( __( 'ERROR: You have no entered your leenk.me API key. Please check your leenk.me settings.' ) );
	
	}

}

function leenkme_ajax_refeed() {

	check_ajax_referer( 'leenkme' );
	
	if ( isset( $_POST['id'] ) ) {

		if ( get_post_meta( $_POST['id'], 'friendfeed_exclude_myfeed', true ) 
				&& get_post_meta( $_POST['id'], 'friendfeed_exclude_group', true ) ) {

			die( 'You have excluded this post from feeding to your FriendFeed MyFeed and Group. If you would like to feed it, edit the post and remove the appropriate exclude check boxes.' );

		} else {

			$post = get_post( $_POST['id'] );
			
			$connection_array = leenkme_publish_to_friendfeed( array(), $post, true );
			$results = leenkme_ajax_connect( $connection_array );
		
			if ( isset( $results ) ) {		
				
				foreach( $results as $result ) {	
		
					if ( is_wp_error( $result ) ) {
		
						$out[] = "<p>" . $result->get_error_message() . "</p>";
		
					} else if ( isset( $result['response']['code'] ) ) {
		
						$out[] = "<p>" . $result['body'] . "</p>";
		
					} else {
		
						$out[] = "<p>" . __( 'Error received! Please check your <a href="admin.php?page=leenkme_friendfeed">Friendfeed settings</a> and try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' ) . "</p>";
		
					}
		
				}
				
				die( join( $out ) );
				
			} else {
				
				die( __( 'ERROR: Unknown error, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' ) );
	
			}
			
		}
		
	} else {
		
		die( 'ERROR: Unable to determine Post ID.' );
	
	}

}

function refeed_row_action( $actions, $post ) {
	global $dl_pluginleenkme;
	$leenkme_options = $dl_pluginleenkme->get_leenkme_settings();
	if ( in_array( $post->post_type, $leenkme_options['post_types'] ) ) {
		// Only show ReFeed button if the post is "published"
		if ( 'publish' === $post->post_status ) {
			$actions['refeed'] = '<a class="refeed_row_action" id="' . $post->ID . '" title="' . esc_attr( __( 'ReFeed this Post' ) ) . '" href="#">' . __( 'ReFeed' ) . '</a>';
		}
	}

	return $actions;
}
									
// Add function to pubslih to friendfeed
function leenkme_publish_to_friendfeed( $connect_arr = array(), $post, $debug = false ) {
	
	global $wpdb, $dl_pluginleenkme, $dl_pluginleenkmeFriendFeed;
	$maxBodyLen = 420;
	
	if ( get_post_meta( $post->ID, 'friendfeed_exclude_myfeed', true ) )		
		$exclude_myfeed = true;
	else
		$exclude_myfeed = false;
	
	if ( get_post_meta( $post->ID, 'friendfeed_exclude_group', true ) )
		$exclude_group = true;
	else
		$exclude_group = false;
	
	if ( !( $exclude_myfeed && $exclude_group ) ) {
		
		$leenkme_settings = $dl_pluginleenkme->get_leenkme_settings();
		$friendfeed_settings = $dl_pluginleenkmeFriendFeed->get_leenkme_friendfeed_settings();
		
		if ( in_array($post->post_type, $leenkme_settings['post_types'] ) ) {
			
			$options = get_option( 'leenkme_friendfeed' );
			
			if ( $options['feed_all_users'] ) {
				
				$user_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM '. $wpdb->users ) );
				
			} else {
				
				$user_ids[] = $post->post_author;
				
			}
			
			$url = get_permalink( $post->ID );
			$post_title = strip_tags( $post->post_title );
			$wp_sitename = strip_tags( get_bloginfo( 'name' ) );
			$wp_tagline = strip_tags( get_bloginfo( 'description' ) );
			
			foreach ( $user_ids as $user_id ) {

				$user_settings = $dl_pluginleenkme->get_user_settings( $user_id );

				if ( empty( $user_settings['leenkme_API'] ) ) {

					clean_user_cache( $user_id );
					continue;	//Skip user if they do not have an API key set
					
				}
				
				$api_key = $user_settings['leenkme_API'];
				
				$options = $dl_pluginleenkmeFriendFeed->get_user_settings( $user_id );
				
				if ( !empty( $options ) ) {
					
					if ( !empty( $options['feed_cats'] ) && isset( $options['clude'] )
							&& !( 'in' == $options['clude'] && in_array( '0', $options['feed_cats'] ) ) ) {
						
						if ( 'ex' == $options['clude'] && in_array( '0', $options['feed_cats'] ) ) {
							
							if ( $debug ) echo "<p>You have your <a href='admin.php?page=leenkme_friendfeed'>Leenk.me FriendFeed settings</a> set to Exclude All Categories.</p>";
							clean_user_cache( $user_id );
							continue;
							
						}
						
						$match = false;
						
						$post_categories = wp_get_post_categories( $post->ID );
						
						foreach ( $post_categories as $cat ) {
						
							if ( in_array( (int)$cat, $options['feed_cats'] ) ) {
							
								$match = true;
								
							}
							
						}
						
						if ( ( 'ex' == $options['clude'] && $match ) ) {
							
							if ( $debug ) echo "<p>Post in an excluded category, check your <a href='admin.php?page=leenkme_friendfeed'>Leenk.me FriendFeed settings</a> or remove the post from the excluded category.</p>";
							clean_user_cache( $user_id );
							continue;
							
						} else if ( ( 'in' == $options['clude'] && !$match ) ) {
							
							if ( $debug ) echo "<p>Post not found in an included category, check your <a href='admin.php?page=leenkme_friendfeed'>Leenk.me FriendFeed settings</a> or add the post into the included category.</p>";
							clean_user_cache( $user_id );
							continue;
							
						}
					}
						
					if ( !$options['friendfeed_myfeed'] && !$options['friendfeed_group']) {
						
						clean_user_cache( $user_id );
						continue;	//Skip this user if they don't have Profile or Page checked in plugins FriendFeed Settings
						
					}
	
					// Added friendfeed profile to connection array if enabled
					if ( $options['friendfeed_myfeed'] && !$exclude_myfeed ) {
						
						$connect_arr[$api_key]['friendfeed_myfeed'] = true;
						
					}
	
					// Added friendfeed page to connection array if enabled
					if ( $options['friendfeed_group'] && !$exclude_group ) {
						
						$connect_arr[$api_key]['friendfeed_group'] = true;
						
					}
					
					if ( !$body = get_post_meta( $post->ID, 'friendfeed_body', true ) ) {
						
						if ( !empty( $post->post_excerpt ) ) {
							
							//use the post_excerpt if available for the friendfeed description
							$body = strip_tags( strip_shortcodes( $post->post_excerpt ) ); 
							
						} else {
							
							//otherwise we'll pare down the description
							$body = strip_tags( strip_shortcodes( $post->post_content ) ); 
							
						}
						
					}
					
					$body = str_ireplace( '%TITLE%', $post_title, $body );
					$body = str_ireplace( '%WPSITENAME%', $wp_sitename, $body );
					$body = str_ireplace( '%WPTAGLINE%', $wp_tagline, $body );
					$bodyLen = strlen( utf8_decode( $body ) );
					
					if ( $bodyLen > $maxBodyLen ) {
						
						$diff = $maxBodyLen - $bodyLen;  // reversed because I need a negative number
						$body = substr( $body, 0, $diff ); // subtract 1 for 0 based array and 3 more for adding an ellipsis
						
					}
					
					if ( !( $picture = apply_filters( 'friendfeed_image', get_post_meta( $post->ID, 'friendfeed_image', true ), $post->ID ) ) ) {
						if ( function_exists('has_post_thumbnail') && has_post_thumbnail( $post->ID ) ) {
							
							$post_thumbnail_id = get_post_thumbnail_id( $post->ID );
							list( $picture, $width, $height ) = wp_get_attachment_image_src( $post_thumbnail_id );
							
						} else if ( $images = get_children( 'post_parent=' . $post->ID . '&post_type=attachment&post_mime_type=image&numberposts=1' ) ) {
							
							foreach ( $images as $attachment_id => $attachment ) {
								
								list( $picture, $width, $height ) = wp_get_attachment_image_src( $attachment_id );
								break;
								
							}
							
						} else if ( !empty( $options['default_image'] ) ) {
							
							$picture = $options['default_image'];
							
						}
					}	
													
					if ( isset( $picture ) && !empty( $picture ) ) {
						
						$connect_arr[$api_key]['friendfeed_picture'] = $picture;
						
					}
					
					$connect_arr[$api_key]['friendfeed_link'] = $url;
					$connect_arr[$api_key]['friendfeed_body'] = $body;
					
				}
				
				clean_user_cache( $user_id );
				
			}
			
		}
		
		$wpdb->flush();
		
	}
		
	return $connect_arr;
	
}

// Actions and filters	
if ( isset( $dl_pluginleenkmeFriendFeed ) ) {
	add_action( 'admin_init', array( $dl_pluginleenkmeFriendFeed, 'leenkme_add_friendfeed_meta_tag_options' ), 1 );
	add_action( 'save_post', array( $dl_pluginleenkmeFriendFeed, 'leenkme_friendfeed_meta_tags' ) );
	
	// Whenever you publish a post, post to friendfeed
	add_filter('leenkme_connect', 'leenkme_publish_to_friendfeed', 20, 2);
		  
	// Add jQuery & AJAX for leenk.me Test
	add_action( 'admin_head-leenk-me_page_leenkme_friendfeed', 'leenkme_js' );
	
	add_action( 'wp_ajax_ff_publish', 'leenkme_ajax_ff' );
	add_action( 'wp_ajax_refeed', 'leenkme_ajax_refeed' );
	
	// edit-post.php post row update
	add_filter( 'post_row_actions', 'refeed_row_action', 10, 2 );
	add_filter( 'page_row_actions', 'refeed_row_action', 10, 2 );
}