( ( $ ) => {
	'use strict';

	window.hide_preloader = () => {
		$( '.preloader' ).hide();
	};

	document
		.getElementById( 'ecomframe' )
		.addEventListener( 'load', hide_preloader, false );
	document.querySelector( '.ecomlogin' ).submit();

	if ( $( '.withdrawal-popup' ).length ) {
		let withPopup = $( '.withdrawal-popup' ),
			withBtn = $( '.withdrawal-banner' );

		withPopup.dialog( {
			autoOpen: false,
			modal: true,
			width: 650,
			height: 450,
			buttons: [
				{
					text: 'Ok, Entendi',
					class: 'ui-priority-secondary',
					click: () => {
						withPopup.dialog( 'close' );
					},
				},
				{
					text: 'Quero Mais Detalhes',
					class: 'ui-priority-primary',
					click: () => {
						withPopup.dialog( 'close' );
						LC_API.open_chat_window();
					},
				},
			],
		} );

		withBtn.on( 'click', () => {
			withPopup.dialog( 'open' );
		} );
	}
} )( jQuery );
