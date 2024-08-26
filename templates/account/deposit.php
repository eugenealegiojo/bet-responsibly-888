<?php
/**
 * Deposit template
 */

use Parlay\Api\DataManager;
use function Parlay\Api\PGS;

$settings   = DataManager::get_api_settings();
$entrypoint = 'home'; //'deposit';
$lang       = ! empty( $settings['language'] ) ? $settings['language'] : 'pt';
$ecomm_url  = trailingslashit( $settings['ecommerce_url'] ) . "{$lang}/home";
$userdata   = PGS()->session->get_userdata();
$data       = [
	'entrypoint' => 'deposit',
	'jsessionid' => $userdata['user_token'],
	'sessionid'  => $userdata['user_token'],
	'userid'     => $userdata['userId'],
	'siteid'     => $settings['site_id'],
	'referrer'   => home_url( '/my-account/logout' ),
	'mobile'     => false,
];

// $promoCode = get_query_var( 'promo' );
// if (isset($promoCode)) {
//  $data['promo'] = $promoCode;
// }

?>
<div class="iframe-wrapper">
	<form method="post" id="ecomlogin" target="ecomframe" name="ecomlogin" class="ecomlogin" action="<?php echo \Parlay\Api\Account::account_url( 'ecom' ); ?>">
		<input type="hidden" name="url" value="<?php echo esc_url( $ecomm_url ); ?>">
		<input type="hidden" name="data" value='<?php echo esc_attr( json_encode( $data ) ); ?>'>
	</form>

	<div class="preloader info"><?php echo __( 'Loading...', 'parlay-api' ); ?></div>
	<iframe src="" name="ecomframe" id="ecomframe" width="100%" height="100" style="border: 0; overflow: hidden;">
		<?php _e( "Sorry your browser doesn't support frames.", 'parlay-api' ); ?>
	</iframe>
</div>
