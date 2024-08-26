<?php
/**
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 */

$block_attrs      = isset( $block ) ? get_block_wrapper_attributes() : '';
$title            = isset( $attributes['title'] ) ? $attributes['title'] : __( 'Login', 'parlay-api' );
$forgot_pass_text = isset( $attributes['forgot_password_text'] ) ? $attributes['forgot_password_text'] : '';
$forgot_pass_link = isset( $attributes['forgot_password_link'] ) ? $attributes['forgot_password_link'] : '';
?>
<div <?php echo $block_attrs; ?>>
	<div class="parlay-login-block parlay-page-login">
		<?php if ( ! parlay_is_authenticated() ) : ?>
			<h2><?php echo $title; ?></h2>
			<form id="parlay-api-login-form" class="parlay-form" method="post" data-endpoint="/account/login">
				
				<input type="text" id="username" name="username" placeholder="<?php echo esc_attr__( 'Username', 'parlay-api' ); ?>">
				<input type="password" id="password" name="password" placeholder="<?php echo esc_attr__( 'Password', 'parlay-api' ); ?>">
				
				<?php if ( ! empty( $forgot_pass_text ) ) : ?>
					<div class="forgot-pwd-link">
						<a href="<?php echo esc_url( $forgot_pass_link ); ?>">
							<?php echo $forgot_pass_text; ?>
						</a>
					</div>
				<?php endif; ?>	

				<button class="submit" type="submit"><?php _e( 'Login', 'parlay-api' ); ?></button>
				
				<?php wp_nonce_field( 'wp_rest' ); ?>
			</form>
			<div class="form-error-message"></div>

			
		<?php else : ?>
			<button class="submit" type="submit">
				<a href="<?php echo esc_url( parlay_logout_url() ); ?>"><?php _e( 'Logout', 'parlay-api' ); ?></a>
			</button>	
		<?php endif; ?>
	</div>
</div>
