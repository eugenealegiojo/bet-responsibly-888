<?php

namespace Parlay\Api;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Parlay\SiteApi\Accounts\User;
use Parlay\SiteApi\Api\JsonRequest;
use Parlay\SiteApi\Accounts\UserFactory;
use Parlay\SiteApi\SiteApiException;
use Parlay\Api\DataManager;
use Parlay\Api\Mailer;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class for handling user authentication and processes with the API.
 */
class UserAuth {

	/**
	 * Token validation
	 */
	private static $is_token_valid;

	/**
	 * Auth errors/messages
	 */
	private static $errors = [];

	/**
	 * Initialize user authentication
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function init() {
		add_action( 'wp', __CLASS__ . '::init_user_auth' );
	}

	/**
	 * Initialize token validation
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function init_user_auth() {
		$validated      = PGS()->session->get_userdata( 'authenticated' );
		$last_validated = PGS()->session->get( 'last_validated' );
		$token_valid    = false;

		$last_validated_human = date( 'F j, Y, g:i a', $last_validated );
		do_action( 'qm/debug', "Last validated: {$last_validated_human} (" . $last_validated . ')' );

		if ( ! $validated || false === $validated ) {
			$token_valid = false;
		} elseif ( $last_validated && time() - $last_validated > HOUR_IN_SECONDS * 2 ) {
			// Validate token every 2 hours based on last_validate
			do_action( 'qm/debug', 'Passed 2 hours. Validating token from API server.' );
			$token_valid = self::validate_token();
		} elseif ( ! $last_validated ) {
			// Token has not passed 2 hours since last validation
			do_action( 'qm/debug', 'Missing last_validated.' );
			$token_valid = false;
		} else {
			// Token has passed 2 hours since last validation
			$token_valid = true;
		}

		// Set token validation. Let the authentication logic know if token is valid
		self::$is_token_valid = $token_valid;
	}

	/**
	 * Validate token from the API server. To make sure the current user has a valid token.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function validate_token() {
		$token    = PGS()->session->get_userdata( 'user_token' );
		$base_url = trailingslashit( DataManager::get_api_settings( 'api_url' ) );

		// Make sure we have a token to validate
		if ( empty( $token ) ) {
			do_action( 'qm/debug', 'Token is empty' );
			return false;
		}

		// Check if token is valid from the API server
		$response = wp_remote_get( $base_url . 'site-api/v2/auth', [
			'timeout' => 10,
			'headers' => [
				'Content-Type'    => 'application/json',
				'x-session-token' => $token,
			],
		] );

		// Check if token validation request was successful
		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) <= 204 ) {
			do_action( 'qm/debug', 'Token is valid: ' . $token );

			PGS()->session->set( 'last_validated', time() );
			return true;
		} else {
			do_action( 'qm/debug', 'Setting authenticated to false for token: ' . $token );
			self::set_unauthenticated();

			error_log( 'new userdata value: ' . print_r( \Parlay\Api\Plugin::instance()->session->get_userdata(), true ) );
			return false;
		}

		return false;
	}

	/**
	 * Set current user as unauthenticated
	 *
	 * @since 1.0.0
	 */
	public static function set_unauthenticated() {
		PGS()->session->set_userdata( 'authenticated', false );
	}

	/**
	 * Check if token is valid
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function is_valid_token() {
		return self::$is_token_valid;
	}

	/**
	 * Check if user is authenticated
	 *
	 * @since 1.0.0
	 *
	 * @param bool $redirect   Redirect to login page if not authenticated
	 * @return bool
	 */
	public static function is_authenticated( $redirect = false ) {
		$authenticated = PGS()->session->get_userdata( 'authenticated' );
		$redirect_url  = '/login';

		// Disable authentication when editing/previewing in elementor
		if ( parlay_is_elementor_mode() ) {
			return true;
		}

		if ( empty( $authenticated ) || false === self::is_valid_token() ) {
			$token = PGS()->session->get_userdata( 'user_token' );

			if ( $redirect ) {
				// Check if user token still exists
				if ( $token ) {
					PGS()->set_alert( 'token' );
					// self::logout();

					if ( is_page( \Parlay\Api\Account::SLUG ) ) {
						$redirect_url .= '?referer=' . esc_url_raw( home_url( add_query_arg( array(), $wp->request ) ) );
					}
				}
				error_log( 'Not authenticated and set redirect to: ' . $redirect_url );
				self::redirect( $redirect_url );

			} elseif ( $token ) {
				PGS()->set_alert( 'token' );
				PGS()->session->reset_session();
			}

			return false;
		}

		if ( $redirect && ! $authenticated ) {
			self::redirect( '/login' );
		}

		return (bool) $authenticated;
	}

	/**
	 * Redirect to URL
	 *
	 * @since 1.0.0
	 * @param string $url
	 * @return void
	 */
	public static function redirect( $url ) {
		$parsed_url = wp_parse_url( $url );

		// Check for relative URL
		if ( isset( $parsed_url['host'] ) && $parsed_url['host'] === wp_parse_url( home_url(), PHP_URL_HOST ) ) {
			wp_redirect( home_url( '/' . ltrim( $parsed_url['path'], '/' ) ) );
		} else {
			wp_redirect( home_url( $url ) );
		}
		exit;
	}

	/**
	 * Login
	 *
	 * @since 1.0.0
	 * @param string $username
	 * @param string $password
	 * @param array  $extra_args
	 * @return bool
	 */
	public static function login( $username, $password, $extra_args = [] ) {
		if ( empty( $username ) || empty( $username ) ) {
			error_log( 'Empty username or password.' );
			self::$errors['code']                = 400;
			self::$errors['response']['message'] = __( 'Username and password are required.', 'parlay-api' );
			return false;
		}

		// $api_settings = DataManager::get_api_settings();
		$api_settings = get_option( 'parlay_settings' );

		if ( ! $api_settings && is_multisite() ) {
			$api_settings = get_site_option( 'parlay_settings' );
			// $api_settings = get_blog_option( get_current_blog_id(), 'parlay_settings' );
		}

		if ( ! $api_settings ) {
			error_log( 'No settings found!' );
			self::$errors['code']                = 400;
			self::$errors['response']['message'] = __( 'No API settings found.', 'parlay-api' );
			return false;
		}

		$base_url  = trailingslashit( $api_settings['api_url'] );
		$api_route = $base_url . 'site-api/v2/auth';
		$api_token = $api_settings['api_token'];

		$body = wp_json_encode( [
			'alias'    => $username,
			'password' => $password,
		] );

		// Set request arguments
		$args = array(
			'timeout' => 10,
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_token,
			),
			'body'    => $body,
		);

		error_log( 'logging in to: ' . $api_route );

		$response = wp_remote_post( $api_route, $args );
		$res_code = wp_remote_retrieve_response_code( $response );
		$res_body = wp_remote_retrieve_body( $response );

		if ( is_wp_error( $res_body ) ) {
			error_log( 'login error: ' . print_r( $res_body, true ) );
			do_action( 'qm/debug', 'login error: ' . print_r( $res_body, true ) );
			self::$errors['response'] = $response->get_error_message();
			return false;
		} else {
			$res = json_decode( $res_body, true );

			error_log( 'login code: ' . print_r( $res, true ) );

			do_action( 'qm/debug', 'login code: ' . print_r( $res_code, true ) );

			if ( isset( $res['key'] ) && isset( $res['message']) ) {
				if ( isset( PGS()->error_i18n[ $res['key'] ] ) ) {
					$res['message'] = PGS()->error_i18n[ $res['key'] ];
				}
			}

			if ( $res_code !== 200 ) {
				do_action( 'qm/debug', 'Login failed' );
				self::$errors['response'] = $res;
				self::$errors['code']     = $res_code;
				return false;
			}

			// Handle successful auth
			if ( ! isset( $res['token'] ) || empty( $res['token'] ) ) {
				do_action( 'qm/debug', 'Unable to generate token.' );
				self::$errors['response'] = __( 'Unable to generate token', 'parlay-api' );
				self::$errors['code']     = 401;
				return false;
			}

			if ( ! isset( $res['status'] ) || 'A' !== $res['status'] ) {
				do_action( 'qm/debug', 'Account is not active.' );
				self::$errors['response'] = __( 'Account is not active', 'parlay-api' );
				self::$errors['code']     = 401;
				return false;
			}

			error_log( 'login successful: ' . $res['userId'] );

			// error_log( 'login data: ' . print_r( $res, true ) );

			// Proceed with successful login.
			// self::$log->info( 'Login successful' );

			// Plugin::$session->set( 'player', [
			PGS()->session->set( 'player', array_merge([
				'authenticated'  => true,
				'userId'         => $res['userId'],
				'user_token'     => $res['token'],
				'alias'          => $res['alias'],
				'account_status' => $res['status'],
				'last_login'     => $res['lastLogin'],
			], $extra_args ) );

			// Set last validate
			PGS()->session->set( 'last_validated', time() );

			return true;
		}
	}

	/**
	 * Create user account
	 *
	 * @since 1.0.0
	 * @param array
	 */
	public static function register( $params ) {

		try {
			$uf               = new UserFactory( DataManager::get_config() );
			$skip_validations = [ 'lastName', 'phoneNumber' ];
			$response         = $uf->register( $params, $skip_validations );

			error_log( 'register response: ' . print_r( $response, true ) );
		
			if ( ! isset( $response['success'] ) || false === $response['success'] ) {
				self::$errors['code'] = 400;
				if ( isset( $response['messages'] ) ) {
					if ( is_array( $response['messages'] ) ) {
						$messages = [];
						foreach( $response['messages'] as $key => $error_token ) {
							$messages[ $key ] = PGS()->show_error_i18n( $error_token );
						}
						self::$errors['response'] = $messages;

					} else {
						self::$errors['response'] = $response['messages'];
					}
					
				} else {
					self::$errors['response'] = $response;
				}
				error_log( 'error params: ' . print_r( $params, true ) );

				// Log error
				// self::$log->error( 'User registration failed', [ 'response' => $response ] );

				// if ( isset( $response['messages'] ) ) {
				//  self::$errors['response'] = $response['messages'];
				// } else {
				//  self::$errors['response'] = $response;
				// }

				return false;

			}

			/*
			Expected response:
			[
				'activationKey' => (string)$req->rows->row['activationKey'],
				'isActivated' => (string)$req->rows->row['isActivated'],
				'success' => (bool)$req->rows->row['success'],
				'userId' => (string)$req->rows->row['userId'],
			]
			*/

			if ( 'production' == PGS_ENVIRONMENT ) {
				// log_message('info', 'Adding contact into ESP DB.');
				// $this->object->mailinglist->addSubscriber($accountInfo);
				// log_message('info', 'Adding contact done!');
			}

			$mailer = new Mailer();

			if ( 'false' === $response['isActivated'] || false === $response['isActivated'] ) {
				// self::$log->info( 'Sending activation email.' );

				if ( PGS_ENVIRONMENT == 'production' ) {
					// $wasEmailSent = $this->object->mailinglist->sendActivationEmail(
					//  $accountInfo,
					//  $accountCreate['user_data']['activationKey']
					// );

					// if ($wasEmailSent === false) {
					//  $wasEmailSent = $this->object->mailer->sendActivationEmail(
					//      $accountInfo,
					//      $accountCreate['user_data']['activationKey']
					//  );
					// }
				} else {
					$wasEmailSent = $mailer->sendActivationEmail(
						$params,
						$response['activationKey']
					);

				}
				error_log( 'Sending activation email...: ' . $wasEmailSent );

				self::$errors['code'] = 200;
				if ( $wasEmailSent ) {

					// Store activation key in transient
					$transient_data = [
						'email' => $params['email'],
						'alias' => $params['alias'],
					];
					set_transient('pgs_activation_' . $response['activationKey'], $transient_data ); 

					PGS()->set_alert( 'register_success' );

					// Unset affiliate trackingId
					\Parlay\Api\Affiliates::reset_tracking();

					// Return a message to the user that
					return [
						'success' => true,
						'message' => __( 'Thank you for registering! Please check your email to activate your account.', 'parlay-api' ),
					];
				} else {

					$activation_link = home_url( '/' . \Parlay\Api\Account::SLUG . '/activate-account' );

					$msg = sprintf(
						__( 'Failed to send activation email to %$s. Activation link: {link}', 'parlay-api' ),
					);
					$msg = sprintf(
						$msg,
						[
							'email' => $params['email'],
							'link'  => $activation_link . '/' . $response['activationKey'],
						]
					);

					error_log( 'Activation email failed: ' . $msg );
					return [
						'success' => true,
						'message' => $msg,
					];
				}

				return true;
			} else {

				if ( PGS_ENVIRONMENT == 'production' ) {
					// Send welcome email
					// if ($this->object->mailinglist->sendWelcomeEmail($accountInfo) === false) {
					//  $this->object->mailer->sendWelcomeEmail($accountInfo);
					// }
				} else {
					$mailer->sendWelcomeEmail( $params );
				}

				error_log( 'User registration successful: ' . print_r( $response, true ) );

				$extra_args = [
					'gender'        => $params['gender'],
					'activationKey' => $response['activationKey'],
				];

				if ( self::login( $params['alias'], $params['password'], $extra_args ) ) {
					return [
						'success' => true,
						'message' => __( 'Your account was successfully created.', 'parlay-api' ),
					];
				} else {
					return [
						'success' => true,
						'message' => __( 'Failed to login your account.', 'parlay-api' ),
					];
					error_log( 'Failed authentication after registration.' );
				}
				return false;
			}
		} catch ( SiteApiException $error ) {
			error_log( 'SiteApiException error: ' . print_r( $error->getMessage(), true ) );
			self::$errors['code']     = 500;
			self::$errors['response'] = $error->getMessage();
			return false;
		}
	}

	public static function activate_account( $activation_key ) {
		$uf = new UserFactory( DataManager::get_config() );
		$sanitized_key = sanitize_text_field( $activation_key );
		
		// Check if key exists in transient
		$user_data = get_transient( 'pgs_activation_' . $sanitized_key );
		if ( empty( $user_data ) ) {
			error_log( 'Activation key not found in transient: ' . print_r($sanitized_key, true) );
			return false;
		}

		if ( $uf->activateAccount( $sanitized_key ) ) {
			// Send welcome email
			$mailer = new Mailer();
			$mailer->sendWelcomeEmail( $user_data );

			// Remove user transient
			 delete_transient( 'pgs_activation_' . $sanitized_key );

			return true;
		}

		return false;
	}

	public static function update_account( $account_info ) {
		if ( ! self::is_authenticated() ) {
			self::$errors['response'] = __( 'User is not authenticated', 'parlay-api' );
			self::$errors['code']     = 500;
			return false;
		}

		if ( isset( $account_info['password'] ) && ! empty( $account_info['password'] ) ) {
			if ( $account_info['password'] !== $account_info['confirm_password'] ) {
				self::$errors['response'] = __( 'Password does not match', 'parlay-api' );
				self::$errors['code']     = 500;
				return false;
			}
		}

		if ( ! empty( $account_info['fullname'] ) ) {
			$fullName                  = explode( ' ', $account_info['fullname'] );
			$account_info['firstName'] = array_shift( $fullName );
			$account_info['lastName']  = implode( ' ', $fullName );

			unset( $account_info['fullname'] );
		}

		$account_info['language'] = isset( $account_info['language'] ) ? $account_info['language'] : 'pt';
		$account_info['currency'] = isset( $account_info['currency'] ) ? $account_info['currency'] : 'USD';

		if ( empty( $account_info['phoneNumber'] ) ) {
			$account_info['phoneNumber'] = '(11) 1111-1111';
		}

		$api_settings = DataManager::get_api_settings();
		$base_url     = trailingslashit( $api_settings['api_url'] );
		$api_route    = $base_url . 'site-api/v2/player/profile';

		$body = wp_json_encode( $account_info );

		// Set request arguments
		$request_args = [
			'timeout' => 15,
			'method'  => 'PUT',
			'headers' => [
				'Content-Type'    => 'application/json',
				'x-session-token' => PGS()->session->get_userdata( 'user_token' ),
			],
			'body'    => $body,
		];

		error_log( 'requesting update to: ' . $api_route . ' with: ' . print_r( $request_args, true ) );
		$response = wp_remote_request( $api_route, $request_args );
		$res_code = wp_remote_retrieve_response_code( $response );
		$res_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $res_code > 204 ) {
			if ( isset( $res_body['key'] ) && 'invalid.token.session' === $res_body['key'] ) {
				error_log( 'Updating failed, token has expired' );
				PGS()->set_alert( 'token' );
				UserAuth::set_unauthenticated();
			}

			do_action( 'qm/debug', 'Error updating account. Response: ' . print_r( $res_body, true ) );

			return false;
		}

		if ( $res_code === 200 ) {
			// Update the current session
			$current_session  = PGS()->session->get_userdata();
			$new_session_data = wp_parse_args( $account_info, $current_session );

			PGS()->session->set( 'player', $new_session_data );
			PGS()->session->set( 'info_cached', true );

			// Update last_validated for activity tracking
			PGS()->session->set( 'last_validated', time() );

			return [
				'code' => $res_code,
				'body' => $res_body,
			];
		}

		self::$errors['response'] = $res_body;
		self::$errors['code']     = $res_code;
		return false;
	}

	/**
	 * Logout
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function logout( $error = '' ) {
		$token_param = '';
		if ( 'invalid.token.session' === $error ) {
			$token_param = '?psg_error=token';
		}

		// reset user data
		PGS()->session->reset_session();

		self::redirect( '/login' . $token_param );
		exit;
	}

	/**
	 * Forgot password
	 * 
	 * @param string $email
	 * @since 1.1.0
	 */
	public static function forgot_password( $email ) {
		
		$user = new User( DataManager::get_config(), [ 'email' => $email, 'city' => '' ] );
		try {
			
			$reset = $user->createForgotPasswordToken();

			if ( ! $reset ) {
				self::$errors['code']     = 400;
				self::$errors['response']['message'] = PGS()->show_error_i18n( 'error.no.match' );
				return false;
			}
			
			$req = $user->request('site-api/getplayerprofileforadmin.action', [
				'userId' => $reset['token2'], 
			], null, true );

			$player = array_values( (array)$req->rows->row );
			if ( ! isset( $player[0] ) ) {
				return [
					'success' => true,
					'message' => __( 'Account not found.', 'parlay-api' ),
				];
			}

			$mailer = new Mailer();
			$player[0]['reset_pwd_token'] = $reset['token'];
			$reset_email = $mailer->sendForgotPasswordEmail( $player[0] );

			if ( $reset_email ) {

				// Store reset token in transient extra validation
				$transient_data = [
					'email' => $player[0]['email'],
					'alias' => $player[0]['alias'],
				];
				set_transient('pgs_reset_pwd_token_' . $reset['token'], $transient_data, HOUR_IN_SECONDS * 24 );

				return [
					'success' => true,
					'message' => __( 'Your account has been found, and you have been e-mailed instructions on how to complete the password reset process.', 'parlay-api' ),
				];
			} else {
				error_log( 'Failed to send reset email to: ' . $player[0]['email'] );
				self::$errors['code']     = 400;
				self::$errors['response']['message'] = __( 'Failed to send reset email.', 'parlay-api' );
				return false;
			}
			
		} catch ( SiteApiException $e ) {
			error_log( 'Forgot password failed: ' . $e->getMessage() );

			$error_translation = PGS()->show_error_i18n( $e->getToken() );
			if ( $error_translation !== $e->getToken() ) {
				self::$errors['code']     = 400;
				self::$errors['response']['message'] = $error_translation;
			} else {
				self::$errors['code']     = 500;
				self::$errors['response'] = $e->getMessage();
			}

			return false;
		}
	}

	/**
	 * Reset Password
	 * 
	 * @param array $params
	 * @since 1.1.0
	 */
	public static function reset_password( $params ){
		try {
			error_log( 'reset params: ' . print_r( $params, true ) );
			$token_data = get_transient( 'pgs_reset_pwd_token_' . $params['reset_token'] );
			if ( empty( $token_data ) ) {
				return false;
			}

			$user = new User( DataManager::get_config(), [ 
				'email'    => $token_data['email'], 
				'password' => $params['password'],
			] );

			if ( $user->resetPassword( $params['reset_token'] ) ) {
				// Remove user transient
				delete_transient( 'pgs_reset_pwd_token_' . $params['reset_token'] );

				return [
					'success' => true,
					'message' => __( 'Your password has successfully been reset. Redirecting to login page...', 'parlay-api' ),
				];
			} else {
				return [
					'success' => true,
					'message' => __( 'Unable to reset password.', 'parlay-api' ),
				];
			}
		
		} catch ( SiteApiException $e ) {
			$error_translation = PGS()->show_error_i18n( $e->getToken() );
			if ( $error_translation !== $e->getToken() ) {
				self::$errors['code']     = 400;
				self::$errors['response']['message'] = $error_translation;
			} else {
				self::$errors['code']     = 500;
				self::$errors['response'] = $e->getMessage();
			}
			
			return false;
		}
	}

	public static function get_errors() {
		return self::$errors;
	}
}
