<?php

namespace Parlay\Api\Route\Account;

use Parlay\Api\Route\Account\Endpoint;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use Parlay\Api\UserAuth;

class ForgotPassword extends Endpoint {

	public function __construct() {
		$this->methods  = WP_REST_Server::CREATABLE; // POST
		$this->endpoint = 'forgot-password';
	}

	public function args() {
		return [
			'email' => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	public function handleRequest( WP_REST_Request $request ) {
		$email  = $request->get_param( 'email' );
		$result = UserAuth::forgot_password( $email );

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
						'type'    => $result['success'] ? 'success' : 'error',
						'message' => $result['message'],
						'timer'   => 4000,
					],
				]
			);
		}
	}

	public function checkPermission( WP_REST_Request $request ) {
		return true;
	}
}
