( function ( $ ) {
	$( function () {
		$( '.add-interaction' ).on( 'click', function () {
			$( this ).toggleClass( 'active' );

			// Show the comment field
			if ( $( this ).hasClass( 'active' ) ) {
				$( this ).text( '- Cancel' );
				$( '.ticket-comment-field' ).show();
			} else {
				$( this ).text( '+ Adicionar interação' );
				$( '.ticket-comment-field' ).hide();
			}
		} );
	} );
} )( jQuery );
