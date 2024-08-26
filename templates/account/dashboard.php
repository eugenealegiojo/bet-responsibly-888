<?php
/**
 * My account dashboard
 *
 * $attributes
 */

use Parlay\Api\DataManager;
use Parlay\Api\Account;

$data   = DataManager::get_player_data();
$fields = Account::get_account_fields();

?>

<div class="profile-top-buttons">
	<a href="<?php echo parlay_account_url( '/edit-profile' ); ?>" class="sc_button sc_button_size_small">
		<?php _e( 'Update Profile', 'parlay-api' ); ?>
	</a>
	<a href="<?php echo parlay_account_url( '/deposit' ); ?>" class="sc_button sc_button_size_small color_style_link2">
		<?php _e( 'Deposit', 'parlay-api' ); ?>
	</a>
	<a href="<?php echo parlay_account_url( '/withdraw' ); ?>" class="sc_button sc_button_size_small color_style_link2">
		<?php _e( 'Withdraw', 'parlay-api' ); ?>
	</a>
</div>
<div class="information-wrapper">
	<div class="wrap">
		<h3><?php _e( 'Personal Information', 'parlay-api' ); ?></h3>
		<div class="profile-fields">
			<?php foreach ( $fields['personal'] as $key => $field ) : ?>
				<div class="field-info">
					<label><?php echo $field->label; ?></label>
					<span>:</span>
					<span class="field-text">
						<?php
						if ( is_array( $data ) ) :
							if ( 'fullname' === $key && array_key_exists( 'firstName', $data ) && array_key_exists( 'lastName', $data ) ) :
								echo $data['firstName'] . ' ' . $data['lastName'];
							elseif ( 'birthday' === $key && isset( $data[ $key ] ) ) :
								echo $data[ $key ]['month'] . '/' . $data[ $key ]['day'] . '/' . $data[ $key ]['year'];
							elseif ( 'receiveBroadcast' === $key ) :
								echo false === $data[ $key ] ? __( 'No', 'parlay-api' ) : __( 'Yes', 'parlay-api' );
							else :
								echo isset( $data[ $key ] ) ? $data[ $key ] : $field->value;
							endif;
						endif;
						?>
					</span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="wrap">
		<h3><?php _e( 'Account Information', 'parlay-api' ); ?></h3>
		<div class="profile-fields">
			<?php foreach ( $fields['account'] as $key => $field ) : ?>
				<div class="field-info">
					<label><?php echo $field->label; ?></label>
					<span>:</span>
					<span class="field-text">
						<?php echo isset( $data[ $key ] ) ? $data[ $key ] : $field->value; ?>
					</span>
				</div>  
			<?php endforeach; ?>
		</div>
	</div>
</div>
