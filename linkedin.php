<?php		
// Define class
class leenkme_LinkedIn {
	// Class members		
	var $options_name				= 'leenkme_linkedin';
	var $linkedin_comment			= 'linkedin_comment';
	var $linkedin_title				= 'linkedin_title';
	var $default_image				= 'default_image';
	var $share_cats					= 'share_cats';
	var $share_all_users			= 'share_all_users';

	// Constructor
	function leenkme_LinkedIn() {
		//Not Currently Needed
	}
	
	/*--------------------------------------------------------------------
		Administrative Functions
	  --------------------------------------------------------------------*/
	
	function get_leenkme_linkedin_settings() {
		$share_all_users = '';
		
		$options = array( $this->share_all_users => $share_all_users );
	
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
		$linkedin_comment		= '%TITLE%';
		$linkedin_title			= '%WPSITENAME%';
		$default_image			= '';
		$share_cats			= '';
		
		$options = array(
							 $this->linkedin_comment		=> $linkedin_comment,
							 $this->linkedin_title			=> $linkedin_title,
							 $this->default_image 			=> $default_image,
							 $this->share_cats 			=> $share_cats
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
	function print_linkedin_settings_page() {
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;
		
		// Get the user options
		$user_settings = $this->get_user_settings( $user_id );
		$linkedin_settings = $this->get_leenkme_linkedin_settings();
		
		if ( isset( $_POST['update_linkedin_settings'] ) ) {
			if ( isset( $_POST['linkedin_comment'] ) ) {
				$user_settings[$this->linkedin_comment] = $_POST['linkedin_comment'];
			}

			if ( isset( $_POST['linkedin_title'] ) ) {
				$user_settings[$this->linkedin_title] = $_POST['linkedin_title'];
			}
			
			if ( isset( $_POST['default_image'] ) ) {
				$user_settings[$this->default_image] = $_POST['default_image'];
			}

			if ( isset( $_POST['share_cats'] ) ) {
				$user_settings[$this->share_cats] = $_POST['share_cats'];
			}
			
			update_user_option($user_id, $this->options_name, $user_settings);
			
			if ( current_user_can( 'leenkme_manage_all_settings' ) ) { //we're dealing with the main Admin options
				if ( isset( $_POST['share_all_users'] ) ) {
					$linkedin_settings[$this->share_all_users] = true;
				} else {
					$linkedin_settings[$this->share_all_users] = false;
				}
				
				update_option( $this->options_name, $linkedin_settings );
			}
			
			// update settings notification ?>
			<div class="updated"><p><strong><?php _e( 'Settings Updated.', 'leenkme_LinkedIn' );?></strong></p></div>
			<?php
		}
		// Display HTML form for the options below
		?>
		<div class=wrap>
            <form id="leenkme" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                <h2>LinkedIn Settings (<a href="http://leenk.me/2010/12/01/how-to-use-the-leenk-me-linkedin-plugin-for-wordpress/" target="_blank">help</a>)</h2>
                <div id="linkedin_format_options" style="margin-top:25px; border-top: 1px solid grey;">
                	<h3>Message Settings</h3>
                    <p>Default Comment: <input name="linkedin_comment" type="text" style="width: 500px;" value="<?php _e( $user_settings[$this->linkedin_comment], 'leenkme_LinkedIn' ) ?>" /></p>
                    <p>Default Link Name: <input name="linkedin_title" type="text" style="width: 500px;" value="<?php _e( $user_settings[$this->linkedin_title], 'leenkme_LinkedIn' ) ?>" /></p>
                    <div class="linkedin-format" style="margin-left: 50px;">
                    <p style="font-size: 11px; margin-bottom: 0px;">Format Options:</p>
                    <ul style="font-size: 11px;">
                        <li>%TITLE% - Displays the post title.</li>
                        <li>%WPSITENAME% - Displays the WordPress site name (found in Settings -> General).</li>
                        <li>%WPTAGLINE% - Displays the WordPress TagLine (found in Settings -> General).</li>
                    </ul>
                    </div>
                    <p>Default Image URL: <input name="default_image" type="text" style="width: 500px;" value="<?php _e( $user_settings[$this->default_image], 'leenkme_LinkedIn' ) ?>" /></p>                    <div class="publish-cats" style="margin-left: 50px;">
				</div>
                <div id="linkedin_publish_options" style="margin-top:25px; border-top: 1px solid grey;">
                	<h3>Publish Settings</h3>
                    <p>Share Categories: <input name="share_cats" type="text" style="width: 250px;" value="<?php _e( $user_settings[$this->share_cats], 'leenkme_LinkedIn' ) ?>" /></p>
                    <div class="publish-cats" style="margin-left: 50px;">
                    <p style="font-size: 11px; margin-bottom: 0px;">Share content on your LinkedIn account from several specific category IDs, e.g. 3,4,5<br />Share all posts except those from a category by prefixing its ID with a '-' (minus) sign, e.g. -3,-4,-5</p>
                    </div>
                    <?php if ( current_user_can('leenkme_manage_all_settings') ) { //then we're displaying the main Admin options ?>
                    <p>Share All Authors? <input type="checkbox" name="share_all_users" <?php if ( $linkedin_settings[$this->share_all_users] ) echo 'checked="checked"'; ?> /></p>
                    <div class="publish-allusers" style="margin-left: 50px;">
                    <p style="font-size: 11px; margin-bottom: 0px;">Check this box if you want leenk.me to share to each available author account.</p>
                    </div>
                    <?php } ?>
                    <p><input type="button" class="button" name="verify_linkedin_connect" id="li_share" value="<?php _e( 'Share a Test Message', 'leenkme_LinkedIn' ) ?>" />
                    <?php wp_nonce_field( 'li_share', 'li_share_wpnonce' ); ?></p>
                </div>
                <p class="submit">
                    <input class="button-primary" type="submit" name="update_linkedin_settings" value="<?php _e( 'Save Settings', 'leenkme_LinkedIn' ) ?>" />
                </p>
            </form>
		</div>
		<?php
	}
	
	function leenkme_linkedin_meta_tags( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
		
		if ( isset( $_POST['linkedin_comment'] ) && !empty( $_POST['linkedin_comment'] ) ) {
			update_post_meta( $post_id, 'linkedin_comment', $_POST['linkedin_comment'] );
		} else {
			delete_post_meta( $post_id, 'linkedin_comment' );
		}
		
		if ( isset( $_POST['linkedin_title'] ) && !empty( $_POST['linkedin_title'] ) ) {
			update_post_meta( $post_id, 'linkedin_title', $_POST['linkedin_title'] );
		} else {
			delete_post_meta( $post_id, 'linkedin_title' );
		}
		
		if ( isset( $_POST['linkedin_description'] ) && !empty( $_POST['linkedin_description'] ) ) {
			update_post_meta( $post_id, 'linkedin_description', $_POST['linkedin_description'] );
		} else {
			delete_post_meta( $post_id, 'linkedin_description' );
		}

		if ( isset($_POST["linkedin_image"] ) && !empty( $_POST["linkedin_image"] ) ) {
			update_post_meta( $post_id, 'linkedin_image', $_POST["linkedin_image"] );
		} else {
			delete_post_meta( $post_id, 'linkedin_image' );
		}

		if ( isset( $_POST['linkedin_exclude'] ) ) {
			update_post_meta( $post_id, 'linkedin_exclude', $_POST['linkedin_exclude'] );
		} else {
			delete_post_meta( $post_id, 'linkedin_exclude' );
		}
	}
	
	function leenkme_add_linkedin_meta_tag_options() {
		global $post, $current_user, $dl_pluginleenkme;
		
		$leenkme_settings = $dl_pluginleenkme->get_leenkme_settings();
		if ( in_array($post->post_type, $leenkme_settings['post_types'] ) ) {
			get_currentuserinfo();
			$user_id = $current_user->ID;
			
			$linkedin_exclude = get_post_meta( $post->ID, 'linkedin_exclude', true );
			$linkedin_comment = get_post_meta( $post->ID, 'linkedin_comment', true );
			$linkedin_title = get_post_meta( $post->ID, 'linkedin_title', true );
			$linkedin_description = get_post_meta( $post->ID, 'linkedin_description', true );
			$linkedin_image = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, 'linkedin_image', true ) ) );
			$linkedin_exclude = get_post_meta( $post->ID, 'linkedin_exclude', true );
			$user_settings = $this->get_user_settings( $user_id ); ?>
	
			<div id="postlm" class="postbox">
			<h3><?php _e( 'leenk.me LinkedIn', 'leenkme' ) ?></h3>
			<div class="inside">
			<div id="postlm">
		
			<input value="linkedin_edit" type="hidden" name="linkedin_edit" />
			<table>
				<tr><td scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;"><?php _e( 'Format Options:', 'leenkme' ) ?></td>
				  <td style="vertical-align:top; width:80px;">
					<p>%TITLE%, %WPSITENAME%, %WPTAGLINE%</p>
				</td></tr>
				<tr><td scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Custom Comment:', 'leenkme' ) ?></td>
				  <td><input value="<?php echo $linkedin_comment; ?>" type="text" name="linkedin_comment" size="80px"/></td></tr>
				<tr><td scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Custom Link Name:', 'leenkme' ) ?></td>
				  <td><input value="<?php echo $linkedin_title; ?>" type="text" name="linkedin_title" size="80px"/></td></tr>
				<tr><td scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-bottom:5px; padding-right:10px; vertical-align:top;"><?php _e( 'Custom Description:', 'leenkme' ) ?></td>
				  <td><textarea style="margin-top: 5px;" name="linkedin_description" cols="66" rows="5"><?php echo $linkedin_description; ?></textarea>
				</td></tr>
				<tr><td scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Image URL:', 'leenkme' ) ?></td>
				  <td><input value="<?php echo $linkedin_image; ?>" type="text" name="linkedin_image" size="80px"/></td></tr>
				<tr><td scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;"></td>
				  <td style="vertical-align:top; width:80px;">
					<p>Paste the URL to the image or set the "Featured Image" if your theme supports it.</p>
				</td></tr>
				<tr><td scope="row" style="text-align:right; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Exclude from LinkedIn:', 'leenkme' ) ?></td>
				  <td><input style="margin-top: 5px;" type="checkbox" name="linkedin_exclude" <?php if ( $linkedin_exclude ) echo 'checked="checked"'; ?> />
				</td></tr>
				<?php // Only show ReShare button if the post is "published"
				if ( 'publish' === $post->post_status ) { ?>
				<tr><td colspan="2">
				<input style="float: right;" type="button" class="button" name="reshare_linkedin" id="reshare_button" value="<?php _e( 'ReShare', 'leenkme_LinkedIn' ) ?>" />
				</td></tr>
				<?php } ?>
			</table>
			</div></div></div>
			<?php 
		}
	}
}

if ( class_exists( 'leenkme_LinkedIn' ) ) {
	$dl_pluginleenkmeLinkedIn = new leenkme_LinkedIn();
}

// Example followed from http://codex.wordpress.org/AJAX_in_Plugins
function leenkme_linkedin_js() {
?>

		$('input#li_share').click(function() {
			var data = {
				action:		'li_share',
				_wpnonce:	$('input#li_share_wpnonce').val()
			};
			
			ajax_response(data);
		});

		$('input#reshare_button').click(function() {
			var data = {
				action: 	'reshare',
				id:  		$('input#post_ID').val(),
				_wpnonce: 	$('input#leenkme_wpnonce').val()
			};
			
			ajax_response(data);
		});

		$('a.reshare_row_action').click(function() {
			var data = {
				action: 	'reshare',
				id:  		$(this).attr('id'),
				_wpnonce: 	$('input#leenkme_wpnonce').val()
			};
            
			ajax_response(data);
		});
<?php
}

function leenkme_ajax_li() {
	check_ajax_referer( 'li_share' );
	global $current_user;
	get_currentuserinfo();
	$user_id = $current_user->ID;
	
	global $dl_pluginleenkme;
	$user_settings = $dl_pluginleenkme->get_user_settings( $user_id );
	if ( $api_key = $user_settings['leenkme_API'] ) {
		$comment = "Testing leenk.me's LinkedIn Plugin for WordPress";
		$title = 'leenk.me test';
		$url = 'http://leenk.me/';
		$picture = 'http://leenk.me/leenkme.png';
		$description = 'leenk.me is a webapp that allows you to publish to popular social networking sites whenever you publish a new post from your WordPress website.';
		$code = 'anyone';
		
		$connect_arr[$api_key]['li_comment'] = $comment;
		$connect_arr[$api_key]['li_title'] = $title;
		$connect_arr[$api_key]['li_url'] = $url;
		$connect_arr[$api_key]['li_image'] = $picture;
		$connect_arr[$api_key]['li_desc'] = $description;
		$connect_arr[$api_key]['li_code'] = $code;
		
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

function leenkme_ajax_reshare() {
	check_ajax_referer( 'leenkme' );
	
	if ( isset( $_POST['id'] ) ) {
		if ( get_post_meta( $_POST['id'], 'linkedin_exclude', true ) ) {
			die( 'You have excluded this post from sharinging to your LinkedIn profile. If you would like to share it, edit the post and remove the appropriate exclude check box.' );
		} else {
			$post = get_post( $_POST['id'] );
			
			$result = leenkme_ajax_connect( leenkme_share_to_linkedin( array(), $post ) );
			
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

function reshare_row_action( $actions, $post ) {
	global $dl_pluginleenkme;
	$leenkme_options = $dl_pluginleenkme->get_leenkme_settings();
	if ( in_array( $post->post_type, $leenkme_options['post_types'] ) ) {
		// Only show ReShare button if the post is "published"
		if ( 'publish' === $post->post_status ) {
			$actions['reshare'] = '<a class="reshare_row_action" id="' . $post->ID . '" title="' . esc_attr( __( 'ReShare this Post' ) ) . '" href="#">' . __( 'ReShare' ) . '</a>';
		}
	}

	return $actions;
}
									
// Add function to share on LinkedIn
function leenkme_share_to_linkedin( $connect_arr = array(), $post ) {
	global $wpdb, $dl_pluginleenkme, $dl_pluginleenkmeLinkedIn;
	$maxLen = 400;	//LinkedIn has a 400 character limit for descriptions
	
	if ( get_post_meta( $post->ID, 'linkedin_exclude', true ) ) {
		$linkedin_exclude = true;
	} else {
		$linkedin_exclude = false;
	}
	
	if ( !$linkedin_exclude ) {
		$leenkme_settings = $dl_pluginleenkme->get_leenkme_settings();
		if ( in_array($post->post_type, $leenkme_settings['post_types'] ) ) {
			$options = get_option( 'leenkme_linkedin' );
			
			if ( $options['share_all_users'] ) {
				$user_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM '. $wpdb->users ) );
			} else {
				$user_ids[] = $post->post_author;
			}
			
			$url = get_permalink( $post->ID );
			$post_title = strip_tags( $post->post_title );
			$wp_sitename = strip_tags( get_bloginfo( 'name' ) );
			$wp_tagline = strip_tags( get_bloginfo( 'description' ) );
			
			foreach ( $user_ids as $user_id ) {
				$user_settings = $dl_pluginleenkme->get_user_settings($user_id);
				if ( empty( $user_settings['leenkme_API'] ) ) {
					continue;	//Skip user if they do not have an API key set
				}
				
				$api_key = $user_settings['leenkme_API'];
				
				$options = $dl_pluginleenkmeLinkedIn->get_user_settings( $user_id );
				if ( !empty( $options ) ) {
					if ( !empty( $options['share_cats'] ) ) {	
						$continue = FALSE;
						$cats = split( ",", $options['share_cats'] );
						
						foreach ( $cats as $cat ) {
							if ( preg_match( '/^-\d+/', $cat ) ) {
								$cat = preg_replace( '/^-/', '', $cat );
								if ( in_category( (int)$cat, $post ) ) {
									$continue = FALSE;
									break;	// In an excluded category, break out of foreach
								} else  {
									$continue = TRUE; // if not, than we can continue
								}
							} else if ( preg_match( '/\d+/', $cat ) ) {
								if ( in_category( (int)$cat, $post ) ) {
									$continue = TRUE; // if  in an included category, set continue = TRUE.
								}
							}
						}
					
						if ( !$continue ) continue;	// Skip to next in foreach
					}

					// Get META LinkedIn comment
					$comment = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, 'linkedin_comment', true ) ) );
					
					// If META LinkedIn comment is not set, use the default LinkedIn comment format set in options page(s)
					if ( !isset( $comment ) || empty( $comment ) ) {
						$comment = htmlspecialchars( stripcslashes( $options['linkedin_comment'] ) );
					}
					
					$comment = str_ireplace( '%TITLE%', $post_title, $comment );
					$comment = str_ireplace( '%WPSITENAME%', $wp_sitename, $comment );
					$comment = str_ireplace( '%WPTAGLINE%', $wp_tagline, $comment );
		
					// Get META LinkedIn link name
					$linktitle = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, 'linkedin_title', true ) ) );
					
					// If META LinkedIn link name is not set, use the default LinkedIn comment format set in options page
					if ( !isset( $linktitle ) || empty( $linktitle ) ) {
						$linktitle = htmlspecialchars( stripcslashes( $options['linkedin_title'] ) );
					}
					
					$linktitle = str_ireplace( '%TITLE%', $post_title, $linktitle );
					$linktitle = str_ireplace( '%WPSITENAME%', $wp_sitename, $linktitle );
					$linktitle = str_ireplace( '%WPTAGLINE%', $wp_tagline, $linktitle );
					
					if ( !$description = get_post_meta( $post->ID, 'linkedin_description', true ) ) {
						if ( !empty( $post->post_excerpt ) ) {
							//use the post_excerpt if available for the LinkedIn description
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
					
					if ( $descLen >= $maxLen ) {
						$diff = $maxLen - $descLen;  // reversed because I need a negative number
						$description = substr( $description, 0, $diff - 4 ) . "..."; // subtract 1 for 0 based array and 3 more for adding an ellipsis
					}
				
					if ( !( $picture = apply_filters( 'linkedin_image', get_post_meta( $post->ID, 'linkedin_image', true ), $post->ID ) ) ) {
						if ( function_exists('has_post_thumbnail') && has_post_thumbnail( $post->ID ) ) {
							$post_thumbnail_id = get_post_thumbnail_id( $post->ID );
							list( $picture, $width, $height ) = wp_get_attachment_image_src( $post_thumbnail_id );
						} else if ( $images = get_children( 'post_parent=' . $post->ID . '&post_type=attachment&post_mime_type=image&numberposts=1' ) ) {
							foreach ( $images as $attachment_id => $attachment ) {
								list( $picture, $width, $height ) = wp_get_attachment_image_src( $attachment_id );
								break;
							}
						}  else if ( !empty( $options['default_image'] ) ) {
							$picture = $options['default_image'];
						}
					}	
													
					if ( isset( $picture ) && !empty( $picture ) ) {
						$connect_arr[$api_key]['li_image'] = $picture;
					}
					
					$connect_arr[$api_key]['li_comment'] = $comment;
					$connect_arr[$api_key]['li_url'] = $url;
					$connect_arr[$api_key]['li_title'] = $linktitle;
					$connect_arr[$api_key]['li_desc'] = $description;
					$connect_arr[$api_key]['li_code'] = 'anyone';
				}
			}
		}
		$wpdb->flush();
	}
		
	return $connect_arr;
}

// Actions and filters	
if ( isset( $dl_pluginleenkmeLinkedIn ) ) {
	add_action( 'edit_form_advanced', array( $dl_pluginleenkmeLinkedIn, 'leenkme_add_linkedin_meta_tag_options' ), 1 );
	add_action( 'edit_page_form', array( $dl_pluginleenkmeLinkedIn, 'leenkme_add_linkedin_meta_tag_options' ), 1 );
	add_action( 'save_post', array( $dl_pluginleenkmeLinkedIn, 'leenkme_linkedin_meta_tags' ) );
	
	// Whenever you publish a post, post to LinkedIn
	add_filter('leenkme_connect', 'leenkme_share_to_linkedin', 20, 2);
		  
	// Add jQuery & AJAX for leenk.me Test
	add_action( 'admin_head-leenk-me_page_leenkme_linkedin', 'leenkme_js' );
	
	add_action( 'wp_ajax_li_share', 'leenkme_ajax_li' );
	add_action( 'wp_ajax_reshare', 'leenkme_ajax_reshare' );
	
	// edit-post.php post row update
	add_filter( 'post_row_actions', 'reshare_row_action', 10, 2 );
	add_filter( 'page_row_actions', 'reshare_row_action', 10, 2 );
}