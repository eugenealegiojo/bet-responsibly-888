<?php
/**
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 */

$block_attrs = isset( $block ) ? get_block_wrapper_attributes() : '';
$title       = isset( $attributes['title'] ) ? $attributes['title'] : '';
$heading     = isset( $attributes['heading_text'] ) ? $attributes['heading_text'] : '';
$button_text = isset( $attributes['button_text'] ) ? $attributes['button_text'] : __('Reset Password', 'parlay-api');
?>
<div <?php echo $block_attrs; ?>>
	<div class="parlay-forgot-password-block">
		<?php if ( ! parlay_is_authenticated( true, home_url() ) ) : ?>
			<?php if ( ! empty( $title ) ) : ?>
				<h3 class="heading-text"><?php echo $title; ?></h3>
			<?php endif; ?>

			<?php if ( ! empty( $heading ) ) : ?>
				<p class="heading-text""><?php echo $heading; ?></p>
			<?php endif; ?>

			<form id="parlay-api-forgot-password" class="parlay-form" method="post" data-endpoint="/account/forgot-password">
				<input type="email" id="email" name="email" required="" placeholder="<?php echo esc_attr__( 'Enter your email', 'parlay-api' ); ?>">
				<?php wp_nonce_field( 'wp_rest' ); ?>
				<button class="submit" type="submit"><?php echo $button_text; ?></button>
			</form>
			<div class="form-error-message"></div>
		<?php endif; ?>
	</div>
</div>
