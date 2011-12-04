var $lm_post_linkedin_jquery = jQuery.noConflict();

$lm_post_linkedin_jquery(document).ready(function($) {
	
	$( 'input#title' ).live('change', function() {
		
		lm_refresh_linkedin();
		
	});
	
	$( 'textarea#lm_li_comment, input#lm_li_linktitle, textarea#lm_li_description' ).live('click', function() {
		
		$( 'input[name=lm_linkedin_type]' ).val( 1 );
		$( 'span.li_default_format' ).hide();
		$( 'span.li_manual_format' ).show();
		$( 'a#set_to_default_li_post' ).show();
		
	});
	
	$( 'a#set_to_default_li_post' ).live('click', function( e ) {
	
		e.preventDefault();
		
		$( 'input[name=lm_linkedin_type]' ).val( 0 );
		$( 'span.li_default_format' ).show();
		$( 'span.li_manual_format' ).hide();
		$( 'a#set_to_default_li_post' ).hide();
		
		lm_refresh_linkedin();
		
	});
		
	$('input#lm_reshare_button').live('click', function() {
		
		linkedin_array = {
			'comment':			$( 'textarea#lm_li_comment' ).val(),
			'linktitle':		$( 'input#lm_li_linktitle' ).val(),
			'description':		$( 'textarea#lm_li_description' ).val(),
			'picture':			$( 'input[name=linkedin_image]' ).val()
		};
		
		var data = {
			'action': 			'reshare',
			'id':  				$( 'input#post_ID' ).val(),
			'linkedin_array':  	linkedin_array,
			'_wpnonce': 		$( 'input#leenkme_wpnonce' ).val()
		};
		
		ajax_response( data );
		
	});
	
	function lm_refresh_linkedin() {
		
		//console.log( 'Refreshing Facebook Preview' );
		
		if ( 0 == ( $( 'input[name=lm_linkedin_type]' ).val() ) ) {
			
			linkedin_array = {
				'comment':			$( 'input[name=linkedin_comment_format]' ).val(),
				'linktitle':		$( 'input[name=linkedin_linktitle_format]' ).val(),
				'description':		$( 'input[name=linkedin_description_format]' ).val()
			};
			
			data = {
				'action': 			'get_leenkme_expanded_li_post',
				'post_id': 			$( 'input#post_ID' ).val(),
				'linkedin_array': 	linkedin_array,
				'title': 			$( 'input#title' ).val(),
				'excerpt':			$( 'textarea#content' ).val().substring( 0, 400 ),
				'_wpnonce': 		$('input#tweet_wpnonce').val()
			};
			
			$lm_post_linkedin_jquery.post( ajaxurl, data, function( response ) {
				data = $lm_post_linkedin_jquery.parseJSON( response );
				$( 'textarea#lm_li_comment' ).val( data['comment'] );
				$( 'input#lm_li_linktitle' ).val( data['linktitle'] );
				$( 'textarea#lm_li_description' ).val( data['description'] );
				$( 'img#lm_li_image_src' ).attr( 'src', data['picture'] );
				$( 'input[name=linkedin_image]' ).val( data['picture'] );
			});
			
		} else {
			
			linkedin_array = {
				'comment':			$( 'textarea#lm_li_comment' ).val(),
				'linktitle':		$( 'input#lm_li_linktitle' ).val(),
				'description':		$( 'textarea#lm_li_description' ).val()
			};
			
			data = {
				'action': 			'get_leenkme_expanded_li_post',
				'post_id': 			$( 'input#post_ID' ).val(),
				'linkedin_array': 	linkedin_array,
				'title': 			$( 'input#title' ).val(),
				'excerpt':			$( 'textarea#content' ).val().substring( 0, 400 ),
				_wpnonce: 			$('input#tweet_wpnonce').val()
			};	
			
			// We just need this to refresh the image being used.
			$lm_post_linkedin_jquery.post( ajaxurl, data, function( response ) {
				data = $lm_post_linkedin_jquery.parseJSON( response );
				$( 'img#lm_li_image_src' ).attr( 'src', data['picture'] );
				$( 'input[name=linkedin_image]' ).val( data['picture'] );
			});

			
		}
		
	}
	
	linkedin_auto_refresh = window.setInterval( function(){ lm_refresh_linkedin(); }, 1000 * 3 );
	
});