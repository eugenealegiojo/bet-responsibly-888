( function ( $ ) {
	$( function () {
		if ( typeof window.ParlayApiForm !== 'undefined' ) {
			window.ParlayApiForm.init(
				'.parlay-login-block',
				'/account/login'
			);
		}
	} );
} )( jQuery );
