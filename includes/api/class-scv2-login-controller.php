<?php
/**
 * SCV2 - Login controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Login v2 controller class.
 */
class SCV2_Login_v2_Controller {

	/**
	 * Endpoint namespace.
	 */
	protected $namespace = 'scv2/v2';

	/**
	 * Route base.
	 */
	protected $rest_base = 'login';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Login user - scv2/v2/login (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'login' ),
				'permission_callback' => array( $this, 'get_permission_callback' ),
			)
		);
	} // register_routes()

	/**
	 * Check whether a given request has permission to read site data.
	 */
	public function get_permission_callback() {
		if ( strval( get_current_user_id() ) <= 0 ) {
			return new WP_Error( 'scv2_rest_not_authorized', __( 'Sorry, you are not authorized.', 'cart-rest-api-for-woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	} // END get_permission_callback()

	/**
	 * Login user.
	 */
	public function login() {
		$current_user = get_userdata( get_current_user_id() );

		$user_roles = $current_user->roles;

		$display_user_roles = array();

		foreach ( $user_roles as $role ) {
			$display_user_roles[] = ucfirst( $role );
		}

		$response = array(
			'user_id'      => strval( get_current_user_id() ),
			'display_name' => esc_html( $current_user->display_name ),
			'role'         => implode( ', ', $display_user_roles ),
			'dev_note'     => __( "Don't forget to store the users login information in order to authenticate all other routes with SCV2.", 'cart-rest-api-for-woocommerce' ),
		);

		return new WP_REST_Response( $response, 200 );
	} // END login()

} // END class
