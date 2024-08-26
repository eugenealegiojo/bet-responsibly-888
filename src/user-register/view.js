( function ( $ ) {
	$( function () {
		if ( typeof window.ParlayApiForm !== 'undefined' ) {
			parlay_frontend.errors[ 'required' ] = '*';
			window.ParlayApiForm.init(
				'#parlay-form-register',
				'/account/register'
			);
		}
	} );
} )( jQuery );
