<?php

namespace Parlay\Api;

use Parlay\Api\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class Elementor_Widget {

	/**
	 * Singleton instance
	 *
	 * @access private
	 * @since 1.0.0
	 */
	private static $_instance = null;

	/**
	 * Create a single instance of the class if it doesn't already exist.
	 *
	 * @return self
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'elementor/init', [ $this, 'init' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_parlay_widget_category' ] );
	}

	/**
	 * Initiate the addon.
	 */
	public function init() {
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'register_widget_scripts' ] );
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'after_enqueue_scripts' ] );
	}

	public function register_widgets( $widgets_manager ) {
		require_once PARLAY_API_DIR . 'includes/elementor-widgets/login-register.php';
		require_once PARLAY_API_DIR . 'includes/elementor-widgets/register-form.php';
		require_once PARLAY_API_DIR . 'includes/elementor-widgets/login-form.php';
		require_once PARLAY_API_DIR . 'includes/elementor-widgets/games-list.php';
		require_once PARLAY_API_DIR . 'includes/elementor-widgets/my-account.php';
		require_once PARLAY_API_DIR . 'includes/elementor-widgets/forgot-password.php';

		$widgets_manager->register( new \Parlay\Api\Login_Register_Widget() );
		$widgets_manager->register( new \Parlay\Api\Register_Form_Widget() );
		$widgets_manager->register( new \Parlay\Api\Login_Form_Widget() );
		$widgets_manager->register( new \Parlay\Api\GamesList() );
		$widgets_manager->register( new \Parlay\Api\MyAccount_Widget() );
		$widgets_manager->register( new \Parlay\Api\ForgotPassword_Form_Widget() );
	}

	public function register_parlay_widget_category( $elements_manager ) {
		$elements_manager->add_category(
			'parlay-games-api',
			[
				'title' => esc_html__( 'Parlay Games API', 'parlay-api' ),
				'icon'  => 'fa fa-plug', // Optional icon
			]
		);
	}

	public function register_widget_scripts() {
		// Styles
		wp_register_style( 'parlay-api-style', PARLAY_API_ASSETS_URL . '/css/parlay-form.css', [], PARLAY_API_VERSION );
		wp_register_style( 'parlay-games-widget', PARLAY_API_ASSETS_URL . '/css/games-widget.css', [], PARLAY_API_VERSION );

		// Login widget
		if ( ! \Parlay\Api\UserAuth::is_authenticated() ) {
			wp_register_style( 'parlay-login-widget', PARLAY_API_ASSETS_URL . '/css/login-widget.css', [], PARLAY_API_VERSION );
			wp_register_script( 'parlay-login-widget', PARLAY_API_ASSETS_URL . '/js/login-widget.js', [ 'jquery', 'parlay-api-script' ], PARLAY_API_VERSION, true );
			// wp_register_script( 'parlay-login-widget-popup', PARLAY_API_ASSETS_URL . '/js/login-widget-popup.js', [ 'jquery', 'parlay-api-script' ], PARLAY_API_VERSION, true );
		}

		// Scripts

		// wp_register_script( 'parlay-register-widget', PARLAY_API_ASSETS_URL . '/js/register-widget.js', [ 'jquery', 'parlay-api-script' ], PARLAY_API_VERSION, true );
		wp_register_script( 'parlay-games-filter', PARLAY_API_ASSETS_URL . '/js/games-filter.js', [], PARLAY_API_VERSION, true );
		wp_register_script( 'parlay-games-widget', PARLAY_API_ASSETS_URL . '/js/games-widget.js', [], PARLAY_API_VERSION, true );
		wp_localize_script( 'parlay-games-widget', 'parlayGames', array_merge(
			[
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'login_url'        => home_url( '/login' ),
				'popgame_url'      => home_url( '/popgame' ),
				'is_authenticated' => \Parlay\Api\UserAuth::is_authenticated(),
			],
			PGS()->localized_strings()
		) );

		wp_register_script( 'parlay-api-script', PARLAY_API_ASSETS_URL . '/js/parlay-form.js', [ 'jquery' ], PARLAY_API_VERSION, true );
		wp_localize_script( 'parlay-api-script', 'parlay_frontend', array_merge(
			[
				'redirect_url' => \Parlay\Api\DataManager::get_redirect_form_url(),
			], PGS()->localized_strings()
		) );

		// Account widget
		// wp_register_script( 'parlay-account-widget', PARLAY_API_ASSETS_URL . '/js/account-widget.js', [ 'jquery' ], PARLAY_API_VERSION, true );

		// Account pages scripts/styles. Load the specific script for the page.
		if ( \Parlay\Api\UserAuth::is_authenticated() ) {
			$current_account_page = Account::get_current_page();
			if ( 'deposit' === $current_account_page ) {
				wp_enqueue_script( 'parlay-account-widget-deposit', PARLAY_API_ASSETS_URL . '/js/account-deposit.js', [], PARLAY_API_VERSION, true );
			} elseif ( 'withdraw' === $current_account_page ) {
				wp_enqueue_script( 'parlay-account-widget-withdraw', PARLAY_API_ASSETS_URL . '/js/account-withdraw.js', [], PARLAY_API_VERSION, true );
			} elseif ( 'ticket' === $current_account_page ) {
				wp_enqueue_script( 'parlay-account-account-ticket', PARLAY_API_ASSETS_URL . '/js/account-ticket.js', [], PARLAY_API_VERSION, true );
			} else {
				wp_enqueue_script( 'parlay-account-widget', PARLAY_API_ASSETS_URL . '/js/account-widget.js', [ 'jquery' ], PARLAY_API_VERSION, true );
			}

			if ( in_array( $current_account_page, [ 'deposit', 'withdraw' ] ) ) {
				wp_enqueue_script( 'parlay-account-ecom', PARLAY_API_ASSETS_URL . '/js/ecom-receive-message.js', [ 'jquery' ], PARLAY_API_VERSION, true );
				wp_localize_script( 'parlay-account-ecom', 'ecom_frontend',
					[
						'ecomRoot' => \Parlay\Api\DataManager::get_api_settings( 'ecommerce_url' ),
					]
				);
			}

			wp_enqueue_style( 'parlay-account-style', PARLAY_API_ASSETS_URL . '/css/account-style.css', [], PARLAY_API_VERSION );
		}
	}

	/**
	 * Enqueue scripts and styles for game order. It should load only on editor.
	 *
	 * @since 1.2.0
	 */
	public function after_enqueue_scripts() {
		wp_enqueue_style( 'tagify-css', PARLAY_API_ASSETS_URL . '/css/tagify.min.css' );
		wp_enqueue_style( 'games-widget-editor', PARLAY_API_ASSETS_URL . '/css/games-widget-editor.css' );

		wp_register_script( 'tagify-js', PARLAY_API_ASSETS_URL . '/js/tagify.min.js', [], PARLAY_API_VERSION, true );
		wp_register_script( 'games-widget-editor', PARLAY_API_ASSETS_URL . '/js/game-widget-editor.js', [ 'tagify-js' ], PARLAY_API_VERSION, true );
		wp_enqueue_script( 'games-widget-editor' );

		$lang          = \Parlay\Api\DataManager::get_api_settings( 'language' );
		$game_json_url = '';
		if ( file_exists( PARLAY_API_DIR . "cache/casino_games_{$lang}.json" ) ) {
			$game_json_url = PARLAY_API_URL . "cache/casino_games_{$lang}.json";
		}

		wp_localize_script( 'games-widget-editor', 'ParlayGamesList', [
			'game_data_json_url' => $game_json_url,
		] );
	}
}
