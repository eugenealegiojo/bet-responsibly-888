<?php

namespace Parlay\Api;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Session Class
 *
 * @since 1.0.0
 */
class Session {

	/**
	 * Holds our session data.
	 *
	 * @var array
	 * @access private
	 * @since 1.0.0
	 */
	private $session;

	/**
	 * Session index key
	 *
	 * @var string
	 * @access private
	 * @since 1.0.0
	 */
	private $key = 'ParlayAPI';

	/**
	 * Session expiration (in milliseconds)
	 * Default: 86400 (24 hours)
	 *
	 * @since 1.0.0
	 * @var int
	 */
	private $expiration = HOUR_IN_SECONDS * 24; //86400;

	/**
	 * Session cookie name
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $sess_cookie_name = 'lobby_ParlayAPI_session';

	/**
	 * Configuration for the session / cookie
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $config = [];

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->config = [
			'cookie_secure'     => is_ssl(),
			'cookie_domain'     => '.' . parse_url( get_site_url(), PHP_URL_HOST ),
			'cookie_httponly'   => true,
			'serialize_handler' => 'php_serialize',
			'use_strict_mode'   => true,
		];

		if ( is_multisite() ) {
			$this->key              = $this->key . '_' . get_current_blog_id();
			$this->sess_cookie_name = $this->sess_cookie_name . '_' . get_current_blog_id();
		}

		add_action( 'init', array( $this, 'maybe_start_session' ), -2 );
		add_action( 'init', [ $this, 'init' ], -1 );
	}

	/**
	 * Starts a new session if one hasn't started yet.
	 *
	 * @since 1.0.0
	 */
	public function maybe_start_session() {

		// Bail if should not start session.
		if ( ! $this->should_start_session() ) {
			return;
		}

		// Bail if headers already sent.
		if ( headers_sent() ) {
			return;
		}

		// Start if PHP_SESSION_ACTIVE is defined and session-status is not active
		// if ( defined( 'PHP_SESSION_ACTIVE' ) && ( session_status() !== PHP_SESSION_ACTIVE ) ) {
			// error_log( 'starting session: ' . $this->sess_cookie_name );
			// if ( PHP_SESSION_NONE === session_status() ) {
			// session_name( $this->sess_cookie_name );
			// session_start( $this->config );

			// $this->maybe_session_expired();
		// }

		if ( ! session_id() ) {
			$cookie_domain = $_SERVER['HTTP_HOST'];
			session_set_cookie_params( 0, '/', $cookie_domain );
			session_name( $this->sess_cookie_name );
			session_start( $this->config );
		}
	}

	/**
	 * Setup the session instance.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->session = isset( $_SESSION[ $this->key ] ) ? $_SESSION[ $this->key ] : [];
		// $this->maybe_session_expired();
	}

	/**
	 * Retrieve a session variable.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Session key.
	 * @return mixed Session variable.
	 */
	public function get( $key ) {
		$key    = sanitize_key( $key );
		$return = false;

		if ( isset( $this->session[ $key ] ) && ! empty( $this->session[ $key ] ) ) {
			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $this->session[ $key ], $matches );

			if ( ! empty( $matches ) ) {
				$this->set( $key, null );
				return false;
			}

			if ( is_numeric( $this->session[ $key ] ) ) {
				$return = $this->session[ $key ];
			} else {
				$maybe_json = json_decode( $this->session[ $key ] );

				// Since json_last_error is PHP 5.3+, we have to rely on a `null` value for failing to parse JSON.
				if ( is_null( $maybe_json ) ) {
					$is_serialized = is_serialized( $this->session[ $key ] );
					if ( $is_serialized ) {
						$value = @unserialize( $this->session[ $key ] );
						$this->set( $key, (array) $value );
						$return = $value;
					} else {
						$return = $this->session[ $key ];
					}
				} else {
					$return = json_decode( $this->session[ $key ], true );
				}
			}
		}

		return $return;
	}

	/**
	 * Get all the user data from the session.
	 *
	 * @since 1.0.0
	 */
	public function get_userdata( $key = '' ) {
		if ( ! isset( $this->session['player'] ) ) {
			return;
		}

		$userdata = $this->get( 'player' );

		if ( ! empty( $key ) && isset( $userdata[ $key ] ) ) {
			return $userdata[ $key ];
		} elseif ( empty( $key ) ) {
			return $userdata;
		}
	}

	public function set_userdata( $key, $value ) {
		if ( ! $this->get_userdata() ) {
			return;
		}

		$player = $this->get_userdata();

		if ( ! isset( $player[ $key ] ) ) {
			return;
		}

		$player[ $key ] = $value;
		$this->set( 'player', $player );

		do_action( 'qm/debug', 'set userdata: ' . $key . ' => ' . print_r( $player, true ) );
	}

	/**
	 * Set a session variable.
	 *
	 * @since 1.0.0
	 *
	 * @param string           $key   Session key.
	 * @param int|string|array $value Session variable.
	 *
	 * @return mixed Session variable
	 */
	public function set( $key, $value ) {
		$key = sanitize_key( $key );

		if ( is_array( $value ) ) {
			$this->session[ $key ] = wp_json_encode( $value );
		} else {
			$this->session[ $key ] = esc_attr( $value );
		}

		$_SESSION[ $this->key ] = $this->session;
	}

	/**
	 * Determines if we should start sessions.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if sessions should start, false otherwise.
	 */
	public function should_start_session() {

		// Set default return value to true.
		$start_session = true;

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$blacklist = $this->get_blacklist();
			$uri       = ltrim( $_SERVER['REQUEST_URI'], '/' );
			$uri       = untrailingslashit( $uri );

			if ( in_array( $uri, $blacklist, true ) ) {
				$start_session = false;
			}

			if ( false !== strpos( $uri, 'feed=' ) ) {
				$start_session = false;
			}

			// We do not want to start sessions in the admin unless we're processing an ajax request.
			if ( is_admin() && false === strpos( $uri, 'wp-admin/admin-ajax.php' ) ) {
				$start_session = false;
			}

			// Starting sessions while saving the file editor can break the save process, so don't start.
			if ( false !== strpos( $uri, 'wp_scrape_key' ) ) {
				$start_session = false;
			}
		}

		// Filter & return.
		return (bool) $start_session;
	}

	/**
	 * Retrieve the URI blacklist.
	 *
	 * These are the URIs where we never start sessions.
	 *
	 * @since 1.0.0
	 *
	 * @return array URI blacklist.
	 */
	public function get_blacklist() {
		$blacklist = [
			'feed',
			'feed/rss',
			'feed/rss2',
			'feed/rdf',
			'feed/atom',
			'comments/feed',
		];

		// Look to see if WordPress is in a sub folder or this is a network site that uses sub folders
		$folder = str_replace( network_home_url(), '', get_site_url() );

		if ( ! empty( $folder ) ) {
			foreach ( $blacklist as $path ) {
				$blacklist[] = $folder . '/' . $path;
			}
		}

		return $blacklist;
	}

	/**
	 * Checks if session has expired.
	 *
	 * @since 1.0.0
	 */
	private function maybe_session_expired() {
		if ( PHP_SESSION_ACTIVE !== session_status() || ! session_name() ) {
			do_action( 'qm/debug', 'PHP_SESSION_ACTIVE: ' . PHP_SESSION_ACTIVE );
			return;
		}

		if ( ! isset( $_SESSION[ $this->key ]['regenerated'] ) ) {
			$this->set( 'regenerated', time() );
			return;
		}

		$expiry_time = time() - $this->expiration;

		if ( $this->get( 'regenerated' ) <= $expiry_time ) {
			do_action( 'qm/debug', 'session expired - regenerating' );
			$this->regenerate_id();
		}
	}

	/**
	 * Regenerates session id.
	 *
	 * @since 1.0.0
	 */
	public function regenerate_id() {
		//Copy old session data, including its id
		$_SESSION['old_session_id'] = session_id();
		$old_session_data           = $this->session;

		//Regenerate session id and store it
		$new_session_id = session_create_id();
		session_destroy();

		//Switch back to the new session id and send the cookie
		session_name( $this->sess_cookie_name );
		session_id( $new_session_id );
		session_start( $this->config );

		//Restore the old session data into the new session
		$this->session = $old_session_data;

		//Update the session creation time
		$this->set( 'regenerated', time() );
		unset( $_SESSION['old_session_id'] );

		session_write_close();
	}

	/**
	 * Destroys the session and erases session storage.
	 *
	 * @since 1.0.0
	 */
	public function destroy() {
		unset( $_SESSION[ $this->key ] );

		if ( ini_get( 'session.use_cookies' ) ) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
				$params['path'], $params['domain'],
				$params['secure'], $params['httponly']
			);
		}

		session_destroy();
	}

	/**
	 * Reset session from lobby variables.
	 */
	public function reset_session() {
		$this->set( 'player', null );
		$this->set( 'regenerated', null );
		$this->set( 'info_cached', null );
		$this->set( 'last_validated', null );

		$this->destroy();
	}
}
