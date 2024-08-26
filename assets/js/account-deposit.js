( ( $ ) => {
	'use strict';

	window.hide_preloader = () => {
		$( '.preloader' ).hide();
	};

	document
		.getElementById( 'ecomframe' )
		.addEventListener( 'load', hide_preloader, false );
	document.querySelector( '.ecomlogin' ).submit();
} )( jQuery );
