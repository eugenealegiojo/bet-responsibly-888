/**
 * Custom media field edit page/modal
 * @since 1.0.0
 */

// Get the cached games list in .json format
async function fetchGamesList() {
	if ( ParlayMediaEdit.game_data_json_url === '' ) {
		return [];
	}

	try {
		const response = await fetch( ParlayMediaEdit.game_data_json_url );
		if ( ! response.ok ) {
			throw new Error( 'Failed to fetch suggestions' );
		}
		const data = await response.json();
		return data.map( ( item ) => ( {
			value: item.gameId,
			name: item.name,
		} ) );
	} catch ( error ) {
		console.error( 'Error fetching JSON:', error );
		return [];
	}
}

( function ( $ ) {
	'use strict';

	$( async function () {
		const gameList = ( await fetchGamesList() ) || [];
		const inputEl = 'input[name^="attachments"][name$="[parlay_game_id]"]';

		const tagifyProps = {
			whitelist: gameList || [],
			enforceWhitelist: false,
			backspace: false,
			maxTags: 10,
			dropdown: {
				searchKeys: [ 'value', 'name' ],
				maxItems: 20,
				classname: 'games-list',
				enabled: 0,
				closeOnSelect: false,
				position: 'all',
			},
			delimiters: ',| ',
			originalInputValueFormat: ( valuesArr ) =>
				valuesArr.map( ( item ) => item.value ).join( ',' ),
			templates: {
				tag: function ( tagData ) {
					let displayName = tagData.name;

					if ( gameList.length > 0 && tagData.name === undefined ) {
						const tagItem = gameList.find(
							( item ) => item.value === tagData.value
						);
						if ( tagItem && typeof tagItem.name !== 'undefined' ) {
							displayName = tagItem.name;
						}
					}

					if ( displayName === undefined ) {
						displayName = tagData.value;
					}

					return `<tag title="${
						tagData.value
					}" contenteditable="false" spellcheck="false" tabIndex="-1" class="tagify__tag ${
						tagData.class ? tagData.class : ''
					}" ${ this.getAttributes( tagData ) }>
                                <x title="remove tag" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x>
                                <div>
                                    <span class="tagify__tag-text">${ displayName }</span>
                                </div>
                            </tag>`;
				},
				dropdownItem: function ( tagData ) {
					return `<div ${ this.getAttributes(
						tagData
					) } class="tagify__dropdown__item ${
						tagData.class ? tagData.class : ''
					}">
                                <strong>${ tagData.name }</strong> <span> (${
									tagData.value
								})</span>
                            </div>`;
				},
			},
		};

		// Render Tagify on the media edit page
		if (
			$( inputEl ).length &&
			$( inputEl ).closest( '.media-modal' ).length <= 0
		) {
			new Tagify( $( inputEl )[ 0 ], tagifyProps );
		}

		// Render Tagify in the media modal
		var attachmentCompat = wp.media.view.AttachmentCompat;
		wp.media.view.AttachmentCompat = attachmentCompat.extend( {
			render: function () {
				attachmentCompat.prototype.render.apply( this, arguments );
				var $el = this.$el;
				var input = $el.find( inputEl );

				// Check if the input element exists
				if ( input.length ) {
					// Add dropdown wrapper below the field
					input.after( '<div class="games-list-wrap"></div>' );

					// Add properties specific for media modal
					tagifyProps.dropdown.appendTarget =
						$el.find( '.games-list-wrap' )[ 0 ];
					const tagify = new Tagify( input[ 0 ], tagifyProps );
				}
			},
		} );
	} );
} )( jQuery );
