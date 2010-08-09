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
	function get_user_settings( $user_id = "" ) {
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
                    <p>Publish Fan Page? <input type="checkbox" id="facebook_page" name="facebook_page" <?php if ( $user_settings[$this->facebook_page] ) echo "checked='checked'"; ?> /></p>
                </div>
                <div id="facebook_format_options">
                    <p>Default Image URL: <input name="default_image" type="text" style="width: 500px;" value="<?php _e( apply_filters( 'format_to_edit', $user_settings[$this->default_image] ), 'leenkme_Facebook' ) ?>" /></p>
				</div>
                <div id="facebook_options">
                    <p>Publish Categories: <input name="publish_cats" type="text" style="width: 250px;" value="<?php _e( apply_filters( 'format_to_edit', $user_settings[$this->publish_cats] ), 'leenkme_Facebook' ) ?>" /></p>
                    <div class="publish-cats" style="margin-left: 50px;">
                    <p style="font-size: 11px; margin-bottom: 0px;">Publish to your wall from several specific category IDs, e.g. 3,4,5<br />Publish all posts to your wall except those from a category by prefixing its ID with a '-' (minus) sign, e.g. -3,-4,-5</p>
                    </div>
                    <?php if ( current_user_can('activate_plugins') ) { //then we're displaying the main Admin options ?>
                    <p>Publish All Authors? <input value="1" type="checkbox" name="publish_all_users" <?php if ( $leenkme_settings[$this->publish_all_users] ) echo "checked"; ?> /></p>
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
		$user_settings = get_user_option( $this->options_name, $user_id ); ?>

		<div id="postlm" class="postbox">
		<h3><?php _e( 'leenk.me Facebook', 'leenkme' ) ?></h3>
		<div class="inside">
		<div id="postlm">
	
		<input value="facebook_edit" type="hidden" name="facebook_edit" />
		<table>
			<tr>
			<th style="text-align:right;" colspan="2">
			</th>
			</tr>
			
			<tr><th scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Image URL:', 'leenkme' ) ?></th>
			<td><input value="<?php echo $facebook_image; ?>" type="text" name="facebook_image" size="80px"/></td></tr>
			<?php if ( $user_settings['facebook_profile'] ) { ?>
			<tr><th scope="row" style="text-align:right; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Exclude from Profile:', 'leenkme' ) ?></th>
			<td>
				<input style="margin-top: 5px;" value="1" type="checkbox" name="facebook_exclude_profile" <?php if ( $exclude_profile ) echo "checked"; ?> />
			</td></tr>
            <?php } ?>
			<?php if ( $user_settings['facebook_page'] ) { ?>
            <tr><th scope="row" style="text-align:right; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Exclude from Page:', 'leenkme' ) ?></th>
			<td>
				<input style="margin-top: 5px;" value="1" type="checkbox" name="facebook_exclude_page" <?php if ( $exclude_page ) echo "checked"; ?> />
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
		
	$message = "Testing leenk.me's Facebook Plugin for WordPress";
	$url = "http://leenk.me/";
	$picture = "http://leenk.me/leenkme.png";
	$description = "leenk.me is a webapp that allows you to publish to popular social networking sites whenever you publish a new post from your WordPress website.";
	
	$body = array( 	'leenkme_API' => $user_settings['leenkme_API'], 
					'facebook_message' => $message, 
					'facebook_link' => $url,
					'facebook_picture' => $picture,
					'facebook_description' => $description );
					
	if ( isset( $_POST['facebook_profile'] ) && "true" === $_POST['facebook_profile'] ) {
		$body['facebook_profile'] = true;
	}
	
	if ( isset( $_POST['facebook_page'] ) && "true" === $_POST['facebook_page'] ) {
		$body['facebook_page'] = true;
	}
	
	$result = leenkme_connect($body);
	
	if ( isset( $result["response"]["code"] ) ) {
		die( $result["body"] );
	} else {
		die( "ERROR: Unknown error, please try again. If this continues to fail, contact support@leenk.me." );
	}
}

function leenkme_ajax_republish() {
	check_ajax_referer( 'republish' );
	
	if ( isset( $_POST['id'] ) ) {
		$post = get_post( $_POST['id'] );
		
		$result = leenkme_publish_to_facebook( $post, true );
		
		if ( isset( $result["response"]["code"] ) ) {
			die( $result["body"] );
		} else if ( isset( $result ) ) {
			die( $result );
		} else {
			die( "ERROR: Unknown error, please try again. If this continues to fail, contact support@leenk.me." );
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
function leenkme_publish_to_facebook( $post, $republish = false ) {
	global $wpdb;
	$maxMessageLen = 420;
	$maxContentLen = 240;
	
	if ( get_post_meta( $post->ID, 'facebook_exclude_profile', true ) ) {
		$exclude_profile = true;
	} else {
		$exclude_profile = false;
	}
	
	if ( get_post_meta( $post->ID, 'facebook_exclude_page', true ) ) {
		$exclude_page = true;
	} else {
		$exclude_page = false;	
	}

	$url = get_permalink( $post->ID );
	
	if ( 'post' === $post->post_type ) {
		$options = get_option( 'leenkme_facebook' );
		
		if ( $options['publish_all_users'] ) {
			$user_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->users" ) );
		} else {
			$user_ids[] = $post->post_author;
		}
		
		foreach ( $user_ids as $user_id ) {
			$options = get_user_option( 'leenkme_facebook', $user_id );
			
			if ( !empty( $options ) ) {		
				$continue = FALSE;
				if ( !empty( $options['facebook_publish_cats'] ) ) {
					$cats = split( ",", $options['facebook_publish_cats'] );
					foreach ( $cats as $cat ) {
						if ( preg_match( '/^-\d+/', $cat ) ) {
							$cat = preg_replace( '/^-/', '', $cat );
							if ( in_category( (int)$cat, $post ) ) {
								return "Post is in an excluded category.<br />";
							} else  {
								$continue = TRUE; // if not, than we can continue -- thanks Webmaster HC at hablacentro.com :)
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
				
				if ( !$continue ) return "Post is not in an included category.<br />";
				
				$message = $post->post_title;
				$messageLen = strlen( $message );
				
				if ( $messageLen > $maxMessageLen ) {
					$diff = $maxMessageLen - $messageLen;  // reversed because I need a negative number
					$message = substr( $message, 0, $diff - 4 ) . "..."; // subtract 1 for 0 based array and 3 more for adding an ellipsis
				}
				
				if ( !empty( $post->post_excerpt ) ) {
					$content = $post->post_excerpt;
				} else {
					$content = $post->post_content;
				}
				$contentLen = strlen( $content );
				
				if ( $contentLen > $maxContentLen ) {
					$diff = $maxContentLen - $contentLen;
					$content = substr( $content, 0, $diff - 4 ) . "...";
				}
				
				if ( !( $picture = get_post_meta( $post->ID, 'facebook_image', true ) ) ) {
					if ( has_post_thumbnail( $post->ID ) ) {
						$post_thumbnail_id = get_post_thumbnail_id( $post->ID );
						list( $picture, $width, $height ) = wp_get_attachment_image_src( $post_thumbnail_id );
					} else if ( !empty( $options['default_image'] ) ) {
						$picture = $options['default_image'];
					}
				}
				
				// If a user removes his UN or PW and saves, it will be blank - we may as well skip blank entries.
				global $dl_pluginleenkme;
				$user_settings = $dl_pluginleenkme->get_user_settings($user_id);
				if ( '' != $user_settings['leenkme_API'] ) {
					$body = array( 	'leenkme_API'			=> $user_settings['leenkme_API'], 
									'facebook_message' 		=> $message,
									'facebook_link'			=> $url,
									'facebook_name'			=> get_bloginfo('name'),		//Site Name
									'facebook_caption'		=> get_bloginfo('description'), //Tag Line
									'facebook_description'	=> $content );
									
					if ( isset( $picture ) && !empty( $picture ) ) {
						$body['facebook_picture'] = $picture;
					}
									
					if ( !$exclude_profile && $options['facebook_profile'] ) {
						$body['facebook_profile'] = true;
					}
					
					if ( !$exclude_page && $options['facebook_page'] ) {
						$body['facebook_page'] = true;
					}

					if ( !$exclude_profile || !$exclude_page ) {
						$result = leenkme_connect( $body );
					} else {
						return "You have excluded this post from publishing to your Facebook profile and page. If you would like to publish it, edit the post and remove the appropriate exclude check boxes.<br />";
					}
				}
			}
		}
	}
	$wpdb->flush();
	
	// Combine all the results into one string, return is currently only used for republish functionality
	if ( $republish ) { // Added because of compat issue with WP3.0
		return $result;
	}
}

// Actions and filters	
if ( isset( $dl_pluginleenkmeFacebook ) ) {
	/*--------------------------------------------------------------------
	    Actions
	  --------------------------------------------------------------------*/
	add_action( 'edit_form_advanced', array( $dl_pluginleenkmeFacebook, 'leenkme_add_facebook_meta_tag_options' ), 1 );
	add_action( 'save_post', array( $dl_pluginleenkmeFacebook, 'leenkme_facebook_meta_tags' ) );
	
	// Whenever you publish a post, post to facebook
	add_action( 'new_to_publish', 'leenkme_publish_to_facebook', 20 );
	add_action( 'draft_to_publish', 'leenkme_publish_to_facebook', 20 );
	add_action( 'future_to_publish', 'leenkme_publish_to_facebook', 20 );
		  
	// Add jQuery & AJAX for leenk.me Test
	add_action( 'admin_head-leenk-me_page_leenkme_facebook', 'leenkme_js' );
	
	add_action( 'wp_ajax_fb_publish', 'leenkme_ajax_fb' );
	add_action( 'wp_ajax_republish', 'leenkme_ajax_republish' );
	
	// edit-post.php post row update
	add_filter( 'post_row_actions', 'republish_row_action', 10, 2 );
}
?>