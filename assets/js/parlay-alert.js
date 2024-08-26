if ( typeof Swal !== 'undefined' ) {
	window.ParlayAlert = {
		type: 'success',
		message: '',
		timer: 2000,
		redirect: '',
		confirm: '',
		buttonText: 'OK',

		init: function ( $config ) {
			this.configure( $config );
			if ( this.message ) {
				this.fireAlert();
			}
		},

		configure: function ( $config ) {
			if ( typeof $config !== 'undefined' ) {
				this.type = $config.type;
				this.message = $config.message;
				this.timer = $config.timer;
				this.redirect = $config.redirect;
				this.confirm = $config.confirm;

				if ( this.confirm === 'login' ) {
					this.confirm = true;
					this.buttonText = 'Login';
				}
			}
		},

		fireAlert: function () {
			const Toast = Swal.mixin( {
				toast: true,
				position: 'top-end',
				showConfirmButton: this.confirm || false,
				confirmButtonText: this.buttonText || 'OK',
				timer: this.timer || 2000,
				timerProgressBar: true,
				didOpen: ( toast ) => {
					toast.onmouseenter = Swal.stopTimer;
					toast.onmouseleave = Swal.resumeTimer;
				},
			} );

			Toast.fire( {
				icon: this.type,
				title: this.message,
			} ).then( ( result ) => {
				if (
					result.dismiss === Swal.DismissReason.timer &&
					this.redirect
				) {
					window.location.href = this.redirect;
				}
				
				if ( result.isConfirmed ) {
					if ( typeof parlayFrontend !== 'undefined' ) {
						window.location.href = parlayFrontend.login_url;
					} else if( this.redirect ) {
						window.location.href = this.redirect;
					}
					
				}
			} );
		},
	};

	if ( typeof parlayFrontend !== 'undefined' ) {
		window.ParlayAlert.init( parlayFrontend.alert );
	}
}
