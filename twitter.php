<?php

if ( ! class_exists( 'leenkme_Twitter' ) ) {
	
	// Define class
	class leenkme_Twitter {
		
		// Class members		
		var $options_name			= 'leenkme_twitter';
		var $tweetFormat			= 'leenkme_tweetformat';
		var $tweetCats				= 'tweetcats';
		var $clude					= 'clude';
		var $tweetAllUsers			= 'leenkme_tweetallusers';
	
		// Constructor
		function leenkme_Twitter() {
			//Not Currently Needed
		}
		
		/*--------------------------------------------------------------------
			Administrative Functions
		  --------------------------------------------------------------------*/
		
		function get_leenkme_twitter_settings() {
			
			global $wpdb;
			
			$user_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ID) FROM ' . $wpdb->users ) );
			
			if ( 1 < $user_count )
				$tweetAllUsers = true;
			else
				$tweetAllUsers = false;
			
			$options = array( $this->tweetAllUsers => $tweetAllUsers );
		
			$leenkme_twitter_settings = get_option( $this->options_name );
			if ( !empty( $leenkme_twitter_settings ) ) {
				
				foreach ( $leenkme_twitter_settings as $key => $option ) {
					
					$options[$key] = $option;
					
				}
				
			}
			
			return $options;
		}
	  
		// Option loader function
		function get_user_settings( $user_id ) {
			
			// Default values for the options
			$tweetFormat 		= 'Blogged %TITLE%: %URL%';
			$tweetCats		 	= array( '0' );
			$clude				= 'in';
			
			$options = array(
								 $this->tweetFormat 		=> $tweetFormat,
								 $this->tweetCats 			=> $tweetCats,
								 $this->clude	 			=> $clude
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
			global $dl_pluginleenkme;
			
			global $current_user;
			get_currentuserinfo();
			$user_id = $current_user->ID;
			
			// Get the user options
			$user_settings = $this->get_user_settings( $user_id );
			$twitter_settings = $this->get_leenkme_twitter_settings();
			
			if ( isset( $_POST['update_twitter_settings'] ) ) {		
				
				if ( isset( $_POST['leenkme_tweetformat'] ) )
					$user_settings[$this->tweetFormat] = $_POST['leenkme_tweetformat'];
				
				if ( isset( $_POST['clude'] ) && isset( $_POST['tweetcats'] ) ) {
					
					$user_settings[$this->clude] = $_POST['clude'];
					$user_settings[$this->tweetCats] = $_POST['tweetcats'];
					
				} else {
					
					$user_settings[$this->clude] = 'in';
					$user_settings[$this->tweetCats] = array( '0' );
					
				}
				
				update_user_option( $user_id, $this->options_name, $user_settings );
				
				if ( current_user_can( 'leenkme_manage_all_settings' ) ) { //we're dealing with the main Admin options
				
					if ( isset( $_POST['leenkme_tweetallusers'] ) )
						$twitter_settings[$this->tweetAllUsers] = true;
					else
						$twitter_settings[$this->tweetAllUsers] = false;
						
					update_option( $this->options_name, $twitter_settings );
					
				}
				
				// update settings notification ?>
				<div class="updated"><p><strong><?php _e( 'Settings Updated.', 'leenkme_Twitter' );?></strong></p></div>
				<?php
			}
			// Display HTML form for the options below
			?>
			<div class=wrap>
            <div style="width:70%;" class="postbox-container">
            <div class="metabox-holder">	
            <div class="meta-box-sortables ui-sortable">
				<form id="leenkme" method="post" action="">
					<h2 style='margin-bottom: 10px;' ><img src='<?php echo $dl_pluginleenkme->base_url; ?>/leenkme-logo-32x32.png' style='vertical-align: top;' /> Twitter Settings (<a href="http://leenk.me/2010/09/04/how-to-use-the-leenk-me-twitter-plugin-for-wordpress/" target="_blank">help</a>)</h2>
                    <div id="post-types" class="postbox">
                    
                        <div class="handlediv" title="Click to toggle"><br /></div>
                        <h3 class="hndle"><span><?php _e( 'Message Settings' ); ?></span></h3>
                        
                        <div class="inside">
                            <p>Tweet Format: <input name="leenkme_tweetformat" type="text" maxlength="140" style="width: 75%;" value="<?php _e( htmlspecialchars( stripcslashes( $user_settings[$this->tweetFormat] ) ), 'leenkme_Twitter') ?>" /></p>
                            
                            <p style="font-size: 11px;;">Format Options:</p>
                            <ul style="font-size: 11px; margin-left: 50px;">
                                <li>%TITLE% - Displays Title of your post in your Twitter feed.*</li>
                                <li>%URL% - Displays TinyURL of your post in your Twitter feed.*</li>
                                <li>%CATS% - Displays the categories of your post in your Twitter feed as a hashtag.*</li>
                                <li>%TAGS% - Displays ags your post in your Twitter feed as a hashtag.*</li>
                            </ul>
                            
							<p style="font-size: 11px; margin-top: 25px;">*NOTE: Twitter only allows a maximum of 140 characters per tweet. If your format is too long to accommodate %TITLE% and/or %URL% then this plugin will cut off your title to fit and/or remove the URL. URL is given preference (since it's either all or nothing). So if your TITLE ends up making your Tweet go over the 140 characters, it will take a substring of your title (plus some ellipsis). If you use the %CATS% or %TAGS% variable, categories are given priority, it will display every category that will fit within the tweet length limitation. After adding the categories leenk.me moves onto tags and will add every tag that will fit within the tweet length limitation. leenk.me will also strip out any non-word character from the Twitter "hashtag" a single word.</p>
					
                    
							<p>
								<input type="button" class="button" name="verify_twitter_connect" id="tweet" value="<?php _e( 'Send a Test Tweet', 'leenkme_Twitter' ) ?>" />
								<?php wp_nonce_field( 'tweet', 'tweet_wpnonce' ); ?>
                                
                                <input class="button-primary" type="submit" name="update_twitter_settings" value="<?php _e( 'Save Settings', 'leenkme_Twitter' ) ?>" />
                            </p>
                        
                        </div>
                    </div>
				   
					<div id="post-types" class="postbox">
                    
                        <div class="handlediv" title="Click to toggle"><br /></div>
                        <h3 class="hndle"><span><?php _e( 'Publish Settings' ); ?></span></h3>
                        
                        <div class="inside">
                        <p>Tweet Categories:</p>
                        
                        <div class="tweet-cats" style="margin-left: 50px;">
                            <p>
                            <input type='radio' name='clude' id='include_cat' value='in' <?php checked( 'in', $user_settings[$this->clude] ); ?> /><label for='include_cat'>Include</label> &nbsp; &nbsp; <input type='radio' name='clude' id='exclude_cat' value='ex' <?php checked( 'ex', $user_settings[$this->clude] ); ?> /><label for='exclude_cat'>Exclude</label> </p>
                            
                            <p>
                            <select id='categories' name='tweetcats[]' multiple="multiple" size="5" style="height: 70px; width: 150px;">
                                <option value="0" <?php selected( in_array( "0", (array)$user_settings[$this->tweetCats] ) ); ?>>All Categories</option>
                            <?php 
                            $categories = get_categories( array( 'hide_empty' => 0, 'orderby' => 'name' ) );
                            foreach ( (array)$categories as $category ) {
                                ?>
                                
                                <option value="<?php echo $category->term_id; ?>" <?php selected( in_array( $category->term_id, (array)$user_settings[$this->tweetCats] ) ); ?>><?php echo $category->name; ?></option>
            
            
                                <?php
                            }
                            ?>
                            </select></p>
                            
                            <p style="font-size: 11px; margin-bottom: 0px;">To 'deselect' hold the SHIFT key on your keyboard while you click the category.</p>
                        </div>
                        <?php if ( current_user_can( 'leenkme_manage_all_settings' ) ) { //then we're displaying the main Admin options ?>
                        <p>Tweet All Authors? <input type="checkbox" name="leenkme_tweetallusers" <?php checked( $twitter_settings[$this->tweetAllUsers] ); ?> /></p>
                        <div class="tweet-allusers" style="margin-left: 50px;">
                        <p style="font-size: 11px; margin-bottom: 0px;">Check this box if you want leenk.me to tweet to each available author account.</p>
                        </div>
                        <?php } ?>
                        
                        <p>
                            <input class="button-primary" type="submit" name="update_twitter_settings" value="<?php _e( 'Save Settings', 'leenkme_Twitter' ) ?>" />
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
		
		function leenkme_twitter_meta_tags( $post_id ) {
			
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return;
				
			if ( isset( $_REQUEST['_inline_edit'] ) )
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
			global $dl_pluginleenkme;
			
			$leenkme_settings = $dl_pluginleenkme->get_leenkme_settings();
			foreach ( $leenkme_settings['post_types'] as $post_type ) {
				
				add_meta_box( 
					'leenkme-Twitter',
					__( 'leenk.me Twitter', 'leenkme' ),
					array( $this, 'leenkme_twitter_meta_box' ),
					$post_type 
				);
				
			}
			
		}
		
		function leenkme_twitter_meta_box() {
			global $post;
			
			$tweet = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, 'leenkme_tweet', true ) ) );
			$exclude = get_post_meta( $post->ID, 'twitter_exclude', true ); ?>
		
			<input value="twitter_edit" type="hidden" name="twitter_edit" />
			<table>
				<tr><td scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;"><?php _e( 'Format Options:', 'leenkme' ) ?></td>
				<td style="vertical-align:top; width:80px;">
					<p>%TITLE%, %URL%, %CATS%, %TAGS%</p>
				</td></tr>
				<tr><td scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Tweet Format:', 'leenkme' ) ?></td>
				<td><input value="<?php echo $tweet ?>" type="text" name="leenkme_tweet" maxlength="140" size="80px"/></td></tr>
				<tr><td scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;"></td>
				  <td style="vertical-align:top; width:80px;">
					<p><span style="font-weight:bold;">NOTE</span> Twitter limits the tweet to 140 characters.</p>
				</td></tr>
				<tr><td scope="row" style="text-align:right; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Exclude from Twitter:', 'leenkme' ) ?></td>
				<td>
					<input style="margin-top: 5px;" type="checkbox" name="twitter_exclude" <?php checked( $exclude || "on" == $exclude ); ?> />
				</td></tr>
				<?php // Only show ReTweet button if the post is "published"
				if ( "publish" === $post->post_status ) { ?>
				<tr><td colspan="2">
				<input style="float: right;" type="button" class="button" name="retweet_twitter" id="retweet_button" value="<?php _e( 'ReTweet', 'leenkme_Twitter' ) ?>" />
				</td></tr>
				<?php } ?>
			</table>
		
		<?php

		}

	}

}

if ( class_exists( 'leenkme_Twitter' ) ) {
	$dl_pluginleenkmeTwitter = new leenkme_Twitter();
}

// Example followed from http://codex.wordpress.org/AJAX_in_Plugins
function leenkme_twitter_js() {
?>

		$('input#tweet').live('click', function() {
			var data = {
				action: 	'tweet',
				_wpnonce: 	$('input#tweet_wpnonce').val()
			};
			
			ajax_response(data);
		});
		
		$('input#retweet_button').live('click', function() {
			var data = {
				action: 	'retweet',
				id:  		$('input#post_ID').val(),
				_wpnonce: 	$('input#leenkme_wpnonce').val()
			};
            
			ajax_response(data);
		});
		
		$('a.retweet_row_action').live('click', function() {
			var data = {
				action: 	'retweet',
				id:  		$(this).attr('id'),
				_wpnonce: 	$('input#leenkme_wpnonce').val()
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

function leenkme_ajax_retweet() {
	
	check_ajax_referer( 'leenkme' );
	
	if ( isset( $_POST['id'] ) ) {
		
		if ( get_post_meta( $_POST['id'], 'twitter_exclude', true ) ) {
		
			die( 'You have excluded this post from publishing to your Twitter account. If you would like to publish it, edit the post and remove the exclude check box in the post settings.' );
		
		} else {
			
			$post = get_post( $_POST['id'] );
			
			$results = leenkme_ajax_connect( leenkme_publish_to_twitter( array(), $post, true ) );
			
			if ( isset( $results ) ) {		
				
				foreach( $results as $result ) {	
		
					if ( is_wp_error( $result ) ) {
		
						$out[] = "<p>" . $result->get_error_message() . "</p>";
		
					} else if ( isset( $result['response']['code'] ) ) {
		
						$out[] = "<p>" . $result['body'] . "</p>";
		
					} else {
		
						$out[] = "<p>" . __( 'Error received! Please check your <a href="admin.php?page=leenkme_twitter">Twitter settings</a> and try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' ) . "</p>";
		
					}
		
				}
				
				die( join( $out ) );
				
			} else {
				
				die( __( 'ERROR: Unknown error, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' ) );

			}
			
		}
		
	} else {
		
		die( __( 'ERROR: Unable to determine Post ID.' ) );
	
	}

}

function retweet_row_action( $actions, $post ) {
	global $dl_pluginleenkme;
	$leenkme_options = $dl_pluginleenkme->get_leenkme_settings();
	if ( in_array( $post->post_type, $leenkme_options['post_types'] ) ) {
		// Only show ReTweet button if the post is "published"
		if ( 'publish' === $post->post_status ) {
			$actions['retweet'] = '<a class="retweet_row_action" id="' . $post->ID . '" title="' . esc_attr( __( 'ReTweet this Post' ) ) . '" href="#">' . __( 'ReTweet' ) . '</a>';
		}
	}

	return $actions;
}

// Add function to publish to twitter
function leenkme_publish_to_twitter( $connect_arr = array(), $post, $debug = false ) {
	global $wpdb, $dl_pluginleenkme, $dl_pluginleenkmeTwitter;
	$maxLen = 140;
	
	if ( get_post_meta( $post->ID, 'twitter_exclude', true ) )
		$exclude_twitter = true;
	else
		$exclude_twitter = false;
	
	if ( !$exclude_twitter ) {
		// I've made an assumption that most users will include the %URL% text
		// So, instead of trying to get the link several times for multi-user setups
		// I'm getting the URL once and using it later --- for the sake of efficiency
		$url = leenkme_get_shortened_url( get_permalink( $post->ID ), $post->ID ); //else use TinyURL's URL shortening service.
		
		$leenkme_settings = $dl_pluginleenkme->get_leenkme_settings();
		if ( in_array($post->post_type, $leenkme_settings['post_types'] ) ) {
			$options = get_option( 'leenkme_twitter' );
			
			if ( $options['leenkme_tweetallusers'] ) {
				
				$user_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->users ) );
				
			} else {
				
				$user_ids[] = $post->post_author;
				
			}
			
			foreach ( $user_ids as $user_id ) {
				
				$user_settings = $dl_pluginleenkme->get_user_settings( $user_id );
				
				if ( empty( $user_settings['leenkme_API'] ) ) {
					
					clean_user_cache( $user_id );
					continue; 	//Skip user if they do not have an API key set
					
				}
				
				$api_key = $user_settings['leenkme_API'];
				
				$options = $dl_pluginleenkmeTwitter->get_user_settings( $user_id );
				
				if ( !empty( $options ) ) {	
					
					if ( ( !empty( $options['tweetcats'] ) && isset( $options['clude'] ) )
							&& !( 'in' == $options['clude'] && in_array( '0', $options['tweetcats'] ) ) ) {
						
						if ( 'ex' == $options['clude'] && in_array( '0', (array)$options['tweetcats'] ) ) {
							
							if ( $debug ) echo "<p>You have your <a href='admin.php?page=leenkme_twitter'>Leenk.me Twitter settings</a> set to Exclude All Categories.</p>";
							clean_user_cache( $user_id );
							continue;
							
						}
						
						$match = false;
						
						$post_categories = wp_get_post_categories( $post->ID );
						
						foreach ( $post_categories as $cat ) {
						
							if ( in_array( (int)$cat, $options['tweetcats'] ) ) {
							
								$match = true;
								
							}
							
						}
						
						if ( ( 'ex' == $options['clude'] && $match ) ) {
							
							if ( $debug ) echo "<p>Post in an excluded category, check your <a href='admin.php?page=leenkme_twitter'>Leenk.me Twitter settings</a> or remove the post from the excluded category.</p>";
							clean_user_cache( $user_id );
							continue;
							
						} else if ( ( 'in' == $options['clude'] && !$match ) ) {
							
							if ( $debug ) echo "<p>Post not found in an included category, check your <a href='admin.php?page=leenkme_twitter'>Leenk.me Twitter settings</a> or add the post into the included category.</p>";
							clean_user_cache( $user_id );
							continue;
							
						}
					}
					
					// Get META tweet format
					$tweet = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, 'leenkme_tweet', true ) ) );
					
					// If META tweet format is not set, use the default tweetformat set in options page(s)
					if ( !isset( $tweet ) || empty( $tweet ) ) {
						
						$tweet = htmlspecialchars( stripcslashes( $options['leenkme_tweetformat'] ) );
						
					}
					
					if ( preg_match( '/%URL%/i', $tweet ) ) {
						
						$urlLen = strlen( $url );
						$tweetLen = strlen( utf8_decode( $tweet ) );
						$totalLen = $urlLen + $tweetLen - 5; // subtract 5 for "%URL%".
						
						if ( $totalLen <= $maxLen ) {
							
							$tweet = str_ireplace( "%URL%", $url, $tweet );
							
						} else {
							
							$tweet = str_ireplace( "%URL%", "", $tweet ); // Too Long (need to get rid of URL).
							
						}
						
					}
					
					if ( preg_match( '/%TITLE%/i', $tweet ) ) {
						
						$title = $post->post_title;
						$titleLen = strlen( utf8_decode( $title ) ); 
						$tweetLen = strlen( utf8_decode( $tweet ) );
						$totalLen = $titleLen + $tweetLen - 7;	// subtract 7 for "%TITLE%".
						
						if ( $totalLen <= $maxLen ) {
							
							$tweet = str_ireplace( "%TITLE%", $title, $tweet );
							
						} else {
							
							$diff = $maxLen - $totalLen;  // reversed because I need a negative number
							$newTitle =  utf8_encode( substr( utf8_decode( $newTitle ), 0, $diff ) );
							$tweet = str_ireplace( "%TITLE%", $newTitle, $tweet );
							
						}
						
					}
					
					if ( preg_match( '/%CATS%/i', $tweet ) ) {
						
						$post_categories = wp_get_post_categories( $post->ID );
						
						$cat_str = "";
						foreach($post_categories as $c){
							
							$cat = get_category( $c );
							$cat_str .= "#" . preg_replace( '/\W/', '', $cat->name ) . " ";
							
						}
						$cat_str = trim( $cat_str );
					
						$tweetLen = strlen( utf8_decode( $tweet ) );
						$catLen = strlen( utf8_decode( $cat_str ) );
						$totalLen = $catLen + $tweetLen - 6;	// subtract 5 for "%CATS%".
						
						if ( $totalLen > $maxLen ) {
							
							$split_cat_str = preg_split( '/\s/', $cat_str );
							
							while ( $totalLen > $maxLen ) {
								
								array_pop( $split_cat_str );
								
								$cat_str = join( ' ', (array)$split_cat_str );
								$catLen = strlen( utf8_decode( $cat_str ) );
								$totalLen = $catLen + $tweetLen - 6;	// subtract 5 for "%CATS%".

							}
							
						}
						
						$tweet = str_ireplace( "%CATS%", $cat_str, $tweet );
						
					}
					
					if ( preg_match( '/%TAGS%/i', $tweet ) ) {
						
						$post_tags = wp_get_post_tags( $post->ID );
						
						$tag_str = "";
						foreach($post_tags as $t){
							
							$tag = get_tag( $t );
							$tag_str .= "#" . preg_replace( '/\W/', '', $tag->name ) . " ";
							
						}
						$tag_str = trim( $tag_str );
					
						$tweetLen = strlen( utf8_decode( $tweet ) );
						$tagLen = strlen( utf8_decode( $tag_str ) );
						$totalLen = $tagLen + $tweetLen - 6;	// subtract 5 for "%CATS%".
						
						if ( $totalLen > $maxLen ) {
							
							$split_tag_str = preg_split( '/\s/', $tag_str );
							
							while ( $totalLen > $maxLen ) {
								
								array_pop( $split_tag_str );
								
								$tag_str = join( " ", (array)$split_tag_str );
								$tagLen = strlen( utf8_decode( $tag_str ) );
								$totalLen = $tagLen + $tweetLen - 6;	// subtract 5 for "%CATS%".
								
							}
							
						}
						
						$tweet = str_ireplace( "%TAGS%", $tag_str, $tweet );
						
					}

					if ( strlen( utf8_decode( $tweet ) ) <= $maxLen ) {
						
						$connect_arr[$api_key]['twitter_status'] = $tweet;
						
					}
					
				}
				
				clean_user_cache( $user_id );
				
			}
			
		}
		
		$wpdb->flush();
		
	}
		
	return $connect_arr;
	
}

/*
// Add function to publish comments to twitter
function leenkme_comment_to_twitter( $connect_arr = array(), $comment, $debug = false ) {
	global $wpdb, $dl_pluginleenkme, $dl_pluginleenkmeTwitter;
	$maxLen = 140;
	
	$post = get_post( $comment->comment_post_ID );
	
	if ( empty( $post ) )
		return $connect_arr;
	
	if ( get_post_meta( $post->ID, 'twitter_exclude', true ) )
		$exclude_twitter = true;
	else
		$exclude_twitter = false;
	
	if ( !$exclude_twitter ) {
		// I've made an assumption that most users will include the %URL% text
		// So, instead of trying to get the link several times for multi-user setups
		// I'm getting the URL once and using it later --- for the sake of efficiency
		$url = leenkme_get_shortened_url( get_comment_link( $comment->comment_ID ) ); //else use TinyURL's URL shortening service.
		
		$leenkme_settings = $dl_pluginleenkme->get_leenkme_settings();
		if ( in_array($post->post_type, $leenkme_settings['post_types'] ) ) {
			
			$options = get_option( 'leenkme_twitter' );
			
			if ( $options['leenkme_tweetallusers'] )
				$user_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->users ) );
			else
				$user_ids[] = $post->post_author;
			
			foreach ( $user_ids as $user_id ) {
				
				$user_settings = $dl_pluginleenkme->get_user_settings( $user_id );
				if ( empty( $user_settings['leenkme_API'] ) ) {
					
					clean_user_cache( $user_id );
					continue; 	//Skip user if they do not have an API key set
					
				}
				
				$api_key = $user_settings['leenkme_API'];
				
				$options = $dl_pluginleenkmeTwitter->get_user_settings( $user_id );
				
				if ( !empty( $options ) ) {	
					
					if ( ( !empty( $options['tweetcats'] ) && isset( $options['clude'] ) )
							&& !( 'in' == $options['clude'] && in_array( '0', $options['tweetcats'] ) ) ) {
						
						if ( 'ex' == $options['clude'] && in_array( '0', (array)$options['tweetcats'] ) ) {
							
							if ( $debug ) echo "<p>You have your <a href='admin.php?page=leenkme_twitter'>Leenk.me Twitter settings</a> set to Exclude All Categories.</p>";
							clean_user_cache( $user_id );
							continue;
							
						}
						
						$match = false;
						
						$post_categories = wp_get_post_categories( $post->ID );
						
						foreach ( $post_categories as $cat ) {
						
							if ( in_array( (int)$cat, $options['tweetcats'] ) ) {
							
								$match = true;
								
							}
							
						}
						
						if ( ( 'ex' == $options['clude'] && $match ) ) {
							
							if ( $debug ) echo "<p>Post in an excluded category, check your <a href='admin.php?page=leenkme_twitter'>Leenk.me Twitter settings</a> or remove the post from the excluded category.</p>";
							clean_user_cache( $user_id );
							continue;
							
						} else if ( ( 'in' == $options['clude'] && !$match ) ) {
							
							if ( $debug ) echo "<p>Post not found in an included category, check your <a href='admin.php?page=leenkme_twitter'>Leenk.me Twitter settings</a> or add the post into the included category.</p>";
							clean_user_cache( $user_id );
							continue;
							
						}
						
					}
					
					// Get META tweet format
					$tweet = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, 'leenkme_tweet_comment', true ) ) );
					$tweet = "%AUTHOR% said %COMMENT% on %TITLE% at %URL%";
					
					// If META tweet format is not set, use the default tweetformat set in options page(s)
					if ( !isset( $tweet ) || empty( $tweet ) ) {
						
						$tweet = htmlspecialchars( stripcslashes( $options['leenkme_tweetformat'] ) );
						
					}
					
					if ( preg_match( '/%URL%/i', $tweet ) ) {
						
						$urlLen = strlen( $url );
						$tweetLen = strlen( utf8_decode( $tweet ) );
						$totalLen = $urlLen + $tweetLen - 5; // subtract 5 for "%URL%".
						
						if ( $totalLen <= $maxLen ) {
							
							$tweet = str_ireplace( "%URL%", $url, $tweet );
							
						} else {
							
							$tweet = str_ireplace( "%URL%", "", $tweet ); // Too Long (need to get rid of URL).
							
						}
						
					}
					
					if ( preg_match( '/%AUTHOR%/i', $tweet ) ) {
						
						$author = $comment->comment_author;
						$authorLen = strlen( utf8_decode( $author ) ); 
						$tweetLen = strlen( utf8_decode( $tweet ) );
						$totalLen = $authorLen + $tweetLen - 8;	// subtract 7 for "%AUTHOR%".
						
						if ( $totalLen <= $maxLen ) {
							
							$tweet = str_ireplace( "%AUTHOR%", $author, $tweet );
							
						} else {
							
							$tweet = str_ireplace( "%AUTHOR%", "", $tweet );
							
						}
						
					}
					
					if ( preg_match( '/%COMMENT%/i', $tweet ) ) {
						
						$content = $comment->comment_content;
						$contentLen = strlen( utf8_decode( $content ) ); 
						$tweetLen = strlen( utf8_decode( $tweet ) );
						$totalLen = $contentLen + $tweetLen - 9;	// subtract 9 for "%COMMENT%".
						
						if ( $totalLen <= $maxLen ) {
							
							$tweet = str_ireplace( "%COMMENT%", $content, $tweet );
							
						} else {
							
							$diff = $maxLen - $totalLen;  // reversed because I need a negative number
							$newComment = substr( $content, 0, $diff - 4 ); // subtract 1 for 0 based array and 3 more for adding an ellipsis
							$tweet = str_ireplace( "%COMMENT%", $newComment . "...", $tweet );
							
						}
						
					}
					
					if ( preg_match( '/%TITLE%/i', $tweet ) ) {
						
						$title = $post->post_title;
						$titleLen = strlen( utf8_decode( $title ) ); 
						$tweetLen = strlen( utf8_decode( $tweet ) );
						$totalLen = $titleLen + $tweetLen - 7;	// subtract 7 for "%TITLE%".
						
						if ( $totalLen <= $maxLen ) {
							
							$tweet = str_ireplace( "%TITLE%", $title, $tweet );
							
						} else {
							
							$diff = $maxLen - $totalLen;  // reversed because I need a negative number
							$newTitle = substr( $title, 0, $diff - 4 ); // subtract 1 for 0 based array and 3 more for adding an ellipsis
							$tweet = str_ireplace( "%TITLE%", $newTitle . "...", $tweet );
							
						}
						
					}
					
					if ( preg_match( '/%CATS%/i', $tweet ) ) {
						
						$post_categories = wp_get_post_categories( $post->ID );
						
						$cat_str = "";
						
						foreach($post_categories as $c){
							
							$cat = get_category( $c );
							$cat_str .= "#" . preg_replace( '/\W/', '', $cat->name ) . " ";
							
						}
						
						$cat_str = trim( $cat_str );
					
						$tweetLen = strlen( utf8_decode( $tweet ) );
						$catLen = strlen( utf8_decode( $cat_str ) );
						$totalLen = $catLen + $tweetLen - 6;	// subtract 5 for "%CATS%".
						
						if ( $totalLen > $maxLen ) {
							
							$diff = $totalLen - $maxLen;
							
							$split_cat_str = preg_split( '/\s/', $cat_str );
							
							while ( $diff < $catLen ) {
								
								array_pop( $split_cat_str );
								
								$cat_str = join( ' ', (array)$split_cat_str );
								$catLen = strlen( utf8_decode( $cat_str ) );
								
							}
							
						}
						
						$tweet = str_ireplace( "%CATS%", $cat_str, $tweet );
						
					}
					
					if ( preg_match( '/%TAGS%/i', $tweet ) ) {
						
						$post_tags = wp_get_post_tags( $post->ID );
						
						$tag_str = "";
						
						foreach($post_tags as $t){
							
							$tag = get_tag( $t );
							$tag_str .= "#" . preg_replace( '/\W/', '', $tag->name ) . " ";
							
						}
						
						$tag_str = trim( $tag_str );
					
						$tweetLen = strlen( utf8_decode( $tweet ) );
						$tagLen = strlen( utf8_decode( $tag_str ) );
						$totalLen = $tagLen + $tweetLen - 6;	// subtract 5 for "%CATS%".
						
						if ( $totalLen > $maxLen ) {
							
							$diff = $totalLen - $maxLen;
							
							$split_tag_str = preg_split( '/\s/', $tag_str );
							
							while ( $diff < $tagLen ) {
								
								array_pop( $split_tag_str );
								
								$tag_str = join( " ", (array)$split_tag_str );
								$tagLen = strlen( utf8_decode( $tag_str ) );
								
							}
							
						}
						
						$tweet = str_ireplace( "%TAGS%", $tag_str, $tweet );
						
					}

					if ( strlen( utf8_decode( $tweet ) ) <= $maxLen ) {
						
						$connect_arr[$api_key]['twitter_status'] = $tweet;
						
					}
					
				}
				
				clean_user_cache( $user_id );
				
			}
			
		}
		
		$wpdb->flush();
		
	}
		
	return $connect_arr;
	
}
*/

// Example followed from http://planetozh.com/blog/2009/08/how-to-make-http-requests-with-wordpress/
function leenkme_get_shortened_url( $url, $post_id = null ) { 

	$plugins = get_option( 'active_plugins' );
	$required_plugin = 'twitter-friendly-links/twitter-friendly-links.php';
	
	//check to see if Twitter Friendly Links plugin is activated			
	if ( in_array( $required_plugin , $plugins ) ) {
		
		return permalink_to_twitter_link( $url ); // if yes, we want to use that for our URL shortening service.
		
	} else {
													
		$result = wp_remote_request( apply_filters( 'leenkme_url_shortener', 'http://tinyurl.com/api-create.php?url=' . urlencode( $url ), $url ) );
		
		if ( is_wp_error( $result ) && !is_null( $post_id ) ) { //if we get an error just us the normal permalink URL
		
			return home_url( '?p=' . $post_id );
		
		} else {
		
			return $result['body']; 
		
		}
	
	}

}

// Actions and filters	
if ( isset( $dl_pluginleenkmeTwitter ) ) {
	add_action( 'admin_init', array( $dl_pluginleenkmeTwitter, 'leenkme_add_twitter_meta_tag_options' ), 1 );
	add_action( 'save_post', array( $dl_pluginleenkmeTwitter, 'leenkme_twitter_meta_tags' ) );
	
	// Whenever you publish a post, post to twitter
	add_filter( 'leenkme_connect', 'leenkme_publish_to_twitter', 10, 2 );
	//add_filter( 'leenkme_comment_connect', 'leenkme_comment_to_twitter', 10, 2 );
		  
	// Add jQuery & AJAX for leenk.me Test
	add_action( 'admin_head-leenk-me_page_leenkme_twitter', 'leenkme_js' );
	
	add_action( 'wp_ajax_tweet', 'leenkme_ajax_tweet' );
	add_action( 'wp_ajax_retweet', 'leenkme_ajax_retweet' );
	
	// edit-post.php post row update
	add_filter( 'post_row_actions', 'retweet_row_action', 10, 2 );
	add_filter( 'page_row_actions', 'retweet_row_action', 10, 2 );
}