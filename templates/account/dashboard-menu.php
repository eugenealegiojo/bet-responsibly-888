<?php
/**
 * Account overview menu template
 */

use Parlay\Api\DataManager;
use function Parlay\Api\PGS;
use Parlay\Api\Utils;

$data = PGS()->session->get_userdata();
$get_balance = DataManager::get_player_balance();

if ( ! parlay_is_authenticated() || ! is_array( $data ) ) {
    return;
}

?>
<div class="account-menu">
    <a href="<?php echo parlay_account_url(); ?>" id="account-menu-link" class="icon-user-alt logged-in">
        <?php _e( 'My Account', 'parlay-api' ); ?>
    </a>
    <div id="account-menu-dropdown" class="account-dropdown">
        <div class="account-alias"><?php echo isset( $data['alias'] ) ? $data['alias'] : ''; ?></div>
        <ul>
            <li><?php _e( 'Cash', 'parlay-api' ); ?>: <span class="number"><?php echo Utils::format_currency( $get_balance['cash'] ); ?></span></li>
            <li><?php _e( 'Bonus', 'parlay-api' ); ?>: <span class="number"><?php echo Utils::format_currency( $get_balance['bonus'] ); ?></span></li>
            <li><?php _e( 'Total', 'parlay-api' ); ?>: <span class="number"><?php echo Utils::format_currency( $get_balance['total'] ); ?></span></li>
            
            <li class="menu-link account-link">
                <a href="<?php echo parlay_account_url('/withdraw'); ?>"><?php _e( 'Withdraw', 'parlay-api' ); ?></a>
            </li>
            <li class="menu-link">
                <a href="<?php echo parlay_account_url('/tickets'); ?>"><?php _e( 'Help Desk', 'parlay-api' ); ?></a>
            </li>
            <li class="menu-link logout">
                <div class="logout-button">
                    <a href="<?php echo esc_url( parlay_logout_url() ); ?>"><?php _e( 'Logout', 'parlay-api' ); ?></a>
                </div>
            </li>
        </ul>
    </div>
</div>