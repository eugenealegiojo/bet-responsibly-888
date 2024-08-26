<?php

if ( ! function_exists( 'env' ) ) {
	/**
	 * Gets the value of an environment variable.
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function env( $key, $default = null ) {
		$value = getenv( $key );

		if ( $value === false ) {
			return $default;
		}

		switch ( strtolower( $value ) ) {
			case 'true':
			case '(true)':
				return true;
			case 'false':
			case '(false)':
				return false;
			case 'empty':
			case '(empty)':
				return '';
			case 'null':
			case '(null)':
				return;
		}

		if ( ( $valueLength = strlen( $value ) ) > 1 && $value[0] === '"' && $value[ $valueLength - 1 ] === '"' ) {
			return substr( $value, 1, -1 );
		}

		return $value;
	}
}

// Elementor Compatibility
function parlay_is_elementor_mode() {
	if ( defined( 'ELEMENTOR_IS_EDITOR' ) && ELEMENTOR_IS_EDITOR ) {
		error_log( 'on ELEMENTOR_IS_EDITOR' );
		return true;
	}

	if ( isset( $_GET['elementor-preview'] ) ) {
		error_log( 'on Elelmentor preview' );
		return true;
	}

	return false;
}
