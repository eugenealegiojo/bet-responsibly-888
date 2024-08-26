<?php
/**
 * Edit profile
 */

use Parlay\Api\DataManager;
use Parlay\Api\Account;

$data   = DataManager::get_player_data();
$fields = Account::get_account_fields();

if ( ! $data ) {
	return;
}
?>

<div class="information-wrapper parlay-form">
	<div class="wrap">
		<h3><?php esc_html_e( 'Personal Information', 'parlay-api' ); ?></h3>
		<div class="profile-fields">
		<?php foreach ( $fields['personal'] as $key => $field ) : ?>
			<div class="field-info">
				<label><?php echo $field->label; ?></label>
				<span>:</span>
				<div class="field-text">
					<?php
					if ( isset( $field->update_allowed ) && false === $field->update_allowed ) :

						if ( 'birthday' === $key && isset( $data[ $key ] ) ) :
							echo $data[ $key ]['month'] . '/' . $data[ $key ]['day'] . '/' . $data[ $key ]['year'];
						else :
							echo isset( $data[ $key ] ) ? $data[ $key ] : $field->value;
						endif;
					else :
						?>
						<?php
						if ( 'text' === $field->type ) :
							$required = isset( $field->required ) && true === $field->required ? 'required' : '';
							if ( 'fullname' === $key && array_key_exists( 'firstName', $data ) && array_key_exists( 'lastName', $data ) ) :
								$text_value = $data['firstName'] . ' ' . $data['lastName'];
							else :
								$text_value = isset( $data[ $key ] ) ? $data[ $key ] : $field->value;
							endif;
							?>
							<input type="text" name="<?php echo $key; ?>" value="<?php echo $text_value; ?>" <?php echo $required; ?>>
						<?php endif; ?>

						<?php
						if ( 'select' === $field->type ) :
							$options  = $field->options;
							$selected = isset( $data[ $key ] ) ? $data[ $key ] : $field->value;

							echo '<select name="' . $key . '">';
							foreach ( $options as $option_key => $option_value ) {
								echo '<option value="' . $option_key . '" ' . ( $selected == $option_key ? 'selected' : '' ) . '>' . $option_value . '</option>';
							}
							echo '</select>';
						endif;

						if ( 'checkbox' === $field->type ) :
							?>
							<input type="checkbox" name="<?php echo $key; ?>" value="yes" <?php echo isset( $data[ $key ] ) && true === $data[ $key ] ? 'checked' : ''; ?>>
							<?php echo esc_html_x( 'Yes', 'parlay-api', 'parlay-api' ); ?>
						<?php endif; ?>
					<?php endif; ?>
				</div> <!-- .field-text -->
			</div> <!-- .field-info -->
		<?php endforeach; ?>
		</div>
	</div>

	<div class="wrap">
		<h3><?php esc_html_e( 'Account Information', 'parlay-api' ); ?></h3>
		<div class="profile-fields">
			<?php foreach ( $fields['account'] as $key => $field ) : ?>
				<div class="field-info">
					<label><?php echo $field->label; ?></label>
					<span>:</span>
					<div class="field-text">
						<?php if ( isset( $field->update_allowed ) && false === $field->update_allowed ) : ?>
							<?php echo isset( $data[ $key ] ) ? $data[ $key ] : $field->value; ?>
						<?php else : ?>
							<input type="text" name="<?php echo $key; ?>" value="<?php echo isset( $data[ $key ] ) ? $data[ $key ] : $field->value; ?>">
						<?php endif; ?>
					</div>
				</div>  
			<?php endforeach; ?>
			<div class="field-info">
				<label><?php esc_html_e( 'Password', 'parlay-api' ); ?></label>
				<span>:</span>
				<div class="field-text">
					<input type="password" id="password"  name="password" />
				</div>
			</div>
			<div class="field-info">
				<label><?php esc_html_e( 'Confirm Password', 'parlay-api' ); ?></label>
				<span>:</span>
				<div class="field-text">
					<input type="password" id="confirm_password" name="confirm_password" />
				</div>
			</div>
			<div class="button-wrap">
				<input type="submit" class="sc_button sc_button_size_small submit" name="update_profile" value="<?php echo esc_attr__( 'Update', 'parlay-api' ); ?>">
			</div>
			<?php //wp_nonce_field( 'wp_rest' ); ?>
			
		</div>
	</div>
</div>
