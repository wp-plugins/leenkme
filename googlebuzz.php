<?php		
// Define class
class leenkme_GoogleBuzz {	 
	// Class members		
	var $options_name			= 'leenkme_googlebuzz';
	var $buzz_cats				= 'buzz_cats';
	var $clude					= 'clude';
	var $buzz_all_users			= 'buzz_all_users';

	// Constructor
	function leenkme_GoogleBuzz() {
		//Not Currently Needed
	}
	
	/*--------------------------------------------------------------------
		Administrative Functions
	  --------------------------------------------------------------------*/
	
	function get_leenkme_googlebuzz_settings() {
		global $wpdb;
		
		$user_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ID) FROM ' . $wpdb->users ) );
		
		if ( 1 < $user_count ) {
			$buzz_all_users = true;
		} else {
			$buzz_all_users = false;
		}
		
		$options = array( $this->buzz_all_users => $buzz_all_users );
	
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
		$buzz_cats		= array( '0' );
		$clude			= 'in';
		
		$options = array(
							 $this->buzz_cats 		=> $buzz_cats,
							 $this->clude	 		=> $clude
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
	function print_googlebuzz_settings_page() {
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;
		
		// Get the user options
		$user_settings = $this->get_user_settings( $user_id );
		$googlebuzz_settings = $this->get_leenkme_googlebuzz_settings();
		
		if ( isset( $_POST['update_googlebuzz_settings'] ) ) {
			if ( isset( $_POST['clude'] ) && isset( $_POST['buzz_cats'] ) ) {
				$user_settings[$this->clude] = $_POST['clude'];
				$user_settings[$this->buzz_cats] = $_POST['buzz_cats'];
			} else {
				$user_settings[$this->clude] = 'in';
				$user_settings[$this->buzz_cats] = array( '0' );
			}
			
			update_user_option($user_id, $this->options_name, $user_settings);
			
			if ( current_user_can( 'leenkme_manage_all_settings' ) ) { //we're dealing with the main Admin options
				if ( isset( $_POST['buzz_all_users'] ) ) {
					$googlebuzz_settings[$this->buzz_all_users] = true;
				} else {
					$googlebuzz_settings[$this->buzz_all_users] = false;
				}
				
				update_option( $this->options_name, $googlebuzz_settings );
			}
			
			// update settings notification ?>
			<div class="updated"><p><strong><?php _e( 'Settings Updated.', 'leenkme_GoogleBuzz' );?></strong></p></div>
			<?php
		}
		// Display HTML form for the options below
		?>
		<div class=wrap>
            <form id="leenkme" method="post" action="">
                <h2>Google Buzz Settings (<a href="http://leenk.me/2010/09/04/how-to-use-the-leenk-me-google-buzz-plugin-for-wordpress/" target="_blank">help</a>)</h2>
                <div id="googlebuzz_options">
                <h3>Publish Settings</h3>
                    <p>Buzz Categories:</p>
                
                    <div class="tweet-cats" style="margin-left: 50px;">
                    	<p>
                        <input type='radio' name='clude' id='include_cat' value='in' <?php checked( 'in', $user_settings[$this->clude] ); ?> /><label for='include_cat'>Include</label> &nbsp; &nbsp; <input type='radio' name='clude' id='exclude_cat' value='ex' <?php checked( 'ex', $user_settings[$this->clude] ); ?> /><label for='exclude_cat'>Exclude</label> </p>
                        <p>
                        <select id='categories' name='buzz_cats[]' multiple="multiple" size="5" style="height: 70px; width: 150px;">
                        <option value="0" <?php selected( in_array( "0", (array)$user_settings[$this->buzz_cats] ) ); ?>>All Categories</option>
                        <?php 
                        $categories = get_categories( array( 'hide_empty' => 0, 'orderby' => 'name' ) );
                        foreach ( (array)$categories as $category ) {
                            ?>
                            
                            <option value="<?php echo $category->term_id; ?>" <?php selected( in_array( $category->term_id, (array)$user_settings[$this->buzz_cats] ) ); ?>><?php echo $category->name; ?></option>
        
        
                            <?php
                        }
                        ?>
                        </select></p>
                        <p style="font-size: 11px; margin-bottom: 0px;">To 'deselect' hold the SHIFT key on your keyboard while you click the category.</p>
                    </div>
                    <?php if ( current_user_can('leenkme_manage_all_settings') ) { //then we're displaying the main Admin options ?>
                    <p>Buzz All Authors? <input type="checkbox" name="buzz_all_users" <?php checked( $googlebuzz_settings[$this->buzz_all_users] ); ?> /></p>
                    <div class="buzz-allusers" style="margin-left: 50px;">
                    <p style="font-size: 11px; margin-bottom: 0px;">Check this box if you want leenk.me to buzz to each available author account.</p>
                    </div>
                    <?php } ?>
                    <p><input type="button" class="button" name="verify_googlebuzz_connect" id="gb_buzz" value="<?php _e( 'Buzz a Test Message', 'leenkme_GoogleBuzz' ) ?>" />
                    <?php wp_nonce_field( 'gb_buzz', 'gb_buzz_wpnonce' ); ?></p>
                </div>
                <p class="submit">
                    <input class="button-primary" type="submit" name="update_googlebuzz_settings" value="<?php _e( 'Save Settings', 'leenkme_GoogleBuzz' ) ?>" />
                </p>
            </form>
		</div>
		<?php
	}
	
	function leenkme_googlebuzz_meta_tags( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
			
		if ( isset( $_REQUEST['_inline_edit'] ) )
			return;
	
		if ( isset( $_POST['googlebuzz_custombuzz'] ) && !empty( $_POST['googlebuzz_custombuzz'] ) ) {
			update_post_meta( $post_id, 'googlebuzz_custombuzz', $_POST['googlebuzz_custombuzz'] );
		} else {
			delete_post_meta( $post_id, 'googlebuzz_custombuzz' );
		}
		
		if ( isset( $_POST['googlebuzz_exclude'] ) ) {
			update_post_meta( $post_id, 'googlebuzz_exclude', $_POST['googlebuzz_exclude'] );
		} else {
			delete_post_meta( $post_id, 'googlebuzz_exclude' );
		}
	}
	
	function leenkme_add_googlebuzz_meta_tag_options() {
		global $post, $dl_pluginleenkme;
		
		$leenkme_settings = $dl_pluginleenkme->get_leenkme_settings();
		if ( in_array($post->post_type, $leenkme_settings['post_types'] ) ) {			
			$exclude = get_post_meta( $post->ID, 'googlebuzz_exclude', true );
			$buzz = get_post_meta( $post->ID, 'googlebuzz_custombuzz', true ); ?>
	
			<div id="postlm" class="postbox">
			<h3><?php _e( 'leenk.me Google Buzz', 'leenkme' ) ?></h3>
			<div class="inside">
			<div id="postlm">
		
			<input value="googlebuzz_edit" type="hidden" name="googlebuzz_edit" />
			<table>			
				<tr><td scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-bottom:5px; padding-right:10px; vertical-align:top;"><?php _e( 'Custom Buzz:', 'leenkme' ) ?></td>
				<td>
					<textarea onkeydown="this.value=this.value.substr(0,239);" style="margin-top: 5px;" name="googlebuzz_custombuzz" cols="66" rows="5"><?php echo $buzz; ?></textarea>
				</td></tr>
				
				<tr><td scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;"></td>
				<td style="vertical-align:top; width:80px;">
					<p><span style="font-weight:bold;">NOTE</span> an artificial 240 character limit has been set for the Custom Buzz message.</p>
				</td></tr>
				<tr><td scope="row" style="text-align:right; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Exclude from Buzz:', 'leenkme' ) ?></td>
				<td>
					<input style="margin-top: 5px;" type="checkbox" name="googlebuzz_exclude" <?php checked( $exclude ); ?> />
				</td></tr>
				<?php // Only show ReBuzz button if the post is "buzzed"
				if ( 'publish' === $post->post_status ) { ?>
				<tr><td colspan="2">
				<input style="float: right;" type="button" class="button" name="rebuzz_googlebuzz" id="rebuzz_button" value="<?php _e( 'ReBuzz', 'leenkme_GoogleBuzz' ) ?>" />
				</td></tr>
				<?php } ?>
			</table>
			</div></div></div>
			<?php 
		}
	}
}

if ( class_exists( 'leenkme_GoogleBuzz' ) ) {
	$dl_pluginleenkmeGoogleBuzz = new leenkme_GoogleBuzz();
}

// Example followed from http://codex.wordpress.org/AJAX_in_Plugins
function leenkme_googlebuzz_js() {
?>

		$('input#gb_buzz').live('click', function() {
			var data = {
				action:				'gb_buzz',
				_wpnonce:			$('input#gb_buzz_wpnonce').val()
			};
			
			ajax_response(data);
		});

		$('input#rebuzz_button').live('click', function() {
			var data = {
				action: 			'rebuzz',
				id:  				$('input#post_ID').val(),
				_wpnonce: 			$('input#leenkme_wpnonce').val()
			};
			
			ajax_response(data);
		});

		$('a.rebuzz_row_action').live('click', function() {
			var data = {
				action: 			'rebuzz',
				id:  				$(this).attr('id'),
				_wpnonce: 			$('input#leenkme_wpnonce').val()
			};
			
			ajax_response(data);
		});
<?php
}

function leenkme_ajax_gb() {
	check_ajax_referer( 'gb_buzz' );
	global $current_user;
	get_currentuserinfo();
	$user_id = $current_user->ID;
	
	global $dl_pluginleenkme;
	$user_settings = $dl_pluginleenkme->get_user_settings( $user_id );
	if ( $api_key = $user_settings['leenkme_API'] ) {
		$message = "Testing leenk.me's Google Buzz Plugin for WordPress";
		$url = 'http://leenk.me/';
		$title = 'leenk.me';
		
		$connect_arr[$api_key]['googlebuzz_message'] = $message;
		$connect_arr[$api_key]['googlebuzz_link'] = $url;
		$connect_arr[$api_key]['googlebuzz_title'] = $title;
		
		$result = leenkme_ajax_connect( $connect_arr );
			
		if ( isset( $result ) ) {			
			if ( is_wp_error( $result ) ) {
				die( $result->get_error_message() );	
			} else if ( isset( $result['response']['code'] ) ) {
				die( $result['body'] );
			} else {
				die( 'ERROR: Received unknown result, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' );
			}
		} else {
			die( 'ERROR: Unknown error, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' );
		}
	} else {
		die( 'ERROR: You have no entered your leenk.me API key. Please check your leenk.me settings.' );
	}
}

function leenkme_ajax_rebuzz() {
	check_ajax_referer( 'leenkme' );
	
	if ( isset( $_POST['id'] ) ) {
		if ( get_post_meta( $_POST['id'], 'googlebuzz_exclude', true ) ) {
			die( 'You have excluded this post from publishing to your Google Buzz profile. If you would like to publish it, edit the post and remove the exclude check box in the post settings.' );
		} else {
			$post = get_post( $_POST['id'] );
			
			$result = leenkme_ajax_connect( leenkme_buzz_to_googlebuzz( array(), $post, true ) );
			
			if ( isset( $result ) ) {	
				if ( is_wp_error( $result ) ) {
					die( $result->get_error_message() );	
				} else if ( isset( $result['response']['code'] ) ) {
					die( $result['body'] );
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

function rebuzz_row_action( $actions, $post ) {
	global $dl_pluginleenkme;
	$leenkme_options = $dl_pluginleenkme->get_leenkme_settings();
	if ( in_array( $post->post_type, $leenkme_options['post_types'] ) ) {
		// Only show ReBuzz button if the post is "buzzed"
		if ( 'publish' === $post->post_status ) {
			$actions['rebuzz'] = '<a class="rebuzz_row_action" id="' . $post->ID . '" title="' . esc_attr( __( 'ReBuzz this Post' ) ) . '" href="#">' . __( 'ReBuzz' ) . '</a>';
		}
	}

	return $actions;
}
									
// Add function to pubslih to googlebuzz
function leenkme_buzz_to_googlebuzz( $connect_arr = array(), $post, $debug = false ) {
	global $wpdb, $dl_pluginleenkme, $dl_pluginleenkmeGoogleBuzz;
	$maxMessageLen = 240;
	
	if ( get_post_meta( $post->ID, 'googlebuzz_exclude', true ) ) {
		$exclude_googlebuzz = true;
	} else {
		$exclude_googlebuzz = false;
	}
	
	if ( !$exclude_googlebuzz ) {
		$leenkme_settings = $dl_pluginleenkme->get_leenkme_settings();
		if ( in_array($post->post_type, $leenkme_settings['post_types'] ) ) {
			$options = get_option( 'leenkme_googlebuzz' );
			
			if ( $options['buzz_all_users'] ) {
				$user_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->users" ) );
			} else {
				$user_ids[] = $post->post_author;
			}
			
			$url = get_permalink( $post->ID );
			
			if ( !$message = get_post_meta( $post->ID, 'googlebuzz_custombuzz', true ) ) {
				if ( !empty( $post->post_excerpt ) ) {
					//use the post_excerpt if available for the message
					$message = strip_tags( strip_shortcodes( $post->post_excerpt ) ); 
				} else {
					//otherwise we'll use the post_content for the message
					$message = strip_tags( strip_shortcodes( $post->post_content ) ); 
				}
			}
			$messageLen = strlen( utf8_decode( $message ) );
			
			if ( $messageLen > $maxMessageLen ) {
				$diff = $maxMessageLen - $messageLen;
				$message = substr( $message, 0, $diff - 4 ) . '...';
			}
					
			$title = strip_tags( $post->post_title );
			
			foreach ( $user_ids as $user_id ) {
				$user_settings = $dl_pluginleenkme->get_user_settings($user_id);
				if ( empty( $user_settings['leenkme_API'] ) ) {
					continue;	//Skip user if they do not have an API key set
				}
				
				$api_key = $user_settings['leenkme_API'];

				$options = $dl_pluginleenkmeGoogleBuzz->get_user_settings( $user_id );
				if ( !empty( $options ) ) {
					
					if ( !empty( $options['buzz_cats'] ) && isset( $options['clude'] )
							&& !( 'in' == $options['clude'] && in_array( '0', $options['buzz_cats'] ) ) ) {
						
						if ( 'ex' == $options['clude'] && in_array( '0', $options['buzz_cats'] ) ) {
							if ( $debug ) echo "<p>You have your <a href='admin.php?page=leenkme_googlebuzz'>Leenk.me Google Buzz settings</a> set to Exclude All Categories.</p>";
							continue;
						}
						
						$match = false;
						
						$post_categories = wp_get_post_categories( $post->ID );
						
						foreach ( $post_categories as $cat ) {
						
							if ( in_array( (int)$cat, $options['buzz_cats'] ) ) {
							
								$match = true;
								
							}
							
						}
						
						if ( ( 'ex' == $options['clude'] && $match ) ) {
							if ( $debug ) echo "<p>Post in an excluded category, check your <a href='admin.php?page=leenkme_googlebuzz'>Leenk.me Google Buzz settings</a> or remove the post from the excluded category.</p>";
							continue;
						} else if ( ( 'in' == $options['clude'] && !$match ) ) {
							if ( $debug ) echo "<p>Post not found in an included category, check your <a href='admin.php?page=leenkme_googlebuzz'>Leenk.me Google Buzz settings</a> or add the post into the included category.</p>";
							continue;
						}
					}
					
					$connect_arr[$api_key]['googlebuzz_message'] = $message;
					$connect_arr[$api_key]['googlebuzz_link'] = $url;
					$connect_arr[$api_key]['googlebuzz_title'] = $title;
				}
			}
		}
		$wpdb->flush();
	}
		
	return $connect_arr;
}

// Actions and filters	
if ( isset( $dl_pluginleenkmeGoogleBuzz ) ) {
	add_action( 'edit_form_advanced', array( $dl_pluginleenkmeGoogleBuzz, 'leenkme_add_googlebuzz_meta_tag_options' ), 1 );
	add_action( 'edit_page_form', array( $dl_pluginleenkmeGoogleBuzz, 'leenkme_add_googlebuzz_meta_tag_options' ), 1 );
	add_action( 'save_post', array( $dl_pluginleenkmeGoogleBuzz, 'leenkme_googlebuzz_meta_tags' ) );
	
	// Whenever you buzz a post, post to googlebuzz
	add_filter('leenkme_connect', 'leenkme_buzz_to_googlebuzz', 20, 2);
		  
	// Add jQuery & AJAX for leenk.me Test
	add_action( 'admin_head-leenk-me_page_leenkme_googlebuzz', 'leenkme_js' );
	
	add_action( 'wp_ajax_gb_buzz', 'leenkme_ajax_gb' );
	add_action( 'wp_ajax_rebuzz', 'leenkme_ajax_rebuzz' );
	
	// edit-post.php post row update
	add_filter( 'post_row_actions', 'rebuzz_row_action', 10, 2 );
	add_filter( 'page_row_actions', 'rebuzz_row_action', 10, 2 );
}