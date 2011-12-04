<?php	

if ( ! class_exists( 'leenkme_LinkedIn' ) ) {
		
	// Define class
	class leenkme_LinkedIn {
	
		// Constructor
		function leenkme_LinkedIn() {
			//Not Currently Needed
		}
		
		/*--------------------------------------------------------------------
			Administrative Functions
		  --------------------------------------------------------------------*/
		
		// Option loader function
		function get_user_settings( $user_id ) {
			
			// Default values for the options
			$options = array(
								 'linkedin_comment'		=> '%TITLE%',
								 'linkedin_title'		=> '%WPSITENAME%',
								 'linkedin_description'	=> '%EXCERPT%',
								 'default_image' 		=> '',
								 'force_linkedin_image' => false,
								 'share_cats'			=> array( '0' ),
								 'clude'				=> 'in'
							);
							
			// Get values from the WP options table in the database, re-assign if found
			$user_settings = get_user_option( 'leenkme_linkedin', $user_id );
			if ( !empty( $user_settings ) ) {
				
				foreach ( $user_settings as $key => $option ) {
					
					$options[$key] = $option;
					
				}
				
			}
			
			// Need this for initial INIT, for people who don't save the default settings...
			update_user_option( $user_id, 'leenkme_linkedin', $user_settings );
			
			return $options;
			
		}
		
		// Print the admin page for the plugin
		function print_linkedin_settings_page() {
			global $dl_pluginleenkme, $current_user;
			
			get_currentuserinfo();
			$user_id = $current_user->ID;
			
			// Get the user options
			$user_settings = $this->get_user_settings( $user_id );
			$linkedin_settings = get_option( 'leenkme_linkedin' );
			
			if ( isset( $_POST['update_linkedin_settings'] ) ) {
				
				if ( isset( $_POST['linkedin_comment'] ) )
					$user_settings['linkedin_comment'] = $_POST['linkedin_comment'];
	
				if ( isset( $_POST['linkedin_title'] ) )
					$user_settings['linkedin_title'] = $_POST['linkedin_title'];
	
				if ( isset( $_POST['linkedin_description'] ) )
					$user_settings['linkedin_description'] = $_POST['linkedin_description'];
				
				if ( isset( $_POST['default_image'] ) )
					$user_settings['default_image'] = $_POST['default_image'];
				
				if ( isset( $_POST['force_linkedin_image'] ) )
					$user_settings['force_linkedin_image'] = true;
				else
					$user_settings['force_linkedin_image'] = false;
	
				if ( isset( $_POST['clude'] ) && isset( $_POST['share_cats'] ) ) {
					
					$user_settings['clude'] = $_POST['clude'];
					$user_settings['share_cats'] = $_POST['share_cats'];
					
				} else {
					
					$user_settings['clude'] = 'in';
					$user_settings['share_cats'] = array( '0' );
					
				}
				
				update_user_option($user_id, 'leenkme', $user_settings);
				
				// update settings notification ?>
				<div class="updated"><p><strong><?php _e( 'Settings Updated.', 'leenkme' );?></strong></p></div>
				<?php
			}
			
			// Display HTML form for the options below
			?>
			<div class=wrap>
			<div style="width:70%;" class="postbox-container">
			<div class="metabox-holder">	
			<div class="meta-box-sortables ui-sortable">
				<form id="leenkme" method="post" action="">
					<h2 style='margin-bottom: 10px;' ><img src='<?php echo $dl_pluginleenkme->base_url; ?>/leenkme-logo-32x32.png' style='vertical-align: top;' /> LinkedIn <?php _e( 'Settings', 'leenkme' ); ?> (<a href="http://leenk.me/2010/12/01/how-to-use-the-leenk-me-linkedin-plugin-for-wordpress/" target="_blank"><?php _e( 'help', 'leenkme' ); ?></a>)</h2>
                    
					<div id="post-types" class="postbox">
					
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span><?php _e( 'Message Settings' ); ?></span></h3>
						
						<div class="inside">
                        	<table id="linkedin_settings_table">
                            <tr>
                            	<td><?php _e( 'Default Comment:', 'leenkme' ); ?></td>
                                <td><textarea name="linkedin_comment" style="width: 500px;" maxlength="700"><?php echo $user_settings['linkedin_comment']; ?></textarea></td>
                            </tr>
                            <tr>
                            	<td><?php _e( 'Default Link Name:', 'leenkme' ); ?></td>
                                <td><input name="linkedin_title" type="text" style="width: 500px;" value="<?php echo $user_settings['linkedin_title']; ?>" maxlength="200"/></td>
                            </tr>
                            <tr>
                            	<td style='vertical-align: top; padding-top: 5px;'><?php _e( 'Default Description:', 'leenkme' ); ?></td>
                                <td><textarea name="facebook_description" style="width: 500px;" maxlength="256"><?php echo $user_settings['linkedin_description']; ?></textarea></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="linkedin-format" style="margin-left: 50px;">
                                    <p style="font-size: 11px; margin-bottom: 0px;"><?php _e( 'Format Options:', 'leenkme' ); ?></p>
                                    <ul style="font-size: 11px;">
                                        <li>%TITLE% - <?php _e( 'Displays the post title.', 'leenkme' ); ?></li>
                                        <li>%WPSITENAME% - <?php _e( 'Displays the WordPress site name (found in Settings -> General).', 'leenkme' ); ?></li>
                                        <li>%WPTAGLINE% - <?php _e( 'Displays the WordPress TagLine (found in Settings -> General).', 'leenkme' ); ?></li>
                                        <li>%EXCERPT% - <?php _e( 'Displays the WordPress Post Excerpt (only used with Description Field).', 'leenkme' ); ?></li>
                                    </ul>
                                    </div>
                            	</td>
                            </tr>
                            <tr>
                            	<td><?php _e( 'Default Image URL:', 'leenkme' ); ?></td>
                                <td>
                                    <input name="default_image" type="text" style="width: 500px;" value="<?php _e( $user_settings['default_image'], 'leenkme' ) ?>" />
                                    <input type="checkbox" id="force_linkedin_image" name="force_linkedin_image" <?php checked( $user_settings['force_linkedin_image'] ); ?> /> <?php _e( 'Always use', 'leenkme' ); ?>
                                </td>
                            </tr> 
                            </table>
                            
                            <p>
                                <input type="button" class="button" name="verify_linkedin_connect" id="li_share" value="<?php _e( 'Share a Test Message', 'leenkme' ) ?>" />
                                <?php wp_nonce_field( 'li_share', 'li_share_wpnonce' ); ?>
                            
                                <input class="button-primary" type="submit" name="update_linkedin_settings" value="<?php _e( 'Save Settings', 'leenkme' ) ?>" />
                            </p>
                    
                        </div>
                    
                    </div>
                       
					<div id="post-types" class="postbox">
					
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span><?php _e( 'Publish Settings', 'leenkme' ); ?></span></h3>
						
						<div class="inside">
						<p><?php _e( 'Share Categories:', 'leenkme' ); ?></p>
					
						<div class="share-cats" style="margin-left: 50px;">
						<p>
						<input type='radio' name='clude' id='include_cat' value='in' <?php checked( 'in', $user_settings['clude'] ); ?> /><label for='include_cat'><?php _e( 'Include', 'leenkme' ); ?></label> &nbsp; &nbsp; <input type='radio' name='clude' id='exclude_cat' value='ex' <?php checked( 'ex', $user_settings['clude'] ); ?> /><label for='exclude_cat'><?php _e( 'Exclude', 'leenkme' ); ?></label> </p>
						<p>
						<select id='categories' name='share_cats[]' multiple="multiple" size="5" style="height: 70px; width: 150px;">
							<option value="0" <?php selected( in_array( "0", (array)$user_settings['share_cats'] ) ); ?>><?php _e( 'All Categories', 'leenkme' ); ?></option>
						<?php 
						$categories = get_categories( array( 'hide_empty' => 0, 'orderby' => 'name' ) );
						
						foreach ( (array)$categories as $category ) {
							?>
							
							<option value="<?php echo $category->term_id; ?>" <?php selected( in_array( $category->term_id, (array)$user_settings['share_cats'] ) ); ?>><?php echo $category->name; ?></option>
		
		
							<?php
						}
						?>
                        
						</select></p>
						<p style="font-size: 11px; margin-bottom: 0px;"><?php _e( 'To "deselect" hold the SHIFT key on your keyboard while you click the category.', 'leenkme' ); ?></p>
						
						</div>
                        
                        <p>
                            <input class="button-primary" type="submit" name="update_linkedin_settings" value="<?php _e( 'Save Settings', 'leenkme' ) ?>" />
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
		
		function leenkme_linkedin_meta_tags( $post_id ) {
			
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return;
				
			if ( isset( $_REQUEST['_inline_edit'] ) )
				return;
	
			if ( isset( $_POST['linkedin_exclude'] ) )
				update_post_meta( $post_id, '_linkedin_exclude', $_POST['linkedin_exclude'] );
			else
				delete_post_meta( $post_id, '_linkedin_exclude' );
			
			if ( isset( $_POST['linkedin_comment'] ) && !empty( $_POST['linkedin_comment'] ) )
				update_post_meta( $post_id, '_linkedin_comment', $_POST['linkedin_comment'] );
			else
				delete_post_meta( $post_id, '_linkedin_comment' );
			
			if ( isset( $_POST['linkedin_title'] ) && !empty( $_POST['linkedin_title'] ) )
				update_post_meta( $post_id, '_linkedin_title', $_POST['linkedin_title'] );
			else
				delete_post_meta( $post_id, '_linkedin_title' );
			
			if ( isset( $_POST['linkedin_description'] ) && !empty( $_POST['linkedin_description'] ) )
				update_post_meta( $post_id, '_linkedin_description', $_POST['linkedin_description'] );
			else
				delete_post_meta( $post_id, '_linkedin_description' );
	
			if ( isset($_POST["linkedin_image"] ) && !empty( $_POST["linkedin_image"] ) )
				update_post_meta( $post_id, '_linkedin_image', $_POST["linkedin_image"] );
			else
				delete_post_meta( $post_id, '_linkedin_image' );
	
			if ( isset($_POST["lm_linkedin_type"] ) && !empty( $_POST["lm_linkedin_type"] ) )
				update_post_meta( $post_id, '_lm_linkedin_type', $_POST["lm_linkedin_type"] );
			else
				delete_post_meta( $post_id, '_lm_linkedin_type' );
				
		}
		
		function leenkme_linkedin_meta_box()  {
			
			global $post, $current_user;
		
			get_currentuserinfo();
			$user_id = $current_user->ID;
			
			if ( $linkedin_exclude = get_post_meta( $post->ID, 'linkedin_exclude', true ) ) {
				
				delete_post_meta( $post->ID, 'linkedin_exclude', true );
				update_post_meta( $post->ID, '_linkedin_exclude', $exclude_group );
				
				
			}
			$linkedin_exclude = get_post_meta( $post->ID, 'linkedin_exclude', true ); 
			
			if ( $linkedin_array['comment'] = get_post_meta( $post->ID, 'linkedin_comment', true ) ) {
				
				delete_post_meta( $post->ID, 'linkedin_comment', true );
				update_post_meta( $post->ID, '_linkedin_comment', $linkedin_array['comment'] );
				
				
			}
			$linkedin_array['comment'] = get_post_meta( $post->ID, '_linkedin_comment', true);
			
			if ( $linkedin_array['linktitle'] = get_post_meta( $post->ID, 'linkedin_title', true ) ) {
				
				delete_post_meta( $post->ID, 'linkedin_title', true );
				update_post_meta( $post->ID, '_linkedin_title', $linkedin_array['linktitle'] );
				
				
			}
			$linkedin_array['linktitle'] = get_post_meta( $post->ID, '_linkedin_title', true);
			
			if ( $linkedin_array['description'] = get_post_meta( $post->ID, 'linkedin_description', true ) ) {
				
				delete_post_meta( $post->ID, 'linkedin_description', true );
				update_post_meta( $post->ID, '_linkedin_description', $linkedin_array['description'] );
				
				
			}
			$linkedin_array['description'] = get_post_meta( $post->ID, '_linkedin_description', true);
			
			if ( $linkedin_array['picture'] = get_post_meta( $post->ID, 'linkedin_image', true ) ) {
				
				delete_post_meta( $post->ID, 'linkedin_image', true );
				update_post_meta( $post->ID, '_linkedin_image', $linkedin_array['picture'] );
				
				
			}
			$linkedin_array['picture'] = get_post_meta( $post->ID, '_linkedin_image', true );
			
			$format_type = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, '_lm_linkedin_type', true ) ) );
			
			$user_settings = $this->get_user_settings( $user_id ); ?>
    
    		<div id="li_format_options">
				<?php 
                _e( 'Format:', 'leenkme' );
                echo " ";
                ?>
                    
                <span id="lm_linkedin_format" class="li_manual_format manual_format" style="display:<?php if ( $format_type ) echo "inline"; else echo "none"; ?>"><?php _e( 'Manual', 'leenkme' ); ?></span> <a id="set_to_default_li_post" href="#" style="display:<?php if ( $format_type ) echo "inline"; else echo "none"; ?>">Reset</a>
                <span id="lm_linkedin_format" class="li_default_format default_format" style="display:<?php if ( $format_type ) echo "none"; else echo "inline"; ?>"><?php _e( 'Default', 'leenkme' ); ?></span>
                <input type="hidden" name="lm_linkedin_type" value="<?php echo $format_type; ?>" />
                <input type="hidden" name="linkedin_comment_format" value="<?php echo $user_settings['linkedin_comment']; ?>" />
                <input type="hidden" name="linkedin_linktitle_format" value="<?php echo $user_settings['linkedin_title']; ?>" />
                <input type="hidden" name="linkedin_description_format" value="<?php echo $user_settings['linkedin_description']; ?>" />
                <input type="hidden" name="linkedin_image" value="<?php echo $linkedin_array['picture'] ?>" />
            </div>
            
            <div id="lm_linkedin_box">
            
            	<?php 
				if ( 0 == $format_type ) {
				
					 $linkedin_array['comment'] 		= $user_settings['linkedin_comment'];
					 $linkedin_array['linktitle'] 		= $user_settings['linkedin_title'];
					 $linkedin_array['description']		= $user_settings['linkedin_description'];
				
				}
				
				$linkedin_content = get_leenkme_expanded_li_post( $post->ID, $linkedin_array ); ?>
            
                <textarea id="lm_li_comment" name="linkedin_comment" maxlength="700"><?php echo $linkedin_content['comment']; ?></textarea>
            
                <div id="lm_li_attachment_meta_area">
                
                	<div id="lm_li_image">
                		<img id='lm_li_image_src' src='<?php echo $linkedin_content['picture']; ?>' />
                    </div>
            
                    <div id="lm_li_content_area">
                        <input id="lm_li_linktitle" value="<?php echo $linkedin_content['linktitle']; ?>" type="text" name="linkedin_title" maxlength="200" />
            	<!--
                        <p id="lm_li_caption"><?php echo $_SERVER['HTTP_HOST']; ?></p>
                -->
                        <textarea id="lm_li_description" name="linkedin_description" maxlength="256"><?php echo $linkedin_content['description']; ?></textarea>
                    </div>
                
                </div>
                
            </div>
            
            <div id="lm_linkedin_options">
            
            	<div id="lm_li_exlusions">
                    <?php _e( 'Exclude from LinkedIn:', 'leenkme' ) ?>
                    <input type="checkbox" name="linkedin_exclude" <?php checked( $linkedin_exclude || "on" == $linkedin_exclude ); ?> />
                </div>
                
                <div id="lm_li_reshare">
					<?php // Only show RePublish button if the post is "published"
                    if ( 'publish' === $post->post_status ) { ?>
                    <input style="float: right;" type="button" class="button" name="reshare_linkedin" id="lm_reshare_button" value="<?php _e( 'ReShare', 'leenkme' ) ?>" />
                    <?php } ?>
                </div>
                
            </div>
		
			<input value="linkedin_edit" type="hidden" name="linkedin_edit" />
			<?php 
			
		}
		
	}

}

if ( class_exists( 'leenkme_LinkedIn' ) ) {
	$dl_pluginleenkmeLinkedIn = new leenkme_LinkedIn();
}

function get_leenkme_expanded_li_post( $post_id, $linkedin_array, $post_title = false, $excerpt = false ) {
	
	if ( !empty( $linkedin_array ) ) {

		global $current_user, $dl_pluginleenkmeLinkedIn;
		
		get_currentuserinfo();
		$user_id = $current_user->ID;

		$maxCommentLen = 700;
		$maxLinkNameLen = 200;
		$maxDescLen = 256;

		if ( false === $post_title )
			$post_title = get_the_title( $post_id );
			
		$wp_sitename = strip_tags( get_bloginfo( 'name' ) );
		$wp_tagline = strip_tags( get_bloginfo( 'description' ) );
	
		if ( false === $excerpt ) {
			
			$post = get_post( $post_id );
		
			if ( !empty( $post->post_excerpt ) ) {
				
				//use the post_excerpt if available for the facebook description
				$excerpt = $post->post_excerpt; 
				
			} else {
				
				//otherwise we'll pare down the description
				$excerpt = $post->post_content; 
				
			}
			
		}
		
		$linkedin_array['comment'] 		= leenkme_trim_words( leenkme_replacements_args( $linkedin_array['comment'] , $post_title, $excerpt ), $maxCommentLen );
		$linkedin_array['linktitle'] 	= leenkme_trim_words( leenkme_replacements_args( $linkedin_array['linktitle'], $post_title, $excerpt ), $maxLinkNameLen );
		$linkedin_array['description'] 	= leenkme_trim_words( leenkme_replacements_args( $linkedin_array['description'], $post_title, $excerpt, true ), $maxDescLen );
		
		$user_settings = $dl_pluginleenkmeLinkedIn->get_user_settings( $user_id );
			
		$linkedin_array['picture'] = leenkme_get_picture( $user_settings, $post_id, 'linkedin' );
	
	}
	
	return $linkedin_array;

}

function get_leenkme_expanded_li_post_ajax() {
	
	if ( isset( $_POST['post_id'] ) )
		$post_id = $_POST['post_id'];
	else
		die( __( 'Error: Unable to determine post ID', 'leenkme' ) );
		
	if ( isset( $_POST['linkedin_array'] ) )
		$linkedin_array = $_POST['linkedin_array'];
	else
		die( __( 'Error: Unable to deteremine default Facebook settings', 'leenkme' ) );
	
	if ( isset( $_POST['title'] ) )
		$title = $_POST['title'];
	else
		die( __( 'Error: Unable to post title', 'leenkme' ) );
	
	if ( isset( $_POST['excerpt'] ) )
		$excerpt = $_POST['excerpt'];
	else
		die( __( 'Error: Unable to post excerpt', 'leenkme' ) );

	die( json_encode( get_leenkme_expanded_li_post( $post_id, $linkedin_array, $title, $excerpt ) ) );
	
}

function leenkme_ajax_reshare() {

	check_ajax_referer( 'leenkme' );
	
	if ( isset( $_POST['id'] ) ) {

		if ( get_post_meta( $_POST['id'], 'linkedin_exclude', true ) ) {

			die( __( 'You have excluded this post from sharing to your LinkedIn profile. If you would like to share it, edit the post and remove the appropriate exclude check box.', 'leenkme' ) );

		} else {
			
			$results = leenkme_ajax_connect( leenkme_publish_to_linkedin( array(), $_POST['id'], $_POST['linkedin_array'], true ) );
	
			if ( isset( $results ) ) {		
				
				foreach( $results as $result ) {	
		
					if ( is_wp_error( $result ) ) {
		
						$out[] = "<p>" . $result->get_error_message() . "</p>";
		
					} else if ( isset( $result['response']['code'] ) ) {
		
						$out[] = "<p>" . $result['body'] . "</p>";
		
					} else {
		
						$out[] = "<p>" . __( 'Error received! Please check your <a href="admin.php?page=leenkme_linkedin">LinkedIn settings</a> and try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.', 'leenkme' ) . "</p>";
		
					}
		
				}
				
				die( join( (array)$out ) );
				
			} else {
				
				die( __( 'ERROR: Unknown error, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.', 'leenkme' ) );
	
			}
			
		}
		
	} else {
		
		die( __( 'ERROR: Unable to determine Post ID.', 'leenkme' ) );
	
	}

}

function leenkme_ajax_li() {

	check_ajax_referer( 'li_share' );

	global $current_user;
	get_currentuserinfo();
	$user_id = $current_user->ID;
	
	global $dl_pluginleenkme;
	$user_settings = $dl_pluginleenkme->get_user_settings( $user_id );

	if ( $api_key = $user_settings['leenkme_API'] ) {

		$comment = __( "Testing leenk.me's LinkedIn Plugin for WordPress", 'leenkme' );
		$title = __( 'leenk.me test', 'leenkme' );
		$url = 'http://leenk.me/';
		$picture = 'http://leenk.me/leenkme.png';
		$description = __( 'leenk.me is a webapp that allows you to publish to popular social networking sites whenever you publish a new post from your WordPress website.', 'leenkme' );
		$code = 'anyone';
		
		$connect_arr[$api_key]['li_comment'] = $comment;
		$connect_arr[$api_key]['li_title'] = $title;
		$connect_arr[$api_key]['li_url'] = $url;
		$connect_arr[$api_key]['li_image'] = $picture;
		$connect_arr[$api_key]['li_desc'] = $description;
		$connect_arr[$api_key]['li_code'] = $code;
		
		$result = leenkme_ajax_connect($connect_arr);
		
		if ( isset( $result[0] ) ) {	
				
			if ( is_wp_error( $result[0] ) ) {
				
				die( $result[0]->get_error_message() );	
				
			} else if ( isset( $result[0]['response']['code'] ) ) {
				
				die( $result[0]['body'] );
				
			} else {
				
				die( __( 'ERROR: Unknown error, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.', 'leenkme' ) );
			
			}
			
		} else {
			
			die( __( 'ERROR: Unknown error, please try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.', 'leenkme' ) );

		}
		
	} else {
		
		die( __( 'ERROR: You have no entered your leenk.me API key. Please check your leenk.me settings.', 'leenkme' ) );
	
	}

}
									
// Add function to share on LinkedIn
function leenkme_publish_to_linkedin( $connect_arr = array(), $post_id, $linkedin_array = array(), $debug = false  ) {
	
	// https://developer.linkedin.com/documents/share-api
	global $wpdb, $dl_pluginleenkme, $dl_pluginleenkmeLinkedIn;
	
	if ( get_post_meta( $post_id, 'linkedin_exclude', true ) )
		$linkedin_exclude = true;
	else
		$linkedin_exclude = false;
	
	if ( !$linkedin_exclude ) {
		
		$leenkme_settings = $dl_pluginleenkme->get_leenkme_settings();
		
		if ( in_array( get_post_type( $post_id ), $leenkme_settings['post_types'] ) ) {
			
			$options = get_option( 'leenkme_linkedin' );
			
			$user_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT user_id FROM ' . $wpdb->usermeta . ' WHERE `meta_value` LIKE %s', '%leenkme_API%' ) );
			
			$url = get_post_meta( $post_id, '_leenkme_shortened_url', true );
			
			foreach ( $user_ids as $user_id ) {
				
				$user_settings = $dl_pluginleenkme->get_user_settings($user_id);
				
				if ( empty( $user_settings['leenkme_API'] ) ) {
					
					clean_user_cache( $user_id );
					continue;	//Skip user if they do not have an API key set
					
				}
				
				$api_key = $user_settings['leenkme_API'];
				
				$options = $dl_pluginleenkmeLinkedIn->get_user_settings( $user_id );
				if ( !empty( $options ) ) {
					
					if ( !empty( $options['share_cats'] ) && isset( $options['clude'] )
							&& !( 'in' == $options['clude'] && in_array( '0', $options['share_cats'] ) ) ) {
						
						if ( 'ex' == $options['clude'] && in_array( '0', $options['share_cats'] ) ) {

							if ( $debug ) echo '<p>' . __( 'You have your <a href="admin.php?page=leenkme_linkedin">leenk.me LinkedIn settings</a> set to Exclude All Categories.', 'leenkme' ) . '</p>';
							clean_user_cache( $user_id );
							continue;

						}
						
						$match = false;
						
						$post_categories = wp_get_post_categories( $post_id );
						
						foreach ( $post_categories as $cat ) {
						
							if ( in_array( (int)$cat, $options['share_cats'] ) ) {
							
								$match = true;
								
							}
							
						}
						
						if ( ( 'ex' == $options['clude'] && $match ) ) {

							if ( $debug ) echo '<p>' . __( 'Post in an excluded category, check your <a href="admin.php?page=leenkme_linkedin">Leenk.me LinkedIn settings</a> or remove the post from the excluded category.', 'leenkme' ) . '</p>';
							clean_user_cache( $user_id );
							continue;

						} else if ( ( 'in' == $options['clude'] && !$match ) ) {
							
							if ( $debug ) echo '<p>' . __( 'Post not found in an included category, check your <a href="admin.php?page=leenkme_linkedin">Leenk.me LinkedIn settings</a> or add the post into the included category.', 'leenkme' ) . '</p>';
							clean_user_cache( $user_id );
							continue;
							
						}
					}
						
					if ( empty( $linkedin_array ) ) {
					
						if (   !( $linkedin_array['comment'] 		= get_post_meta( $post_id, '_linkedin_comment', true ) ) 
							&& !( $linkedin_array['linktitle'] 		= get_post_meta( $post_id, '_linkedin_title', true ) ) 
							&& !( $linkedin_array['description']	= get_post_meta( $post_id, '_linkedin_description', true ) ) 
							&& !( $linkedin_array['picture']		= get_post_meta( $post_id, '_linkedin_image', true ) ) ) {
						
							$linkedin_array['comment'] 		= $options['linkedin_comment'];
							$linkedin_array['linktitle'] 	= $options['linkedin_title'];
							$linkedin_array['description'] 	= $options['linkedin_description'];
							$linkedin_array = get_leenkme_expanded_li_post( $post_id, $linkedin_array );
						
						}
					
					}
													
					if ( isset( $linkedin_array['picture'] ) && !empty( $linkedin_array['picture'] ) )
						$connect_arr[$api_key]['li_image'] = $linkedin_array['picture'];
					
					$connect_arr[$api_key]['li_comment'] 	= $linkedin_array['comment'] ;
					$connect_arr[$api_key]['li_url']		= $url;
					$connect_arr[$api_key]['li_title']		= $linkedin_array['linktitle'];
					$connect_arr[$api_key]['li_desc'] 		= $linkedin_array['description'];
					$connect_arr[$api_key]['li_code'] 		= 'anyone';
					
				}
				
				clean_user_cache( $user_id );
				
			}
			
		}
		
		$wpdb->flush();
		
	}
		
	return $connect_arr;
	
}

// Actions and filters	
if ( isset( $dl_pluginleenkmeLinkedIn ) ) {
	
	add_action( 'save_post', array( $dl_pluginleenkmeLinkedIn, 'leenkme_linkedin_meta_tags' ) );
	
	// Whenever you publish a post, post to LinkedIn
	add_filter('leenkme_connect', 'leenkme_publish_to_linkedin', 20, 2);
	
	add_action( 'wp_ajax_get_leenkme_expanded_li_post', 'get_leenkme_expanded_li_post_ajax' );
	add_action( 'wp_ajax_li_share', 'leenkme_ajax_li' );
	add_action( 'wp_ajax_reshare', 'leenkme_ajax_reshare' );
	
}