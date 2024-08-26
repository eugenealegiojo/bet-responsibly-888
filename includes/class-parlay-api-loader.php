<?php

/**
 * Parlay Games API Loader
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'ParlayApiLoader' ) ) {
	final class ParlayApiLoader {

		/**
		 * Requirements array
		 *
		 * @todo Extend WP_Dependencies
		 * @var array
		 * @since 1.0.0
		 */
		private static $requirements = array(

			// PHP
			'php' => array(
				'minimum' => '7.4',
				'name'    => 'PHP',
				'exists'  => true,
				'current' => false,
				'checked' => false,
				'met'     => false,
			),

			// WordPress
			'wp'  => array(
				'minimum' => '5.8',
				'name'    => 'WordPress',
				'exists'  => true,
				'current' => false,
				'checked' => false,
				'met'     => false,
			),
		);

		/**
		 * Setup plugin requirements
		 *
		 * @since 1.0.0
		 */
		public static function init() {

			self::define_constants();

			// Load or quit
			self::met()
				? self::load()
				: self::quit();
		}

		/**
		 * Define plugin constants.
		 *
		 * @since 1.0.0
		 */
		private static function define_constants() {
			define( 'PARLAY_API_FILE', trailingslashit( dirname( __DIR__, 1 ) ) . 'parlay-games-api.php' );
			define( 'PARLAY_API_BASE', plugin_basename( PARLAY_API_FILE ) );
			define( 'PARLAY_API_DIR', trailingslashit( plugin_dir_path( PARLAY_API_FILE ) ) );
			define( 'PARLAY_API_URL', trailingslashit( plugin_dir_url( __DIR__ ) ) );
			define( 'PARLAY_API_ASSETS_URL', PARLAY_API_URL . 'assets' );
			define( 'PARLAY_API_BUILD_DIR', PARLAY_API_DIR . 'build' );
			define( 'PARLAY_API_BUILD_URL', PARLAY_API_URL . 'build' );
			define( 'PARLAY_API_VERSION', '1.0.0' );

			define( 'PGS_ENVIRONMENT', 'development' );
		}

		/**
		 * Quit without loading
		 *
		 * @since 1.0.0
		 */
		private static function quit() {
			add_action( 'admin_head', __CLASS__ . '::admin_head' );
			add_filter( 'plugin_action_links_' . PARLAY_API_BASE, __CLASS__ . '::plugin_row_links' );
			add_action( 'after_plugin_row_' . PARLAY_API_BASE, __CLASS__ . '::plugin_row_notice' );
		}

		/**
		 * Load the main plugin class.
		 *
		 * @since 1.0.0
		 */
		private static function load() {

			require_once PARLAY_API_DIR . 'vendor/autoload.php';
			require_once PARLAY_API_DIR . 'includes/compatibility.php';
			require_once PARLAY_API_DIR . 'includes/template-functions.php';
			require_once PARLAY_API_DIR . 'includes/elementor-widget.php';
			require_once PARLAY_API_DIR . 'includes/class-parlay-filesystem.php';
			require_once PARLAY_API_DIR . 'includes/admin/class-parlay-media-edit.php';
			require_once PARLAY_API_DIR . 'includes/updater/updater.php';

			// Maybe hook-in the bootstrapper
			if ( class_exists( '\Parlay\Api\Plugin' ) ) {

				// Bootstrap to plugins_loaded before priority 10 to make sure
				// add-ons are loaded after us.
				add_action( 'plugins_loaded', __CLASS__ . '::bootstrap', 4 );

				// Load translations
				add_action( 'plugins_loaded', __CLASS__ . '::load_textdomain' );

				// Register the activation hook
				register_activation_hook( PARLAY_API_FILE, __CLASS__ . '::install' );
			}
		}

		/**
		 * Install, usually on an activation hook.
		 *
		 * @since 1.0.0
		 */
		public static function install() {

			// Bootstrap to include all of the necessary files.
			self::bootstrap();
			flush_rewrite_rules();
		}

		/**
		 * Bootstrap everything.
		 *
		 * @since 1.0.0
		 */
		public static function bootstrap() {
			\Parlay\Api\Plugin::instance();
		}

		/**
		 * Plugin specific URL for an external requirements page.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		private static function unmet_requirements_url() {
			return 'https://parlaygames.com/';
		}

		/**
		 * Plugin specific text to quickly explain what's wrong.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		private static function unmet_requirements_text() {
			esc_html_e( 'This plugin is not fully active.', 'parlay-api' );
		}

		/**
		 * Plugin specific text to describe a single unmet requirement.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		private static function unmet_requirements_description_text() {
			return esc_html__( 'Requires %1$s (%2$s), but (%3$s) is installed.', 'parlay-api' );
		}

		/**
		 * Plugin specific text to describe a single missing requirement.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		private static function unmet_requirements_missing_text() {
			return esc_html__( 'Requires %1$s (%2$s), but it appears to be missing.', 'parlay-api' );
		}

		/**
		 * Plugin specific text used to link to an external requirements page.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		private static function unmet_requirements_link() {
			return esc_html__( 'Requirements', 'parlay-api' );
		}

		/**
		 * Plugin specific aria label text to describe the requirements link.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		private static function unmet_requirements_label() {
			return esc_html__( 'Parlay Games API', 'parlay-api' );
		}

		/**
		 * Plugin specific text used in CSS to identify attribute IDs and classes.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		private static function unmet_requirements_name() {
			return 'parlay-api-requirements';
		}

		/** Agnostic Methods ******************************************************/

		/**
		 * Plugin agnostic method to output the additional plugin row
		 *
		 * @since 1.0.0
		 */
		public static function plugin_row_notice() {
			// wp_is_auto_update_enabled_for_type was introduced in WordPress 5.5.
			$colspan = function_exists( 'wp_is_auto_update_enabled_for_type' ) && wp_is_auto_update_enabled_for_type( 'plugin' ) ? 2 : 1;
			?>
			<tr class="active <?php echo esc_attr( self::unmet_requirements_name() ); ?>-row">
				<th class="check-column">
					<span class="dashicons dashicons-warning"></span>
				</th>
				<td class="column-primary">
					<?php self::unmet_requirements_text(); ?>
				</td>
				<td class="column-description" colspan="<?php echo esc_attr( $colspan ); ?>">
					<?php self::unmet_requirements_description(); ?>
				</td>
			</tr>
			<?php
		}

		/**
		 * Plugin agnostic method used to output all unmet requirement information
		 *
		 * @since 1.0.0
		 */
		private static function unmet_requirements_description() {
			foreach ( self::$requirements as $properties ) {
				if ( empty( $properties['met'] ) ) {
					self::unmet_requirement_description( $properties );
				}
			}
		}

		/**
		 * Plugin agnostic method to output specific unmet requirement information
		 *
		 * @since 1.0.0
		 * @param array $requirement
		 */
		private static function unmet_requirement_description( $requirement = array() ) {

			// Requirement exists, but is out of date
			if ( ! empty( $requirement['exists'] ) ) {
				$text = sprintf(
					self::unmet_requirements_description_text(),
					'<strong>' . esc_html( $requirement['name'] ) . '</strong>',
					'<strong>' . esc_html( $requirement['minimum'] ) . '</strong>',
					'<strong>' . esc_html( $requirement['current'] ) . '</strong>'
				);

				// Requirement could not be found
			} else {
				$text = sprintf(
					self::unmet_requirements_missing_text(),
					'<strong>' . esc_html( $requirement['name'] ) . '</strong>',
					'<strong>' . esc_html( $requirement['minimum'] ) . '</strong>'
				);
			}

			// Output the description
			echo '<p>' . $text . '</p>';
		}

		/**
		 * Plugin agnostic method to output unmet requirements styling
		 *
		 * @since 1.0.0
		 */
		public static function admin_head() {

			// Get the requirements row name
			$name = self::unmet_requirements_name();
			?>

			<style id="<?php echo esc_attr( $name ); ?>">
				.plugins tr[data-plugin="<?php echo esc_html( PARLAY_API_BASE ); ?>"] th,
				.plugins tr[data-plugin="<?php echo esc_html( PARLAY_API_BASE ); ?>"] td,
				.plugins .<?php echo esc_html( $name ); ?>-row th,
				.plugins .<?php echo esc_html( $name ); ?>-row td {
					background: #fff5f5;
				}
				.plugins tr[data-plugin="<?php echo esc_html( PARLAY_API_BASE ); ?>"] th {
					box-shadow: none;
				}
				.plugins .<?php echo esc_html( $name ); ?>-row th span {
					margin-left: 6px;
					color: #dc3232;
				}
				.plugins tr[data-plugin="<?php echo esc_html( PARLAY_API_BASE ); ?>"] th,
				.plugins .<?php echo esc_html( $name ); ?>-row th.check-column {
					border-left: 4px solid #dc3232 !important;
				}
				.plugins .<?php echo esc_html( $name ); ?>-row .column-description p {
					margin: 0;
					padding: 0;
				}
				.plugins .<?php echo esc_html( $name ); ?>-row .column-description p:not(:last-of-type) {
					margin-bottom: 8px;
				}
			</style>
			<?php
		}

		/**
		 * Plugin agnostic method to add the "Requirements" link to row actions
		 *
		 * @since 1.0.0
		 * @param array $links
		 * @return array
		 */
		public static function plugin_row_links( $links = array() ) {

			// Add the Requirements link
			$links['requirements'] =
				'<a href="' . esc_url( self::unmet_requirements_url() ) . '" aria-label="' . esc_attr( self::unmet_requirements_label() ) . '">'
				. esc_html( self::unmet_requirements_link() )
				. '</a>';

			// Return links with Requirements link
			return $links;
		}

		/** Checkers **************************************************************/

		/**
		 * Plugin specific requirements checker
		 *
		 * @since 1.0.0
		 */
		private static function check() {

			// Loop through requirements
			foreach ( self::$requirements as $dependency => $properties ) {

				// Which dependency are we checking?
				switch ( $dependency ) {

					// PHP
					case 'php':
						$version = phpversion();
						break;

					// WP
					case 'wp':
						$version = get_bloginfo( 'version' );
						break;

					// Unknown
					default:
						$version = false;
						break;
				}

				// Merge to original array
				if ( ! empty( $version ) ) {
					self::$requirements[ $dependency ] = array_merge(
						self::$requirements[ $dependency ],
						array(
							'current' => $version,
							'checked' => true,
							'met'     => version_compare( $version, $properties['minimum'], '>=' ),
						)
					);
				}
			}
		}

		/**
		 * Have all requirements been met?
		 *
		 * @since 1.0.0
		 *
		 * @return boolean
		 */
		public static function met() {

			// Run the check
			self::check();

			$to_meet = wp_list_pluck( self::$requirements, 'met' );

			// Look for unmet dependencies, and exit if so
			foreach ( $to_meet as $met ) {
				if ( empty( $met ) ) {
					return false;
				}
			}

			return true;
		}

		/** Translations **********************************************************/

		/**
		 * Plugin specific text-domain loader.
		 *
		 * WordPress Core automatically loads the custom wp-content/languages/parlay-api/.mo file if it's found,
		 * this is no longer needed.
		 * @since 1.0.0
		 * @return void
		 */
		public static function load_textdomain() {

			// Set filter for plugin's languages directory.
			$parlay_lang_dir = dirname( plugin_basename( PARLAY_API_FILE ) ) . '/languages';

			unload_textdomain( 'parlay-api' );
			load_plugin_textdomain( 'parlay-api', false, $parlay_lang_dir );
		}
	}
}

ParlayApiLoader::init();
