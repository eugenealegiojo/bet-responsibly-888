<?php

namespace Parlay\Api\Route\Games;

use Parlay\Api\Route\RouteInterface;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use Parlay\Api\Plugin;

class Launcher implements RouteInterface {

	/**
	 * Base endpoint
	 *
	 * @var string
	 */
	protected $endpointBase = 'games/';

	/**
	 * WP_REST_Request
	 *
	 * @var string
	 */
	protected $request;

	/**
	 * Route methods
	 *
	 * @var string
	 */
	protected $methods = 'GET';

	/**
	 * Returns arguments for Route
	 *
	 * @since 1.0.0
	 * @return array
	 *
	 */
	public function args() {
		return [
			'id'       => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'category' => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Register route endpoint
	 *
	 * @since 1.0.0
	 */
	public function registerRoute() {
		register_rest_route(
			Plugin::instance()->rest_base['endpoint'],
			$this->endpointBase . 'launch',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'handleRequest' ],
					'permission_callback' => [ $this, 'checkPermission' ],

				],
				'args' => $this->args(),
			]
		);
	}

	public function handleRequest( WP_REST_Request $request ) {
		$args = [
			'category'    => $request->get_param( 'category' ),
			'return_type' => 'url', // launch code | url
		];

		if ( 'casino' === $request->get_param( 'category' ) ) {
			$args['gameId'] = $request->get_param( 'id' );
		} elseif ( 'bingo' === $request->get_param( 'category' ) ) {
			$args['roomId'] = $request->get_param( 'id' );
		}

		// $args['user_token'] = 'FBF3AB73DDA3AF8A4BDAB0F464DFA81E';

		error_log( 'Game launcher args: ' . print_r( $args, true ) );

		$result = \Parlay\Api\DataManager::get_launch_url( $args );

		if ( ! $result ) {
			error_log( 'launch not found: ' . print_r( $result, true ) );

			return new WP_REST_Response(
				[
					'status'   => 400,
					'response' => 'Launch url not found',
				]
			);
		} else {
			error_log( 'launching game... ' . print_r( $result, true ) );

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

	/**
	 * Setup properties
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request
	 */
	private function setupProperties( $request ) {
		$this->request = $request;
	}
}
