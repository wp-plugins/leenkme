var $lm_post_jquery = jQuery.noConflict();

$lm_post_jquery(document).ready(function($) {

	//When page loads...
	$( '.leenkme_tab_content' ).hide(); //Hide all content
	$( 'ul.leenkme_tabs li:first' ).addClass('active').show(); //Activate first tab
	$( '.leenkme_tab_content:first' ).show(); //Show first tab content

	//On Click Event
	$( 'ul.leenkme_tabs li' ).click(function() {

		$( 'ul.leenkme_tabs li' ).removeClass('active'); //Remove any 'active' class
		$( this ).addClass('active'); //Add 'active' class to selected tab
		$( '.leenkme_tab_content' ).hide(); //Hide all tab content

		var activeTab = $( this ).find( 'a' ).attr( 'href' ); //Find the href attribute value to identify the active tab + content
		$( activeTab ).fadeIn(); //Fade in the active ID content
		return false;
		
	});

});