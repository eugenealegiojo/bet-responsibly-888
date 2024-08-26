<?php

namespace Parlay\Api\Route\Account;

use Parlay\Api\Route\Account\Endpoint;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use Parlay\Api\UserAuth;

class Login extends Endpoint {

	public function __construct() {
		$this->methods  = WP_REST_Server::CREATABLE; // POST
		$this->endpoint = 'login';
	}

	public function args() {
		return [
			'username' => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'password' => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	public function handleRequest( WP_REST_Request $request ) {
		$username = $request->get_param( 'username' );
		$password = $request->get_param( 'password' );
		$result   = UserAuth::login( $username, $password );

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
				]
			);
		}
	}

	public function checkPermission( WP_REST_Request $request ) {
		return true;
	}
}
