/**
 * Account widget JS
 */
( function ( $ ) {
	$( function () {
		if ( typeof window.ParlayApiForm !== 'undefined' ) {
			parlay_frontend.errors[ 'required' ] = '*';
			window.ParlayApiForm.init( '#pgs-account-form' );
		}
	} );
} )( jQuery );
