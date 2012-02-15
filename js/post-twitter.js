var $lm_post_twitter_jquery = jQuery.noConflict();

$lm_post_twitter_jquery(document).ready(function($) {
	
	$( 'input#title' ).live('change', function() {
		
		lm_refresh_tweet();
		
	});
	
	$( 'textarea#leenkme_tweet' ).live('mousedown', function() {
		
		$( 'input[name=lm_tweet_type]' ).val( 1 );
		$( 'span.tw_default_format' ).hide();
		$( 'span.tw_manual_format' ).show();
		$( 'a#set_to_default_tweet' ).show();
		
		//console.log( "Enabling Twitter's manual mode and stopping auto-refresh." );
		window.clearInterval( twitter_auto_refresh );
		
	});
	
	$( 'a#set_to_default_tweet' ).live('click', function( e ) {
	
		e.preventDefault();
		
		$( 'input[name=lm_tweet_type]' ).val( 0 );
		$( 'span.tw_default_format' ).show();
		$( 'span.tw_manual_format' ).hide();
		$( 'a#set_to_default_tweet' ).hide();
		
		lm_refresh_tweet();

		//console.log( "Enabling Twitter's automatic mode and enabling auto-refresh." );
		twitter_auto_refresh = window.setInterval( function(){ lm_refresh_tweet(); }, 10000 );
		
	});
	
	$( 'textarea#leenkme_tweet' ).bind('keyup paste', function() {
		
		$( 'span#lm_tweet_count' ).text( lm_tweet_len( $( this ).val() ) );
		
	});
		
	$('input#lm_retweet_button').live('click', function() {
		
		var data = {
			'action': 	'retweet',
			'id':  		$('input#post_ID').val(),
			'tweet':  	$('textarea#leenkme_tweet').val(),
			'_wpnonce': $('input#leenkme_wpnonce').val()
		};
		
		ajax_response( data );
		
	});
	
	function lm_refresh_tweet() {
		
		//console.log( 'Refreshing Twitter Preview' );
		
		if ( 0 == ( $( 'input[name=lm_tweet_type]' ).val() ) ) {
			
			cats = new Array;
			$( 'input[name="post_category[]"]' ).each( function() {
				if ( true == $( this ).attr( 'checked' ) || 'checked' == $( this ).attr( 'checked' ) ) {
					var str = $( this ).val();
					cats[ cats.length ] = str;
				}
			});
		
			data = {
				'action': 	'get_leenkme_expanded_tweet',
				'post_id': 	$( 'input#post_ID' ).val(),
				'title': 	$( 'input#title' ).val(),
				'cats': 	cats.join( ',' ),
				'tags': 	$( '.the-tags' ).val(),
				'tweet': 	$( 'input[name=lm_tweet_format]' ).val(),
				'_wpnonce': $('input#tweet_wpnonce').val()
			};
			
			$lm_post_twitter_jquery.post( ajaxurl, data, function( response ) {
				$( 'textarea#leenkme_tweet' ).val( response );
				$( 'span#lm_tweet_count' ).text( lm_tweet_len( response ) );
			});
			
		}
		
	}
	
	function lm_tweet_len( response ) {
		
		tweet_len = 140 - response.length;
	
		if ( 10 > tweet_len ) {
			
			$( 'span#lm_tweet_count' ).removeClass();
			$( 'span#lm_tweet_count' ).addClass( 'lm_tweet_count_superwarn' );
			
		} else if ( 20 > tweet_len ) {
			
			$( 'span#lm_tweet_count' ).removeClass();
			$( 'span#lm_tweet_count' ).addClass( 'lm_tweet_count_warn' );
			
		} else {
			
			$( 'span#lm_tweet_count' ).removeClass();
			$( 'span#lm_tweet_count' ).addClass( 'lm_tweet_count' );
			
		}
		return tweet_len;	
		
	}
	
	if ( 0 == $( 'input[name=lm_tweet_type]' ).val() ) {

		twitter_auto_refresh = window.setInterval( function() { lm_refresh_tweet(); }, 5250 );
		
	}

});