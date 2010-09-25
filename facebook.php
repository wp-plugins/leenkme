<?php		
// Define class
class leenkme_Facebook {
	/*--------------------------------------------------------------------
		General Functions
	  --------------------------------------------------------------------*/
	  
	// Class members		
	var $options_name			= "leenkme_facebook";
	var $facebook_profile		= "facebook_profile";
	var $facebook_page			= "facebook_page";
	var $default_image			= "default_image";
	var $publish_cats			= "publish_cats";
	var $publish_all_users		= "publish_all_users";

	// Constructor
	function leenkme_Facebook() {
		//Not Currently Needed
	}
	
	/*--------------------------------------------------------------------
		Administrative Functions
	  --------------------------------------------------------------------*/
	
	function get_leenkme_settings() {
		$publish_all_users = "";
		
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
		$facebook_profile	= true;
		$facebook_page		= false;
		$default_image		= "";
		$publish_cats		= "";
		
		$options = array(
							 $this->facebook_profile 	=> $facebook_profile,
							 $this->facebook_page 		=> $facebook_page,
							 $this->default_image 		=> $default_image,
							 $this->publish_cats 		=> $publish_cats
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
		global $dl_pluginleenkme;
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;
		
		// Get the user options
		$user_settings = $this->get_user_settings( $user_id );
		$leenkme_settings = $this->get_leenkme_settings();
		
		if ( isset( $_POST['update_facebook_settings'] ) ) {
			if ( isset( $_POST['facebook_profile'] ) ) {
				$user_settings[$this->facebook_profile] = true;
			} else {
				$user_settings[$this->facebook_profile] = false;
			}
			
			if ( isset( $_POST['facebook_page'] ) ) {
				$user_settings[$this->facebook_page] = true;
			} else {
				$user_settings[$this->facebook_page] = false;
			}
			
			if ( isset( $_POST['default_image'] ) ) {
				$user_settings[$this->default_image] = $_POST['default_image'];
			}

			if ( isset( $_POST['publish_cats'] ) ) {
				$user_settings[$this->publish_cats] = $_POST['publish_cats'];
			}
			
			update_user_option($user_id, $this->options_name, $user_settings);
			
			if ( current_user_can( 'activate_plugins' ) ) { //we're dealing with the main Admin options
				if ( isset( $_POST['publish_all_users'] ) ) {
					$leenkme_settings[$this->publish_all_users] = true;
				} else {
					$leenkme_settings[$this->publish_all_users] = false;
				}
				
				update_option( $this->options_name, $leenkme_settings );
			}
			
			// update settings notification ?>
			<div class="updated"><p><strong><?php _e("Settings Updated.", "leenkme_Facebook");?></strong></p></div>
			<?php
		}
		// Display HTML form for the options below
		?>
		<div class=wrap>
            <form id="leenkme" method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
                <h2>Facebook Settings</h2>
                <div id="facebook_publish_options">
                    <p>Publish to Personal Profile? <input type="checkbox" id="facebook_profile" name="facebook_profile" <?php if ( $user_settings[$this->facebook_profile] ) echo "checked='checked'"; ?> /></p>
                    <p>Publish to Fan Page? <input type="checkbox" id="facebook_page" name="facebook_page" <?php if ( $user_settings[$this->facebook_page] ) echo "checked='checked'"; ?> /></p>
                </div>
                <div id="facebook_format_options">
                    <p>Default Image URL: <input name="default_image" type="text" style="width: 500px;" value="<?php _e( apply_filters( 'format_to_edit', $user_settings[$this->default_image] ), 'leenkme_Facebook' ) ?>" /></p>                    <div class="publish-cats" style="margin-left: 50px;">
                    <p style="font-size: 11px; margin-bottom: 0px;"><strong>NOTE</strong> Do not use an image URL hosted by Facebook. Facebook does not like this and will reject your message.</p>
                    </div>
				</div>
                <div id="facebook_options">
                    <p>Publish Categories: <input name="publish_cats" type="text" style="width: 250px;" value="<?php _e( apply_filters( 'format_to_edit', $user_settings[$this->publish_cats] ), 'leenkme_Facebook' ) ?>" /></p>
                    <div class="publish-cats" style="margin-left: 50px;">
                    <p style="font-size: 11px; margin-bottom: 0px;">Publish to your wall from several specific category IDs, e.g. 3,4,5<br />Publish all posts to your wall except those from a category by prefixing its ID with a '-' (minus) sign, e.g. -3,-4,-5</p>
                    </div>
                    <?php if ( current_user_can('activate_plugins') ) { //then we're displaying the main Admin options ?>
                    <p>Publish All Authors? <input type="checkbox" name="publish_all_users" <?php if ( $leenkme_settings[$this->publish_all_users] ) echo 'checked="checked"'; ?> /></p>
                    <div class="publish-allusers" style="margin-left: 50px;">
                    <p style="font-size: 11px; margin-bottom: 0px;">Check this box if you want leenk.me to publish to each available author account.</p>
                    </div>
                    <?php } ?>
                    <p><input type="button" class="button" name="verify_facebook_connect" id="fb_publish" value="<?php _e( 'Publish a Test Message', 'leenkme_Facebook' ) ?>" />
                    <?php wp_nonce_field( 'fb_publish', 'fb_publish_wpnonce' ); ?></p>
                </div>
                <p class="submit">
                    <input class="button-primary" type="submit" name="update_facebook_settings" value="<?php _e( 'Save Settings', 'leenkme_Facebook' ) ?>" />
                </p>
            </form>
		</div>
		<?php
	}
	
	function leenkme_facebook_meta_tags( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( isset($_POST["facebook_image"] ) && !empty( $_POST["facebook_image"] ) ) {
			update_post_meta( $post_id, 'facebook_image', $_POST["facebook_image"] );
		} else {
			delete_post_meta( $post_id, 'facebook_image' );
		}

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
	}
	
	function leenkme_add_facebook_meta_tag_options() {
		global $post;
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;
		
		$facebook_image = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, 'facebook_image', true ) ) );
		$exclude_profile = get_post_meta( $post->ID, 'facebook_exclude_profile', true ); 
		$exclude_page = get_post_meta( $post->ID, 'facebook_exclude_page', true ); 
		$user_settings = $this->get_user_settings( $user_id ); ?>

		<div id="postlm" class="postbox">
		<h3><?php _e( 'leenk.me Facebook', 'leenkme' ) ?></h3>
		<div class="inside">
		<div id="postlm">
	
		<input value="facebook_edit" type="hidden" name="facebook_edit" />
		<table>
			<tr><td scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Image URL:', 'leenkme' ) ?></td>
			<td><input value="<?php echo $facebook_image; ?>" type="text" name="facebook_image" size="80px"/></td></tr>
			<?php if ( $user_settings['facebook_profile'] ) { ?>
			<tr><td scope="row" style="text-align:right; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Exclude from Profile:', 'leenkme' ) ?></td>
			<td>
				<input style="margin-top: 5px;" type="checkbox" name="facebook_exclude_profile" <?php if ( $exclude_profile ) echo 'checked="checked"'; ?> />
			</td></tr>
            <?php } ?>
			<?php if ( $user_settings['facebook_page'] ) { ?>
            <tr><td scope="row" style="text-align:right; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Exclude from Page:', 'leenkme' ) ?></td>
			<td>
				<input style="margin-top: 5px;" type="checkbox" name="facebook_exclude_page" <?php if ( $exclude_page ) echo 'checked="checked"'; ?> />
			</td></tr>
			<?php } ?>
			<?php // Only show RePublish button if the post is "published"
            if ( 'publish' === $post->post_status ) { ?>
            <tr><td colspan="2">
            <input style="float: right;" type="button" class="button" name="republish_facebook" id="republish_button" value="<?php _e( 'RePublish', 'leenkme_Facebook' ) ?>" />
            <?php wp_nonce_field( 'republish', 'republish_wpnonce' ); ?>
            </td></tr>
            <?php } ?>
		</table>
		</div></div></div>
		<?php 
	}
}

if ( class_exists( "leenkme_Facebook" ) ) {
	$dl_pluginleenkmeFacebook = new leenkme_Facebook();
}

// Example followed from http://codex.wordpress.org/AJAX_in_Plugins
function leenkme_facebook_js() {
?>

		$('input#fb_publish').click(function() {
			var facebook_profile = $('input#facebook_profile').attr('checked')
			var facebook_page = $('input#facebook_page').attr('checked')
			
			var data = {
				action:				'fb_publish',
				facebook_profile:	facebook_profile,
				facebook_page:		facebook_page,
				_wpnonce:			$('input#fb_publish_wpnonce').val()
			};
			
			ajax_response(data);
		});

		$('input#republish_button').click(function() {
			var data = {
				action: 			'republish',
				id:  				$('input#post_ID').val(),
				_wpnonce: 			$('input#republish_wpnonce').val()
			};
			
			ajax_response(data);
		});

		$('a.republish_row_action').click(function() {
			var data = {
				action: 			'republish',
				id:  				$(this).attr('id'),
				_wpnonce: 			$('input#republish_wpnonce').val()
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
		$url = "http://leenk.me/";
		$picture = "http://leenk.me/leenkme.png";
		$description = "leenk.me is a webapp that allows you to publish to popular social networking sites whenever you publish a new post from your WordPress website.";
		
		$connect_arr[$api_key]['facebook_message'] = $message;
		$connect_arr[$api_key]['facebook_link'] = $url;
		$connect_arr[$api_key]['facebook_picture'] = $picture;
		$connect_arr[$api_key]['facebook_description'] = $description;
						
		if ( isset( $_POST['facebook_profile'] ) && "true" === $_POST['facebook_profile'] ) {
			$connect_arr[$api_key]['facebook_profile'] = true;
		}
		
		if ( isset( $_POST['facebook_page'] ) && "true" === $_POST['facebook_page'] ) {
			$connect_arr[$api_key]['facebook_page'] = true;
		}
		
		$result = leenkme_ajax_connect($connect_arr);
		
		if ( isset( $result ) ) {			
			if ( is_wp_error( $result ) ) {
				die( $result->get_error_message() );	
			} else if ( isset( $result["response"]["code"] ) ) {
				die( $result["body"] );
			} else {
				die( "ERROR: Unknown error, please try again. If this continues to fail, contact support@leenk.me." );
			}
		} else {
			die( "ERROR: Unknown error, please try again. If this continues to fail, contact support@leenk.me." );
		}
	} else {
		die( "ERROR: You have no entered your leenk.me API key. Please check your leenk.me settings." );
	}
}

function leenkme_ajax_republish() {
	check_ajax_referer( 'republish' );
	
	if ( isset( $_POST['id'] ) ) {
		if ( get_post_meta( $_POST['id'], 'facebook_exclude_profile', true ) 
				&& get_post_meta( $_POST['id'], 'facebook_exclude_page', true ) ) {
			die( "You have excluded this post from publishing to your Facebook profile and page. If you would like to publish it, edit the post and remove the appropriate exclude check boxes." );
		} else {
			$post = get_post( $_POST['id'] );
			
			$result = leenkme_ajax_connect( leenkme_publish_to_facebook( array(), $post ) );
			
			if ( isset( $result ) ) {			
				if ( is_wp_error( $result ) ) {
					die( $result->get_error_message() );	
				} else if ( isset( $result["response"]["code"] ) ) {
					die( $result["body"] );
				} else {
					die( "ERROR: Received unknown result, please try again. If this continues to fail, contact support@leenk.me." );
				}
			} else {
				die( "ERROR: Unknown error, please try again. If this continues to fail, contact support@leenk.me." );
			}
		}
	} else {
		die( "ERROR: Unable to determine Post ID." );
	}
}

function republish_row_action( $actions, $post ) {
	// Only show RePublish button if the post is "published"
	if ( "publish" === $post->post_status ) {
		$actions['republish'] = "<a class='republish_row_action' id='" . $post->ID . "' title='" . esc_attr( __( 'RePublish this Post' ) ) . "' href='#'>" . __( 'RePublish' ) . "</a>" .
		wp_nonce_field( 'republish', 'republish_wpnonce' );
	}

	return $actions;
}
									
// Add function to pubslih to facebook
function leenkme_publish_to_facebook( $connect_arr = array(), $post ) {
	global $wpdb;
	$maxMessageLen = 420;
	$maxContentLen = 240;

	if ( get_post_meta( $post->ID, 'facebook_exclude_profile', true ) ) {
		$exclude_profile = true;
	} else {
		$exclude_profile = false;
	}
	
	if ( get_post_meta( $post->ID, 'facebook_exclude_page', true ) ) {
		$exclude_profile = true;
	} else {
		$exclude_page = false;
	}
	
	if ( !$exclude_profile && !$exclude_page ) {
		if ( 'post' === $post->post_type ) {
			$options = get_option( 'leenkme_facebook' );
			
			if ( $options['publish_all_users'] ) {
				$user_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->users" ) );
			} else {
				$user_ids[] = $post->post_author;
			}
			
			$url = get_permalink( $post->ID );
			$site_name = strip_tags( get_bloginfo( 'name' ) );
			$site_caption = strip_tags( get_bloginfo( 'description' ) );
			
			if ( !empty( $post->post_excerpt ) ) {
				$content = strip_tags( $post->post_excerpt ); //use the post_excerpt if available for the facebook description
			} else {
				$content = strip_tags( $post->post_content ); //otherwise we'll pare down the content
			}
			$contentLen = strlen( $content );
			
			if ( $contentLen > $maxContentLen ) {
				$diff = $maxContentLen - $contentLen;
				$content = substr( $content, 0, $diff - 4 ) . "...";
			}
					
			$message = strip_tags( $post->post_title );
			$messageLen = strlen( $message );
			
			if ( $messageLen > $maxMessageLen ) {
				$diff = $maxMessageLen - $messageLen;  // reversed because I need a negative number
				$message = substr( $message, 0, $diff - 4 ) . "..."; // subtract 1 for 0 based array and 3 more for adding an ellipsis
			}
			
			foreach ( $user_ids as $user_id ) {
				global $dl_pluginleenkmeFacebook;
				$options = $dl_pluginleenkmeFacebook->get_user_settings( $user_id );
				
				global $dl_pluginleenkme;
				$user_settings = $dl_pluginleenkme->get_user_settings($user_id);
				if ( empty( $user_settings['leenkme_API'] ) ) {
					continue;	//Skip user if they do not have an API key set
				}
				
				$api_key = $user_settings['leenkme_API'];

				if ( !$options['facebook_profile'] && !$options['facebook_page'] ) {
					continue;	//Skip this user if they don't have Profile or Page checked in plugins Facebook Settings
				}

				// Added facebook profile to connection array if enabled
				if ( $options['facebook_profile'] ) {
					$connect_arr[$api_key]['facebook_profile'] = true;
				}

				// Added facebook page to connection array if enabled
				if ( $options['facebook_page'] ) {
					$connect_arr[$api_key]['facebook_page'] = true;
				}
				
				if ( !empty( $options ) ) {		
					$continue = FALSE;
					if ( !empty( $options['facebook_publish_cats'] ) ) {
						$cats = split( ",", $options['facebook_publish_cats'] );
						foreach ( $cats as $cat ) {
							if ( preg_match( '/^-\d+/', $cat ) ) {
								$cat = preg_replace( '/^-/', '', $cat );
								if ( in_category( (int)$cat, $post ) ) {
									continue;	// Skip to next in foreach
								} else  {
									$continue = TRUE; // if not, than we can continue
								}
							} else if ( preg_match( '/\d+/', $cat ) ) {
								if ( in_category( (int)$cat, $post ) ) {
									$continue = TRUE; // if  in an included category, set continue = TRUE.
								}
							}
						}
					} else { // If no includes or excludes are defined, then continue
						$continue = TRUE;
					}
					
					if ( !$continue ) continue;	// Skip to next in foreach
					
					if ( !( $picture = get_post_meta( $post->ID, 'facebook_image', true ) ) ) {
						if ( function_exists('has_post_thumbnail') && has_post_thumbnail( $post->ID ) ) {
							$post_thumbnail_id = get_post_thumbnail_id( $post->ID );
							list( $picture, $width, $height ) = wp_get_attachment_image_src( $post_thumbnail_id );
						} else if ( !empty( $options['default_image'] ) ) {
							$picture = $options['default_image'];
						}
					}	
													
					if ( isset( $picture ) && !empty( $picture ) ) {
						$connect_arr[$api_key]['facebook_picture'] = $picture;
					}
					
					$connect_arr[$api_key]['facebook_message'] = $message;
					$connect_arr[$api_key]['facebook_link'] = $url;
					$connect_arr[$api_key]['facebook_name'] = $site_name;
					$connect_arr[$api_key]['facebook_caption'] = $site_caption;
					$connect_arr[$api_key]['facebook_description'] = $content;
				}
			}
		}
		$wpdb->flush();
	}
		
	return $connect_arr;
}

// Actions and filters	
if ( isset( $dl_pluginleenkmeFacebook ) ) {
	/*--------------------------------------------------------------------
	    Actions
	  --------------------------------------------------------------------*/
	add_action( 'edit_form_advanced', array( $dl_pluginleenkmeFacebook, 'leenkme_add_facebook_meta_tag_options' ), 1 );
	add_action( 'save_post', array( $dl_pluginleenkmeFacebook, 'leenkme_facebook_meta_tags' ) );
	
	// Whenever you publish a post, post to facebook
	add_filter('leenkme_connect', 'leenkme_publish_to_facebook', 20, 2);
		  
	// Add jQuery & AJAX for leenk.me Test
	add_action( 'admin_head-leenk-me_page_leenkme_facebook', 'leenkme_js' );
	
	add_action( 'wp_ajax_fb_publish', 'leenkme_ajax_fb' );
	add_action( 'wp_ajax_republish', 'leenkme_ajax_republish' );
	
	// edit-post.php post row update
	add_filter( 'post_row_actions', 'republish_row_action', 10, 2 );
}
?>
