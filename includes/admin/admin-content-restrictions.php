<?php
/**
 * Content Restrictions
 */

use Parlay\Api\DataManager;

?>
<div id="pgi-content_restrictions-form" class="pgi-settings-form">

	<form class="parlay-settings-form" action="" method="post">
		<h3 class="pgi-settings-form-header">
			<?php _e( 'Content Restrictions', 'parlay-api' ); ?>
		</h3>
		<p><?php _e( 'Select the post types you want to restrict from public access.', 'parlay-api' ); ?></p>

		<?php

		$saved_post_types = DataManager::get_post_types();
		$post_types       = get_post_types( array(
			'public' => true,
		), 'objects' );

		foreach ( $post_types as $post_type ) :

			$checked = in_array( $post_type->name, $saved_post_types ) ? 'checked' : '';

			if ( 'attachment' == $post_type->name ) {
				continue;
			}

			?>
			<p>
				<label>
					<input type="checkbox" name="pgi-post-types[]" value="<?php echo $post_type->name; ?>" <?php echo $checked; ?> />
					<?php echo $post_type->labels->name; ?>
				</label>
			</p>
		<?php endforeach; ?>
		<p class="submit">
			<input type="submit" name="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'parlay-api' ); ?>">
			<?php wp_nonce_field( 'pgi-content-restrictions', 'pgi-restrict-nonce' ); ?>
		</p>
	</form>
</div>