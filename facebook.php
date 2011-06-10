<?php

if ( ! class_exists( 'leenkme_Facebook' ) ) {
	
	// Define class
	class leenkme_Facebook {
		
		// Class members		
		var $options_name				= 'leenkme_facebook';
		var $facebook_profile			= 'facebook_profile';
		var $facebook_page				= 'facebook_page';
		var $facebook_group				= 'facebook_group';
		var $facebook_message			= 'facebook_message';
		var $facebook_linkname			= 'facebook_linkname';
		var $facebook_caption			= 'facebook_caption';
		var $default_image				= 'default_image';
		var $publish_cats				= 'publish_cats';
		var $clude						= 'clude';
		var $publish_all_users			= 'publish_all_users';
	
		// Constructor
		function leenkme_Facebook() {
			//Not Currently Needed
		}
		
		/*--------------------------------------------------------------------
			Administrative Functions
		  --------------------------------------------------------------------*/
		
		function get_leenkme_facebook_settings() {
			global $wpdb;
			
			$user_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ID) FROM ' . $wpdb->users ) );
			
			if ( 1 < $user_count )
				$publish_all_users = true;
			else
				$publish_all_users = false;
			
			$options = array( $this->publish_all_users => $publish_all_users );
		
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
			$facebook_profile		= true;
			$facebook_page			= false;
			$facebook_group			= false;
			$facebook_message		= '%TITLE%';
			$facebook_linkname		= '%WPSITENAME%';
			$facebook_caption 		= '%WPTAGLINE%';
			$default_image			= '';
			$publish_cats			= array( '0' );
			$clude					= 'in';
			
			$options = array(
								 $this->facebook_profile 		=> $facebook_profile,
								 $this->facebook_page 			=> $facebook_page,
								 $this->facebook_group 			=> $facebook_group,
								 $this->facebook_message		=> $facebook_message,
								 $this->facebook_linkname		=> $facebook_linkname,
								 $this->facebook_caption 		=> $facebook_caption,
								 $this->default_image 			=> $default_image,
								 $this->publish_cats 			=> $publish_cats,
								 $this->clude	 				=> $clude
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
		function print_facebook_settings_page() {
			global $dl_pluginleenkme, $current_user;
			
			get_currentuserinfo();
			$user_id = $current_user->ID;
			
			// Get the user options
			$user_settings = $this->get_user_settings( $user_id );
			$facebook_settings = $this->get_leenkme_facebook_settings();
			
			if ( isset( $_POST['update_facebook_settings'] ) ) {
				
				if ( isset( $_POST['facebook_profile'] ) )
					$user_settings[$this->facebook_profile] = true;
				else
					$user_settings[$this->facebook_profile] = false;
				
				if ( isset( $_POST['facebook_page'] ) )
					$user_settings[$this->facebook_page] = true;
				else
					$user_settings[$this->facebook_page] = false;
				
				if ( isset( $_POST['facebook_group'] ) )
					$user_settings[$this->facebook_group] = true;
				else
					$user_settings[$this->facebook_group] = false;
				
				if ( isset( $_POST['facebook_message'] ) )
					$user_settings[$this->facebook_message] = $_POST['facebook_message'];
	
				if ( isset( $_POST['facebook_linkname'] ) )
					$user_settings[$this->facebook_linkname] = $_POST['facebook_linkname'];
				
				if ( isset( $_POST['facebook_caption'] ) )
					$user_settings[$this->facebook_caption] = $_POST['facebook_caption'];
				
				if ( isset( $_POST['default_image'] ) )
					$user_settings[$this->default_image] = $_POST['default_image'];
	
				if ( isset( $_POST['clude'] ) && isset( $_POST['publish_cats'] ) ) {
					
					$user_settings[$this->clude] = $_POST['clude'];
					$user_settings[$this->publish_cats] = $_POST['publish_cats'];
					
				} else {
					
					$user_settings[$this->clude] = 'in';
					$user_settings[$this->publish_cats] = array( '0' );
					
				}
				
				update_user_option($user_id, $this->options_name, $user_settings);
				
				if ( current_user_can( 'leenkme_manage_all_settings' ) ) { //we're dealing with the main Admin options
				
					if ( isset( $_POST['publish_all_users'] ) )
						$facebook_settings[$this->publish_all_users] = true;
					else
						$facebook_settings[$this->publish_all_users] = false;
					
					update_option( $this->options_name, $facebook_settings );
					
				}
				
				// update settings notification ?>
				<div class="updated"><p><strong><?php _e( 'Settings Updated.', 'leenkme_Facebook' );?></strong></p></div>
				<?php
				
			}
			// Display HTML form for the options below
			?>
			<div class=wrap>
			<div style="width:70%;" class="postbox-container">
			<div class="metabox-holder">	
			<div class="meta-box-sortables ui-sortable">
				<form id="leenkme" method="post" action="">
					<h2 style='margin-bottom: 10px;' ><img src='<?php echo $dl_pluginleenkme->base_url; ?>/leenkme-logo-32x32.png' style='vertical-align: top;' /> Facebook Settings (<a href="http://leenk.me/2010/09/04/how-to-use-the-leenk-me-facebook-plugin-for-wordpress/" target="_blank">help</a>)</h2>
					<div id="post-types" class="postbox">
					
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span><?php _e( 'Social Settings' ); ?></span></h3>
						
						<div class="inside">
						
							<p>Publish to Personal Profile? <input type="checkbox" id="facebook_profile" name="facebook_profile" <?php checked( $user_settings[$this->facebook_profile] ); ?> /></p>
							<p>Publish to Fan Page? <input type="checkbox" id="facebook_page" name="facebook_page" <?php checked( $user_settings[$this->facebook_page] ); ?> /></p>
							<p>Publish to Group? <input type="checkbox" id="facebook_group" name="facebook_group" <?php checked( $user_settings[$this->facebook_group] ); ?> /></p>
						
							<p>
								<input type="button" class="button" name="verify_facebook_connect" id="fb_publish" value="<?php _e( 'Publish a Test Message', 'leenkme_Facebook' ) ?>" />
								<?php wp_nonce_field( 'fb_publish', 'fb_publish_wpnonce' ); ?>
                            
								<input class="button-primary" type="submit" name="update_facebook_settings" value="<?php _e( 'Save Settings', 'leenkme_Facebook' ) ?>" />
							</p>
							
						</div>
					
					</div>
					
					<div id="post-types" class="postbox">
					
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span><?php _e( 'Message Settings' ); ?></span></h3>
						
						<div class="inside">
							<p>Default Message: <input name="facebook_message" type="text" style="width: 500px;" value="<?php _e( $user_settings[$this->facebook_message], 'leenkme_Facebook' ) ?>" /></p>
							<p>Default Link Name: <input name="facebook_linkname" type="text" style="width: 500px;" value="<?php _e( $user_settings[$this->facebook_linkname], 'leenkme_Facebook' ) ?>" /></p>
							<p>Default Caption: <input name="facebook_caption" type="text" style="width: 500px;" value="<?php _e( $user_settings[$this->facebook_caption], 'leenkme_Facebook' ) ?>" /></p>
							<div class="facebook-format" style="margin-left: 50px;">
								<p style="font-size: 11px; margin-bottom: 0px;">Format Options:</p>
								<ul style="font-size: 11px;">
									<li>%TITLE% - Displays the post title.</li>
									<li>%WPSITENAME% - Displays the WordPress site name (found in Settings -> General).</li>
									<li>%WPTAGLINE% - Displays the WordPress TagLine (found in Settings -> General).</li>
								</ul>
							</div>
							<p>Default Image URL: <input name="default_image" type="text" style="width: 500px;" value="<?php _e(  $user_settings[$this->default_image], 'leenkme_Facebook' ) ?>" /></p>                    
							<div class="publish-cats" style="margin-left: 50px;">
								<p style="font-size: 11px; margin-bottom: 0px;"><strong>NOTE</strong> Do not use an image URL hosted by Facebook. Facebook will reject your message.</p>
							</div>
							
							<p>
								<input class="button-primary" type="submit" name="update_facebook_settings" value="<?php _e( 'Save Settings', 'leenkme_Facebook' ) ?>" />
							</p>
							
						</div>
					</div>
					
					<div id="post-types" class="postbox">
					
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span><?php _e( 'Publish Settings' ); ?></span></h3>
						
						<div class="inside">
							<p>Publish Categories:</p>
						
							<div class="tweet-cats" style="margin-left: 50px;">
								<p>
								<input type='radio' name='clude' id='include_cat' value='in' <?php checked( 'in', $user_settings[$this->clude] ); ?> /><label for='include_cat'>Include</label> &nbsp; &nbsp; <input type='radio' name='clude' id='exclude_cat' value='ex' <?php checked( 'ex', $user_settings[$this->clude] ); ?> /><label for='exclude_cat'>Exclude</label> </p>
								<p>
								<select id='categories' name='publish_cats[]' multiple="multiple" size="5" style="height: 70px; width: 150px;">
								<option value="0" <?php selected( in_array( "0", (array)$user_settings[$this->publish_cats] ) ); ?>>All Categories</option>
								<?php 
								$categories = get_categories( array( 'hide_empty' => 0, 'orderby' => 'name' ) );
								foreach ( (array)$categories as $category ) {
									?>
									
									<option value="<?php echo $category->term_id; ?>" <?php selected( in_array( $category->term_id, (array)$user_settings[$this->publish_cats] ) ); ?>><?php echo $category->name; ?></option>
				
				
									<?php
								}
								?>
								</select></p>
								<p style="font-size: 11px; margin-bottom: 0px;">To 'deselect' hold the SHIFT key on your keyboard while you click the category.</p>
							</div>
							
							<?php if ( current_user_can('leenkme_manage_all_settings') ) { //then we're displaying the main Admin options ?>
							<p>Publish All Authors? <input type="checkbox" name="publish_all_users" <?php checked( $facebook_settings[$this->publish_all_users] ); ?> /></p>
							<div class="publish-allusers" style="margin-left: 50px;">
							<p style="font-size: 11px; margin-bottom: 0px;">Check this box if you want leenk.me to publish to each available author account.</p>
							</div>
							<?php } ?>
							
							<p>
								<input class="button-primary" type="submit" name="update_facebook_settings" value="<?php _e( 'Save Settings', 'leenkme_Facebook' ) ?>" />
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
		
		function leenkme_facebook_meta_tags( $post_id ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return;
				
			if ( isset( $_REQUEST['_inline_edit'] ) )
				return;
	
			if ( isset( $_POST["facebook_exclude_profile"] ) ) {
				update_post_meta( $post_id, 'facebook_exclude_profile', $_POST["facebook_exclude_profile"] );
			} else {
				delete_post_meta( $post_id, 'facebook_exclude_profile' );
			}
	
			if ( isset( $_POST["facebook_exclude_page"] ) ) {
				update_post_meta( $post_id, 'facebook_exclude_page', $_POST["facebook_exclude_page"] );
			} else {
				delete_post_meta( $post_id, 'facebook_exclude_page' );
			}
	
			if ( isset( $_POST["facebook_exclude_group"] ) ) {
				update_post_meta( $post_id, 'facebook_exclude_group', $_POST["facebook_exclude_group"] );
			} else {
				delete_post_meta( $post_id, 'facebook_exclude_group' );
			}
			
			if ( isset( $_POST['facebook_message'] ) && !empty( $_POST['facebook_message'] ) ) {
				update_post_meta( $post_id, 'facebook_message', $_POST['facebook_message'] );
			} else {
				delete_post_meta( $post_id, 'facebook_message' );
			}
			
			if ( isset( $_POST['facebook_linkname'] ) && !empty( $_POST['facebook_linkname'] ) ) {
				update_post_meta( $post_id, 'facebook_linkname', $_POST['facebook_linkname'] );
			} else {
				delete_post_meta( $post_id, 'facebook_linkname' );
			}
			
			if ( isset( $_POST['facebook_caption'] ) && !empty( $_POST['facebook_caption'] ) ) {
				update_post_meta( $post_id, 'facebook_caption', $_POST['facebook_caption'] );
			} else {
				delete_post_meta( $post_id, 'facebook_caption' );
			}
			
			if ( isset( $_POST['facebook_description'] ) && !empty( $_POST['facebook_description'] ) ) {
				update_post_meta( $post_id, 'facebook_description', $_POST['facebook_description'] );
			} else {
				delete_post_meta( $post_id, 'facebook_description' );
			}
	
			if ( isset($_POST["facebook_image"] ) && !empty( $_POST["facebook_image"] ) ) {
				update_post_meta( $post_id, 'facebook_image', $_POST["facebook_image"] );
			} else {
				delete_post_meta( $post_id, 'facebook_image' );
			}
		}
		
		function leenkme_add_facebook_meta_tag_options() {
			global $dl_pluginleenkme;
			
			$leenkme_settings = $dl_pluginleenkme->get_leenkme_settings();
			foreach ( $leenkme_settings['post_types'] as $post_type ) {
				
				add_meta_box( 
					'leenkme-Facebook',
					__( 'leenk.me Facebook', 'leenkme' ),
					array( $this, 'leenkme_facebook_meta_box' ),
					$post_type 
				);
				
			}
			
		}
		
		function leenkme_facebook_meta_box() {
			global $post, $current_user;
			
			get_currentuserinfo();
			$user_id = $current_user->ID;
			
			$exclude_profile = get_post_meta( $post->ID, 'facebook_exclude_profile', true ); 
			$exclude_page = get_post_meta( $post->ID, 'facebook_exclude_page', true ); 
			$exclude_group = get_post_meta( $post->ID, 'facebook_exclude_group', true ); 
			$facebook_message = get_post_meta( $post->ID, 'facebook_message', true);
			$facebook_linkname = get_post_meta( $post->ID, 'facebook_linkname', true);
			$facebook_caption = get_post_meta( $post->ID, 'facebook_caption', true);
			$facebook_description = get_post_meta( $post->ID, 'facebook_description', true);
			$facebook_image = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, 'facebook_image', true ) ) );
			$user_settings = $this->get_user_settings( $user_id );
			$facebook_settings = $this->get_leenkme_facebook_settings(); ?>
		
			<input value="facebook_edit" type="hidden" name="facebook_edit" />
			<table>
				<tr><td scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;"><?php _e( 'Format Options:', 'leenkme' ) ?></td>
				  <td style="vertical-align:top; width:80px;">
					<p>%TITLE%, %WPSITENAME%, %WPTAGLINE%</p>
				</td></tr>
				<tr><td scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Custom Message:', 'leenkme' ) ?></td>
				  <td><input value="<?php echo $facebook_message; ?>" type="text" name="facebook_message" size="80px"/></td></tr>
				
				<tr><td scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Custom Link Name:', 'leenkme' ) ?></td>
				  <td><input value="<?php echo $facebook_linkname; ?>" type="text" name="facebook_linkname" size="80px" /></td></tr>
				<tr><td scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Custom Caption:', 'leenkme' ) ?></td>
				  <td><input value="<?php echo $facebook_caption; ?>" type="text" name="facebook_caption" size="80px" /></td></tr>
				<tr><td scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-bottom:5px; padding-right:10px; vertical-align:top;"><?php _e( 'Custom Description:', 'leenkme' ) ?></td>
				  <td><textarea style="margin-top: 5px;" name="facebook_description" cols="66" rows="5"><?php echo $facebook_description; ?></textarea>
				</td></tr>
				<tr><td scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Image URL:', 'leenkme' ) ?></td>
				  <td><input value="<?php echo $facebook_image; ?>" type="text" name="facebook_image" size="80px" /></td></tr>
				<tr><td scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;"></td>
				  <td style="vertical-align:top; width:80px;">
					<p>Paste the URL to the image or set the "Featured Image" if your theme supports it.</p>
				</td></tr>
				<?php if ( $user_settings['facebook_profile'] ) { ?>
				<tr><td scope="row" style="text-align:right; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Exclude from Profile:', 'leenkme' ) ?></td>
				  <td><input style="margin-top: 5px;" type="checkbox" name="facebook_exclude_profile" <?php checked( $exclude_profile || "on" == $exclude_profile ); ?> />
				</td></tr>
				<?php } ?>
				<?php if ( $user_settings['facebook_page'] ) { ?>
				<tr><td scope="row" style="text-align:right; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Exclude from Page:', 'leenkme' ) ?></td>
				  <td><input style="margin-top: 5px;" type="checkbox" name="facebook_exclude_page" <?php checked( $exclude_page || "on" == $exclude_page ); ?> />
				</td></tr>
				<?php } ?>
				<?php if ( $user_settings['facebook_group'] ) { ?>
				<tr><td scope="row" style="text-align:right; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Exclude from Group:', 'leenkme' ) ?></td>
				  <td><input style="margin-top: 5px;" type="checkbox" name="facebook_exclude_group" <?php checked( $exclude_group || "on" == $exclude_group ); ?> />
				</td></tr>
				<?php } ?>
				<?php // Only show RePublish button if the post is "published"
				if ( 'publish' === $post->post_status ) { ?>
				<tr><td colspan="2">
				<input style="float: right;" type="button" class="button" name="republish_facebook" id="republish_button" value="<?php _e( 'RePublish', 'leenkme_Facebook' ) ?>" />
				</td></tr>
				<?php } ?>
			</table>
		<?php 

		}

	}

}

if ( class_exists( 'leenkme_Facebook' ) ) {
	
	$dl_pluginleenkmeFacebook = new leenkme_Facebook();
	
}

// Example followed from http://codex.wordpress.org/AJAX_in_Plugins
function leenkme_facebook_js() {
?>

		$('input#fb_publish').live('click', function() {
			var facebook_profile = $('input#facebook_profile').attr('checked')
			var facebook_page = $('input#facebook_page').attr('checked')
			var facebook_group = $('input#facebook_group').attr('checked')
			
			var data = {
				action:				'fb_publish',
				facebook_profile:	facebook_profile,
				facebook_page:		facebook_page,
				facebook_group:		facebook_group,
				_wpnonce:			$('input#fb_publish_wpnonce').val()
			};
			
			ajax_response(data);
		});

		$('input#republish_button').live('click', function() {
			var data = {
				action: 			'republish',
				id:  				$('input#post_ID').val(),
				_wpnonce: 			$('input#leenkme_wpnonce').val()
			};
			
			ajax_response(data);
		});

		$('a.republish_row_action').live('click', function() {
			var data = {
				action: 			'republish',
				id:  				$(this).attr('id'),
				_wpnonce: 			$('input#leenkme_wpnonce').val()
			};
            
			ajax_response(data);
		});
<?php
}

function leenkme_ajax_fb() {
	
	check_ajax_referer( 'fb_publish' );
	
	global $current_user;
	get_currentuserinfo();
	$user_id = $current_user->ID;
	
	global $dl_pluginleenkme;
	$user_settings = $dl_pluginleenkme->get_user_settings( $user_id );
	
	if ( $api_key = $user_settings['leenkme_API'] ) {
		
		$message = "Testing leenk.me's Facebook Plugin for WordPress";
		$url = 'http://leenk.me/';
		$picture = 'http://leenk.me/leenkme.png';
		$description = 'leenk.me is a webapp that allows you to publish to popular social networking sites whenever you publish a new post from your WordPress website.';
		
		$connect_arr[$api_key]['facebook_message'] = $message;
		$connect_arr[$api_key]['facebook_link'] = $url;
		$connect_arr[$api_key]['facebook_picture'] = $picture;
		$connect_arr[$api_key]['facebook_description'] = $description;
						
		if ( isset( $_POST['facebook_profile'] ) && 'true' === $_POST['facebook_profile'] )
			$connect_arr[$api_key]['facebook_profile'] = true;
		
		if ( isset( $_POST['facebook_page'] ) && 'true' === $_POST['facebook_page'] )
			$connect_arr[$api_key]['facebook_page'] = true;
		
		if ( isset( $_POST['facebook_group'] ) && 'true' === $_POST['facebook_group'] )
			$connect_arr[$api_key]['facebook_group'] = true;
		
		$result = leenkme_ajax_connect($connect_arr);
		
		if ( isset( $result ) ) {
		
			if ( is_wp_error( $result ) ) {
				
				die( $result->get_error_message() );	
				
			} else if ( isset( $result['response']['code'] ) ) {
				
				die( $result['body'] );
				
			} else {
				
				die( 'ERROR: Unknown error, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' );
				
			}
			
		} else {
			
			die( 'ERROR: Unknown error, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' );
			
		}
		
	} else {
		
		die( 'ERROR: You have no entered your leenk.me API key. Please check your leenk.me settings.' );
		
	}
	
}

function leenkme_ajax_republish() {
	
	check_ajax_referer( 'leenkme' );
	
	if ( isset( $_POST['id'] ) ) {
		
		if ( get_post_meta( $_POST['id'], 'facebook_exclude_profile', true ) 
				&& get_post_meta( $_POST['id'], 'facebook_exclude_page', true )
				&& get_post_meta( $_POST['id'], 'facebook_exclude_group', true ) ) {
					
			die( 'You have excluded this post from publishing to your Facebook profile, Fan Page, and Group. If you would like to publish it, edit the post and remove the appropriate exclude check boxes.' );
			
		} else {
			
			$post = get_post( $_POST['id'] );
			
			$result = leenkme_ajax_connect( leenkme_publish_to_facebook( array(), $post, true ) );
			
			if ( isset( $result ) ) {		
				
				if ( is_wp_error( $result ) ) {
					
					die( $result->get_error_message() );	
					
				} else if ( isset( $result["response"]["code"] ) ) {
					
					die( $result["body"] );
					
				} else {
					
					die( 'ERROR: Received unknown result, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' );
				}
				
			} else {
				
				die( 'ERROR: Unknown error, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' );
			}
			
		}
		
	} else {
		
		die( 'ERROR: Unable to determine Post ID.' );
		
	}
}

function republish_row_action( $actions, $post ) {
	
	global $dl_pluginleenkme;
	
	$leenkme_options = $dl_pluginleenkme->get_leenkme_settings();
	
	if ( in_array( $post->post_type, $leenkme_options['post_types'] ) ) {
		
		// Only show RePublish button if the post is "published"
		if ( 'publish' === $post->post_status )
			$actions['republish'] = '<a class="republish_row_action" id="' . $post->ID . '" title="' . esc_attr( __( 'RePublish this Post' ) ) . '" href="#">' . __( 'RePublish' ) . '</a>';
		
	}
	

	return $actions;
	
}
									
// Add function to pubslih to facebook
function leenkme_publish_to_facebook( $connect_arr = array(), $post, $debug = false ) {
	
	global $wpdb, $dl_pluginleenkme, $dl_pluginleenkmeFacebook;
	
	$maxMessageLen = 420;
	$maxDescLen = 300;
	
	if ( get_post_meta( $post->ID, 'facebook_exclude_profile', true ) )
		$exclude_profile = true;
	else
		$exclude_profile = false;
	
	if ( get_post_meta( $post->ID, 'facebook_exclude_page', true ) )
		$exclude_page = true;
	else
		$exclude_page = false;
	
	if ( get_post_meta( $post->ID, 'facebook_exclude_group', true ) )
		$exclude_group = true;
	else
		$exclude_group = false;
	
	if ( !$exclude_profile && !$exclude_page && !$exclude_group ) {
		
		$leenkme_settings = $dl_pluginleenkme->get_leenkme_settings();
		$facebook_settings = $dl_pluginleenkmeFacebook->get_leenkme_facebook_settings();
		
		if ( in_array($post->post_type, $leenkme_settings['post_types'] ) ) {
			
			$options = get_option( 'leenkme_facebook' );
			
			if ( $options['publish_all_users'] )
				$user_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM '. $wpdb->users ) );
			else
				$user_ids[] = $post->post_author;
			
			$url = get_permalink( $post->ID );
			$post_title = strip_tags( $post->post_title );
			$wp_sitename = strip_tags( get_bloginfo( 'name' ) );
			$wp_tagline = strip_tags( get_bloginfo( 'description' ) );
			
			foreach ( $user_ids as $user_id ) {
				
				$user_settings = $dl_pluginleenkme->get_user_settings( $user_id );
				
				if ( empty( $user_settings['leenkme_API'] ) )
					continue;	//Skip user if they do not have an API key set
				
				$api_key = $user_settings['leenkme_API'];
				
				$options = $dl_pluginleenkmeFacebook->get_user_settings( $user_id );
				if ( !empty( $options ) ) {
					
					if ( !empty( $options['publish_cats'] ) && isset( $options['clude'] )
							&& !( 'in' == $options['clude'] && in_array( '0', $options['publish_cats'] ) ) ) {
						
						if ( 'ex' == $options['clude'] && in_array( '0', $options['publish_cats'] ) ) {
							
							if ( $debug ) echo "<p>You have your <a href='admin.php?page=leenkme_facebook'>Leenk.me Facebook settings</a> set to Exclude All Categories.</p>";
							continue;
							
						}
						
						$match = false;
						
						$post_categories = wp_get_post_categories( $post->ID );
						
						foreach ( $post_categories as $cat ) {
						
							if ( in_array( (int)$cat, $options['publish_cats'] ) ) {
							
								$match = true;
								
							}
							
						}
						
						if ( ( 'ex' == $options['clude'] && $match ) ) {
							
							if ( $debug ) echo "<p>Post in an excluded category, check your <a href='admin.php?page=leenkme_facebook'>Leenk.me Facebook settings</a> or remove the post from the excluded category.</p>";
							continue;
							
						} else if ( ( 'in' == $options['clude'] && !$match ) ) {
							
							if ( $debug ) echo "<p>Post not found in an included category, check your <a href='admin.php?page=leenkme_facebook'>Leenk.me Facebook settings</a> or add the post into the included category.</p>";
							continue;
							
						}
						
					}
						
					if ( !$options['facebook_profile'] && !$options['facebook_page']  && !$options['facebook_group'])
						continue;	//Skip this user if they don't have Profile or Page checked in plugins Facebook Settings
	
					// Added facebook profile to connection array if enabled
					if ( $options['facebook_profile'] )
						$connect_arr[$api_key]['facebook_profile'] = true;
	
					// Added facebook page to connection array if enabled
					if ( $options['facebook_page'] )
						$connect_arr[$api_key]['facebook_page'] = true;
	
					// Added facebook page to connection array if enabled
					if ( $options['facebook_group'] )
						$connect_arr[$api_key]['facebook_group'] = true;

					// Get META facebook message
					$message = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, 'facebook_message', true ) ) );
					
					// If META facebook message is not set, use the default facebook message format set in options page(s)
					if ( !isset( $message ) || empty( $message ) ) {
						
						$message = htmlspecialchars( stripcslashes( $options['facebook_message'] ) );
						
					}
					
					$message = str_ireplace( '%TITLE%', $post_title, $message );
					$message = str_ireplace( '%WPSITENAME%', $wp_sitename, $message );
					$message = str_ireplace( '%WPTAGLINE%', $wp_tagline, $message );
					$messageLen = strlen( utf8_decode( $message ) );
					
					if ( $messageLen > $maxMessageLen ) {
						
						$diff = $maxMessageLen - $messageLen;  // reversed because I need a negative number
						$message = substr( $message, 0, $diff - 4 ) . "..."; // subtract 1 for 0 based array and 3 more for adding an ellipsis
						
					}
		
					// Get META facebook link name
					$linkname = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, 'facebook_linkname', true ) ) );
					
					// If META facebook link name is not set, use the default facebook message format set in options page(s)
					if ( !isset( $linkname ) || empty( $linkname ) ) {
						
						$linkname = htmlspecialchars( stripcslashes( $options['facebook_linkname'] ) );
						
					}
					
					$linkname = str_ireplace( '%TITLE%', $post_title, $linkname );
					$linkname = str_ireplace( '%WPSITENAME%', $wp_sitename, $linkname );
					$linkname = str_ireplace( '%WPTAGLINE%', $wp_tagline, $linkname );
		
					// Get META facebook caption
					$caption = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, 'facebook_caption', true ) ) );
					
					// If META facebook message is not set, use the default facebook message format set in options page(s)
					if ( !isset( $caption ) || empty( $caption ) ) {
						
						$caption = htmlspecialchars( stripcslashes( $options['facebook_caption'] ) );
						
					}
					
					$caption = str_ireplace( '%TITLE%', $post_title, $caption );
					$caption = str_ireplace( '%WPSITENAME%', $wp_sitename, $caption );
					$caption = str_ireplace( '%WPTAGLINE%', $wp_tagline, $caption );
					
					if ( !$description = get_post_meta( $post->ID, 'facebook_description', true ) ) {
						
						if ( !empty( $post->post_excerpt ) ) {
							
							//use the post_excerpt if available for the facebook description
							$description = strip_tags( strip_shortcodes( $post->post_excerpt ) ); 
							
						} else {
							
							//otherwise we'll pare down the description
							$description = strip_tags( strip_shortcodes( $post->post_content ) ); 
							
						}
						
					}
					
					$description = str_ireplace( '%TITLE%', $post_title, $description );
					$description = str_ireplace( '%WPSITENAME%', $wp_sitename, $description );
					$description = str_ireplace( '%WPTAGLINE%', $wp_tagline, $description );
					$descLen = strlen( utf8_decode( $description ) );
					
					if ( $descLen > $maxDescLen ) {
						
						$diff = $maxDescLen - $descLen;  // reversed because I need a negative number
						$description = substr( $description, 0, $diff ); // subtract 1 for 0 based array and 3 more for adding an ellipsis
						
					}
					
					if ( !( $picture = apply_filters( 'facebook_image', get_post_meta( $post->ID, 'facebook_image', true ), $post->ID ) ) ) {
						
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
													
					if ( isset( $picture ) && !empty( $picture ) )
						$connect_arr[$api_key]['facebook_picture'] = $picture;
					
					$connect_arr[$api_key]['facebook_message'] = $message;
					$connect_arr[$api_key]['facebook_link'] = $url;
					$connect_arr[$api_key]['facebook_name'] = $linkname;
					$connect_arr[$api_key]['facebook_caption'] = $caption;
					$connect_arr[$api_key]['facebook_description'] = $description;
					
				}
				
			}
			
		}
		
		$wpdb->flush();
	}
		
	return $connect_arr;
	
}

// Actions and filters	
if ( isset( $dl_pluginleenkmeFacebook ) ) {
	
	add_action( 'admin_init', array( $dl_pluginleenkmeFacebook, 'leenkme_add_facebook_meta_tag_options' ), 1 );
	add_action( 'save_post', array( $dl_pluginleenkmeFacebook, 'leenkme_facebook_meta_tags' ) );
	
	// Whenever you publish a post, post to facebook
	add_filter('leenkme_connect', 'leenkme_publish_to_facebook', 20, 2);
		  
	// Add jQuery & AJAX for leenk.me Test
	add_action( 'admin_head-leenk-me_page_leenkme_facebook', 'leenkme_js' );
	
	add_action( 'wp_ajax_fb_publish', 'leenkme_ajax_fb' );
	add_action( 'wp_ajax_republish', 'leenkme_ajax_republish' );
	
	// edit-post.php post row update
	add_filter( 'post_row_actions', 'republish_row_action', 10, 2 );
	add_filter( 'page_row_actions', 'republish_row_action', 10, 2 );
	
}