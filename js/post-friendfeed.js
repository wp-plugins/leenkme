var $lm_post_friendfeed_jquery = jQuery.noConflict();

$lm_post_friendfeed_jquery(document).ready(function($) {
	
	$( 'input#title' ).live('change', function() {
		
		lm_refresh_friendfeed();
		
	});
	
	$( 'textarea#lm_ff_body, input#lm_ff_linkname, input#lm_ff_caption, textarea#lm_ff_description' ).live('mousedown', function() {
		
		$( 'input[name=lm_friendfeed_type]' ).val( 1 );
		$( 'span.ff_default_format' ).hide();
		$( 'span.ff_manual_format' ).show();
		$( 'a#set_to_default_ff_post' ).show();
		
	});
	
	$( 'a#set_to_default_ff_post' ).live('click', function( e ) {
	
		e.preventDefault();
		
		$( 'input[name=lm_friendfeed_type]' ).val( 0 );
		$( 'span.ff_default_format' ).show();
		$( 'span.ff_manual_format' ).hide();
		$( 'a#set_to_default_ff_post' ).hide();
		
		lm_refresh_friendfeed();
		
	});
		
	$('input#lm_refeed_button').live('click', function() {
		
		friendfeed_array = {
			'body':				$( 'textarea#lm_ff_body' ).val(),
			'picture':			$( 'input[name=friendfeed_image]' ).val()
		};
		
		var data = {
			'action': 			'refeed',
			'id':  				$( 'input#post_ID' ).val(),
			'friendfeed_array': friendfeed_array,
			'_wpnonce': 		$( 'input#leenkme_wpnonce' ).val()
		};
		
		ajax_response( data );
		
	});
	
	function lm_refresh_friendfeed() {
		
		//console.log( 'Refreshing friendfeed Preview' );
		
		if ( 0 == ( $( 'input[name=lm_friendfeed_type]' ).val() ) ) {
			
			friendfeed_array = {
				'body':				$( 'input[name=friendfeed_body_format]' ).val(),
			};
			
			data = {
				'action': 			'get_leenkme_expanded_ff_post',
				'post_id': 			$( 'input#post_ID' ).val(),
				'friendfeed_array': friendfeed_array,
				'title': 			$( 'input#title' ).val(),
				'excerpt':			$( 'textarea#content' ).val().substring( 0, 400 ),
				'_wpnonce': 		$('input#tweet_wpnonce').val()
			};
			
			$lm_post_friendfeed_jquery.post( ajaxurl, data, function( response ) {
				data = $lm_post_friendfeed_jquery.parseJSON( response );
				$( 'textarea#lm_ff_body' ).val( data['body'] );
				$( 'img#lm_ff_image_src' ).attr( 'src', data['picture'] );
				$( 'input[name=friendfeed_image]' ).val( data['picture'] );
			});
			
		} else {
			
			friendfeed_array = {
				'body':				$( 'textarea#lm_ff_body' ).val(),
				'linkname':			$( 'input#lm_ff_linkname' ).val(),
				'caption':			$( 'input#lm_ff_caption' ).val(),
				'description':		$( 'textarea#lm_ff_description' ).val()
			};
			
			data = {
				'action': 			'get_leenkme_expanded_ff_post',
				'post_id': 			$( 'input#post_ID' ).val(),
				'friendfeed_array': friendfeed_array,
				'title': 			$( 'input#title' ).val(),
				'excerpt':			$( 'textarea#content' ).val().substring( 0, 400 ),
				_wpnonce: 			$('input#tweet_wpnonce').val()
			};	
			
			// We just need this to refresh the image being used.
			$lm_post_friendfeed_jquery.post( ajaxurl, data, function( response ) {
				data = $lm_post_friendfeed_jquery.parseJSON( response );
				$( 'img#lm_ff_image_src' ).attr( 'src', data['picture'] );
				$( 'input[name=friendfeed_image]' ).val( data['picture'] );
			});

			
		}
		
	}
	
	friendfeed_auto_refresh = window.setInterval( function(){ lm_refresh_friendfeed(); }, 1000 * 3 );
	
});