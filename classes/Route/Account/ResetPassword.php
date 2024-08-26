<?php

namespace Parlay\Api\Route\Account;

use Parlay\Api\Route\Account\Endpoint;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use Parlay\Api\UserAuth;

class ResetPassword extends Endpoint {

	public function __construct() {
		$this->methods  = WP_REST_Server::CREATABLE; // POST
		$this->endpoint = 'reset-password';
	}

	public function args() {
		return [
			'reset_token' => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'password'    => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	public function handleRequest( WP_REST_Request $request ) {
		$params['reset_token'] = $request->get_param( 'reset_token' );
		$params['password']    = $request->get_param( 'password' );

		$result = UserAuth::reset_password( $params );

		if ( ! $result ) {
			$errors = UserAuth::get_errors();
			return new WP_REST_Response(
				[
					'status'   => $errors['code'],
					'response' => $errors['response'],
				]
			);
		} else {
			return new WP_REST_Response(
				[
					'status'   => 200,
					'response' => $result,
					'alert'    => [
						'type'     => 'success',
						'message'  => $result['message'],
						'timer'    => 10000,
						'confirm'  => 'login',
						'redirect' => home_url( '/login' ),
					],
				]
			);
		}
	}

	public function checkPermission( WP_REST_Request $request ) {
		return true;
	}
}
