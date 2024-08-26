<?php

namespace Parlay\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for registering blocks
 */
class Block {

	public static function init() {

		$build_dir = PARLAY_API_DIR . 'build';
		foreach ( scandir( $build_dir ) as $result ) {
			$block_location = $build_dir . '/' . $result;

			if ( ! is_dir( $block_location ) || '.' === $result || '..' === $result ) {
				continue;
			}

			register_block_type( $block_location, [
				'render_callback' => function ( $attributes ) use ( $result ) {
					return self::render_block( $attributes, $result );
				},
			] );
		}

		// Frontend
		if ( ! is_admin() ) {
			add_action( 'enqueue_block_assets', __CLASS__ . '::enqueue_scripts' );
		}
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public static function enqueue_scripts() {
		if ( has_block( 'parlay/user-login' ) || has_block( 'parlay/user-register' ) ) {
			wp_enqueue_style( 'parlay-api-style', PARLAY_API_ASSETS_URL . '/css/parlay-form.css', [], PARLAY_API_VERSION );
			wp_enqueue_script( 'parlay-api-script', PARLAY_API_ASSETS_URL . '/js/parlay-form.js', [ 'jquery' ], PARLAY_API_VERSION, true );
			wp_localize_script( 'parlay-api', 'parlay_frontend', PGS()->localized_strings() );
		}
	}

	/**
	 * Block template.
	 */
	public static function render_block( $attributes, $block_name ) {

		ob_start();
		parlay_template( $block_name, $attributes );

		return ob_get_clean();
	}
}
