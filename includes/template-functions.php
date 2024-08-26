<?php
/**
 * Template Functions
 */
use Parlay\Api\UserAuth;

function parlay_login_form_shortcode( $atts ) {

	$atts = shortcode_atts(
		[
			// Add backward compatibility for redirect attribute.
			'login-redirect'  => '',
			'logout-redirect' => '',
		],
		$atts,
		'parlay_api_login'
	);

	// Check login-redirect attribute first, if it empty or not found then check for redirect attribute and add value of this to login-redirect attribute.
	$atts['login-redirect'] = ! empty( $atts['login-redirect'] ) ? $atts['login-redirect'] : ( ! empty( $atts['redirect'] ) ? $atts['redirect'] : '' );

	return parlay_template( 'user-login', $atts );
}
add_shortcode( 'parlay_api_login', 'parlay_login_form_shortcode' );

function parlay_register_form_shortcode( $atts ) {
	ob_start();
	parlay_template( 'user-register', $atts );
	return ob_get_clean();
}
add_shortcode( 'parlay_api_register', 'parlay_register_form_shortcode' );

/**
 * Render template
 *
 * @param string $template_name (required) Name of template
 * @param array $attributes (required)     Array of attributes from block
 */
function parlay_template( $template_name, $attributes = [] ) {

	$template_dir = PARLAY_API_DIR . "templates/{$template_name}.php";
	if ( ! file_exists( $template_dir ) ) {
		return;
	}

	include $template_dir;
}

/**
 * Check if user is authenticated
 *
 * @param bool $redirect   Redirect user if not authenticated
 * @param string $redirect_url Redirect URL
 */
function parlay_is_authenticated( $redirect = false, $redirect_url = '' ) {
	if ( $redirect && UserAuth::is_authenticated() ) {
		wp_safe_redirect( $redirect_url );
		exit;
	}

	return UserAuth::is_authenticated();
}

function parlay_logout_url() {
	return home_url( '/my-account/logout' );
}


/**
 * Get game thumbnail.
 *
 * Display thumbnail order:
 *  1. Media Library - search meta field by ID
 *  2. Thumbnail URL from the API
 *  3. Fallback
 *  4. Default image in the /assets/img/bingo.jpeg
 *
 * @param string $game_id Game ID or Room ID
 * @return bool True if the image exists, false otherwise.
 */
function parlay_game_thumbnail( $game_id, $style = '', $game_thumbnail = '', $fallback = '' ) {
	$thumbnail = '';

	// Try to get the image from the media library
	if ( ! empty( $game_id ) ) {
		error_log( 'thumb ID: ' . $game_id . ' | style: ' . $style );
		$thumbnail = parlay_game_thumbnail_url( $game_id, $style );
	}

	// Try to get the image from the API
	if ( empty( $thumbnail ) && ! empty( $game_thumbnail ) ) {
		error_log( 'empty thumbnail ID: ' . $game_id );
		$response = wp_remote_head( $game_thumbnail );

		// Check if the request was successful and the image exists
		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			$thumbnail = $game_thumbnail;
		}
	}

	// Last resort: try to get the image from the fallback or default
	if ( empty( $thumbnail ) ) {
		if ( ! empty( $fallback ) ) {
			$thumbnail = $fallback;
		} else {
			$thumbnail = PARLAY_API_ASSETS_URL . '/img/bingo.jpeg';
		}
	}

	return $thumbnail;
}

function parlay_game_thumbnail_url( $game_id, $style = '' ) {
	$args = array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'meta_query'     => array(
			array(
				'key'     => 'parlay_game_id',
				'value'   => '(^|,|\b)' . preg_quote( $game_id ) . '($|,|\b)', // Regular expression for game ID
				'compare' => 'REGEXP',
			),
		),
		'posts_per_page' => 10, // Get all attachments
		'fields'         => 'ids',
	);

	// Query the media library
	$media_query = new WP_Query( $args );

	if ( ! empty( $media_query->posts ) ) {
		$attachment_id = $media_query->posts[0];

		// Check if the attachment has a parlay_game_style saved
		if ( ! empty( $style ) ) {
			foreach ( $media_query->posts as $attach_id ) {
				$game_style = get_post_meta( $attach_id, 'parlay_game_style', true );

				if ( ! empty( $game_style ) && $game_style === $style ) {
					$attachment_id = $attach_id;
					break;
				}
			}
		}

		error_log( 'Final thumb ID: ' . $attachment_id );

		return wp_get_attachment_image_url( $attachment_id, 'post-thumbnail' );
	}
}

/**
 * Account page URL
 */
function parlay_account_url( $page = '' ) {
	return home_url( '/my-account' . $page );
}
