<?php

use Parlay\Api\DataManager;

final class Parlay_Media_Edit {

	public static function init() {
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::enqueue_media_edit_scripts' );
		add_filter( 'attachment_fields_to_edit', __CLASS__ . '::media_fields', null, 2 );
		add_filter( 'attachment_fields_to_save', __CLASS__ . '::save_media_fields', 10, 2 );
	}

	public static function enqueue_media_edit_scripts() {
		$screen = get_current_screen();
		if ( ( $screen->base === 'post' || $screen->base === 'upload' ) && $screen->post_type === 'attachment' ) {
			// Styles
			wp_enqueue_style( 'tagify-css', PARLAY_API_ASSETS_URL . '/css/tagify.min.css' );
			
			wp_enqueue_style( 'parlay-media-edit', PARLAY_API_ASSETS_URL . '/css/admin-media-edit.css' );

			// Scripts
			wp_enqueue_script( 'tagify-js', PARLAY_API_ASSETS_URL . '/js/tagify.min.js', [], null, true );
			
			wp_enqueue_script( 'parlay-media-edit', PARLAY_API_ASSETS_URL . '/js/admin-media-edit.js', [ 'jquery', 'tagify-js' ], null, true );

			$lang = DataManager::get_api_settings('language');
			$game_json_url = '';
			if ( file_exists( PARLAY_API_DIR . "cache/casino_games_{$lang}.json" ) ) {
				$game_json_url = PARLAY_API_URL . "cache/casino_games_{$lang}.json";
			}
			
			wp_localize_script( 'parlay-media-edit', 'ParlayMediaEdit', [
				'game_data_json_url' => $game_json_url
			]);
		}
	}

	public static function media_fields( $form_fields, $post ) {
		$parlay_game_id = get_post_meta( $post->ID, 'parlay_game_id', true );

		$form_fields['parlay_game_id'] = array(
			'label' => __( 'Parlay IDs', 'parlay-api' ),
			'input' => 'text',
			'value' => $parlay_game_id,
			'placeholder' => __( 'Type or paste Parlay IDs', 'parlay-api' ),
			'helps' => __( 'Specify the Parlay Game/Room you want this image to appear in the games list. Separate each with a comma.', 'parlay-api' ),
		);

		// Add the Parlay Game Style field
		$parlay_game_style = get_post_meta( $post->ID, 'parlay_game_style', true );
		$styles            = array(
			''                  => __( 'Select Game Style', 'parlay-api' ), // Default empty option
			'STANDARD'          => __( 'STANDARD', 'parlay-api' ),
			'BLAZ'              => __( 'BLAZ', 'parlay-api' ),
			'BHTB'              => __( 'BHTB', 'parlay-api' ),
			'XCASH_DETECTIVE_M' => __( 'XCASH_DETECTIVE_M', 'parlay-api' ),
		);

		$form_fields['parlay_game_style'] = array(
			'label' => __( 'Parlay Game Style', 'parlay-api' ),
			'input' => 'html',
			'html'  => '<select name="attachments[' . $post->ID . '][parlay_game_style]">'
						. implode( '', array_map( function ( $value, $label ) use ( $parlay_game_style ) {
							return sprintf(
								'<option value="%s" %s>%s</option>',
								esc_attr( $value ),
								selected( $parlay_game_style, $value, false ),
								esc_html( $label )
							);
						}, array_keys( $styles ), $styles ) )
						. '</select>',
			'helps' => __( 'Select the game style for this image.', 'parlay-api' ),
		);

		return $form_fields;
	}

	public static function save_media_fields( $post, $attachment ) {
		if ( isset( $attachment['parlay_game_id'] ) ) {
			// Sanitize and save the game IDs as a comma-separated string
			update_post_meta( $post['ID'], 'parlay_game_id', sanitize_text_field($attachment['parlay_game_id']) );
		} else {
			// If no IDs are present, delete the meta
			delete_post_meta( $post['ID'], 'parlay_game_id' );
		}

		if ( isset( $attachment['parlay_game_style'] ) ) {
			update_post_meta( $post['ID'], 'parlay_game_style', sanitize_text_field( $attachment['parlay_game_style'] ) );
		} else {
			delete_post_meta( $post['ID'], 'parlay_game_style' );
		}

		return $post;
	}
}