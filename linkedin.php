<?php	

if ( ! class_exists( 'leenkme_LinkedIn' ) ) {
		
	// Define class
	class leenkme_LinkedIn {
		
		// Class members		
		var $options_name				= 'leenkme_linkedin';
		var $linkedin_profile			= 'linkedin_profile';
		var $linkedin_group				= 'linkedin_group';
		var $linkedin_comment			= 'linkedin_comment';
		var $linkedin_title				= 'linkedin_title';
		var $default_image				= 'default_image';
		var $share_cats					= 'share_cats';
		var $clude						= 'clude';
		var $share_all_users			= 'share_all_users';
	
		// Constructor
		function leenkme_LinkedIn() {
			//Not Currently Needed
		}
		
		/*--------------------------------------------------------------------
			Administrative Functions
		  --------------------------------------------------------------------*/
		
		function get_leenkme_linkedin_settings() {
			
			global $wpdb;
			
			$user_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ID) FROM ' . $wpdb->users ) );
			
			if ( 1 < $user_count )
				$share_all_users = true;
			else
				$share_all_users = false;
			
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
			$linkedin_profile		= true;
			$linkedin_group			= false;
			$linkedin_comment		= '%TITLE%';
			$linkedin_title			= '%WPSITENAME%';
			$default_image			= '';
			$share_cats				= array( '0' );
			$clude					= 'in';
			
			$options = array(
								 $this->linkedin_profile 		=> $linkedin_profile,
								 $this->linkedin_group 			=> $linkedin_group,
								 $this->linkedin_comment		=> $linkedin_comment,
								 $this->linkedin_title			=> $linkedin_title,
								 $this->default_image 			=> $default_image,
								 $this->share_cats 				=> $share_cats,
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
		function print_linkedin_settings_page() {
			global $dl_pluginleenkme, $current_user;
			
			get_currentuserinfo();
			$user_id = $current_user->ID;
			
			// Get the user options
			$user_settings = $this->get_user_settings( $user_id );
			$linkedin_settings = $this->get_leenkme_linkedin_settings();
			
			if ( isset( $_POST['update_linkedin_settings'] ) ) {
				
				if ( isset( $_POST['linkedin_profile'] ) )
					$user_settings[$this->linkedin_profile] = true;
				else
					$user_settings[$this->linkedin_profile] = false;
				
				if ( isset( $_POST['linkedin_group'] ) )
					$user_settings[$this->linkedin_group] = true;
				else
					$user_settings[$this->linkedin_group] = false;
				
				if ( isset( $_POST['linkedin_comment'] ) )
					$user_settings[$this->linkedin_comment] = $_POST['linkedin_comment'];
	
				if ( isset( $_POST['linkedin_title'] ) )
					$user_settings[$this->linkedin_title] = $_POST['linkedin_title'];
				
				if ( isset( $_POST['default_image'] ) )
					$user_settings[$this->default_image] = $_POST['default_image'];
	
				if ( isset( $_POST['clude'] ) && isset( $_POST['share_cats'] ) ) {
					
					$user_settings[$this->clude] = $_POST['clude'];
					$user_settings[$this->share_cats] = $_POST['share_cats'];
					
				} else {
					
					$user_settings[$this->clude] = 'in';
					$user_settings[$this->share_cats] = array( '0' );
					
				}
				
				update_user_option($user_id, $this->options_name, $user_settings);
				
				if ( current_user_can( 'leenkme_manage_all_settings' ) ) { //we're dealing with the main Admin options
				
					if ( isset( $_POST['share_all_users'] ) )
						$linkedin_settings[$this->share_all_users] = true;
					else
						$linkedin_settings[$this->share_all_users] = false;
					
					update_option( $this->options_name, $linkedin_settings );
					
				}
				
				// update settings notification ?>
				<div class="updated"><p><strong><?php _e( 'Settings Updated.', 'leenkme_LinkedIn' );?></strong></p></div>
				<?php
			}
			
			// Display HTML form for the options below
			?>
			<div class=wrap>
			<div style="width:70%;" class="postbox-container">
			<div class="metabox-holder">	
			<div class="meta-box-sortables ui-sortable">
				<form id="leenkme" method="post" action="">
					<h2 style='margin-bottom: 10px;' ><img src='<?php echo $dl_pluginleenkme->base_url; ?>/leenkme-logo-32x32.png' style='vertical-align: top;' /> LinkedIn Settings (<a href="http://leenk.me/2010/12/01/how-to-use-the-leenk-me-linkedin-plugin-for-wordpress/" target="_blank">help</a>)</h2>
					<div id="post-types" class="postbox">
					
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span><?php _e( 'Social Settings' ); ?></span></h3>
						
						<div class="inside">
						
							<p>Share to Personal Profile? <input type="checkbox" id="linkedin_profile" name="linkedin_profile" <?php checked( $user_settings[$this->linkedin_profile] ); ?> /></p>
							<p>Share to Group? <input type="checkbox" id="linkedin_group" name="linkedin_group" <?php checked( $user_settings[$this->linkedin_group] ); ?> /></p>
                        
                            <p>
                                <input type="button" class="button" name="verify_linkedin_connect" id="li_share" value="<?php _e( 'Share a Test Message', 'leenkme_LinkedIn' ) ?>" />
                                <?php wp_nonce_field( 'li_share', 'li_share_wpnonce' ); ?>
                            
                                <input class="button-primary" type="submit" name="update_linkedin_settings" value="<?php _e( 'Save Settings', 'leenkme_LinkedIn' ) ?>" />
                            </p>
							
						</div>
					
					</div>
                    
					<div id="post-types" class="postbox">
					
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span><?php _e( 'Message Settings' ); ?></span></h3>
						
						<div class="inside">
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
						<p>Default Image URL: <input name="default_image" type="text" style="width: 500px;" value="<?php _e( $user_settings[$this->default_image], 'leenkme_LinkedIn' ) ?>" /></p>
                    
                        </div>
                    
                    </div>
                       
					<div id="post-types" class="postbox">
					
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span><?php _e( 'Publish Settings' ); ?></span></h3>
						
						<div class="inside">
						<p>Share Categories:</p>
					
						<div class="share-cats" style="margin-left: 50px;">
						<p>
						<input type='radio' name='clude' id='include_cat' value='in' <?php checked( 'in', $user_settings[$this->clude] ); ?> /><label for='include_cat'>Include</label> &nbsp; &nbsp; <input type='radio' name='clude' id='exclude_cat' value='ex' <?php checked( 'ex', $user_settings[$this->clude] ); ?> /><label for='exclude_cat'>Exclude</label> </p>
						<p>
						<select id='categories' name='share_cats[]' multiple="multiple" size="5" style="height: 70px; width: 150px;">
							<option value="0" <?php selected( in_array( "0", (array)$user_settings[$this->share_cats] ) ); ?>>All Categories</option>
						<?php 
						$categories = get_categories( array( 'hide_empty' => 0, 'orderby' => 'name' ) );
						
						foreach ( (array)$categories as $category ) {
							?>
							
							<option value="<?php echo $category->term_id; ?>" <?php selected( in_array( $category->term_id, (array)$user_settings[$this->share_cats] ) ); ?>><?php echo $category->name; ?></option>
		
		
							<?php
						}
						?>
                        
						</select></p>
						<p style="font-size: 11px; margin-bottom: 0px;">To 'deselect' hold the SHIFT key on your keyboard while you click the category.</p>
						
						</div>
                        
						<?php if ( current_user_can('leenkme_manage_all_settings') ) { //then we're displaying the main Admin options ?>
                        
						<p>Share All Authors? <input type="checkbox" name="share_all_users" <?php checked( $linkedin_settings[$this->share_all_users] ); ?> /></p>
						<div class="publish-allusers" style="margin-left: 50px;">
						<p style="font-size: 11px; margin-bottom: 0px;">Check this box if you want leenk.me to share to each available author account.</p>
						</div>
                        
						<?php } ?>
                        
                        <p>
                            <input class="button-primary" type="submit" name="update_linkedin_settings" value="<?php _e( 'Save Settings', 'leenkme_LinkedIn' ) ?>" />
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
				update_post_meta( $post_id, 'linkedin_exclude', $_POST['linkedin_exclude'] );
			else
				delete_post_meta( $post_id, 'linkedin_exclude' );
	
			if ( isset( $_POST["linkedin_exclude_group"] ) )
				update_post_meta( $post_id, 'linkedin_exclude_group', $_POST["linkedin_exclude_group"] );
			else
				delete_post_meta( $post_id, 'linkedin_exclude_group' );
			
			if ( isset( $_POST['linkedin_comment'] ) && !empty( $_POST['linkedin_comment'] ) )
				update_post_meta( $post_id, 'linkedin_comment', $_POST['linkedin_comment'] );
			else
				delete_post_meta( $post_id, 'linkedin_comment' );
			
			if ( isset( $_POST['linkedin_title'] ) && !empty( $_POST['linkedin_title'] ) )
				update_post_meta( $post_id, 'linkedin_title', $_POST['linkedin_title'] );
			else
				delete_post_meta( $post_id, 'linkedin_title' );
			
			if ( isset( $_POST['linkedin_description'] ) && !empty( $_POST['linkedin_description'] ) )
				update_post_meta( $post_id, 'linkedin_description', $_POST['linkedin_description'] );
			else
				delete_post_meta( $post_id, 'linkedin_description' );
	
			if ( isset($_POST["linkedin_image"] ) && !empty( $_POST["linkedin_image"] ) )
				update_post_meta( $post_id, 'linkedin_image', $_POST["linkedin_image"] );
			else
				delete_post_meta( $post_id, 'linkedin_image' );
	
			if ( isset( $_POST['linkedin_exclude'] ) )
				update_post_meta( $post_id, 'linkedin_exclude', $_POST['linkedin_exclude'] );
			else
				delete_post_meta( $post_id, 'linkedin_exclude' );
				
		}
		
		function leenkme_add_linkedin_meta_tag_options() { 
			global $dl_pluginleenkme;
			
			$leenkme_settings = $dl_pluginleenkme->get_leenkme_settings();
			foreach ( $leenkme_settings['post_types'] as $post_type ) {
				
				add_meta_box( 
					'leenkme-LinkedIn',
					__( 'leenk.me LinkedIn', 'leenkme' ),
					array( $this, 'leenkme_linkedin_meta_box' ),
					$post_type 
				);
				
			}
			
		}
		
		function leenkme_linkedin_meta_box()  {
			
			global $post, $current_user;
		
			get_currentuserinfo();
			$user_id = $current_user->ID;
			
			$linkedin_exclude = get_post_meta( $post->ID, 'linkedin_exclude', true );
			$linkedin_exclude_group = get_post_meta( $post->ID, 'linkedin_exclude_group', true ); 
			$linkedin_comment = get_post_meta( $post->ID, 'linkedin_comment', true );
			$linkedin_title = get_post_meta( $post->ID, 'linkedin_title', true );
			$linkedin_description = get_post_meta( $post->ID, 'linkedin_description', true );
			$linkedin_image = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, 'linkedin_image', true ) ) );
			$linkedin_exclude = get_post_meta( $post->ID, 'linkedin_exclude', true );
			$user_settings = $this->get_user_settings( $user_id ); ?>
		
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
                <?php if ( $user_settings['linkedin_exclude'] ) { ?>
				<tr><td scope="row" style="text-align:right; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Exclude from Profile:', 'leenkme' ) ?></td>
				  <td><input style="margin-top: 5px;" type="checkbox" name="linkedin_exclude" <?php checked( $linkedin_exclude || "on" == $linkedin_exclude ); ?> />
				</td></tr>
				<?php } ?>
				<?php if ( $user_settings['linkedin_group'] ) { ?>
				<tr><td scope="row" style="text-align:right; padding-top: 5px; padding-bottom:5px; padding-right:10px;"><?php _e( 'Exclude from Group:', 'leenkme' ) ?></td>
				  <td><input style="margin-top: 5px;" type="checkbox" name="linkedin_exclude_group" <?php checked( $exclude_group || "on" == $linkedin_exclude_group ); ?> />
				</td></tr>
				<?php } ?>
                
				<?php // Only show ReShare button if the post is "published"
				if ( 'publish' === $post->post_status ) { ?>
                
				<tr><td colspan="2">
				<input style="float: right;" type="button" class="button" name="reshare_linkedin" id="reshare_button" value="<?php _e( 'ReShare', 'leenkme_LinkedIn' ) ?>" />
				</td></tr>
                
				<?php } ?>
                
			</table>
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

		$('input#li_share').live('click', function() {
			var linkedin_profile = $('input#linkedin_profile').attr('checked')
			var linkedin_group = $('input#linkedin_group').attr('checked')
            
			var data = {
				action:		'li_share',
				linkedin_profile:	linkedin_profile,
				linkedin_group:		linkedin_group,
				_wpnonce:	$('input#li_share_wpnonce').val()
			};
			
			ajax_response(data);
		});

		$('input#reshare_button').live('click', function() {
			var linkedin_profile = $('input#linkedin_profile').attr('checked')
			var linkedin_group = $('input#linkedin_group').attr('checked')
            
			var data = {
				action: 	'reshare',
				linkedin_profile:	linkedin_profile,
				linkedin_group:		linkedin_group,
				id:  		$('input#post_ID').val(),
				_wpnonce: 	$('input#leenkme_wpnonce').val()
			};
			
			ajax_response(data);
		});

		$('a.reshare_row_action').live('click', function() {
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
						
		if ( isset( $_POST['linkedin_profile'] ) 
				&& ( 'true' === $_POST['linkedin_profile'] || 'checked' === $_POST['linkedin_profile'] ) )
			$connect_arr[$api_key]['linkedin_profile'] = true;
		
		if ( isset( $_POST['linkedin_group'] ) 
				&& ( 'true' === $_POST['linkedin_group'] || 'checked' === $_POST['linkedin_group'] ) )
			$connect_arr[$api_key]['linkedin_group'] = true;
		
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

function leenkme_ajax_reshare() {

	check_ajax_referer( 'leenkme' );
	
	if ( isset( $_POST['id'] ) ) {

		if ( get_post_meta( $_POST['id'], 'linkedin_exclude', true )
				&& get_post_meta( $_POST['id'], 'linkedin_exclude_group', true ) ) {

			die( 'You have excluded this post from sharing to your LinkedIn profile and group. If you would like to share it, edit the post and remove the appropriate exclude check box.' );

		} else {

			$post = get_post( $_POST['id'] );
			
			$results = leenkme_ajax_connect( leenkme_share_to_linkedin( array(), $post, true ) );
	
			if ( isset( $results ) ) {		
				
				foreach( $results as $result ) {	
		
					if ( is_wp_error( $result ) ) {
		
						$out[] = "<p>" . $result->get_error_message() . "</p>";
		
					} else if ( isset( $result['response']['code'] ) ) {
		
						$out[] = "<p>" . $result['body'] . "</p>";
		
					} else {
		
						$out[] = "<p>" . __( 'Error received! Please check your <a href="admin.php?page=leenkme_linkedin">LinkedIn settings</a> and try again. If this continues to fail, contact <a href="http://leenk.me/contact/" target="_blank">leenk.me support</a>.' ) . "</p>";
		
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
function leenkme_share_to_linkedin( $connect_arr = array(), $post, $debug = false ) {
	// https://developer.linkedin.com/documents/share-api
	global $wpdb, $dl_pluginleenkme, $dl_pluginleenkmeLinkedIn;
	$maxDescLen = 256;	//LinkedIn has a 256 character limit for descriptions
	$maxTitleLen = 200; // MLinkedIn has a 200 character limit for titles
	
	if ( get_post_meta( $post->ID, 'linkedin_exclude', true ) )
		$linkedin_exclude = true;
	else
		$linkedin_exclude = false;
	
	if ( get_post_meta( $post->ID, 'linkedin_exclude_group', true ) )
		$linkedin_exclude_group = true;
	else
		$linkedin_exclude_group = false;
	
	if ( !( $linkedin_exclude && $linkedin_exclude_group ) ) {
		
		$leenkme_settings = $dl_pluginleenkme->get_leenkme_settings();
		
		if ( in_array($post->post_type, $leenkme_settings['post_types'] ) ) {
			
			$options = get_option( 'leenkme_linkedin' );
			
			if ( $options['share_all_users'] )
				$user_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM '. $wpdb->users ) );
			else
				$user_ids[] = $post->post_author;
			
			$url = get_permalink( $post->ID );
			$post_title = strip_tags( $post->post_title );
			$wp_sitename = strip_tags( get_bloginfo( 'name' ) );
			$wp_tagline = strip_tags( get_bloginfo( 'description' ) );
			
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

							if ( $debug ) echo "<p>You have your <a href='admin.php?page=leenkme_linkedin'>Leenk.me LinkedIn settings</a> set to Exclude All Categories.</p>";
							clean_user_cache( $user_id );
							continue;

						}
						
						$match = false;
						
						$post_categories = wp_get_post_categories( $post->ID );
						
						foreach ( $post_categories as $cat ) {
						
							if ( in_array( (int)$cat, $options['share_cats'] ) ) {
							
								$match = true;
								
							}
							
						}
						
						if ( ( 'ex' == $options['clude'] && $match ) ) {

							if ( $debug ) echo "<p>Post in an excluded category, check your <a href='admin.php?page=leenkme_linkedin'>Leenk.me LinkedIn settings</a> or remove the post from the excluded category.</p>";
							clean_user_cache( $user_id );
							continue;

						} else if ( ( 'in' == $options['clude'] && !$match ) ) {
							
							if ( $debug ) echo "<p>Post not found in an included category, check your <a href='admin.php?page=leenkme_linkedin'>Leenk.me LinkedIn settings</a> or add the post into the included category.</p>";
							clean_user_cache( $user_id );
							continue;
							
						}
					}
						
					if ( !$options['linkedin_profile'] && !$options['linkedin_group']) {
					
						clean_user_cache( $user_id );
						continue;	//Skip this user if they don't have Profile or Page checked in plugins Facebook Settings
					
					}
	
					// Added facebook profile to connection array if enabled
					if ( $options['linkedin_profile'] && !$exclude_profile )
						$connect_arr[$api_key]['linkedin_profile'] = true;
	
					// Added facebook page to connection array if enabled
					if ( $options['linkedin_group'] && !$linkedin_exclude_group )
						$connect_arr[$api_key]['linkedin_group'] = true;
					
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
					$linktitle = leenkme_trim_words( $linktitle, $maxTitleLen );
					
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
					$description = leenkme_trim_words( $description, $maxDescLen );
				
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
				
				clean_user_cache( $user_id );
				
			}
			
		}
		
		$wpdb->flush();
		
	}
		
	return $connect_arr;
	
}

// Actions and filters	
if ( isset( $dl_pluginleenkmeLinkedIn ) ) {
	add_action( 'admin_init', array( $dl_pluginleenkmeLinkedIn, 'leenkme_add_linkedin_meta_tag_options' ), 1 );
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