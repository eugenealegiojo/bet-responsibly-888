<?php
/**
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 */

// Checks to see if the template is being used as a block.
$block_wrapper_attrs = '';
if ( $attributes ) {
	$block_wrapper_attrs = get_block_wrapper_attributes();
}
?>
<div <?php echo $block_wrapper_attrs; ?>>
	<form method="post" id="parlay-form-register" class="parlay-form" data-endpoint="/account/register">
		<div class="form-container">
			<div class="row-field">
				<input type="text" id="fullname" name="fullname" required placeholder="<?php echo esc_attr__( 'Full name', 'parlay-api' ); ?>" />
				<span class="sc_form_field_hover">
					<i class="sc_form_field_icon trx_addons_icon-user-alt"></i>
				</span>
			</div>
			<div class="row-field">
				<input type="text" id="alias" name="alias" required placeholder="<?php echo esc_attr__( 'Nickname', 'parlay-api' ); ?>" />
				<span class="sc_form_field_hover">
					<i class="sc_form_field_icon trx_addons_icon-user-alt"></i>
				</span>
			</div>
			<div class="row-field full-width">
				<input type="email" id="email" name="email" required placeholder="<?php echo esc_attr__( 'Email', 'parlay-api' ); ?>" />
				<span>
					<i class="sc_form_field_icon trx_addons_icon-mail"></i>
				</span>
			</div>
			<div class="row-field full-width">
				<input type="password" id="password" name="password" required placeholder="<?php echo esc_attr__( 'Password', 'parlay-api' ); ?>"/>
				<span>
					<i class="sc_form_field_icon trx_addons_icon-lock"></i>
				</span>
			</div>
			<div class="row-field">
				<input type="date" id="birthday" name="birthday" required placeholder="<?php echo esc_attr__( 'Date of Birth', 'parlay-api' ); ?>" />
				<span>
					<i class="sc_form_field_icon trx_addons_icon-calendar"></i>
				</span>
			</div>
			<div class="row-field">
				<input type="text" id="city" name="city" placeholder="<?php echo esc_attr__( 'City', 'parlay-api' ); ?>" />
				<span>
					<i class="fontello icon-location"></i>
				</span>
			</div>
			<div class="row-field full-width">
				<div class="option-field">
					<label><?php _e( 'Sex', 'parlay-api' ); ?>:</label>
					<div class="radio-choices">
						<label><?php _e( 'Male', 'parlay-api' ); ?></label> 
						<input type="radio" id="gender1" name="gender" value="m" required/>
					</div>
					<div class="radio-choices">
						<label><?php _e( 'Female', 'parlay-api' ); ?></label> 
						<input type="radio" id="gender2" name="gender" value="f" required />
					</div>
				</div>
			</div>

			<div class="row-field">
				<input type="number" id="mobileno" name="mobileno" placeholder="<?php echo esc_attr__( 'Cellphone number', 'parlay-api' ); ?>" required />
				<span>
					<i class="fontello icon-mobile"></i>
				</span>
			</div>
			<div class="row-field">
				<input type="number" id="phoneno" name="phoneno" placeholder="<?php echo esc_attr__( 'Phone 2', 'parlay-api' ); ?>" />
				<span>
					<i class="icon-phone-call"></i>
				</span>
			</div>

			<div class="row-field full-width">
				<div class="option-field">
					<input type="checkbox" id="newsletter" name="newsletter" />
					<label>
						<?php _e( 'I wish to receive promotional newsletters, news, and offers via email, SMS, and phone.', 'parlay-api' ); ?>
					</label>
				</div>
			</div>

			<div class="row-field full-width">
				<input type="submit" class="submit" value="<?php echo esc_attr__( 'Open My Account', 'parlay-api' ); ?>" />
			</div>
			<div class="form-error-message"></div>

			<div class="registration-terms full-width">
				<p>
					<?php _e( 'At the end of my registration, I certify that I am over 18 years of age and accept the', 'parlay-api' ); ?> <a href="<?php echo get_page_link(24286) ?>"><?php _e( 'Terms and Conditions', 'parlay-api' ); ?></a> <?php _e( 'and', 'parlay-api' ); ?> <a href="<?php echo get_page_link(24280) ?>"><?php _e( 'Privacy Policy', 'parlay-api' ); ?></a>.
				</p>
			</div>
			<?php wp_nonce_field( 'wp_rest' ); ?>

			<?php 
				if( \Parlay\Api\Affiliates::get_tracking_data() ) : 
					$tracking_data = \Parlay\Api\Affiliates::get_tracking_data();
			
				?>
				<input type="hidden" value="<?php echo $tracking_data['tracking_id'] ?>" name="trackingId" id="trackingId" data-include-on-submit="true">
        		<input type="hidden" value="<?php echo $tracking_data['affiliate_system_code'] ?>" name="affiliateSystemCode" id="affiliateSystemCode" data-include-on-submit="true">
			<?php endif; ?>
		</div>
	</form>
</div>
