<div id="pgi-api_settings-form" class="pgi-settings-form">

	<form class="parlay-settings-form" action="" method="post">
		<?php
		$x = 0;
		foreach ( $setting_fields as $section_key => $section ) {
			$section['fields'] = array_filter( $section['fields'] );
			if ( empty( $section['fields'] ) ) {
				continue;
			}
			++$x;
			?>
			<h3 class="pgi-settings-form-header">
				<?php echo esc_html( $section['label'] ); ?>
			</h3>
			<?php
			foreach ( $section['fields'] as $field_key => $field ) {
				$value = '';
				if ( isset( $api_settings[ $field_key ] ) ) {
					$value = $api_settings[ $field_key ];
				} elseif ( isset( $field['default'] ) ) {
					$value = $field['default'];
				}
				?>
				<h4><?php echo $field['label']; ?></h4>
				<?php if ( 'text' === $field['type'] ) { ?>
				<input type="text" name="<?php echo $field_key; ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
					<?php
				}
			}
			if ( $x < count( $setting_fields ) ) {
				echo '<br /><br />'; }
		}
		?>
		<p class="submit">
			<input type="submit" name="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'parlay-api' ); ?>">
			<?php wp_nonce_field( 'pgi-settings', 'pgi-settings-nonce' ); ?>
		</p>
	</form>
</div>