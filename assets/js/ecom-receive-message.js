function ecomReceiveMessage( event ) {
	if ( event.origin === ecom_frontend.ecomRoot ) {
		let $data = JSON.parse( event.data );

		if ( $data.action === 'resizeIframe' ) {
			if ( $data.newHeight > 0 ) {
				document.querySelector(
					'iframe[name=ecomframe]'
				).style.height = Math.round( $data.newHeight ) + 'px';
			}
		} else if ( $data.action === 'scrollToPosition' ) {
			let iframe = document.querySelector( 'iframe[name=ecomframe]' );
			window.doScrolling(
				iframe.offsetLeft + $data.x,
				iframe.offsetTop + $data.y,
				500
			);
		} else if ( $data.action === 'updateLiveChatCustomVars' ) {
			let methods = {
					8: 'Envoy',
					12: 'NETELLER',
					13: 'Skrill',
					17: 'Ukash',
					18: 'SafetyPay',
					19: 'LPS',
					20: 'AlliedWallet',
				},
				sub_methods = {
					E10: 'Boleto',
					E11: 'AstroPay',
					E12: 'TBL',
					CM: 'MasterCard',
					CV: 'Visa',
				},
				method,
				params;

			// Only trigger the LiveChat API if it's an instant method
			if (
				$data.paymentCategoryId === 8 &&
				$data.paymentCategoryType !== 'E11'
			) {
				return;
			}

			if (
				$data.paymentCategoryId === 8 ||
				$data.paymentCategoryId === 19
			) {
				method = sub_methods[ $data.paymentCategoryType ];
			} else {
				method = methods[ $data.paymentCategoryId ];
			}

			params = [
				{ name: 'trigger', value: 'declined_deposit' },
				{ name: 'Transaction ID', value: $data.trans_number },
				{ name: 'Error message', value: $data.message },
				{ name: 'Deposit method', value: method },
			];

			// Merge with existing custom variables
			__lc.params = __lc.params.concat( params );

			LC_API.set_custom_variables( __lc.params );
		} else if ( $data.action === 'openLiveChatWindow' ) {
			// Merge with existing custom variables
			__lc.params = __lc.params.concat( [
				{ name: 'Deposit method', value: $data.depositMethod },
				{ name: 'Transaction ID', value: $data.trans_number },
				{ name: 'Amount', value: $data.amount },
			] );

			LC_API.set_custom_variables( __lc.params );
			LC_API.open_chat_window();
		} else if ( $data.action === 'reload' ) {
			window.location.reload();
		} else if ( $data.action === 'disableIframe' ) {
			document.querySelector( '.ecomlogin' ).target = '_top';
			document.querySelector( '.ecomlogin' ).submit();
		} else if ( $data.action === 'setConfig' ) {
			for ( let key in $data.data ) {
				if ( $data.data.hasOwnProperty( key ) ) {
					window.localStorage.setItem( key, $data.data[ key ] );
				}
			}
		}
	}
}
window.addEventListener( 'message', ecomReceiveMessage, false );
