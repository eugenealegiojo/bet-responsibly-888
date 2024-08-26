<?php

namespace Parlay\Api\Route\Account;

use WP_REST_Request;
use WP_REST_Server;
use WP_REST_Response;
use Parlay\Api\UserAuth;
use Parlay\Api\DataManager;
use Parlay\SiteApi\Accounts\UserFactory;

class Register extends Endpoint {

	public function __construct() {
		$this->methods  = WP_REST_Server::CREATABLE;
		$this->endpoint = 'register';
	}

	public function args() {
		return [
			'fullname'            => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'alias'               => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'email'               => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'password'            => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'birthday'            => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'city'                => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'gender'              => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'mobileno'            => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'phoneno'             => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'newsletter'          => [
				'type'     => 'boolean',
				'required' => false,
			],
			'trackingId'          => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'affiliateSystemCode' => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	public function handleRequest( WP_REST_Request $request ) {
		// $fullname  = explode( ' ', $request->get_param( 'fullname' ) );

		error_log( 'settings: ' . print_r( DataManager::get_api_settings(), true ) );

		$api_date = date( 'Y-m-d', strtotime( $request->get_param( 'birthday' ) ) );

		// error_log( 'api_date: ' . $api_date . ' - ' . print_r($accountData, true) );

		$params = [
			'fullName'          => $request->get_param( 'fullname' ),
			'alias'             => $request->get_param( 'alias' ),
			'email'             => $request->get_param( 'email' ),
			'password'          => $request->get_param( 'password' ),
			'birthDate'         => $request->get_param( 'birthday' ),
			'city'              => $request->get_param( 'city' ),
			'gender'            => $request->get_param( 'gender' ),
			'mobilePhone'       => $request->get_param( 'mobileno' ),
			'phoneNumber'       => $request->get_param( 'phoneno' ),
			'marketingOptIn'    => $request->get_param( 'newsletter' ),
			'shortRegistration' => true,
		];

		// PROMOTION TRACKING ID - empty parameter trackingId ='' required to reset default trackingId = '-9999'
		// if ( ! isset( $request->get_param( 'trackingId' ) ) ) {
		// 	// $params[ 'trackingId' ] = '';
		// }

		$params['trackingId'] = $request->get_param( 'trackingId' ) ?? '';

		if ( null !== $request->get_param( 'affiliateSystemCode' ) ) {
			$params['affiliate_system_code'] = $request->get_param( 'affiliateSystemCode' );
		}


		$copy_params = $params;
		// $copy_params['siteId'] =  'SITE';
		// $copy_params['key']    = 'abc';

		// error_log( 'params: ' . http_build_query( $copy_params ) );

		// $result = UserAuth::register( $params );
		$result = $this->processShort( $params );

		if ( ! $result ) {
			error_log( 'error register result: ' . print_r( UserAuth::get_errors(), true ) );
			$errors = UserAuth::get_errors();

			$messages = '';
			if ( is_array( $errors['response'] ) ) {
				$messages = implode( "\n", $errors['response'] );
			} else {
				$messages = $errors['response'];
			}

			return new WP_REST_Response(
				[
					'status'   => $errors['code'],
					'response' => [
						'message' => $messages,
					],
				]
			);
		} else {
			error_log( 'success register result: ' . print_r( $result, true ) );
			return new WP_REST_Response(
				[
					'status'   => 200,
					'response' => $result,
					// 'redirect_url' => home_url(),
				]
			);
		}
	}

	public function processShort( $accountData ) {
		$settings = DataManager::get_api_settings();
		$user_id  = UserFactory::generateUserId( $settings['site_id'] );

		$accountData['userId'] = $user_id;

		if ( ! empty( $accountData['fullName'] ) ) {
			$fullName                 = explode( ' ', $accountData['fullName'] );
			$accountData['firstName'] = array_shift( $fullName );
			$accountData['lastName']  = implode( ' ', $fullName );
		}

		//Prep Bday For API
		if ( isset( $accountData['birthDate'] ) ) {
			// $api_date = convert_to_gmt($account_info['birthMonth'],$account_info['birthDay'],$account_info['birthYear']);

			//Prep Bday For API
			$accountData['birthMonth'] = date( 'm', strtotime( $accountData['birthDate'] ) );
			$accountData['birthDay']   = date( 'd', strtotime( $accountData['birthDate'] ) );
			$accountData['birthYear']  = date( 'Y', strtotime( $accountData['birthDate'] ) );

			// API v1 - Minus 1 on birthMonth (zero-based month)
			if ( $accountData['birthMonth'] > 0 ) {
				$accountData['birthMonth'] = $accountData['birthMonth'] - 1;
			}

			unset( $accountData['birthDate'] );
		}

		$accountData['address1']   = isset( $settings['address'] ) ? $settings['address'] : 'XX';
		$accountData['postalCode'] = isset( $settings['post_code'] ) ? $settings['post_code'] : 'XX';
		$accountData['province']   = isset( $settings['province'] ) ? $settings['province'] : 'SP';
		$accountData['country']    = isset( $settings['country'] ) ? $settings['country'] : 'BR';
		$accountData['language']   = isset( $settings['language'] ) ? $settings['language'] : 'pt';
		$accountData['currency']   = isset( $settings['currency'] ) ? $settings['currency'] : 'USD';

		if ( empty( $accountData['phoneNumber'] ) ) {
			$accountData['phoneNumber'] = '(11) 1111-1111';
		}

		return UserAuth::register( $accountData );
	}

	public function checkPermission( WP_REST_Request $request ) {
		return true;
	}
}
