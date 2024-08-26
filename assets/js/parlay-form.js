( function ( $ ) {
	class ParlayApiForm {
		constructor( formSelector, endpoint ) {
			this.form = $( formSelector );
			this.endpoint = endpoint;
			this.fields = this.form.find( 'input:visible, input[type="hidden"][data-include-on-submit="true"]' );

			this.fields.on( 'input blur', this.onInput.bind( this ) );
			this.form.off().on( 'submit', this.onSubmit.bind( this ) );
		}

		onInput( e ) {
			const field = $( e.target );
			const value = field.val();
			let newValue = '';

			if ( this.fields.is( field ) ) {
				if (
					field.attr( 'id' ) === 'mobileno' ||
					field.attr( 'id' ) === 'phoneno'
				) {
					// Remove non-numeric characters
					for ( var i = 0; i < value.length; i++ ) {
						if (
							! isNaN( value.charAt( i ) ) &&
							value.charAt( i ).trim() !== ''
						) {
							newValue += value.charAt( i );
						}
					}
					if ( newValue.length ) {
						field.val( newValue );
					}
				} else {
					newValue = value;
				}

				field.toggleClass(
					'input-error',
					this.validateField( field, newValue ) ?? false
				);
			}
			this.validate( e );
		}

		onSubmit( e ) {
			e.preventDefault();

			if ( ! this.validate( e, true ) ) {
				return false;
			}

			if ( typeof this.endpoint === 'undefined' ) {
				this.form.get( 0 ).submit();
				// return true;
			} else {
				this.fetchApiData();
			}
		}

		fetchApiData() {
			const data = {};
			const button = this.form.find( '.submit' );
			const _wpnonce = this.form.find( '#_wpnonce' ).val();

			this.fields.each( function () {
				const field = $( this );

				if ( field.attr( 'type' ) === 'checkbox' ) {
					data[ field.attr( 'id' ) ] = field.is( ':checked' );
				} else if ( field.attr( 'type' ) === 'radio' ) {
					const radio = $(
						`input[name="${ field.attr( 'name' ) }"]:checked`
					);

					if ( radio.length ) {
						data[ radio.attr( 'name' ) ] = radio.val();
					}
				} else {
					data[ field.attr( 'id' ) ] = field.val();
				}
			} );

			$.ajax( {
				url: parlay_frontend.restBase + this.endpoint,
				method: 'POST',
				dataType: 'json',
				data,
				beforeSend: function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', _wpnonce );
					this.form.addClass( 'loading' );
					button.prop( 'disabled', true );
				}.bind( this ),

				success: function ( res ) {
					const response = res.response;
					const message = response.success
						? '<p class="success-message">' +
						  response.message +
						  '</p>'
						: response.message;

					this.form.removeClass( 'loading' );
					button.prop( 'disabled', false );

					if ( res.status !== 200 ) {
						this.setErrorMessage(
							response.message ||
								parlay_frontend.errors[ 'other' ],
							false
						);
					}

					if ( res.status === 200 ) {
						let redirectUrl = this.getRedirectUrl();

						if ( typeof res.alert !== 'undefined' ) {
							window.ParlayAlert.init( res.alert );
							this.resetForm();
						} else if ( redirectUrl ) {
							console.log( 'redirect to: ', redirectUrl );
							if ( redirectUrl !== '#' ) {
								window.location.href = redirectUrl;
							}
							else {
								this.form
									.find( '.form-container' )
									.first()
									.html( message );
								return false;
							}
						} else {
							console.log( 'reloading...' );
							window.location.reload();
						}
					}
				}.bind( this ),

				error: function ( error ) {
					let errorMessage = parlay_frontend.errors[ 'other' ];

					if ( error.message ) {
						errorMessage = error.message;
					} else if (
						error.responseJSON &&
						error.responseJSON.message
					) {
						errorMessage = error.responseJSON.message;
					}

					this.setErrorMessage( errorMessage, false );

					this.form.removeClass( 'loading' );
					button.prop( 'disabled', false );
				}.bind( this ),
			} );
		}

		getRedirectUrl() {
			let redirectUrl = parlay_frontend.redirect_url;

			// Check if it's a popup login
			if ( this.form.closest( '#parlay-login-form' ).length ) {
				if ( redirectUrl === '#' ) {
					redirectUrl = window.location.href.replace(
						'/registration',
						'/'
					);
				} else if ( redirectUrl.includes( '/registration' ) ) {
					redirectUrl = redirectUrl.replace( '/registration', '/' );
				} else if ( redirectUrl.includes( '/login' ) ) {
					redirectUrl = redirectUrl.replace( '/login', '/' );
				}
			}

			return redirectUrl;
		}

		setErrorMessage( errors, isFieldError = true ) {
			if ( isFieldError ) {
				this.fields.each( function () {
					const field = $( this );
					const errorMessage = errors[ field.attr( 'id' ) ] || '';
					const errorEl = field.next( '.parlay-input-error' );

					if ( errorMessage === '' || errorMessage === null ) {
						errorEl.remove();
					} else if ( errorEl.length ) {
						errorEl.text( errorMessage );
					} else {
						field.after(
							$( '<span/>' )
								.addClass( 'parlay-input-error' )
								.text( errorMessage )
						);
					}
				} );
			} else {
				if ( this.form.find( '.form-error-message' ).length ) {
					this.form
						.find( '.form-error-message' )
						.show()
						.text( errors );
				} else {
					this.form
						.next( '.form-error-message' )
						.show()
						.text( errors );
				}
			}
		}

		validate( e, allFields = false ) {
			let errors = {};

			if ( allFields ) {
				this.fields.each(
					function ( _, field ) {
						const value = $( field ).val();

						const fieldId = $( field ).attr( 'id' );
						if ( fieldId ) {
							errors = {
								...errors,
								[ fieldId ]: this.validateField(
									$( field ),
									value
								),
							};
						}
					}.bind( this )
				);
			} else {
				const field = $( e.currentTarget );
				const value = field.val();

				const fieldId = field.attr( 'id' );
				if ( fieldId ) {
					errors = {
						[ fieldId ]: this.validateField( field, value ),
					};
				}
			}

			this.setErrorMessage( errors );

			return Object.values( errors ).every( ( error ) => error === null );
		}

		validateField( field, value ) {
			let errorMessage = null;

			errorMessage =
				field.prop( 'required' ) && this.isEmpty( value )
					? parlay_frontend.errors[ 'required' ]
					: null;

			if (
				field.prop( 'required' ) &&
				! errorMessage &&
				field.attr( 'id' ) === 'password' &&
				! this.hasMinLength( value, 5 )
			) {
				errorMessage = parlay_frontend.errors[ 'passwordCount' ];
			}

			if (
				! errorMessage &&
				field.attr( 'id' ) === 'email' &&
				! this.isEmail( value )
			) {
				errorMessage = parlay_frontend.errors[ 'invalidEmail' ];
			}

			if ( field.attr( 'id' ) === 'confirm_password' ) {
				const passwordField = this.form.find( '#password' );
				const passwordValue = passwordField.val();
				errorMessage =
					value !== passwordValue
						? parlay_frontend.errors[ 'passwordMatch' ]
						: null;
			}

			return errorMessage;
		}

		resetForm(){
			this.form[0].reset();
			this.form.find( '.parlay-input-error' ).remove();
			this.form.find( '.form-error-message' ).text( '' );
			this.form.find( '.form-error-message' ).hide();
			this.form.parent().find( '.form-error-message' ).text( '' );
			this.form.parent().find( '.form-error-message' ).hide()
		}

		isEmpty( value ) {
			return value.trim() === '';
		}

		hasMinLength( value, minLength ) {
			return value.length >= minLength;
		}

		isEmail( value ) {
			return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( value );
		}
	}

	$( '.parlay-form' ).each( function () {
		const formSelector = this;
		const endpoint = $( this ).data( 'endpoint' );
		new ParlayApiForm( formSelector, endpoint );
	} );
} )( jQuery );
