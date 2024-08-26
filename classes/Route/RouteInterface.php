<?php

namespace Parlay\Api\Route;

interface RouteInterface {
	/**
	 * Register the route with WordPress using the register_rest_route function.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function registerRoute();
}
