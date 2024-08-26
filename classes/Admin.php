<?php

namespace Parlay\Api;

use Parlay\Api\Utils;
use Parlay\Api\DataManager;

/**
 * Admin class
 */
final class Admin {

	/**
	 * Holds any errors that may arise from
	 * saving admin settings.
	 *
	 * @since 1.0.0
	 * @var array $errors
	 */
	public static $errors = [];

	/**
	 * Initializes admin.
	 */
	public static function init() {
		self::hooks();
	}

	/**
	 * Render admin hooks.
	 *
	 * @since 1.0.0
	 */
	public static function hooks() {
		if ( ! is_admin() ) {
			return;
		}

		register_activation_hook( PARLAY_API_FILE, __CLASS__ . '::activate' );
		register_deactivation_hook( PARLAY_API_FILE, __CLASS__ . '::deactivate' );

		// Add menu
		add_action( 'admin_menu', __CLASS__ . '::add_admin_page' );

		// Enqueue scripts and styles.
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::enqueue_assets' );

		// Email template hooks
		add_action( 'wp_ajax_pgi_load_email_template', __CLASS__ . '::load_email_template' );
		add_action( 'wp_ajax_pgi_save_email_template', __CLASS__ . '::save_email_template' );
		add_action( 'wp_ajax_pgi_send_test_email', __CLASS__ . '::send_test_email' );
		add_action( 'wp_ajax_pgi_handle_editor_image', __CLASS__ . '::handle_editor_image' );

		// Save settings
		self::save_settings();
		self::save_protected_post_types();
		self::save_tools_settings();
	}

	/**
	 * Add admin page for the plugin settings.
	 *
	 * @since 1.0.0
	 */
	public static function add_admin_page() {
		add_menu_page(
			esc_html__( 'Parlay Games', 'parlay-api' ),
			esc_html__( 'Parlay Games', 'parlay-api' ),
			'manage_options',
			'parlay-api',
			__CLASS__ . '::render_settings',
			'dashicons-games',
			'5.5'
		);
	}

	/**
	 * Enqueues the assets for the admin page of the Parlay Games plugin.
	 *
	 * @param string $page The current page slug.
	 * @since 1.0.0
	 */
	public static function enqueue_assets( $page ) {

		if ( strpos( $page, 'parlay-api' ) === false ) {
			return;
		}

		// General styles and js
		wp_enqueue_style(
			'parlay-api-admin',
			PARLAY_API_ASSETS_URL . '/css/admin-settings.css',
			false,
			PARLAY_API_VERSION
		);

		wp_enqueue_style(
			'parlay-email-editor-css',
			PARLAY_API_ASSETS_URL . '/css/grapesjs.min.css',
			false,
			PARLAY_API_VERSION
		);

		wp_enqueue_script(
			'parlay-api-admin',
			PARLAY_API_ASSETS_URL . '/js/admin-settings.js',
			[ 'jquery', 'wp-i18n' ],
			PARLAY_API_VERSION,
			false
		);

		wp_enqueue_script(
			'parlay-email-editor-js',
			PARLAY_API_ASSETS_URL . '/js/grapesjs.min.js',
			[],
			PARLAY_API_VERSION,
			false
		);

		wp_enqueue_script(
			'parlay-email-editor-newsletter-js',
			PARLAY_API_ASSETS_URL . '/js/grapesjs-newsletter-preset.min.js',
			[ 'parlay-email-editor-js' ],
			PARLAY_API_VERSION,
			false
		);

		// SweetAlert2
		wp_register_script( 'parlay-sweetalert2', PARLAY_API_ASSETS_URL . '/js/sweetalert2.all.min.js', [], PARLAY_API_VERSION, true );
		wp_enqueue_script( 'parlay-alert', PARLAY_API_ASSETS_URL . '/js/parlay-alert.js', [ 'parlay-sweetalert2' ], PARLAY_API_VERSION, true );
		wp_enqueue_style( 'parlay-alert', PARLAY_API_ASSETS_URL . '/css/parlay-alert.css', [], PARLAY_API_VERSION );

		wp_enqueue_script( 'parlay-clipboard-js', PARLAY_API_ASSETS_URL . '/js/clipboard.min.js', [], PARLAY_API_VERSION, false );
		wp_enqueue_script( 'parlay-beautify-html', PARLAY_API_ASSETS_URL . '/js/beautify-html.min.js', [], PARLAY_API_VERSION, false );
	}

	/**
	 * List of settings fields
	 *
	 * @var settings
	 */
	private static function settings_fields() {
		return [
			'api_credentials'  => [
				'label'  => __( 'API Credentials', 'parlay-api' ),
				'type'   => 'section',
				'fields' => [
					'api_url'          => [
						'label' => __( 'API URL', 'parlay-api' ),
						'type'  => 'text',
					],
					'site_id'          => [
						'label' => __( 'Site ID', 'parlay-api' ),
						'type'  => 'text',
					],
					'api_key'          => [
						'label' => __( 'API Key', 'parlay-api' ),
						'type'  => 'text',
					],
					'api_token'        => [
						'label' => __( 'API Token', 'parlay-api' ),
						'type'  => 'text',
					],
					'api_token_public' => [
						'label' => __( 'API Token (Public)', 'parlay-api' ),
						'type'  => 'text',
					],
					'ecommerce_url'    => [
						'label' => __( 'Ecommerce URL', 'parlay-api' ),
						'type'  => 'text',
					],
				],
			],
			'account_defaults' => [
				'label'  => __( 'Defaults For Short Registration', 'parlay-api' ),
				'type'   => 'section',
				'fields' => [
					'address'   => [
						'label' => __( 'Address 1', 'parlay-api' ),
						'type'  => 'text',
					],
					'post_code' => [
						'label' => __( 'Postal Code', 'parlay-api' ),
						'type'  => 'text',
					],
					'province'  => [
						'label'   => __( 'Province', 'parlay-api' ),
						'type'    => 'text',
						'default' => 'SP',
					],
					'country'   => [
						'label'   => __( 'Country', 'parlay-api' ),
						'type'    => 'text',
						'default' => 'BR',
					],
					'currency'  => [
						'label' => __( 'Currency', 'parlay-api' ),
						'type'  => 'text',
					],
					'language'  => [
						'label' => __( 'Language', 'parlay-api' ),
						'type'  => 'text',
					],
				],
			],
		];
	}

	/**
	 * Renders the admin settings content.
	 *
	 * @since 1.0.0
	 */
	public static function render_settings() {
		include PARLAY_API_DIR . '/includes/admin/admin-settings.php';
	}

	/**
	 * Renders the nav items for the admin settings menu.
	 *
	 * @since 1.0.0
	 */
	public static function render_nav_items() {

		$item_data = array(
			'welcome'              => array(
				'title'    => __( 'Welcome', 'parlay-api' ),
				'priority' => 50,
			),
			'api_settings'         => array(
				'title'    => __( 'API Settings', 'parlay-api' ),
				'priority' => 51,
			),
			'email_templates'      => array(
				'title'    => __( 'Email Templates', 'parlay-api' ),
				'priority' => 52,
			),
			'content_restrictions' => array(
				'title'    => __( 'Content Restrictions', 'parlay-api' ),
				'priority' => 53,
			),
			'tools'                => array(
				'title'    => __( 'Tools', 'parlay-api' ),
				'priority' => 54,
			),
		);

		foreach ( $item_data as $key => $data ) {
			echo '<li><a href="#' . $key . '">' . $data['title'] . '</a></li>';
		}
	}

	/**
	 * Renders the admin settings page heading.
	 *
	 * @since 1.0.0
	 */
	public static function render_page_heading() {
		$icon          = PARLAY_API_ASSETS_URL . '/img/logo.png';
		$branding_name = '';

		if ( ! empty( $icon ) ) {
			echo '<img role="presentation" src="' . $icon . '" />';
		}

		echo '<span>' . sprintf( _x( '%s Settings', '%s stands for custom branded "Casino" name.', 'parlay-api' ), $branding_name ) . '</span>';
	}

	/**
	 * Renders the update message.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function render_update_message() {
		if ( ! empty( self::$errors ) ) {
			foreach ( self::$errors as $message ) {
				echo '<div class="error"><p>' . $message . '</p></div>';
			}
		} elseif ( ! empty( $_POST ) && ( isset( $_POST['pgi-settings-nonce'] ) || isset( $_POST['pgi-restrict-nonce'] ) || isset( $_POST['pgi-tools-nonce'] ) ) ) {
			echo '<div class="updated"><p>' . __( 'Settings updated!', 'parlay-api' ) . '</p></div>';
		}
	}

	/**
	 * Renders the admin forms.
	 *
	 * @since 1.0.0
	 */
	public static function render_forms() {
		$settings = get_option( 'parlay_settings' );

		$api_settings = [];

		if ( $settings ) {
			$api_settings = $settings;
		} elseif ( isset( $_POST['pgi-settings-nonce'] ) ) {
			$api_settings = $_POST;
		}

		$setting_fields  = self::settings_fields();
		$email_templates = DataManager::get_email_templates();

		include PARLAY_API_DIR . 'includes/admin/admin-welcome.php';
		include PARLAY_API_DIR . 'includes/admin/admin-form.php';
		include PARLAY_API_DIR . 'includes/admin/admin-email-templates-builder.php';
		include PARLAY_API_DIR . 'includes/admin/admin-content-restrictions.php';
		include PARLAY_API_DIR . 'includes/admin/admin-tools.php';
	}

	/**
	 * Save admin settings.
	 *
	 * @since 1.0.0
	 */
	private static function save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			self::add_error( __( 'You do not have sufficient permissions to access this page.', 'parlay-api' ) );
		}

		// Save settings
		if ( isset( $_POST['pgi-settings-nonce'] ) && wp_verify_nonce( $_POST['pgi-settings-nonce'], 'pgi-settings' ) ) {
			$api_settings = [];
			foreach ( self::settings_fields() as $section ) {
				foreach ( $section['fields'] as $field_key => $field ) {
					if ( isset( $_POST[ $field_key ] ) ) {

						if ( empty( $_POST[ $field_key ] ) ) {
							self::add_error( sprintf( __( 'Empty field: %s', 'parlay-api' ), $field['label'] ) );
						}

						if ( 'api_key' === $field_key && ! Utils::isValidKey( $_POST[ $field_key ] ) ) {
							self::add_error( __( 'You submitted an invalid API Key (alphanumeric characters only).', 'parlay-api' ) );
						}

						if ( 'api_token' === $field_key && ! Utils::isValidKey( $_POST[ $field_key ] ) ) {
							self::add_error( __( 'You submitted an invalid API Token (alphanumeric characters only).', 'parlay-api' ) );
						}

						if ( empty( self::$errors ) ) {
							$api_settings[ $field_key ] = $_POST[ $field_key ];
						}
					}
				}
			}

			if ( ! empty( $api_settings ) ) {
				Utils::update_option( 'parlay_settings', $api_settings );
			}
		}
	}

	private static function save_protected_post_types() {
		if ( isset( $_POST['pgi-restrict-nonce'] ) && wp_verify_nonce( $_POST['pgi-restrict-nonce'], 'pgi-content-restrictions' ) ) {

			$post_types = array();

			if ( isset( $_POST['pgi-post-types'] ) && is_array( $_POST['pgi-post-types'] ) ) {
				$post_types = array_map( 'sanitize_text_field', $_POST['pgi-post-types'] );
			}

			Utils::update_option( 'parlay_protected_post_types', $post_types, true );
		}
	}

	private static function save_tools_settings() {
		if ( isset( $_POST['pgi-tools-nonce'] ) && wp_verify_nonce( $_POST['pgi-tools-nonce'], 'pgi-tools' ) ) {

			if ( isset( $_POST['pgi-game-cache'] ) ) {
				$button = $_POST['pgi-game-cache'];

				if ( 'clear-cache' === $button ) {
					do_action( 'qm/debug', 'pgi_clear_games_cache' );
					DataManager::clear_games_cache();
				} elseif ( 'reset-cache' === $button ) {
					do_action( 'qm/debug', 'pgi_reset_games_cache' );
					DataManager::reset_games_cache();
				} elseif ( 'reset-tags-cache' === $button ) {
					do_action( 'qm/debug', 'pgi_reset_games_tags_cache' );
					DataManager::get_tags( 'all', true );
				}
			}
		}
	}

	/**
	 * Get specific template to load into the builder
	 *
	 * @since 1.2.0
	 */
	public static function load_email_template() {
		if ( ! isset( $_POST['template_id'] ) ) {
			wp_send_json_error();
		}

		$template_id = sanitize_text_field( $_POST['template_id'] );
		$template    = DataManager::get_template_data( $template_id );

		wp_send_json_success( $template );
	}

	/**
	 * Save email template
	 *
	 * @since 1.2.0
	 */
	public static function save_email_template() {
		if ( ! isset( $_POST['_pgs_nonce'] ) || ! wp_verify_nonce( $_POST['_pgs_nonce'], 'email-template' ) ) {
			error_log( 'nonce error' );
			wp_send_json_error();
		}

		if ( isset( $_POST['template_id'], $_POST['html'], $_POST['css'], $_POST['subject'] ) ) {
			$template_id = sanitize_text_field( $_POST['template_id'] );
			$html        = wp_unslash( $_POST['html'] ); // stripslashes_deep
			$css         = wp_unslash( $_POST['css'] );
			$from        = sanitize_email( $_POST['from'] );
			$subject     = sanitize_text_field( $_POST['subject'] );

			// Save HTML file to the plugin directory
			$template_base = DataManager::get_email_template_dir() . $template_id;
			PGS()->filesystem->file_put_contents( $template_base . '.html', $html );
			PGS()->filesystem->file_put_contents( $template_base . '.css', $css );

			// Get existing templates
			$saved_templates = get_option( 'parlay_email_templates', [] );

			$saved_templates[ $template_id ] = [
				'html_file' => $template_id . '.html',
				'css_file'  => $template_id . '.css',
				'from'      => ! empty( $from ) ? $from : get_bloginfo( 'admin_email' ),
				'subject'   => $subject,
			];

			// Save templates
			Utils::update_option( 'parlay_email_templates', $saved_templates, true );
			wp_send_json_success();
		}

		wp_send_json_error();
	}

	public static function handle_editor_image() {
		if ( ! isset( $_POST['_pgs_nonce'] ) || ! wp_verify_nonce( $_POST['_pgs_nonce'], 'email-template' ) ) {
			wp_send_json_error();
		}

		$action_type = $_POST['actionType'];
		$image_src   = $_POST['image'];
		$upload_dir  = wp_upload_dir();

		// Upload image from editor
		if ( 'add' === $action_type ) {
			// Extract the image type from the base64 data
			list($type, $data) = explode( ';', $image_src );
			list(, $data)      = explode( ',', $data );
			$data              = base64_decode( $data );

			// Save the image to the uploads directory
			$file_path = $upload_dir['path'] . '/' . uniqid() . '.png';
			file_put_contents( $file_path, $data );

			// Generate the image URL
			$file_url = $upload_dir['url'] . '/' . basename( $file_path );

			// Send the image URL as the response
			wp_send_json_success( [ 'imageUrl' => $file_url ] );
		}

		// Remove image
		if ( 'remove' === $action_type ) {
			$file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $image_src );

			// Delete the file
			if ( file_exists( $file_path ) ) {
				unlink( $file_path );
				wp_send_json_success();
			}
		}

		wp_send_json_error();
	}

	public static function send_test_email() {
		if ( ! isset( $_POST['_pgs_nonce'] ) || ! wp_verify_nonce( $_POST['_pgs_nonce'], 'email-template' ) ) {
			wp_send_json_error();
		}

		if ( isset( $_POST['template_id'], $_POST['html'], $_POST['subject'] ) ) {
			$mailer = new \Parlay\Api\Mailer();
			$data   = [
				'template_id' => sanitize_text_field( $_POST['template_id'] ),
				'from'        => sanitize_email( $_POST['from'] ),
				'to'          => sanitize_email( $_POST['to'] ),
				'subject'     => sanitize_text_field( $_POST['subject'] ),
				'html'        => wp_unslash( $_POST['html'] ),
				'css'         => wp_unslash( $_POST['css'] ),
			];

			wp_send_json_success( $mailer->sendTestEmail( $data ) );
		}

		wp_send_json_error();
	}

	/**
	 * Adds an error message to the list of errors.
	 *
	 * @param string $message The error message to be added.
	 * @return void
	 */
	public static function add_error( $message ) {
		self::$errors[] = $message;
	}

	public static function activate() {
	}
	public static function deactivate() {
	}
}
