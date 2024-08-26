<?php

namespace Parlay\Api\Route\Account;

use Parlay\Api\Route\RouteInterface;
use WP_REST_Request;
use WP_REST_Response;
use Parlay\Api\Plugin;

abstract class Endpoint implements RouteInterface {

	/**
	 * Base endpoint
	 *
	 * @var string
	 */
	protected $endpointBase = 'account/';

	/**
	 * Route endpoint
	 *
	 * @var string
	 */
	protected $endpoint;

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
	abstract public function args();

	/**
	 * Handles route request, and returns response
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|array
	 *
	 */
	abstract public function handleRequest( WP_REST_Request $request );

	/**
	 * Handle permissions. Checks if user can access the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request
	 *
	 */
	abstract public function checkPermission( WP_REST_Request $request );

	/**
	 * Register route endpoint
	 *
	 * @since 1.0.0
	 */
	public function registerRoute() {
		register_rest_route(
			Plugin::instance()->rest_base['endpoint'],
			$this->endpointBase . $this->endpoint,
			[
				[
					'methods'             => $this->methods,
					'callback'            => [ $this, 'handleRequest' ],
					'permission_callback' => [ $this, 'checkPermission' ],

				],
				'args' => $this->args(),
			]
		);
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
