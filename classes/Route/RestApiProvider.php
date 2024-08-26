<?php

namespace Parlay\Api\Route;

use Parlay\Api\Route\Account\Register;
use Parlay\Api\Route\Account\Login;
use Parlay\Api\Route\Games\Launcher;
use Parlay\Api\Route\Account\ForgotPassword;
use Parlay\Api\Route\Account\ResetPassword;

class RestApiProvider {

	/**
	 * @var string[] array of RestRoute classes
	 */
	private $restRoutes = [
		Register::class,
		Login::class,
		Launcher::class,
		ForgotPassword::class,
		ResetPassword::class,
	];

	public function __construct() {
		$this->boot();
	}

	/**
	 * Register the routes for the plugin
	 *
	 * @since 1.0.0
	 */
	public function boot() {
		add_action( 'rest_api_init', [ $this, 'registerRoutes' ] );
	}

	/**
	 * Calls the route registrations within the WordPress REST API hook
	 *
	 * @since 1.0.0
	 */
	public function registerRoutes() {
		foreach ( $this->restRoutes as $route ) {
			$route = new $route();
			$route->registerRoute();
		}
	}
}
