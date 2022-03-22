<?php
/**
 * SCV2 - Logout controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Logout v2 controller class.
 */
class SCV2_Logout_v2_Controller extends SCV2_Logout_Controller {

	/**
	 * Endpoint namespace.
	 */
	protected $namespace = 'scv2/v2';

	/**
	 * Route base.
	 */
	protected $rest_base = 'logout';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Logout user - scv2/v2/logout (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'logout' ),
				'permission_callback' => '__return_true',
			)
		);
	} // register_routes()

} // END class
