/**
 * Login widget
 */
( function ( $ ) {
	const hideLoginForm = function () {
		$( '.parlay-api-login-form' ).removeClass( 'visible' );
	};

	$( document ).on( 'click', function ( e ) {
		const isClickInsideLoginForm =
			$( e.target ).closest( '.parlay-api-login-form' ).length > 0;
		if ( ! isClickInsideLoginForm ) {
			hideLoginForm();
		}
	} );

	$( '.parlay-login-button' ).on( 'click', function ( e ) {
		e.preventDefault();
		const lightboxId = $( this ).find( 'a' ).data( 'lightbox-id' );
		if ( lightboxId ) {
			const loginForm = $( `#${ lightboxId }` );
			const loginWrap = loginForm.closest( '.parlay-api-login-form' );

			loginWrap.toggleClass( 'visible' );
		} else {
			hideLoginForm();
		}
	} );
} )( jQuery );
