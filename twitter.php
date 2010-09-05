<?php		
// Define class
class leenkme_Twitter {
	/*--------------------------------------------------------------------
		General Functions
	  --------------------------------------------------------------------*/
	  
	// Class members		
	var $options_name			= "leenkme_twitter";
	var $tweetFormat			= "leenkme_tweetformat";
	var $tweetCats				= "tweetcats";
	var $tweetAllUsers			= "leenkme_tweetallusers";

	// Constructor
	function leenkme_Twitter() {
		//Not Currently Needed
	}
	
	/*--------------------------------------------------------------------
		Administrative Functions
	  --------------------------------------------------------------------*/
	
	function get_leenkme_settings() {
		$tweetAllUsers = "";
		
		$options = array( $this->tweetAllUsers => $tweetAllUsers );
	
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
		$tweetFormat 		= "Blogged %TITLE%: %URL%";
		$tweetCats		 	= "";
		
		$options = array(
							 $this->tweetFormat 		=> $tweetFormat,
							 $this->tweetCats 			=> $tweetCats
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
	function print_twitter_settings_page() {
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;
		
		// Get the user options
		$user_settings = $this->get_user_settings( $user_id );
		$leenkme_settings = $this->get_leenkme_settings();
		
		if ( isset( $_POST['update_twitter_settings'] ) ) {			
			if ( isset( $_POST['leenkme_tweetformat'] ) ) {
				$user_settings[$this->tweetFormat] = $_POST['leenkme_tweetformat'];
			}
			
			if ( isset( $_POST['tweetcats'] ) ) {
				$user_settings[$this->tweetCats] = $_POST['tweetcats'];
			}
			
			update_user_option( $user_id, $this->options_name, $user_settings );
			
			if ( current_user_can( 'activate_plugins' ) ) { //we're dealing with the main Admin options
				if ( isset( $_POST['leenkme_tweetallusers'] ) ) {
					$leenkme_settings[$this->tweetAllUsers] = true;
				} else {
					$leenkme_settings[$this->tweetAllUsers] = false;
				}
				update_option( $this->options_name, $leenkme_settings );
			}
			
			// update settings notification ?>
			<div class="updated"><p><strong><?php _e( "Settings Updated.", "leenkme_Twitter" );?></strong></p></div>
			<?php
		}
		// Display HTML form for the options below
		?>
		<div class=wrap>
			<form id="leenkme" method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
				<h2>Twitter Settings</h2>
				<p>Tweet Format: <input name="leenkme_tweetformat" type="text" maxlength="140" style="width: 75%;" value="<?php _e( apply_filters( 'format_to_edit', htmlspecialchars( stripcslashes( $user_settings[$this->tweetFormat] ) ) ), 'leenkme_Twitter') ?>" /></p>
				<div class="tweet-format" style="margin-left: 50px;">
				<p style="font-size: 11px; margin-bottom: 0px;">Format Options:</p>
				<ul style="font-size: 11px;">
					<li>%TITLE% - Displays Title of your post in your Twitter feed.*</li>
					<li>%URL% - Displays TinyURL of your post in your Twitter feed.*</li>
				</ul>
				</div>
				<p>Tweet Categories: <input name="tweetcats" type="text" style="width: 25%;" value="<?php _e( apply_filters( 'format_to_edit', $user_settings[$this->tweetCats] ), 'leenkme_Twitter' ) ?>" /></p>
				<div class="tweet-cats" style="margin-left: 50px;">
				<p style="font-size: 11px; margin-bottom: 0px;">Tweet posts from several specific category IDs, e.g. 3,4,5<br />Tweet all posts except those from a category by prefixing its ID with a '-' (minus) sign, e.g. -3,-4,-5</p>
				</div>
				<?php if ( current_user_can( 'activate_plugins' ) ) { //then we're displaying the main Admin options ?>
				<p>Tweet All Authors? <input type="checkbox" name="leenkme_tweetallusers" <?php if ( $leenkme_settings[$this->tweetAllUsers] ) echo 'checked="checked"'; ?> /></p>
				<div class="tweet-allusers" style="margin-left: 50px;">
				<p style="font-size: 11px; margin-bottom: 0px;">Check this box if you want leenk.me to tweet to each available author account.</p>
				</div>
				<?php } ?>
				<p><input type="button" class="button" name="verify_twitter_connect" id="tweet" value="<?php _e( 'Send a Test Tweet', 'leenkme_Twitter' ) ?>" />
				<?php wp_nonce_field( 'tweet', 'tweet_wpnonce' ); ?></p>
				<p style="font-size: 11px; margin-top: 25px;">*NOTE: Twitter only allows a maximum of 140 characters per tweet. If your format is too long to accommodate %TITLE% and/or %URL% then this plugin will cut off your title to fit and/or remove the URL. URL is given preference (since it's either all or nothing). So if your TITLE ends up making your Tweet go over the 140 characters, it will take a substring of your title (plus some ellipsis).</p>
				
				<p class="submit">
					<input class="button-primary" type="submit" name="update_twitter_settings" value="<?php _e( 'Save Settings', 'leenkme_Twitter' ) ?>" />
				</p>
			</form>
		</div>
		<?php
	}
	
	function leenkme_twitter_meta_tags( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
			
		if ( isset( $_POST['leenkme_tweet'] ) && !empty( $_POST['leenkme_tweet'] ) ) {
			update_post_meta( $post_id, 'leenkme_tweet', $_POST['leenkme_tweet'] );
		} else {
			delete_post_meta( $post_id, 'leenkme_tweet' );
		}

		if ( isset( $_POST['twitter_exclude'] ) ) {
			update_post_meta( $post_id, 'twitter_exclude', $_POST['twitter_exclude'] );
		} else {
			delete_post_meta( $post_id, 'twitter_exclude' );
		}
	}
	
	function leenkme_add_twitter_meta_tag_options() {
		global $post;
		
		$tweet = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, 'leenkme_tweet', true ) ) );
		$exclude = get_post_meta( $post->ID, 'twitter_exclude', true ); ?>

		<div id="postlm" class="postbox">
		<h3><?php _e( 'leenk.me Twitter', 'leenkme' ) ?></h3>
		<div class="inside">
		<div id="postlm">
	
		<input value="twitter_edit" type="hidden" name="twitter_edit" />
		<table>
			<tr><td scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Tweet Format:', 'leenkme' ) ?></td>
			<td><input value="<?php echo $tweet ?>" type="text" name="leenkme_tweet" maxlength="140" size="80px"/></td></tr>
			
			
			<tr><td scope="row" style="text-align:right; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Exclude from Twitter:', 'leenkme' ) ?></td>
			<td>
				<input style="margin-top: 5px;" type="checkbox" name="twitter_exclude" <?php if ( $exclude ) echo 'checked="checked"'; ?> />
			</td></tr>
			<tr><td scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;">Format Options:</td>
			<td style="vertical-align:top; width:80px;">
				<ul>
					<li>%TITLE% - Displays Title of your post in your Twitter feed.*</li>
					<li>%URL% - Displays TinyURL of your post in your Twitter feed.*</li>
				</ul>
				<p><span style="font-weight:bold;">NOTE</span> Twitter only allows a maximum of 140 characters per tweet. If your format is too long to accommodate %TITLE% and/or %URL% then this plugin will cut off your title to fit and/or remove the URL. URL is given preference (since it's either all or nothing). So if your TITLE ends up making your Tweet go over the 140 characters, it will take a substring of your title (plus some ellipsis).</p>
            </td></tr>
			<?php // Only show ReTweet button if the post is "published"
            if ( "publish" === $post->post_status ) { ?>
            <tr><td colspan="2">
            <input style="float: right;" type="button" class="button" name="retweet_twitter" id="retweet_button" value="<?php _e( 'ReTweet', 'leenkme_Twitter' ) ?>" />
            <?php wp_nonce_field( 'retweet', 'retweet_wpnonce' ); ?>
            </td></tr>
            <?php } ?>
		</table>
		</div></div></div>
		<?php 
	}
}

if ( class_exists( "leenkme_Twitter" ) ) {
	$dl_pluginleenkmeTwitter = new leenkme_Twitter();
}

// Example followed from http://codex.wordpress.org/AJAX_in_Plugins
function leenkme_twitter_js() {
?>

		$('input#tweet').click(function() {
			var data = {
				action: 	'tweet',
				_wpnonce: 	$('input#tweet_wpnonce').val()
			};
			
			ajax_response(data);
		});
		
		$('input#retweet_button').click(function() {
			var data = {
				action: 	'retweet',
				id:  		$('input#post_ID').val(),
				_wpnonce: 	$('input#retweet_wpnonce').val()
			};
			
			ajax_response(data);
		});
		
		$('a.retweet_row_action').click(function() {
			var data = {
				action: 	'retweet',
				id:  		$(this).attr('id'),
				_wpnonce: 	$('input#retweet_wpnonce').val()
			};
			
			ajax_response(data);
		});
<?php
}

function leenkme_ajax_tweet() {
	check_ajax_referer( 'tweet' );
	global $current_user;
	get_currentuserinfo();
	$user_id = $current_user->ID;
	
	global $dl_pluginleenkme;
	$user_settings = $dl_pluginleenkme->get_user_settings( $user_id );
	if ( $api_key = $user_settings['leenkme_API'] ) {
		$tweet = "Testing @leenk_me's Twitter Plugin for #WordPress - http://leenk.me/ " . rand(10,99);
	
		$connect_arr[$api_key]['twitter_status'] = $tweet;
		
		$result = leenkme_ajax_connect( $connect_arr );
		
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

function leenkme_ajax_retweet() {
	check_ajax_referer( 'retweet' );
	
	if ( isset( $_POST['id'] ) ) {
		if ( get_post_meta( $_POST['id'], 'twitter_exclude', true ) ) {
			die( "You have excluded this post from publishing to your Twitter account. If you would like to publish it, edit the post and remove the exclude check box in the post settings." );
		} else {
			$post = get_post( $_POST['id'] );
			
			$result = leenkme_ajax_connect( leenkme_publish_to_twitter( array(), $post ) );
			
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

function retweet_row_action( $actions, $post ) {
	// Only show ReTweet button if the post is "published"
	if ( $post->post_status == "publish" ) {
		$actions['retweet'] = "<a class='retweet_row_action' id='" . $post->ID . "' title='" . esc_attr( __( 'ReTweet this Post' ) ) . "' href='#'>" . __( 'ReTweet' ) . "</a>" .
		wp_nonce_field( 'retweet', 'retweet_wpnonce' );
	}

	return $actions;
}
									
// Add function to pubslih to twitter
function leenkme_publish_to_twitter( $connect_arr = array(), $post ) {
	global $wpdb;
	$maxLen = 140;
	
	if ( get_post_meta( $post->ID, 'twitter_exclude', true ) ) {
		$exclude_twitter = true;
	} else {
		$exclude_twitter = false;
	}
	
	if ( !$exclude_twitter ) {
		// I've made an assumption that most users will include the %URL% text
		// So, instead of trying to get the link several times for multi-user setups
		// I'm getting the URL once and using it later --- for the sake of efficiency
		$plugins = get_option( 'active_plugins' );
		$required_plugin = 'twitter-friendly-links/twitter-friendly-links.php';
		//check to see if Twitter Friendly Links plugin is activated			
		if ( in_array( $required_plugin , $plugins ) ) {
			$url = permalink_to_twitter_link( get_permalink( $post->ID ) ); // if yes, we want to use that for our URL shortening service.
		} else {
			$url = leenkme_get_tinyurl( get_permalink( $post->ID ) ); //else use TinyURL's URL shortening service.
		}
		
		if ( 'post' === $post->post_type ) {
			$options = get_option( 'leenkme_twitter' );
			
			if ( $options['leenkme_tweetallusers'] ) {
				$user_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->users" ) );
			} else {
				$user_ids[] = $post->post_author;
			}
			
			foreach ( $user_ids as $user_id ) {
				$options = get_user_option( 'leenkme_twitter', $user_id );

				global $dl_pluginleenkme;
				$user_settings = $dl_pluginleenkme->get_user_settings( $user_id );
				if ( empty( $user_settings['leenkme_API'] ) ) {
					continue; //Skip user if they do not have an API key set
				} else {
					$api_key = $user_settings['leenkme_API'];
				}
				
				if( !empty( $options ) ) {					
					$continue = FALSE;
					if ( !empty( $options['tweetcats'] ) ) {
						$cats = split( ",", $options['tweetcats'] );
						foreach ( $cats as $cat ) {
							if ( preg_match( '/^-\d+/', $cat ) ) {
								$cat = preg_replace('/^-/', '', $cat);
								if ( in_category( (int)$cat, $post ) ) {
									continue; // Skip to next in foreach
								} else  {
									$continue = TRUE; // if not, than we can continue -- thanks Webmaster HC at hablacentro.com :)
								}
							} else if ( preg_match('/\d+/', $cat ) ) {
								if ( in_category( (int)$cat, $post ) ) {
									$continue = TRUE; // if  in an included category, set continue = TRUE.
								}
							}
						}
					} else { // If no includes or excludes are defined, then continue
						$continue = TRUE;
					}
					
					if ( !$continue ) continue; // Skip to next in foreach
					
					// Get META tweet format
					$tweet = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, 'leenkme_tweet', true ) ) );
					
					// If META tweet format is not set, use the default tweetformat set in options page(s)
					if ( !isset( $tweet ) || empty( $tweet ) ) {
						$tweet = htmlspecialchars( stripcslashes( $options['leenkme_tweetformat'] ) );
					}
					
					$tweetLen = strlen( $tweet );
					
					if ( preg_match( '/%URL%/i', $tweet ) ) {
						$urlLen = strlen( $url );
						$totalLen = $urlLen + $tweetLen - 5; // subtract 5 for "%URL%".
						
						if ( $totalLen <= $maxLen ) {
							$tweet = str_ireplace( "%URL%", $url, $tweet );
						} else {
							$tweet = str_ireplace( "%URL%", "", $tweet ); // Too Long (need to get rid of URL).
						}
					}
					
					$tweetLen = strlen( $tweet );
					
					if ( preg_match( '/%TITLE%/i', $tweet ) ) {
						$title = $post->post_title;
					
						$titleLen = strlen( $title ); 
						$totalLen = $titleLen + $tweetLen - 7;	// subtract 7 for "%TITLE%".
						
						if ( $totalLen <= $maxLen ) {
							$tweet = str_ireplace( "%TITLE%", $title, $tweet );
						} else {
							$diff = $maxLen - $totalLen;  // reversed because I need a negative number
							$newTitle = substr( $title, 0, $diff - 4 ); // subtract 1 for 0 based array and 3 more for adding an ellipsis
							$tweet = str_ireplace( "%TITLE%", $newTitle . "...", $tweet );
						}
					}

					if ( strlen( $tweet ) <= $maxLen ) {
						$connect_arr[$api_key]['twitter_status'] = $tweet;
					}
				}
			}
		}
		$wpdb->flush();
	}
		
	return $connect_arr;
}

// Example followed from http://planetozh.com/blog/2009/08/how-to-make-http-requests-with-wordpress/
function leenkme_get_tinyurl( $url ) { 
	$api_url = 'http://tinyurl.com/api-create.php?url=';
	$request = new WP_Http;
	$result = $request->request( $api_url . $url );
	
	if ( is_wp_error( $result ) ) { //if we get an error just us the normal permalink URL
		return $url;
	} else {
		return $result['body']; 
	}
}

// Actions and filters	
if ( isset( $dl_pluginleenkmeTwitter ) ) {
	/*--------------------------------------------------------------------
	    Actions
	  --------------------------------------------------------------------*/
	add_action( 'edit_form_advanced', array( $dl_pluginleenkmeTwitter, 'leenkme_add_twitter_meta_tag_options' ), 1 );
	add_action( 'save_post', array( $dl_pluginleenkmeTwitter, 'leenkme_twitter_meta_tags' ) );
	
	// Whenever you publish a post, post to twitter
	add_filter('leenkme_connect', 'leenkme_publish_to_twitter', 10, 2);
		  
	// Add jQuery & AJAX for leenk.me Test
	add_action( 'admin_head-leenk-me_page_leenkme_twitter', 'leenkme_js' );
	
	add_action( 'wp_ajax_tweet', 'leenkme_ajax_tweet' );
	add_action( 'wp_ajax_retweet', 'leenkme_ajax_retweet' );
	
	// edit-post.php post row update
	add_filter( 'post_row_actions', 'retweet_row_action', 10, 2 );
}
?>
