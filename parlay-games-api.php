<?php
/**
 * Plugin Name: Parlay Games API
 * Description: Parlay games integration with the Parlay Site API
 * Author: PGS
 * Version: 1.2.0
 * Text Domain: parlay-api
 * Domain Path: /languages
 * Requires at least: 6.5.0
 * Requires PHP: 8.1
 * License: MIT
 * Tested up to: 6.6.1
 * Requires Plugins: elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/includes/class-parlay-api-loader.php';
