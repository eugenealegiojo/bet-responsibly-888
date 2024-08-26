<?php

namespace Parlay\Api;

use Parlay\Api\Admin;
use Parlay\Api\Block;
use Parlay\Api\DataManager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Parlay\Api\UserAuth;
use Parlay\Api\Account;
use Parlay\Api\Affiliates;
use Parlay_Filesystem;
use Parlay_Media_Edit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class, serves as an entry point.
 */
final class Plugin {

	/**
	 * Holds the session data for the current user session.
	 */
	public $session;

	/**
	 * PGS default configurations
	 */
	public $config;

	/**
	 * PGS API errors translation
	 */
	public $error_i18n;

	/**
	 * Logger
	 */
	public $log;

	/**
	 * Filesystem for plugin caching
	 */
	public $filesystem;

	/**
	 * REST API base endpoint
	 */
	public $rest_base;

	/**
	 * Class instance
	 *
	 * @since 1.0.0
	 * @static
	 * @var Plugin $instance
	 */
	private static $instance = null;

	/**
	 * Instance.
	 *
	 * Ensures only one instance of the plugin class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {

		if ( ! empty( self::$instance ) && ( self::$instance instanceof self ) ) {
			return self::$instance;
		}

		self::$instance = new self();

		// Bootstrap the plugin
		self::$instance->init_hooks();

		self::$instance->session    = new \Parlay\Api\Session();
		self::$instance->filesystem = Parlay_Filesystem::instance();

		self::$instance->log = new Logger( 'PGS' );
		self::$instance->log->pushHandler( new StreamHandler( 'php://stdout', Logger::DEBUG ) );

		// self::$instance->reset_alert();

		new \Parlay\Api\Route\RestApiProvider();
		new \Parlay\Api\DataManager();

		return self::$instance;
	}

	/**
	 * Register all the plugin hooks.
	 *
	 * @since 1.0.0
	 */
	public function init_hooks() {
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'wp', [ $this, 'render_data' ] );

		// Widgets
		add_action( 'plugins_loaded', [ '\Parlay\Api\Elementor_Widget', 'instance' ] );
		add_action( 'template_redirect', [ $this, 'authenticated_content' ] );
		add_action( 'template_redirect', [ $this, 'handle_template_redirect' ] );
		// add_action( 'template_redirect', [$this, 'handle_custom_ajax_request'] );

		// Scripts & styles
		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ] );
	}

	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		// Define REST path
		PGS()->rest_base = [
			'endpoint' => 'parlay-api/v1',
			'url'      => rest_url( 'parlay-api/v1' ),
		];
		$this->init_config_data();

		Block::init();
		UserAuth::init();
		Account::init();
		Affiliates::init();

		if ( is_admin() ) {
			Admin::init();
			Parlay_Media_Edit::init();
		}

		self::rewrite_rules();
	}

	public function init_config_data() {
		include_once PARLAY_API_DIR . 'includes/config/config.php';
		include_once PARLAY_API_DIR . 'includes/config/errors-lang.php';

		PGS()->config = $config;
		PGS()->error_i18n = $lang;
	}

	public function render_data() {

		// $user_data = UserAuth::get_user_data();

		// do_action( 'qm/debug', 'authenticated: ' . print_r( PGS()->session->get( 'authenticated' ), true ) );
		do_action( 'qm/debug', 'info_cached: ' . print_r( PGS()->session->get( 'info_cached' ), true ) );
		do_action( 'qm/debug', 'user session data: ' . print_r( PGS()->session->get_userdata(), true ) );

		// $games = DataManager::getGames();

		// do_action( 'qm/debug', 'games: ' . print_r( $games, true ) );

		// $user = $this->data::getUser();

		// $user = $this->data->registerUser();
	}

	public static function rewrite_rules() {
		add_rewrite_rule(
			'^popgame/([\w-]+)/?',
			'index.php?pgs_redirect=popgame&pgs_popgame=$matches[1]',
			'top'
		);

		add_rewrite_rule(
			'^' . Account::SLUG . '/ecom/?',
			'index.php?pagename=' . Account::SLUG . '&pgs_redirect=ecom',
			'top'
		);

		add_rewrite_tag( '%pgs_redirect%', '([\w-]+)' );
		add_rewrite_tag( '%pgs_popgame%', '([\w-]+)' );
	}

	public function handle_template_redirect() {
		global $wp_query;

		if ( ! isset( $wp_query->query_vars['pgs_redirect'] ) ) {
			return;
		}

		// Handle popgame
		if ( 'popgame' === $wp_query->query_vars['pgs_redirect'] ) {
			$game      = $wp_query->query_vars['pgs_popgame'];
			$game_data = explode( '-', $game );

			if ( ! in_array( $game_data[0], [ 'casino', 'bingo' ] ) || ! isset( $game_data[1] ) ) {
				return;
			}

			$args['category'] = $game_data[0];
			$game_id          = $game_data[1];

			if ( 'casino' === $args['category'] ) {
				$args['gameId'] = $game_id;
			} elseif ( 'bingo' === $args['category'] ) {
				$args['roomId'] = $game_id;
			}

			$launch_url = \Parlay\Api\DataManager::get_launch_url( $args );

			if ( ! empty( $launch_url ) ) {
				include PARLAY_API_DIR . 'templates/redirect/popgame.php';
			}
		}

		// Handle ecom
		if ( 'ecom' === $wp_query->query_vars['pgs_redirect'] ) {
			include PARLAY_API_DIR . 'templates/redirect/ecom.php';
		}

		exit();
	}

	/**
	 * Localized strings for login and registration forms.
	 *
	 * @since 1.0.0
	 */
	public static function localized_strings() {
		return [
			'restBase' => PGS()->rest_base['url'],
			'errors'   => [
				'required'      => __( 'Required field.', 'parlay-api' ),
				'passwordCount' => __( 'Password must be at least 5 characters', 'parlay-api' ),
				'passwordMatch' => __( 'Passwords do not match', 'parlay-api' ),
				'invalidEmail'  => __( 'Invalid email', 'parlay-api' ),
				'other'         => __( 'Something is wrong', 'parlay-api' ),
			],
		];
	}

	/**
	 * Get the error token translation
	 * 
	 * @since 1.0.0
	 */
	public static function show_error_i18n( $code ) {
		return PGS()->error_i18n[ $code ] ?? $code;
	}

	/**
	 * Checks to see if the content is being accessed by an authenticated user.
	 *
	 * @return void
	 */
	public function authenticated_content() {
		$protected_post_types = DataManager::get_post_types();

		if ( ! empty( $protected_post_types ) && is_singular( $protected_post_types ) && ! UserAuth::is_authenticated() ) {
			// Redirect non-logged-in users to the login page
			wp_redirect( home_url( '/login' ) );
			exit;
		}
	}

	/**
	 * Register scripts and styles.
	 */
	public function register_scripts() {
		wp_register_script( 'parlay-sweetalert2', PARLAY_API_ASSETS_URL . '/js/sweetalert2.all.min.js', [], PARLAY_API_VERSION, true );
		wp_register_script( 'parlay-alert', PARLAY_API_ASSETS_URL . '/js/parlay-alert.js', [ 'parlay-sweetalert2' ], PARLAY_API_VERSION, true );
		wp_register_style( 'parlay-alert', PARLAY_API_ASSETS_URL . '/css/parlay-alert.css', [], PARLAY_API_VERSION );

		error_log( 'alert: ' . print_r( PGS()->get_alert(), true ) );
		if ( ! empty( PGS()->get_alert() ) ) {
			wp_enqueue_script( 'parlay-sweetalert2' );
			wp_enqueue_style( 'parlay-alert' );
			wp_enqueue_script( 'parlay-alert' );

			wp_localize_script( 'parlay-alert', 'parlayFrontend', [
				'login_url' => home_url( '/login' ),
				'alert'     => PGS()->get_alert(),
			] );

			// Reset alert
			PGS()->reset_alert();
		}
	}

	public function set_alert( $action, $alert = [] ) {
		if ( 'token' === $action && empty( $alert ) ) {
			$alert = [
				'type'    => 'error',
				'message' => __( 'Your session has expired. Please log in again.', 'parlay-api' ),
				'timer'   => 3000,
			];
		}

		if ( 'register_success' === $action && empty( $alert ) ) {
			$alert = [
				'type'    => 'success',
				'message' => __( 'Thank you for registering! Please check your email to activate your account.', 'parlay-api' ),
				'timer'   => 5000,
			];
		}

		$_SESSION['pgs_alert'][ $action ] = $alert;
	}

	public function get_alert( $action = '' ) {
		if ( ! isset( $_SESSION['pgs_alert'] ) ) {
			return null;
		}

		$alert = null;
		if ( ! empty( $action ) ) {
			if ( empty( isset( $_SESSION['pgs_alert'][ $action ] ) ) ) {
				return null;
			}
			$alert = $_SESSION['pgs_alert'][ $action ];
		} elseif ( isset( $_SESSION['pgs_alert']['token'] ) ) { // Check for token error
			$alert = $_SESSION['pgs_alert']['token'];
		} elseif ( isset( $_SESSION['pgs_alert']['account'] ) ) {
			$alert = $_SESSION['pgs_alert']['account'];
		} elseif ( isset( $_SESSION['pgs_alert']['activate'] ) ) {
			$alert = $_SESSION['pgs_alert']['activate'];
		} elseif ( isset( $_SESSION['pgs_alert']['register_success'] ) ) {
			$alert = $_SESSION['pgs_alert']['register_success'];
		}

		return $alert;
	}

	public function reset_alert() {
		if ( ! isset( $_SESSION['pgs_alert'] ) ) {
			return;
		}

		error_log( 'reseting alert' );

		unset( $_SESSION['pgs_alert'] );
	}
}

/**
 * Returns the instance of Plugin.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 */
function PGS() {
	return Plugin::instance();
}
