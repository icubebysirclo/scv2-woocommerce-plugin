<?php
/**
 * SCV2 - Logout controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Logout controller class.
 */
class SCV2_Logout_Controller extends SCV2_API_Controller {

	/**
	 * Route base.
	 */
	protected $rest_base = 'logout';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Logout user - scv2/v1/logout (POST)
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

	/**
	 * Logout user.
	 */
	public function logout() {
		wp_logout();

		return new WP_REST_Response( true, 200 );
	} // END logout()

} // END class
