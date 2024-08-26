<?php

namespace Parlay\Api\Route\Account;

use WP_REST_Request;
use WP_REST_Server;
use WP_REST_Response;
use Parlay\Api\UserAuth;
use Parlay\Api\DataManager;
use Parlay\SiteApi\Accounts\UserFactory;

class Update extends Endpoint {

	public function __construct() {
		$this->methods  = WP_REST_Server::CREATABLE;
		$this->endpoint = 'update';
	}

	public function args() {
		return [
			'fullname'         => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'password'         => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'confirm_password' => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => function ( $value ) {
					$password = $_POST['password'];
					if ( $value !== $password ) {
						return new WP_Error( ' passwords_do_not_match', 'Passwords do not match', [ 'status' => 400 ] );
					}

					return sanitize_text_field( $value );
				},
			],
			'address1'         => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'city'             => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'state'            => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'postalCode'       => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'country'          => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'gender'           => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'mobilePhone'      => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'phoneNumber'      => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'receiveBroadcast' => [
				'type'     => 'boolean',
				'required' => false,
			],
		];
	}

	public function handleRequest( WP_REST_Request $request ) {
		$params = [
			'fullName'       => $request->get_param( 'fullname' ),
			'password'       => $request->get_param( 'password' ),
			'address1'       => $request->get_param( 'address1' ),
			'city'           => $request->get_param( 'city' ),
			'state'          => $request->get_param( 'state' ),
			'postalCode'     => $request->get_param( 'postalCode' ),
			'country'        => $request->get_param( 'country' ),
			'gender'         => $request->get_param( 'gender' ),
			'mobilePhone'    => $request->get_param( 'mobilePhone' ),
			'phoneNumber'    => $request->get_param( 'phoneNumber' ),
			'marketingOptIn' => $request->get_param( 'receiveBroadcast' ),
		];

		$copy_params = $params;

		error_log( 'updating...: ' . http_build_query( $copy_params ) );

		$result = $this->processShort( $params );

		if ( ! $result ) {
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
			// error_log( 'has result: ' . print_r( $result, true ) );
			return new WP_REST_Response(
				[
					'status'   => 200,
					'response' => $result,
				]
			);
		}
	}

	public function processShort( $accountData ) {
		if ( ! empty( $accountData['fullName'] ) ) {
			$fullName                 = explode( ' ', $accountData['fullName'] );
			$accountData['firstName'] = array_shift( $fullName );
			$accountData['lastName']  = implode( ' ', $fullName );
		}

		$accountData['language'] = isset( $accountData['language'] ) ? $accountData['language'] : 'pt';
		$accountData['currency'] = isset( $accountData['currency'] ) ? $accountData['currency'] : 'USD';

		if ( empty( $accountData['phoneNumber'] ) ) {
			$accountData['phoneNumber'] = '(11) 1111-1111';
		}

		return UserAuth::update_account( $accountData );
	}

	public function checkPermission( WP_REST_Request $request ) {
		return true;
	}
}
