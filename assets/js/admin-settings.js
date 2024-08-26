( function ( $, { __ } ) {

	/**
	 * Admin js class.
	 */
	AdminSettings = {

		/**
		 * Grapesjs editor instance.
		 */
		editor: null,
		
		/**
		 * Initializes class.
		 */
		init: function () {
			this._bind();
			this._initNav();
			this._initClipboard();
			this._initBuilder();
		},

		_bind: function () {
			$( '.pgi-settings-nav a' ).on( 'click', this._navClicked );
			$( '#template-select' ).on( 'change', this._loadTemplate.bind(this) );
			$( '#save-email-template' ).on( 'click', this._saveTemplate.bind(this) );
			$( '#send-test-email' ).on( 'click', this._sendTestClicked.bind(this) );
			$( '#cancel-test' ).on( 'click', this._cancelTestClicked.bind(this) );
			$( '#submit-test-email' ).on( 'click', this._submitTestEmail.bind(this) );
		},

		/**
		 * Initializes the nav for the admin settings page.
		 *
		 * @since 1.0
		 * @access private
		 * @method _initNav
		 */
		_initNav: function () {
			var links = $( '.pgi-settings-nav a' ),
				hash = window.location.hash,
				active =
					hash === '' ? [] : links.filter( '[href~="' + hash + '"]' );

			$( 'a.pgi-active' ).removeClass( 'pgi-active' );
			$( '.pgi-settings-form' ).hide();

			if ( hash === '' || active.length === 0 ) {
				active = links.eq( 0 );
			}

			active.addClass( 'pgi-active' );
			$(
				'#pgi-' + active.attr( 'href' ).split( '#' ).pop() + '-form'
			).fadeIn();
		},

		_navClicked: function () {
			if ( $( this ).attr( 'href' ).indexOf( '#' ) > -1 ) {
				$( 'a.pgi-active' ).removeClass( 'pgi-active' );
				$( '.pgi-settings-form' ).hide();
				$( this ).addClass( 'pgi-active' );
				$(
					'#pgi-' +
						$( this ).attr( 'href' ).split( '#' ).pop() +
						'-form'
				).fadeIn();
			}
		},

		/**
		 * Initializes the clipboard.
		 * 
		 * @since 1.2.0
		 */
		_initClipboard: function() {
			let clipboard = new ClipboardJS('pre.shortcode');
			clipboard.on('success', function(e) {
				$(e.trigger).html( __('Copied!', 'parlay-api') ).delay(1000).fadeOut(400,function(){
					$(e.trigger).html(e.text).fadeIn()
				})
				e.clearSelection();
			});	
		},

		/**
		 * Initializes the email builder.
		 * 
		 * @since 1.2.0
		 */
		_initBuilder: function () {
			this.editor = grapesjs.init({
				container: "#pgi-builder",
				width: 'auto',
				height: "700px",
				fromElement: true,
				storageManager: false,
				plugins: ["grapesjs-preset-newsletter"],
				pluginsOpts: {
					"grapesjs-preset-newsletter": {}
				}
			});
			
			this.editor.on( 'asset:add', (asset) => {
				this._handleImage(asset, 'add');
			});

			this.editor.on( 'asset:remove', (asset) => {
				this._handleImage( asset, 'remove' );
			});
		},

		/**
		 * Handles editor image on add/remove.
		 * 
		 * @since 1.2.0
		 * @param stringb asset 
		 * @param string action
		 */
		_handleImage: function( asset, action ) {
			if (action === 'add' && (
				asset.get('type') !== 'image' || ! asset.get('src').startsWith('data:') 
				) ) {
				window.ParlayAlert.init({
					type: 'error',
					message: __('Invalid image', 'parlay-api'),
					timer: 3000
				});
				return;
			}

			$.post(ajaxurl, { 
				action      : "pgi_handle_editor_image", 
				template_id : $("#template-select").val(),
				_pgs_nonce  : $("#pgi-email-nonce").val(),
				image       : asset.get('src'),
				actionType  : action
			}, function(response) {
				if (response.success) {
					// On Add action0
					if ( action === 'add' ) {
						asset.set('src', response.data.imageUrl);
					}
					// Do nothing on removed...	
				}
			});
		},

		/**
		 * Load the selected template.
		 * 
		 * @since 1.2.0
		*/
		_loadTemplate: function (e) {
			const templateId = $(e.currentTarget).val();
			
			if( templateId === "" ) {
				$(".wrap-email-template").hide();
				return;
			}

			$(".wrap-email-template").addClass('loading-template').show();

			// Show tags for the selected template
			$(".email-tags").hide();
			$(".email-tags[data-template-id='" + templateId + "']").show();

			$.post(ajaxurl, { 
				action      : "pgi_load_email_template", 
				template_id : templateId 
			}, function(response) {
				if (response.success) {
					this.editor.setComponents(response.data.html);
					this.editor.setStyle(response.data.css);

					$("#email-from").val(response.data.from);
					$("#email-subject").val(response.data.subject);
					$(".wrap-email-template").removeClass('loading-template');
				} else {
					// Reset
					this.editor.setComponents("");
					this.editor.setStyle("");
					$("#email-from").val("");
					$("#email-subject").val("");
				}
			}.bind(this));
		},

		/**
		 * Save email template to wp_options table. 
		 * Then, store the actual html file in the templates/email/ directory.
		 * 
		 * @param object e
		 * @returns 
		 */
		_saveTemplate: function(e){
			const button         = $(e.currentTarget),
					templateId   = $("#template-select").val(),
					templateHtml = this.editor.getHtml(),
					templateCss  = this.editor.getCss(),
					emailFrom    = $("#email-from").val(),
					subject      = $("#email-subject").val(),
					_nonce       = $("#pgi-email-nonce").val();
		
			button.prop("disabled", true);

			// Validate email from
			if ( emailFrom.length && ! this.isEmail( emailFrom ) ) {
				window.ParlayAlert.init({
					type: 'error',
					message: __('Invalid email from', 'parlay-api'),
					timer: 3000
				});
				button.prop("disabled", false);
				return false;
			}

			$.post(ajaxurl, {
				action      : "pgi_save_email_template",
				_pgs_nonce  :  _nonce,
				template_id : templateId,
				html        : this._formatHtml( templateHtml ),
				css         : templateCss,
				from        : emailFrom,
				subject     : subject
			}, function(response) {
				let alertType = 'error',
					alertMsg  = __('Failed to save template.', 'parlay-api');

				if (response.success) {
					alertType = 'success';
					alertMsg  = __('Template saved!', 'parlay-api');
				}

				window.ParlayAlert.init({
					type: alertType,
					message: alertMsg,
					timer: 3000
				});

				button.prop("disabled", false);
			}.bind(this));
		},

		/**
		 * Send test email on click.
		 * 
		 * @since 1.2.0
		 */
		_sendTestClicked: function(e) {
			$(e.currentTarget).hide();
            $("#send-test-form").show();
		},
		
		/**
		 * Cancel test email on click.
		 * 
		 * @since 1.2.0 
		 */
		_cancelTestClicked: function(e) {
			$("#send-test-email").show();
            $("#send-test-form").hide();
		},

		/**
		 * Submit test email.
		 * 
		 * @since 1.2.0
		 * @param e 
		 * @returns 
		 */
		_submitTestEmail: function(e) {
			const button    = $(e.currentTarget), 
                _nonce      = $("#pgi-email-nonce").val(),
                templateId  = $("#template-select").val(),
                emailFrom   = $("#email-from").val(),
                emailTo     = $("#email-to").val(),
                subject     = $("#email-subject").val(),
                htmlContent = this._formatHtml( this.editor.getHtml() ),
                cssContent  = this.editor.getCss();

            button.prop("disabled", true);

            // Validate email to
            if ( emailTo.length && ! this.isEmail( emailTo ) ) {
				window.ParlayAlert.init({
					type: 'error',
					message: __('Invalid email to', 'parlay-api'),
					timer: 3000
				});
				button.prop("disabled", false);
				return false;
            }

            $.post(ajaxurl, {
                action      : "pgi_send_test_email",
                _pgs_nonce  :  _nonce,
                template_id : templateId,
                html        : htmlContent,
				css         : cssContent,
                from        : emailFrom,
                to          : emailTo,
                subject     : subject
            }, function(response) {
                let alertType    = 'error',
                    alertMessage = __('Failed to send email.', 'parlay-api');

                if (response.success) {
                    alertType = 'success';
                    alertMessage = __('Email Sent!', 'parlay-api');
                }

                window.ParlayAlert.init({
                    type: alertType,
                    message: alertMessage,
                    timer: 3000
                });

                $("#send-test-email").show();
                $("#send-test-form").hide();

                button.prop("disabled", false);
            });
		},

		/**
		 * Format HTML using js-beautify
		 * 
		 * @since 1.2.0
		 * @param string htmlContent 
		 */
		_formatHtml: function( htmlContent ) {
			const formattedHtml = html_beautify(htmlContent, {
                indent_size: 3,
                wrap_line_length: 80,
                preserve_newlines: true,
                max_preserve_newlines: 2
            });

			return formattedHtml;
		},

		/**
		 * Validate email
		 * 
		 * @since 1.2.0
		 * @param string email
		 */
		isEmail: function( email ){
			return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email );
		}
	};

	$( function () {
		AdminSettings.init();
	} );
} )( jQuery, wp.i18n );
