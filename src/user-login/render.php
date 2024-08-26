<?php
/**
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 */

?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<div id="parlay-login-block">
		<h2><?php echo esc_html( $attributes['headingText'] ); ?></h2>
		<form id="parlay-api-login-form" method="post">
			<input type="text" id="username" name="username" placeholder="<?php echo esc_html( $attributes['usernamePlaceholder'] ); ?>">
			<input type="password" id="password" name="password" placeholder="<?php echo esc_html( $attributes['passwordPlaceholder'] ); ?>">
			<button type="submit"><?php echo esc_html( $attributes['loginButtonText'] ); ?></button>
		</form>
		<div class="login-message"></div>
	</div>
</div>
