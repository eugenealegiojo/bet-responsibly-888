<?php

/**
 * Parlay Updater class.
 *
 * @since 1.2.0
 */
final class ParlayUpdater {

	/**
	 * The API URL for the Parlay Games API plugin update server.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $_updates_api_url
	 */
	static private $_updates_api_url = 'https://bb5.site/updates/';

	/**
	 * An internal array of remote responses with
	 * update data for each product.
	 *
	 * @since 1.2.0
	 * @access private
	 * @var array $_responses
	 */
	static private $_responses = array();

	/**
	 * An array of settings for this instance.
	 */
	private $settings = array();

	/**
	 * Updater constructor method.
	 *
	 * @since 1.2.0
	 * @param array $settings An array of settings for this instance.
	 * @return void
	 */
	public function __construct(){
		$this->settings = [
			'slug'    => 'parlay-games-api',
			'name'    => 'Parlay Games API',
			'version' => PARLAY_API_VERSION,
		];

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_check' ) );
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 99, 3 );
        // add_action( 'in_plugin_update_message-' . self::get_plugin_file( 'parlay-games-api' ), array( $this, 'update_message' ), 1, 2 );
	}

	/**
	 * Get the update data response from the API.
	 *
	 * @since 1.2.0
	 * @return object
	 */
	public function get_response() {
		$slug = $this->settings['slug'];

		if ( isset( ParlayUpdater::$_responses[ $slug ] ) ) {
			return ParlayUpdater::$_responses[ $slug ];
		}

		$api_settings = \Parlay\Api\DataManager::get_api_settings();

		ParlayUpdater::$_responses[ $slug ] = ParlayUpdater::api_request(
			ParlayUpdater::$_updates_api_url,
			array(
				'parlay-api-method' => 'update_info',
				'license'       => $api_settings['api_token_public'],
				'domain'        => ParlayUpdater::validate_domain( $api_settings['site_id'] ),
				'product'       => $this->settings['name'],
				'slug'          => $this->settings['slug'],
				'version'       => $this->settings['version'],
				'php'           => phpversion(),
			)
		);

		return ParlayUpdater::$_responses[ $slug ];
	}

	/**
	 * Checks to see if an update is available for the current product.
	 *
	 * @since 1.2.0
	 * @param object $transient A WordPress transient object with update data.
	 * @return object
	 */
	public function update_check( $transient ) {
		global $pagenow;

		if ( 'plugins.php' == $pagenow && is_multisite() ) {
			return $transient;
		}
		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}
		if ( ! isset( $transient->checked ) ) {
			$transient->checked = array();
		}

		$response = $this->get_response();

		error_log( 'response: ' . print_r( $response, true ) );

		if ( ! isset( $response->error ) || ( isset( $response->error ) && 'No update available.' === $response->error ) ) {

			error_log('no update available');

			$transient->last_checked                       = time();
			$transient->checked[ $this->settings['slug'] ] = $this->settings['version'];

	        $plugin = self::get_plugin_file( $this->settings['slug'] );

            $plugin_version_check = $this->settings['version'];
            if ( isset( $response->new_version ) ) {
                if ( false === strpos( $response->new_version, 'alpha' ) ) {
                    $plugin_version_check = rtrim( $plugin_version_check, '-alpha' );
                }
                if ( false === strpos( $response->new_version, 'beta' ) ) {
                    $plugin_version_check = rtrim( $plugin_version_check, '-beta' );
                }
            }

            if ( isset( $response->new_version ) && version_compare( $response->new_version, $plugin_version_check, '>' ) ) {

                $transient->response[ $plugin ]               = new stdClass();
                $transient->response[ $plugin ]->slug         = $response->slug;
                $transient->response[ $plugin ]->plugin       = $plugin;
                $transient->response[ $plugin ]->new_version  = $response->new_version;
                $transient->response[ $plugin ]->url          = $response->homepage;
                $transient->response[ $plugin ]->package      = $response->package;
                $transient->response[ $plugin ]->tested       = $response->tested;
                $transient->response[ $plugin ]->requires_php = $response->requires_php;
				$transient->response[ $plugin ]->changelog_url = $response->changelog_url;
                $transient->response[ $plugin ]->icons        = '';

            }
		}

		return $transient;
	}

	/**
	 * Retrieves the data for the plugin info lightbox.
	 *
	 * @since 1.2.0
	 * @param bool $false
	 * @param string $action
	 * @param object $args
	 * @return object|bool
	 */
	public function plugin_info( $false, $action, $args ) {
		if ( 'plugin_information' != $action ) {
			return $false;
		}
		if ( ! isset( $args->slug ) || $args->slug != $this->settings['slug'] ) {
			return $false;
		}

		$response  = $this->get_response();
		$changelog = __( 'Could not locate changelog.txt', 'fl-builder' );

		 // Fetch the changelog content
		 $get_changelog = wp_remote_get($response->changelog_url);
		 if (!is_wp_error($get_changelog) && wp_remote_retrieve_response_code($get_changelog) == 200) {
			 	$changelog = wp_remote_retrieve_body( $get_changelog );
		 }

		if ( ! isset( $response->error ) ) {

			$info                = new stdClass();
			$info->name          = $this->settings['name'];
			$info->version       = $response->new_version;
			$info->slug          = $response->slug;
			$info->plugin_name   = $response->plugin_name;
			$info->author        = $response->author;
			$info->homepage      = $response->homepage;
			$info->requires      = $response->requires;
			$info->requires_php  = $response->requires_php;
			$info->tested        = $response->tested;
			$info->last_updated  = $response->last_updated;
			$info->download_link = $response->package;
			$info->sections      = array(
				'description' => 'Parlay games integration with the Parlay Site API.',
				'changelog' => $changelog,
			);
			return $info;
		} else {
			$info              = new stdClass();
			$info->name        = $this->settings['name'];
			$info->version     = $this->settings['version'];
			$info->slug        = $this->settings['slug'];
			$info->plugin_name = $this->settings['name'];
			$info->homepage    = 'https://bb5.site/';

			$info->sections              = array();
			$info->sections['changelog'] = file_get_contents( trailingslashit( plugin_dir_path( PARLAY_API_FILE ) ) . '/changelog.txt' );
			return  $info;
		}

		return $false;
	}

	/**
	 * Static method for rendering the license form.
	 *
	 * @since TBD
	 * @return void
	 */
	static public function render_form() {
		// Activate a subscription?
	}

	/**
	 * Static method for retrieving the plugin file path for a
	 * product relative to the plugins directory.
	 *
	 * @since 1.2.0
	 * @access private
	 * @param string $slug The product slug.
	 * @return string
	 */
	static private function get_plugin_file( $slug ) {
		if ( 'parlay-games-api' == $slug ) {
			$file = $slug . '/parlay-games-api.php';
		} else {
			$file = $slug . '/' . $slug . '.php';
		}

		return $file;
	}

	/**
	 * Static method for sending a request to the store
	 * or update API.
	 *
	 * @since 1.2.0
	 * @access private
	 * @param string $api_url The API URL to use.
	 * @param array $args An array of args to send along with the request.
	 * @return mixed The response or false if there is an error.
	 */
	static private function api_request( $api_url = false, $args = array() ) {
		if ( $api_url ) {

			$params = array();

			foreach ( $args as $key => $val ) {
				$params[] = $key . '=' . urlencode( $val );
			}

			return self::remote_get( $api_url . '?' . implode( '&', $params ) );
		}

		return false;
	}

	/**
	 * Get a remote response.
	 *
	 * @since 1.2.0
	 * @access private
	 * @param string $url The URL to get.
	 * @return mixed The response or false if there is an error.
	 */
	static private function remote_get( $url ) {
		$request      = wp_remote_get( $url );
		$error        = new stdClass();
		$error->error = 'connection';

		if ( is_wp_error( $request ) ) {
			error_log( 'wp error: ' . print_r($request, true) );
			return $error;
		}
		if ( wp_remote_retrieve_response_code( $request ) != 200 ) {
			error_log( 'code error: ' . print_r($request, true) );
			return $error;
		}

		$body = wp_remote_retrieve_body( $request );

		if ( is_wp_error( $body ) ) {
			error_log( 'body wp error: ' . print_r($body, true) );
			return $error;
		}

		$body_decoded = json_decode( $body );

		if ( ! is_object( $body_decoded ) ) {
			error_log( 'success: ' . print_r($body_decoded, true) );
			return $error;
		}

		return $body_decoded;
	}

	/**
	 * Validate domain and strip any query params
	 * @since 1.2.0
	 */
	private static function validate_domain( $url ) {
		$pos = strpos( $url, '?' );
		$url = ( $pos ) ? untrailingslashit( substr( $url, 0, $pos ) ) : $url;
		return $url;
	}
}
