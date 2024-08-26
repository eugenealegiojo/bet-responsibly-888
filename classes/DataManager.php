<?php

namespace Parlay\Api;

use Parlay\SiteApi\Config;
use Parlay\SiteApi\Accounts\UserFactory;
use Parlay\SiteApi\Games\BingoGameFactory;
use Parlay\SiteApi\Games\CasinoGameFactory;
use Parlay\SiteApi\SiteApiException;
use Parlay\Api\UserAuth;
use Parlay\SiteApi\Accounts\User;
use stdClass;

/**
 * Class DataManager
 *
 * Handles and manages API data requests.
 */
class DataManager {

	/**
	 * API configuration.
	 *
	 * @var \Parlay\SiteApi\Config
	 */
	private static $config;

	/**
	 * User factory object
	 *
	 * @var \Parlay\SiteApi\Accounts\UserFactory
	 */
	private static $uf;

	public function __construct() {
		add_action( 'wp_loaded', __CLASS__ . '::setup_config' );
	}

	public static function setup_config() {
		$api_settings = self::get_api_settings();
		if ( ! $api_settings ) {
			return;
		}

		$base_url      = trailingslashit( $api_settings['api_url'] );
		$site_id       = $api_settings['site_id']; // 'SITE'
		$site_key      = $api_settings['api_key'];
		$adminUsername = 'NETWORK_admin';

		/**
		 * Legacy Key: $api_settings['api_key']
		 *         V2: $api_settings['api_token']
		 */
		self::$config = new Config( $base_url, $site_id, $site_key, PGS()->log, $adminUsername );
		self::$uf     = new UserFactory( self::$config );
	}

	public static function get_config() {
		return self::$config;
	}

	public static function get_post_types() {
		$saved_protected = get_option( 'parlay_protected_post_types' );

		if ( $saved_protected ) {
			return $saved_protected;
		}

		return [];
	}

	public static function get_api_settings( $option = '' ) {
		$api_settings = get_option( 'parlay_settings' );

		if ( ! $api_settings ) {
			return false;
		}

		if ( ! empty( $option ) && isset( $api_settings[ $option ] ) ) {
			return $api_settings[ $option ];
		}

		return $api_settings;
	}

	public static function get_api_url() {
		$api_settings = self::get_api_settings();
		if ( ! $api_settings ) {
			return false;
		}

		return trailingslashit( $api_settings['api_url'] );
	}

	public static function get_games( $attrs = [] ) {
		$category = $attrs['category'];
		if ( 'casino' === $category ) {
			if ( isset( $attrs['sort']['sort_by'] ) && 'id' === $attrs['sort']['sort_by'] ) {
				$attrs['sort']['sort_by'] = 'gameId';
			}

			return self::get_games_data( 'casino', $attrs );
			// return self::get_casino_games( $attrs );
		} elseif ( 'bingo' === $category ) {
			$args = [
				'limit_count' => $attrs['limit_count'],
			];

			if ( isset( $attrs['sort'] ) ) {
				$args['sort'] = $attrs['sort'];
			}

			return self::get_games_data( 'bingo', $args, false );
		}
	}

	public static function get_casino_games( $filters = [] ) {
		// Check if games are cached
		$res = self::api_request( 'casino/games', 'GET', [
			'gameType' => $filters['gameType'],
			'freePlay' => $filters['freePlay'],
		] );

		if ( 200 !== $res['code'] ) {
			return false;
		}

		return $res['body'];
	}

	/**
	 * Get game launch URL
	 *
	 * @param mixed $id        Either a casino game ID or a bingo room ID
	 * @param string $category Either 'casino' or 'bingo'
	 * @return void
	 */
	public static function get_launch_url( $args = [] ) {
		$query        = [];
		$content_type = '';
		if ( isset( $args['return_type'] ) && 'code' === $args['return_type'] ) {
			$endpoint     = 'launch';
			$query        = [
				'token'  => PGS()->session->get_userdata( 'user_token' ),
				'gameId' => $args['gameId'] ?? null,
				'roomId' => $args['roomId'] ?? null,
			];
			$content_type = 'text/html';
		} else {

			if ( 'bingo' === $args['category'] ) {
				$endpoint = "bingo/rooms/{$args['roomId']}/launch";
			} elseif ( 'casino' === $args['category'] ) {
				$endpoint = "casino/games/{$args['gameId']}/launch";
			}
		}

		if ( ! $endpoint ) {
			return;
		}

		// $api_route                       .= '/free';
		$res = self::api_request( $endpoint, 'GET', $query, $content_type );

		if ( 200 !== $res['code'] ) {
			return false;
		}

		if ( isset( $args['return_type'] ) && 'code' === $args['return_type'] ) {
			return $res['body'];
		} else {
			return $res['body']['url'];
		}
	}


	public static function api_request( $endpoint, $method = 'GET', $params = [], $content_type = '' ) {
		error_log( 'endpoint request: ' . $endpoint );
		$settings  = self::get_api_settings();
		$base_url  = trailingslashit( $settings['api_url'] );
		$api_route = $base_url . "site-api/v2/{$endpoint}";
		$query     = [];

		if ( false === strpos( $endpoint, 'player/report' )
			&& false === strpos( $endpoint, 'player/tickets' )
			&& false === strpos( $endpoint, 'player/prepurchases' )
			&& 'tags' !== $endpoint
			) {
			$query['lang'] = urlencode( $settings['language'] );
		}

		$request_args = [
			'timeout' => 15,
			'method'  => $method,
			'headers' => [
				'Content-Type' => ! empty( $content_type ) ? $content_type : 'application/json',
			],
		];

		if ( 'POST' === $method && ! empty( $params ) ) {
			$request_args['body'] = json_encode( $params );
		}

		$token = PGS()->session->get_userdata( 'user_token' );

		if ( 'casino/games' === $endpoint ) {
			$request_args['headers']['Authorization'] = 'Bearer ' . $settings['api_token_public'];

			if ( UserAuth::is_authenticated() ) {
				$request_args['headers']['x-session-token'] = $token;
			}
		} elseif ( 'tags' === $endpoint ) {
			$request_args['headers']['Authorization'] = 'Bearer ' . $settings['api_token_public'];
		} else {

			if ( ! UserAuth::is_authenticated() && ! isset( $params['token'] ) ) {
				$request_args['headers']['Authorization'] = 'Bearer ' . $settings['api_token_public'];
			} elseif ( 'launch' !== $endpoint ) {
				$request_args['headers']['x-session-token'] = $token;
			}
		}

		if ( count( $params ) > 0 && 'GET' === $method ) {
			if ( isset( $params['token'] ) && 'launch' !== $endpoint ) {
				unset( $params['token'] );
			}

			$query     = wp_parse_args( $params, $query );
			$api_route = add_query_arg( $query, $api_route );
		}

		error_log( 'query: ' . print_r( add_query_arg( $query, $api_route ), true ) );
		error_log( 'request_args: ' . print_r( $request_args, true ) );
		do_action( 'qm/debug', 'query: ' . print_r( add_query_arg( $query, $api_route ), true ) );

		$response = wp_remote_request( $api_route, $request_args );
		$res_code = wp_remote_retrieve_response_code( $response );

		if ( 'text/html' === $content_type ) {
			$res_body = wp_remote_retrieve_body( $response );
		} else {
			$res_body = json_decode( wp_remote_retrieve_body( $response ), true );
		}

		// Check token error for all endpoints
		if ( $token && 401 === $res_code && isset( $res_body['key'] ) && 'invalid.token.session' === $res_body['key'] ) {
			PGS()->set_alert( 'token' );
			UserAuth::set_unauthenticated();
		}

		return [
			'code' => $res_code,
			'body' => $res_body,
		];
	}

	/**
	 * Get games data
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Name of the game
	 * @param array  $args Array of arguments
	 * @param bool   $cache Whether to cache the data
	 */
	public static function get_games_data( $name, $args = [], $cache = true ) {
		$endpoints  = [
			'casino' => [
				'endpoint' => 'casino/games',
				'filters'  => [
					// 'gameType' => implode( ',', $args['filters']['gameType'] ),
					'freePlay' => isset( $args['freePlay'] ) ? $args['freePlay'] : null,
				],
			],
			'bingo'  => [
				'endpoint' => UserAuth::is_authenticated() && UserAuth::validate_token() ? 'bingo/rooms' : 'bingo/rooms/scheduled?excludeUnscheduled=true',
				'filters'  => [],
			],
		];
		$games_data = [];

		if ( ! isset( $endpoints[ $name ] ) ) {
			return false;
		}

		error_log( 'get_games_data endpoints: ' . print_r( $endpoints, true ) );
		error_log( 'filters: ' . print_r( $args, true ) );

		// Check for cahed games data if requested
		if ( true === $cache ) {
			$settings       = self::get_api_settings();
			$filtered_cache = isset( $args['freePlay'] ) && 'true' === $args['freePlay'] ? $name . '_hasFree' : $name;
			$filtered_cache = $filtered_cache . "_games_{$settings['language']}.json";
			$games_data     = self::get_cached_games( $filtered_cache, $args );
			error_log( 'games data cached' );
		}

		// Check if requesting a new set of games or cached data doesn't exist
		if ( false === $cache || ( true === $cache && empty( $games_data ) ) ) {

			// Make API call to retrieve data
			$res = self::api_request( $endpoints[ $name ]['endpoint'], 'GET', array_filter( $endpoints[ $name ]['filters'] ) );

			if ( 200 !== $res['code'] ) {
				return false;
			}

			$games_data = $res['body'];

			error_log( 'games data from API' );

			// Cache non-filtered games data
			if ( true === $cache ) {
				$cache_dir  = self::get_cache_dir();
				$cache_file = $cache_dir . $filtered_cache;
				PGS()->filesystem->file_put_contents( $cache_file, json_encode( $games_data ) );
			}
		}

		// Bailout if no games found
		if ( empty( $games_data ) ) {
			return false;
		}

		// Filter games
		if ( isset( $args['filters'] ) && count( $args['filters'] ) > 0 ) {
			error_log( 'filtering games data...' );
			$filters    = array_filter( $args['filters'] );
			$games_data = array_values( array_filter(
				$games_data,
				function ( $game ) use ( $filters ) {
					foreach ( $filters as $key => $value ) {

						// Compare array filter to string api value
						if ( array_key_exists( $key, $game ) && is_array( $value ) && is_string( $game[ $key ] ) && ! in_array( $game[ $key ], $value ) ) {
							return false;
						}

						// Compare array filter to array api value
						if ( array_key_exists( $key, $game ) && is_array( $value ) && is_array( $game[ $key ] ) ) {
							$intersection = array_intersect( $value, $game[ $key ] );
							if ( empty( $intersection ) ) {
								return false;
							}
						}
					}
					return true;
				}
			));
		}

		// Sort games
		if ( isset( $args['sort'] ) ) {
			if ( 'custom' === $args['sort']['sort_by'] && ! empty( $args['games_order'] ) ) {
				$games_data = self::ordered_casino_games( $args['games_order'], $games_data );
			} else {

				$sort_key = $args['sort']['sort_by'] ?? 'name';
				$sort_dir = $args['sort']['sort_order'] ?? 'asc';

				error_log( 'sorting by ' . $sort_key . ' ' . $sort_dir );

				// Define comparison function
				$compare = function ( $a, $b ) use ( $sort_key, $sort_dir ) {
					$value_a = $a[ $sort_key ];
					$value_b = $b[ $sort_key ];

					// Compare values based on sort direction
					if ( $sort_key === 'name' || ! is_numeric( $value_a ) || ! is_numeric( $value_b ) ) {
						// For sorting by name or if values are not numeric, use strcasecmp() for case-insensitive string comparison
						if ( $sort_dir === 'asc' ) {
							return strcasecmp( $value_a, $value_b ); // Ascending order
						} else {
							return strcasecmp( $value_b, $value_a ); // Descending order
						}
					} else {
						// For sorting by id (numeric values), compare values directly
						if ( $sort_dir === 'asc' ) {
							return $value_a - $value_b; // Ascending order
						} else {
							return $value_b - $value_a; // Descending order
						}
					}
				};

				// Sort game data
				usort( $games_data, $compare );
			}
		}

		// Limit results
		if ( isset( $args['limit_count'] ) && $args['limit_count'] > 0 ) {
			error_log( 'limiting games data to: ' . $args['limit_count'] );
			$games_data = array_slice( $games_data, 0, $args['limit_count'] );
		}

		if ( ! empty( $games_data ) ) {
			error_log( 'returning games data: ' . print_r( $games_data, true ) );
			return $games_data;
		}
	}

	/**
	 * Custom casino games order
	 *
	 * @since 1.2.0
	 */
	public static function ordered_casino_games( $saved_order, $games ) {
		$ordered_games = [];
		$games_map     = [];
		$games_order   = explode( ',', $saved_order );

		error_log( 'ordered_casino_games: ' . print_r( $games_order, true ) );

		// Casino id key is set as `gameId`
		foreach ( $games as $game ) {
			if ( isset( $game['gameId'] ) ) {
				$games_map[ $game['gameId'] ] = $game;
			}
		}

		// Sort games based on saved order
		foreach ( $games_order as $game_id ) {
			if ( isset( $games_map[ $game_id ] ) ) {
				$ordered_games[] = $games_map[ $game_id ];
				unset( $games_map[ $game_id ] );
			}
		}

		$games = array_merge( $ordered_games, array_values( $games_map ) );

		return $games;
	}

	/**
	 * Get cached games
	 *
	 * @param [type] $name
	 * @param array $args
	 * @return void
	 */
	public static function get_cached_games( $name, $args = [] ) {
		$cache_dir        = self::get_cache_dir();
		$cache_file       = $cache_dir . $name;
		$cache_expiration = 3600; // 1 hour

		if ( ! PGS()->filesystem->file_exists( $cache_file ) ) {
			return;
		}

		$cached_games = PGS()->filesystem->file_get_contents( $cache_file );

		error_log( 'file created at: ' . filemtime( $cache_file ) );
		error_log( 'current time: ' . time() );

		if ( ! empty( $cached_games ) && time() - filemtime( $cache_file ) < $cache_expiration ) {
			$games = json_decode( $cached_games, true );

			error_log( 'games data from cache' );

			return $games;
		}
	}

	public static function get_cache_dir() {
		$cache_dir = PARLAY_API_DIR . 'cache/';

		// Create the cache dir if it doesn't exist.
		if ( ! PGS()->filesystem->file_exists( $cache_dir ) ) {

			// Create the directory.
			PGS()->filesystem->mkdir( $cache_dir );

			// Add an index file for security.
			PGS()->filesystem->file_put_contents( $cache_dir . 'index.html', '' );
		}

		return $cache_dir;
	}

	/**
	 * Get email template dir
	 *
	 * @since 1.2.0
	 * @return string
	 */
	public static function get_email_template_dir() {
		$template_dir = PARLAY_API_DIR . 'templates/email/';

		// Create the cache dir if it doesn't exist.
		if ( ! PGS()->filesystem->file_exists( $template_dir ) ) {

			// Create the directory.
			PGS()->filesystem->mkdir( $template_dir );

			// Add an index file for security.
			PGS()->filesystem->file_put_contents( $template_dir . 'index.html', '' );
		}

		return $template_dir;
	}

	/**
	 * Clear games cache
	 */
	public static function clear_games_cache() {
		$cache_dir = self::get_cache_dir();

		// Remove all the files in the cache directory.
		foreach ( glob( $cache_dir . '*' ) as $file ) {
			PGS()->filesystem->unlink( $file );
		}

		error_log( 'games cache cleared' );
	}

	/**
	 * Reset the games cache
	 */
	public static function reset_games_cache() {
		self::clear_games_cache();
		self::get_games_data( 'casino' );

		error_log( 'games cache reset' );
	}

	/**
	 * Get player data
	 */
	public static function get_player_data() {
		if ( ! UserAuth::is_authenticated() ) {
			return;
		}

		// Check if player data is cached
		if ( PGS()->session->get( 'info_cached' ) ) {
			do_action( 'qm/debug', 'player data from cached' );
			$result = PGS()->session->get_userdata();
			return $result;
		}

		$result = self::api_request( 'player', 'GET' );
		if ( 200 !== $result['code'] ) {
			return false;
		}

		$current_session  = PGS()->session->get_userdata();
		$new_session_data = wp_parse_args( $result['body'], $current_session );

		PGS()->session->set( 'player', $new_session_data );
		PGS()->session->set( 'info_cached', true );

		return $result['body'];
	}

	public static function get_player_balance() {
		if ( ! UserAuth::is_authenticated() ) {
			return;
		}

		$result = self::api_request( 'player/balance', 'GET' );
		if ( 200 !== $result['code'] ) {
			return false;
		}

		return $result['body'];
	}

	public static function get_redirect_form_url() {
		$referrer = wp_get_referer();
		$url      = '';

		if ( is_page( 'login' ) ) {
			$url = home_url();
		} elseif ( is_page( 'registration' ) ) {
			$url = home_url();
		} elseif ( $referrer ) {
			$url = esc_url_raw( $referrer );
		}

		return $url;
	}

	/**
	 * Get tags
	 *
	 * @param string $type Either all | provider | lines | category
	 * @param boolean $refresh Whether to get new tags or from cache
	 * @return array
	 */
	public static function get_tags( $type = 'all', $refresh = false ) {
		$tags        = [];
		$cached_file = self::get_cache_dir() . 'tags.json';

		// Check if tags are cached
		if ( PGS()->filesystem->file_exists( $cached_file ) && false === $refresh ) {
			$cached_tags = PGS()->filesystem->file_get_contents( $cached_file );

			if ( ! empty( $cached_tags ) && time() - filemtime( $cached_file ) < 3600 ) {
				error_log( 'getting tags from cache' );
				$tags = json_decode( $cached_tags, true );
			}
		}

		// Get tags from API
		if ( empty( $tags ) ) {
			error_log( 'getting tags from api' );

			$results = self::api_request( 'tags', 'GET' );
			if ( 200 !== $results['code'] ) {
				return false;
			}

			// Return and cache results
			$tags = $results['body'];
			PGS()->filesystem->file_put_contents( $cached_file, json_encode( $tags ) );
		}

		// Group tags by type by prefix gfp, gfl or gfc
		if ( 'all' === $type ) {
			return $tags;
		}

		// Filter by type
		if ( ! empty( $tags ) ) {
			$tags_prefix   = [
				'provider' => 'gfp',
				'lines'    => 'gfl',
				'category' => 'gfc',
			];
			$filtered_tags = [];

			foreach ( $tags as $tag ) {
				if ( 0 === strpos( $tag['name'], $tags_prefix[ strtolower( $type ) ] ) ) {
					$tag['display_name'] = isset( $tag['translation'] ) ? $tag['translation'] : strtoupper( $tag['name'] );
					$filtered_tags[]     = $tag;
				}
			}
		}

		return $filtered_tags;
	}

	/**
	 * Define email templates
	 *
	 * @since 1.2.0
	 * @param string $template_id
	 * @return array
	 */
	public static function get_email_templates( $template_id = '' ) {
		$template_tags = [
			'{base_url}'      => get_bloginfo( 'url' ),
			'{site_name}'     => get_bloginfo( 'name' ),
			'{support_email}' => get_option( 'admin_email' ),
			'{alias}'         => 'admin',
		];

		$templates = [
			'welcome'            => (object) [
				'id'    => 'welcome',
				'title' => __( 'Welcome', 'parlay-api' ),
				'tags'  => $template_tags,
			],
			'forgot_password'    => (object) [
				'id'    => 'forgot_password',
				'title' => __( 'Forgot Password', 'parlay-api' ),
				'tags'  => array_merge( $template_tags, [
					'{reset_password_url}' => '',
				] ),
			],
			'account_activation' => (object) [
				'id'    => 'account_activation',
				'title' => __( 'Account Activation', 'parlay-api' ),
				'tags'  => array_merge( $template_tags, [
					'{activation_url}' => '',
					'{activation_key}' => '',
				] ),
			],
		];

		if ( ! empty( $template_id ) && isset( $templates[ $template_id ] ) ) {
			return $templates[ $template_id ];
		}

		return $templates;
	}

	/**
	 * Get email template data
	 *
	 * @since 1.2.0
	 * @param string $template_id
	 * @return array
	 */
	public static function get_template_data( $template_id ) {
		$email_template  = self::get_email_templates( $template_id );
		$saved_templates = get_option( 'parlay_email_templates' );
		$template_data   = [
			'from'    => get_bloginfo( 'admin_email' ),
			'subject' => $email_template->title ?? '',
		];

		if ( isset( $saved_templates[ $template_id ] ) ) {
			$template_data = $saved_templates[ $template_id ];
		}

		$template_base = self::get_email_template_dir() . $template_id;
		$html_content  = PGS()->filesystem->file_get_contents( $template_base . '.html' );
		$css_content   = PGS()->filesystem->file_get_contents( $template_base . '.css' );

		$template_data['html'] = ! empty( $html_content ) ? $html_content : '';
		$template_data['css']  = ! empty( $css_content ) ? $css_content : '';
		$template_data['tags'] = $email_template->tags;

		return $template_data;
	}
}
