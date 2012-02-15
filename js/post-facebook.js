var $lm_post_facebook_jquery = jQuery.noConflict();

$lm_post_facebook_jquery(document).ready(function($) {
	
	$( 'input#title' ).live('change', function() {
		
		lm_refresh_facebook();
		
	});
	
	$( 'textarea#lm_fb_message, input#lm_fb_linkname, input#lm_fb_caption, textarea#lm_fb_description' ).live('mousedown', function() {
		
		$( 'input[name=lm_facebook_type]' ).val( 1 );
		$( 'span.fb_default_format' ).hide();
		$( 'span.fb_manual_format' ).show();
		$( 'a#set_to_default_fb_post' ).show();
		
	});
	
	$( 'a#set_to_default_fb_post' ).live('click', function( e ) {
	
		e.preventDefault();
		
		$( 'input[name=lm_facebook_type]' ).val( 0 );
		$( 'span.fb_default_format' ).show();
		$( 'span.fb_manual_format' ).hide();
		$( 'a#set_to_default_fb_post' ).hide();
		
		lm_refresh_facebook();
		
	});
		
	$('input#lm_republish_button').live('click', function() {
		
		facebook_array = {
			'message':			$( 'textarea#lm_fb_message' ).val(),
			'linkname':			$( 'input#lm_fb_linkname' ).val(),
			'caption':			$( 'input#lm_fb_caption' ).val(),
			'description':		$( 'textarea#lm_fb_description' ).val(),
			'picture':			$( 'input[name=facebook_image]' ).val()
		};
		
		var data = {
			'action': 			'republish',
			'id':  				$( 'input#post_ID' ).val(),
			'facebook_array':  	facebook_array,
			'_wpnonce': 		$( 'input#leenkme_wpnonce' ).val()
		};
		
		ajax_response( data );
		
	});
	
	function lm_refresh_facebook() {
		
		//console.log( 'Refreshing Facebook Preview' );
		
		if ( 0 == ( $( 'input[name=lm_facebook_type]' ).val() ) ) {
			
			facebook_array = {
				'message':			$( 'input[name=facebook_message_format]' ).val(),
				'linkname':			$( 'input[name=facebook_linkname_format]' ).val(),
				'caption':			$( 'input[name=facebook_caption_format]' ).val(),
				'description':		$( 'input[name=facebook_description_format]' ).val()
			};
			
			excerpt = $( 'textarea#excerpt' ).val().substring( 0, 400 );
			
			if ( '' == excerpt ) {
				
				excerpt = $( 'textarea#content' ).val().substring( 0, 400 );
			
			}
			
			data = {
				'action': 			'get_leenkme_expanded_fb_post',
				'post_id': 			$( 'input#post_ID' ).val(),
				'facebook_array': 	facebook_array,
				'title': 			$( 'input#title' ).val(),
				'excerpt':			excerpt,
				'_wpnonce': 		$('input#tweet_wpnonce').val()
			};
			
			$lm_post_facebook_jquery.post( ajaxurl, data, function( response ) {
				data = $lm_post_facebook_jquery.parseJSON( response );
				$( 'textarea#lm_fb_message' ).val( data['message'] );
				$( 'input#lm_fb_linkname' ).val( data['linkname'] );
				$( 'input#lm_fb_caption' ).val( data['caption'] );
				$( 'textarea#lm_fb_description' ).val( data['description'] );
				$( 'img#lm_fb_image_src' ).attr( 'src', data['picture'] );
				$( 'input[name=facebook_image]' ).val( data['picture'] );
			});
			
		} else {
			
			facebook_array = {
				'message':			$( 'textarea#lm_fb_message' ).val(),
				'linkname':			$( 'input#lm_fb_linkname' ).val(),
				'caption':			$( 'input#lm_fb_caption' ).val(),
				'description':		$( 'textarea#lm_fb_description' ).val()
			};
			
			data = {
				'action': 			'get_leenkme_expanded_fb_post',
				'post_id': 			$( 'input#post_ID' ).val(),
				'facebook_array': 	facebook_array,
				'title': 			$( 'input#title' ).val(),
				'excerpt':			$( 'textarea#content' ).val().substring( 0, 400 ),
				_wpnonce: 			$('input#tweet_wpnonce').val()
			};
			
			// We just need this to refresh the image being used.
			$lm_post_facebook_jquery.post( ajaxurl, data, function( response ) {
				data = $lm_post_facebook_jquery.parseJSON( response );
				$( 'img#lm_fb_image_src' ).attr( 'src', data['picture'] );
				$( 'input[name=facebook_image]' ).val( data['picture'] );
			});

			
		}
		
	}

	var facebook_auto_refresh = window.setInterval( function() { lm_refresh_facebook(); }, 5000 );
	
});