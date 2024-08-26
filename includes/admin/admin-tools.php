<?php
/**
 * Tools section
 */

?>
<div id="pgi-tools-form" class="pgi-settings-form">
	<form class="parlay-settings-form" action="" method="post">
		<h3 class="pgi-settings-form-header">
			<?php _e( 'Tools', 'parlay-api' ); ?>
		</h3>
		<!--
		<p class="submit">
			<button class="button button-primary" type="submit" value="clear-cache" name="pgi-game-cache">
				<?php //esc_attr_e( 'Clear Game Cache', 'parlay-api' ); ?>
			</button>
		</p>-->
		<p>
			<button class="button button-primary" type="submit" value="reset-cache" name="pgi-game-cache">
				<?php esc_attr_e( 'Reset Game Cache', 'parlay-api' ); ?>
			</button>
		</p>
		<p>
			<button class="button button-primary" type="submit" value="reset-tags-cache" name="pgi-game-cache">
				<?php esc_attr_e( 'Reset Game Tags Cache', 'parlay-api' ); ?>
			</button>
		</p>
		<?php wp_nonce_field( 'pgi-tools', 'pgi-tools-nonce' ); ?>
	</form>
</div>