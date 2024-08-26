<?php
/**
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 */

?>
<div class="parlay-forgot-password-block">
    <?php if ( ! parlay_is_authenticated( true, home_url() ) ) : ?>
        <h3 class="heading-text"><?php _e( 'Reset Password', 'parlay-api' ) ?></h3>
        <form id="parlay-api-forgot-password" class="parlay-form" method="post" data-endpoint="/account/reset-password">
            <input type="hidden" id="reset_token" name="reset_token" value="<?php echo $attributes['token']; ?>" data-include-on-submit="true">
            <input type="password" id="password" name="password" required placeholder="<?php echo esc_attr__( 'New Password', 'parlay-api' ); ?>">
            <input type="password" id="confirm_password" name="confirm-password" required placeholder="<?php echo esc_attr__( 'Confirm New Password', 'parlay-api' ); ?>">
            <?php wp_nonce_field( 'wp_rest' ); ?>
            <button class="submit" type="submit"><?php _e('Submit', 'parlay-api'); ?></button>
        </form>
        <div class="form-error-message"></div>
    <?php endif; ?>
</div>
