( function ( $ ) {
	window.ParlayGames = {
		params: null,
		game: null,

		init: function () {
			$( '.popup-game' ).on( 'click', this.onClickGame.bind( this ) );
		},

		onClickGame: function ( e ) {
			e.preventDefault();
			const el = $( e.target ).parent( 'a' );
			this.game = el;

			if ( ! parlayGames.is_authenticated ) {
				window.location = parlayGames.login_url;
				return false;
			}

			el.addClass( 'loading-game' );

			this.params = {
				id: el.data( 'game-id' ),
				category: el.data( 'category' ),
				return_type: 'url',
			};

			this.openGameWindow(
				parlayGames.popgame_url +
					'/' +
					this.params.category +
					'-' +
					this.params.id,
				'',
				'Game' + this.params.id
			);

			// this.fetchLauncher();
		},

		fetchLauncher: function () {
			const data = this.params;
			// data['action'] = 'handle_custom_ajax_request';

			console.log( 'data: ', data );

			$.ajax( {
				url: parlayGames.restBase + '/games/launch',
				method: 'POST',
				dataType: 'json',
				data,
				success: function ( res ) {
					const response = res.response;

					// console.log("res status: ", res);

					if ( res.status !== 200 ) {
						// window.location = parlayGames.login_url;
						return false;
					}

					if ( res.status === 200 && response ) {
						// Once we have launch URL

						// console.log("response: ", response);
						if ( this.params.return_type === 'code' ) {
							this.openGameWindow( '', response );
						} else {
							this.openGameWindow( response );
						}

						// Hide loader
						this.game.removeClass( 'loading-game' );
						this.params = null;
					}
				}.bind( this ),
				error: function ( error ) {
					console.log( error );
				},
			} );
		},

		openGameWindow: function ( url, content = null, gameId = '' ) {
			const widthVal = 1280;
			const heightVal = 720;

			LeftPosition = screen.width ? ( screen.width - widthVal ) / 2 : 0;
			TopPosition = screen.height
				? ( screen.height - ( heightVal + 50 ) ) / 2
				: 0;

			if ( LeftPosition < 0 ) {
				LeftPosition = 0;
			}
			if ( TopPosition < 0 ) {
				TopPosition = 0;
			}

			const windowprops =
				'left=' +
				LeftPosition +
				',top=' +
				TopPosition +
				',width=' +
				widthVal +
				',height=' +
				heightVal +
				',location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=no';

			if ( url === '' && content ) {
				console.log( 'opening window...' );
				const popup = window.open( '', 'pop', windowprops );
				// popup.document.write(content);

				popup.document.open();
				popup.document.write( content );
				popup.document.close();
			} else {
				window.open( url, gameId, windowprops );
				window.focus();
				this.game.removeClass( 'loading-game' );
				this.params = null;
			}
		},
	};

	$( function () {
		window.ParlayGames.init();
	} );
} )( jQuery );

/**
 *	open_window()
 *
 *	Opens a new window in the browser and submits the given to that
 *	new window. Used for launching casino games.
 */
function open_window( form, popName, widthVal, heightVal, resizeVal ) {
	var xx = null;
	// convert heightVal to integer to allow for addition of 50px for TopPosition
	heightVal = parseInt( heightVal );

	LeftPosition = screen.width ? ( screen.width - widthVal ) / 2 : 0;
	TopPosition = screen.height
		? ( screen.height - ( heightVal + 50 ) ) / 2
		: 0;

	if ( LeftPosition < 0 ) {
		LeftPosition = 0;
	}
	if ( TopPosition < 0 ) {
		TopPosition = 0;
	}

	windowprops =
		'left=' +
		LeftPosition +
		',top=' +
		TopPosition +
		',width=' +
		widthVal +
		',height=' +
		heightVal +
		',location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=' +
		resizeVal +
		',fullscreen=no';

	xx = window.open( '', popName, windowprops );
	form.target = popName;
	form.submit();
	xx.focus();

	return false;
}

/**
 *	launch_wagerworks
 *
 *	Open a WagerWorks game
 */
function launch_wagerworks( url, popName, widthVal, heightVal ) {
	windowprops =
		',width=' +
		widthVal +
		',height=' +
		heightVal +
		',location=no,scrollbars=no,menubars=no,toolbars=no,fullscreen=no';
	xx = window.open( url, 'popWin' + popName, windowprops );
	xx.focus();
	return false;
}

var numloops = 0;
var bingoWindowMap = new Object();

function padout( number ) {
	return number < 10 ? '0' + number : number;
}
function setPromo( theform ) {
	document.getElementById( 'promoCode' ).value =
		document.getElementById( 'promo' ).value;
}

function disableDays() {
	if ( document.selfExclusion.exclusionStatus.value == 'G' ) {
		document.selfExclusion.numDays.disabled = 'disabled';
	} else if ( document.selfExclusion.exclusionStatus.value == 'F' ) {
		document.selfExclusion.numDays.disabled = '';
	}
}
function disableDeposit() {
	if ( document.getElementById( 'depositWindowButton' ).name == 'disabled' ) {
		document.getElementById( 'depositWindowButton' ).disabled = 'disabled';
		document.getElementById( 'duplicateclick' ).style.visibility =
			'visible';
	} else {
		openWindow( document.form1, 'depositPopWin', '1024', '540' );
	}
	document.getElementById( 'depositWindowButton' ).name = 'disabled';
}

/**
 *	update_clocks()
 *
 *	Updates the countdown to the next game in any given bingo room.
 *	See /views/games/bingo_rooms.php for usage.
 */
function updateClocks() {
	var d = new Date();
	var tmp;

	$( 'form.bingoForm' ).each( function () {
		var currentFormId = $( this ).attr( 'id' );
		var currentTime = $( this ).find(
			"input[name='GAMESTARTTIME_" + currentFormId + "']"
		);
		var currentSpan = $( 'span#clock-' + currentFormId );

		tmp = currentTime.val() - 0;

		if ( tmp > 0 ) {
			d = new Date( tmp );
			tmp = tmp - 1000;
			if ( tmp < 0 ) tmp = 0;
			currentSpan.text(
				padout( d.getUTCHours() ) +
					':' +
					padout( d.getUTCMinutes() ) +
					':' +
					padout( d.getUTCSeconds() )
			);
			currentTime.val( tmp );
		} else if ( tmp == 0 ) {
			currentTime.val( 0 );
			currentSpan.text( '00:00:00' );
		}
	} );
}

function loadBingoRooms() {
	$( '#bingorooms-container' ).load( 'bingorooms' );
}

function popBingo( form, popName ) {
	allowResize = 'no';
	allowResize = 'yes';

	// Start at top left
	LeftPosition = 0;
	TopPosition = 0;
	// Go almost full-screen for Flash Bingo
	myHeight = screen.height ? screen.height - 145 : 0;
	myWidth = ( myHeight * 4 ) / 3;

	// Set the left and top window position to zero if logic above has produced a value less than zero
	if ( LeftPosition < 0 ) {
		LeftPosition = 0;
	}
	if ( TopPosition < 0 ) {
		TopPosition = 0;
	}
	// Configure other window properties
	windowprops =
		'left=' +
		LeftPosition +
		',top=' +
		TopPosition +
		',width=' +
		myWidth +
		',height=' +
		myHeight +
		',location=0,scrollbars=0,menubar=0,toolbar=0,resizable=' +
		allowResize;
	// Open window as object
	// IS1315: ensure this window is closed to prevent error message from appearing
	if ( bingoWindowMap[ popName ] != undefined )
		bingoWindowMap[ popName ].close();

	bingoWindowMap[ popName ] = window.open( '', popName, windowprops );
	// Set form target to bingo window name
	form.target = popName;
	// Submit form
	form.submit();
	// Focus on bingo window by object reference
	bingoWindowMap[ popName ].focus();
	return true;
}

jQuery.fn.uncheck = function () {
	return this.each( function () {
		this.checked = false;
	} );
};

jQuery.fn.check = function () {
	return this.each( function () {
		this.checked = true;
	} );
};

jQuery.fn.TreeViewCheckboxesOff = function () {
	$( this ).each( function () {
		$( this ).parent().find( ':checkbox' ).uncheck();
		$( this )
			.removeClass( 'checkbox-checked' )
			.addClass( 'checkbox-unchecked' )
			.removeClass( 'checkbox-partial' );
	} );
};

jQuery.fn.TreeViewCheckboxesOn = function () {
	$( this ).each( function () {
		$( this ).parent().find( ':checkbox' ).check();
		$( this )
			.addClass( 'checkbox-checked' )
			.removeClass( 'checkbox-unchecked' )
			.removeClass( 'checkbox-partial' );
	} );
};

jQuery.fn.TreeViewCheckboxesToggle = function () {
	var check = $( ":checkbox[id='" + this.attr( 'for' ) + "']" )[ 0 ];
	var wasChecked = check.checked;

	var parentListItem = this.parent();
	var parentNodes = parentListItem
		.parents()
		.filter( '.expandable, .collapsable' );
	var hasChildren =
		$( parentListItem ).hasClass( 'expandable' ) ||
		$( parentListItem ).hasClass( 'collapsable' );

	if ( hasChildren ) {
		// An expandable node was clicked.  We have to either check/uncheck all children below.
		var childItems = $( parentListItem ).find( 'li label' );
		if ( wasChecked ) {
			$( childItems ).TreeViewCheckboxesOff();
			this.addClass( 'checkbox-checked' )
				.removeClass( 'checkbox-unchecked' )
				.removeClass( 'checkbox-partial' );
		} else {
			$( childItems ).TreeViewCheckboxesOn();
			this.removeClass( 'checkbox-checked' )
				.addClass( 'checkbox-unchecked' )
				.removeClass( 'checkbox-partial' );
		}
	}

	// Now, traverse up the tree to update any higher-level checkboxes
	if ( parentNodes.length > 0 ) {
		$( parentNodes ).each( function () {
			var label = $( this ).children( 'label' );
			var allChildrenSize = $( this ).find( 'li :checkbox' ).length;
			var checkedChildrenSize = $( this ).find(
				'li :checkbox:checked'
			).length;

			if ( wasChecked ) {
				checkedChildrenSize -= 1;
			} else {
				checkedChildrenSize += 1;
			}

			var theCheckBox = $(
				":checkbox[id='" + $( label ).attr( 'for' ) + "']"
			);
			if (
				allChildrenSize != checkedChildrenSize &&
				checkedChildrenSize == 0
			) {
				$( label )
					.removeClass( 'checkbox-partial' )
					.removeClass( 'checkbox-checked' )
					.addClass( 'checkbox-unchecked' );
				theCheckBox.uncheck();
			} else if ( allChildrenSize != checkedChildrenSize ) {
				$( label )
					.addClass( 'checkbox-partial' )
					.removeClass( 'checkbox-checked' )
					.removeClass( 'checkbox-unchecked' );
				theCheckBox.uncheck();
			} else {
				$( label )
					.addClass( 'checkbox-checked' )
					.removeClass( 'checkbox-unchecked' )
					.removeClass( 'checkbox-partial' );
				theCheckBox.check();
			}
		} );
	}

	// Click the hidden input box for IE only.  Firefox will cascade the click down.
	if ( $.browser.msie ) {
		check.click();
	}

	this.toggleClass( 'checkbox-checked' ).toggleClass( 'checkbox-unchecked' );
};

jQuery.fn.TreeViewCheckboxes = function () {
	$( ':checkbox', this )
		// Hide native checkboxes
		.hide()
		// Find related labels and add the css
		.each( function () {
			var check = this;
			var jlabel = $( "label[for='" + $( check ).attr( 'id' ) + "']" );
			var disabled = $( check ).attr( 'disabled' );

			// Initial state check
			if ( check.checked ) {
				if ( ! check.disabled ) {
					jlabel.addClass( 'checkbox-checked' );
				} else {
					jlabel.addClass( 'checkbox-checked-disabled' );
				}
			} else {
				if ( ! check.disabled ) {
					jlabel.addClass( 'checkbox-unchecked' );
				} else {
					jlabel.addClass( 'checkbox-unchecked-disabled' );
				}
			}

			jlabel.hover(
				function () {
					$( this ).addClass( 'over' );
				},
				function () {
					$( this ).removeClass( 'over' );
				}
			);

			// Label click state
			jlabel.click( function () {
				var check = $(
					":checkbox[id='" + $( this ).attr( 'for' ) + "']"
				)[ 0 ];
				if ( $( check ).attr( 'disabled' ) != true ) {
					$( this ).TreeViewCheckboxesToggle();
				}
			} );
		} );
	var treeNodes = $( this ).find( 'li:first' ).markInputs();
};

jQuery.fn.markInputs = function () {
	var treeNodes = $( this ).find( 'li.expandable, li.collapsable' ).andSelf();
	treeNodes.each( function () {
		$( this ).markInput();
	} );
};

jQuery.fn.markInput = function () {
	var label = $( this ).children( 'label' );
	var check = $( this ).children( ':checkbox' );
	var allChildrenSize = $( this ).find( 'li :checkbox' ).length;
	var checkedChildrenSize = $( this ).find( 'li :checkbox:checked' ).length;

	if ( allChildrenSize != checkedChildrenSize && checkedChildrenSize == 0 ) {
		if ( $( check ).attr( 'disabled' ) != true ) {
			$( label )
				.removeClass( 'checkbox-partial' )
				.removeClass( 'checkbox-checked' )
				.addClass( 'checkbox-unchecked' );
		} else {
			$( label )
				.removeClass( 'checkbox-partial-disabled' )
				.removeClass( 'checkbox-checked-disabled' )
				.addClass( 'checkbox-unchecked-disabled' );
		}
		$( check ).uncheck();
	} else if ( allChildrenSize != checkedChildrenSize ) {
		if ( $( check ).attr( 'disabled' ) != true ) {
			$( label )
				.addClass( 'checkbox-partial' )
				.removeClass( 'checkbox-checked' )
				.removeClass( 'checkbox-unchecked' );
		} else {
			$( label )
				.addClass( 'checkbox-partial-disabled' )
				.removeClass( 'checkbox-checked-disabled' )
				.removeClass( 'checkbox-unchecked-disabled' );
		}
		$( check ).uncheck();
	} else {
		if ( $( check ).attr( 'disabled' ) != true ) {
			$( label )
				.addClass( 'checkbox-checked' )
				.removeClass( 'checkbox-unchecked' )
				.removeClass( 'checkbox-partial' );
		} else {
			$( label )
				.addClass( 'checkbox-checked-disabled' )
				.removeClass( 'checkbox-unchecked-disabled' )
				.removeClass( 'checkbox-partial-disabled' );
		}
		$( check ).check();
	}
};

// Add html5 launcher if html5Root is present
if ( typeof html5Root != 'undefined' ) {
	jQuery( document ).ready( function () {
		//    var html5Root = 'http://localhost/web/html5/';

		$( '.mobile-bingo' ).click( function () {
			var formEl = $( '#' + $( this ).attr( 'data-form' ) );
			var query_str =
				'&' +
				formEl.serialize() +
				'&GAMESERVERURL=' +
				formEl.attr( 'action' ) +
				'&game=BI';
			window.location.assign(
				html5Root +
					'games/bingo?host_name=' +
					location.hostname +
					query_str
			);
		} );
	} );
}
