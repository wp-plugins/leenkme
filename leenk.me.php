<?php
/*
Plugin Name: leenk.me
Plugin URI: http://leenk.me/
Description: Automatically publish to your Twitter, Facebook profile/page, and Google Buzz whenever you publish a new post on your WordPress website with the leenk.me social network connector. You need a <a href="http://leenk.me/">leenk.me API key</a> to use this plugin.
Author: Lew Ayotte @ leenk.me
Version: 1.1.7
Author URI: http://leenk.me/about/
Tags: twitter, facebook, google, google buzz, oauth, profile, fan page, image, images, social network, social media, post, posts, twitter post, tinyurl, twitter friendly links, admin, authors, contributors, exclude, category, categories, retweet, republish, rebuzz, connect, status update, leenk.me, leenk me, leenk, scheduled post, smo, social media optimization, ssl, secure
*/

define( 'leenk.me_version' , '1.1.7' );

class leenkme {
	var $options_name			= "leenkme";
	var $leenkme_API			= "leenkme_API";
	var $twitter				= "twitter";
	var $facebook				= "facebook";
	var $googlebuzz				= "googlebuzz";
	var $base_url 				= "";
	var $api_url				= "";
	
	function leenkme() {
		global $wp_version;
		$this->wp_version = $wp_version;
		$this->base_url = plugins_url() . '/' . dirname( plugin_basename( __FILE__ ) ) . '/';
		$this->api_url = 'https://leenk.me/api/1.1/';
	}
	
	function get_leenkme_settings() {
		$twitter = false;
		$facebook = false;
		$googlebuzz = false;
		
		$options = array( 	$this->twitter 		=> $twitter,
							$this->facebook 	=> $facebook,
							$this->googlebuzz 	=> $googlebuzz	);
	
		$leenkme_settings = get_option( $this->options_name );
		if ( !empty( $leenkme_settings ) ) {
			foreach ( $leenkme_settings as $key => $option ) {
				$options[$key] = $option;
			}
		}
		
		return $options;
	}

	function get_user_settings( $user_id = false ) {
		$leenkme_API = "";
		
		$options = array( $this->leenkme_API => $leenkme_API );

		$user_settings = get_user_option( $this->options_name, $user_id );
		if ( !empty( $user_settings ) ) {
			foreach ( $user_settings as $key => $option ) {
				$options[$key] = $option;
			}
		}
		
		return $options	;
	}
	
	function leenkme_settings_page() {
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;
		
		// Get the user options
		$user_settings = $this->get_user_settings( $user_id );
		$leenkme_settings = $this->get_leenkme_settings();
		
		if ( isset( $_POST['update_leenkme_settings'] ) ) {
			if ( isset( $_POST['leenkme_API'] ) ) {
				$user_settings[$this->leenkme_API] = $_POST['leenkme_API'];
			}			
			update_user_option( $user_id, $this->options_name, $user_settings );
			
			if ( current_user_can( 'activate_plugins' ) ) { //we're dealing with the main Admin options
				if ( isset( $_POST['twitter'] ) ) {
					$leenkme_settings[$this->twitter] = true;
				} else {
					$leenkme_settings[$this->twitter] = false;
				}
				
				if ( isset( $_POST['facebook'] ) ) {
					$leenkme_settings[$this->facebook] = true;
				} else {
					$leenkme_settings[$this->facebook] = false;
				}
				
				if ( isset( $_POST['googlebuzz'] ) ) {
					$leenkme_settings[$this->googlebuzz] = true;
				} else {
					$leenkme_settings[$this->googlebuzz] = false;
				}
				
				update_option( $this->options_name, $leenkme_settings );
				
				// It's not pretty, but the easiest way to get the menu to refresh after save...
				?>
					<script type="text/javascript">
					<!--
					window.location = "<?php echo $_SERVER['PHP_SELF'] .'?page=leenkme&settings_saved'; ?>"
					//-->
					</script>
				<?php
			}
		}
		
		if ( isset( $_POST['update_leenkme_settings'] ) || isset( $_GET['settings_saved'] ) ) {
			// update settings notification ?>
			<div class="updated"><p><strong><?php _e( "leenk.me Settings Updated.", "leenkme" );?></strong></p></div>
			<?php
		}
		// Display HTML form for the options below
		?>
		<div class=wrap>
			<form id="leenkme" method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
				<h2>leenk.me Settings</h2>
				<p>leenk.me API Key: <input id="api" type="text" name="leenkme_API" style="width: 25%;" value="<?php _e(apply_filters( 'format_to_edit', htmlspecialchars( stripcslashes( $user_settings[$this->leenkme_API] ) ) ), 'leenkme') ?>" />
				<input type="button" class="button" name="verify_leenkme_api" id="verify" value="<?php _e( 'Verify leenk.me API', 'leenkme' ) ?>" />
				<?php wp_nonce_field( 'verify', 'leenkme_verify_wpnonce' ); ?>
				</p>
                <?php if ( empty( $user_settings[$this->leenkme_API] ) ) { ?>
                <p>
                <a href="http://leenk.me/">Click here to subscribe to leenk.me and generate an API key</a>
                </p>
                <?php } ?>
				<?php if ( current_user_can( 'activate_plugins' ) ) {?>
                <table id="leenkme_activate_plugins">
                    <tr><td id="leenkme_plugin_name">Twitter: </td>
                    <td id="leenkme_plugin_button"><input type="checkbox" name="twitter" <?php if ( $leenkme_settings[$this->twitter] ) echo 'checked="checked"'; ?> /></td></tr>
                    <tr><td id="leenkme_plugin_name">Facebook: </td>
                    <td id="leenkme_plugin_button"><input type="checkbox" name="facebook" <?php if ( $leenkme_settings[$this->facebook] ) echo 'checked="checked"'; ?> /></td></tr>
                    <tr><td id="leenkme_plugin_name">Google Buzz: </td>
                    <td id="leenkme_plugin_button"><input type="checkbox" name="googlebuzz" <?php if ( $leenkme_settings[$this->googlebuzz] ) echo 'checked="checked"'; ?> /></td></tr>
                </table>
				<?php } ?>
				<p class="submit">
					<input class="button-primary" type="submit" name="update_leenkme_settings" value="<?php _e( 'Save Settings', 'leenkme' ) ?>" />
				</p>
			</form>
		</div>
		<?php
	}
	
	function plugin_enabled( $plugin ) {
		$leenkme_settings = $this->get_leenkme_settings();
		return $leenkme_settings[$plugin];
	}
}

// Instantiate the class
if ( class_exists( "leenkme" ) ) {
	$dl_pluginleenkme = new leenkme();
	
	if ( $dl_pluginleenkme->plugin_enabled( 'twitter' ) ) {
		require_once( 'twitter.php' );
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'facebook' ) ) {
		require_once( 'facebook.php' );
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'googlebuzz' ) ) {
		require_once( 'googlebuzz.php' );
	}
}

// Initialize the admin panel if the plugin has been activated
function leenkme_ap() {
	global $dl_pluginleenkme;
	
	if ( !isset( $dl_pluginleenkme ) ) {
		return;
	}
	
	add_menu_page( __( 'leenk.me Settings', 'leenkme' ), __( 'leenk.me', 'leenkme' ), 'edit_posts', 'leenkme', array( &$dl_pluginleenkme, 'leenkme_settings_page' ), $dl_pluginleenkme->base_url . '/leenkme-logo-16x16.png' );
	
	if (substr($dl_pluginleenkme->wp_version, 0, 3) >= '2.9') {
		add_submenu_page( 'leenkme', __( 'leenk.me Settings', 'leenkme' ), __( 'leenk.me', 'leenkme' ), 'edit_posts', 'leenkme', array( &$dl_pluginleenkme, 'leenkme_settings_page' ) );
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'twitter' ) ) {
		global $dl_pluginleenkmeTwitter;
		add_submenu_page( 'leenkme', __( 'Twitter Settings', 'leenkme' ), __( 'Twitter', 'leenkme' ), 'edit_posts', 'leenkme_twitter', array( &$dl_pluginleenkmeTwitter, 'print_twitter_settings_page' ) );
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'facebook' ) ) {
		global $dl_pluginleenkmeFacebook;
		add_submenu_page( 'leenkme', __( 'Facebook Settings', 'leenkme' ), __( 'Facebook', 'leenkme' ), 'edit_posts', 'leenkme_facebook', array( &$dl_pluginleenkmeFacebook, 'print_facebook_settings_page' ) );
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'googlebuzz' ) ) {
		global $dl_pluginleenkmeGoogleBuzz;
		add_submenu_page( 'leenkme', __( 'Google Buzz Settings', 'leenkme' ), __( 'Google Buzz', 'leenkme' ), 'edit_posts', 'leenkme_googlebuzz', array( &$dl_pluginleenkmeGoogleBuzz, 'print_googlebuzz_settings_page' ) );
	}
}

// Example followed from http://codex.wordpress.org/AJAX_in_Plugins
function leenkme_js() {
	global $dl_pluginleenkme;
?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('input#api').click(function() {
			$('input#api').css('background-color', 'white');
		});
	
		$('input#verify').click(function() {
			var leenkme_API = $('input#api').val();
			var error = false;
			if (leenkme_API == "") {
				$('input#api').css('background-color', 'red');
				return false;
			}
		
			var data = {
				action: 	'verify',
				leenkme_API: leenkme_API,
				_wpnonce: 	$('input#leenkme_verify_wpnonce').val()
			};
			
			ajax_response(data);
		});
<?php 
	if ( $dl_pluginleenkme->plugin_enabled( 'twitter' ) ) {
		leenkme_twitter_js();
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'facebook' ) ) {
		leenkme_facebook_js();
	}
	
	if ( $dl_pluginleenkme->plugin_enabled( 'googlebuzz' ) ) {
		leenkme_googlebuzz_js();
	}
?>

		function ajax_response(data) {
			var style = "position: fixed; " +
						"display: none; " +
						"z-index: 1000; " +
						"top: 50%; " +
						"left: 50%; " +
						"background-color: #E8E8E8; " +
						"border: 1px solid #555; " +
						"padding: 15px; " +
						"width: 500px; " +
						"min-height: 80px; " +
						"margin-left: -250px; " + 
						"margin-top: -150px;" +
						"text-align: center;" +
						"vertical-align: middle;";
			$('body').append("<div id='results' style='" + style + "'></div>");
			$('#results').html("<p>Sending data to leenk.me</p>" +
									"<p><img src='/wp-includes/js/thickbox/loadingAnimation.gif' /></p>");
			$('#results').show();
			
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, data, function(response) {
				$('#results').html('<p>' + response + '</p>' +
										'<input type="button" class="button" name="results_ok_button" id="results_ok_button" value="OK" />');
				$('#results_ok_button').click(remove_results);
			});
		}
		
		function remove_results() {
			jQuery("#results_ok_button").unbind("click");
			jQuery("#results").remove();
			
			if (typeof document.body.style.maxHeight == "undefined") {//if IE 6
				jQuery("body","html").css({height: "auto", width: "auto"});
				jQuery("html").css("overflow","");
			}
			
			document.onkeydown = "";
			document.onkeyup = "";
			return false;
		}
	});
</script>

<?php
}

function leenkme_ajax_verify() {
	check_ajax_referer( 'verify' );
	
	if ( isset( $_POST['leenkme_API'] ) ) {
		$api_key = $_POST['leenkme_API'];
		$connect_arr[$api_key]['verify'] = true;
		
		$result = leenkme_ajax_connect( $connect_arr );
		
		if ( isset( $result["response"]["code"] ) ) {
			die( $result["body"] );
		} else {
			die( "ERROR: Unknown error, please try again. If this continues to fail, contact support@leenk.me." );
		}
	} else {
		die( "Please fill in your API key." );
	}
}

function leenkme_connect( $post ) {
	global $dl_pluginleenkme;
	
	$connect_arr = apply_filters( 'leenkme_connect', array(), $post );

	if ( !empty( $connect_arr ) ) {
		foreach ( $connect_arr as $api_key => $body ) {
			$body['host'] = $_SERVER['SERVER_NAME'];
			$body['leenkme_API'] = $api_key;
			$headers = array( 'Authorization' => 'None' );
			$request = new WP_Http;
			$result = $request->request( $dl_pluginleenkme->api_url, 
											array( 	'method' => 'POST', 
													'body' => $body, 
													'headers' => $headers,
													'sslverify' => false ) );
			
			if ( isset( $result ) ) {
				return $result;
			} else {
				return "Undefined error occurred, Please contact leenk.me support.";
			}
		}
	}
}

function leenkme_ajax_connect( $connect_arr ) {
	global $dl_pluginleenkme;
	
	if ( !empty( $connect_arr ) ) {
		foreach ( $connect_arr as $api_key => $body ) {
			$body['host'] = $_SERVER['SERVER_NAME'];
			$body['leenkme_API'] = $api_key;
			$headers = array( 'Authorization' => 'None' );
			$request = new WP_Http;
			$result = $request->request( $dl_pluginleenkme->api_url, 
											array( 	'method' => 'POST', 
													'body' => $body, 
													'headers' => $headers,
													'sslverify' => false ) );
			
			if ( isset( $result ) ) {
				return $result;
			} else {
				return "Undefined error occurred, Please contact leenk.me support.";
			}
		}
	}
}

function leenkme_help_list( $contextual_help, $screen ) {
	if ( 'leenkme' == $screen->parent_base ) {
		$contextual_help[$screen->id] = '<p>Need help working with the leenk.me plugin? Try these links for more information:</p>' .
'<a href="http://leenk.me/2010/09/04/how-to-use-the-leenk-me-twitter-plugin-for-wordpress/" target="_blank">Twitter</a> | ' .
'<a href="http://leenk.me/2010/09/04/how-to-use-the-leenk-me-facebook-plugin-for-wordpress/" target="_blank">Facebook</a> | ' .
'<a href="http://leenk.me/2010/09/04/how-to-use-the-leenk-me-google-buzz-plugin-for-wordpress/" target="_blank">Google Buzz</a>';
	}

	return $contextual_help;
}

// Actions and filters	
if ( isset( $dl_pluginleenkme ) ) {
	/*--------------------------------------------------------------------
	    Actions
	  --------------------------------------------------------------------*/

	// Add the admin menu
	add_action( 'admin_menu', 'leenkme_ap');
	
	// Whenever you publish a post, connect to leenk.me
	add_action( 'new_to_publish', 'leenkme_connect', 20 );
	add_action( 'draft_to_publish', 'leenkme_connect', 20 );
	add_action( 'future_to_publish', 'leenkme_connect', 20 );
	
	add_action( 'admin_head-toplevel_page_leenkme', 'leenkme_js' );
	add_action( 'admin_head-edit.php', 'leenkme_js' );
	add_action( 'admin_head-post.php', 'leenkme_js' );
	add_action( 'wp_ajax_verify', 'leenkme_ajax_verify', 10, 1 );
	add_action( 'wp_ajax_plugins', 'leenkme_ajax_plugins', 10, 1 );
	
	add_filter( 'contextual_help_list', 'leenkme_help_list', 10, 2);
} 

// From PHP_Compat-1.6.0a2 Compat/Function/str_ireplace.php for PHP4 Compatibility
if ( !function_exists( 'str_ireplace' ) ) {
    function str_ireplace( $search, $replace, $subject ) {
		// Sanity check
		if ( is_string( $search ) && is_array( $replace ) ) {
			user_error( 'Array to string conversion', E_USER_NOTICE );
			$replace = (string)$replace;
		}
	
		// If search isn't an array, make it one
		$search = (array)$search;
		$length_search = count( $search );
	
		// build the replace array
		$replace = is_array( $replace )
		? array_pad( $replace, $length_search, '' )
		: array_pad( array(), $length_search, $replace );
	
		// If subject is not an array, make it one
		$was_string = false;
		if ( is_string( $subject ) ) {
			$was_string = true;
			$subject = array( $subject );
		}
	
		// Prepare the search array
		foreach ( $search as $search_key => $search_value ) {
			$search[$search_key] = '/' . preg_quote( $search_value, '/' ) . '/i';
		}
		
		// Prepare the replace array (escape backreferences)
		$replace = str_replace( array( '\\', '$' ), array( '\\\\', '\$' ), $replace );
	
		$result = preg_replace( $search, $replace, $subject );
		return $was_string ? $result[0] : $result;
	}
}
?>
