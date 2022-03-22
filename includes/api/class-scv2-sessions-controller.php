<?php
/**
 * SCV2 REST API Sessions controller.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SCV2 REST API Sessions v2 controller class.
 */
class SCV2_Sessions_V2_Controller extends SCV2_Cart_V2_Controller {

	/**
	 * Endpoint namespace.
	 */
	protected $namespace = 'scv2/v2';

	/**
	 * Route base.
	 */
	protected $rest_base = 'sessions';

	/**
	 * Register the routes for index.
	 */
	public function register_routes() {
		// Get Sessions - scv2/v2/sessions (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_carts_in_session' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			// 'schema' => array( $this, 'get_item_schema' )
			)
		);
	} // register_routes()

	/**
	 * Check whether a given request has permission to read site data.
	 */
	public function get_items_permissions_check() {
		if ( ! wc_rest_check_manager_permissions( 'settings', 'read' ) ) {
			return new WP_Error( 'scv2_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'cart-rest-api-for-woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	} // END get_items_permissions_check()

	/**
	 * Returns carts in session if any exists.
	 */
	public function get_carts_in_session() {
		try {
			global $wpdb;

			$results = $wpdb->get_results(
				"
				SELECT * 
				FROM {$wpdb->prefix}scv2_carts",
				ARRAY_A
			);

			if ( empty( $results ) ) {
				throw new SCV2_Data_Exception( 'scv2_no_carts_in_session', __( 'No carts in session!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			$sessions = array();

			foreach ( $results as $key => $cart ) {
				$cart_value = maybe_unserialize( $cart['cart_value'] );
				$customer   = maybe_unserialize( $cart_value['customer'] );

				$email      = ! empty( $customer['email'] ) ? $customer['email'] : '';
				$first_name = ! empty( $customer['first_name'] ) ? $customer['first_name'] : '';
				$last_name  = ! empty( $customer['last_name'] ) ? $customer['last_name'] : '';

				if ( ! empty( $first_name ) || ! empty( $last_name ) ) {
					$name = $first_name . ' ' . $last_name;
				} else {
					$name = '';
				}

				$cart_source = $cart['cart_source'];

				$sessions[] = array(
					'cart_id'         => $cart['cart_id'],
					'cart_key'        => $cart['cart_key'],
					'customers_name'  => $name,
					'customers_email' => $email,
					'cart_created'    => gmdate( 'm/d/Y H:i:s', $cart['cart_created'] ),
					'cart_expiry'     => gmdate( 'm/d/Y H:i:s', $cart['cart_expiry'] ),
					'cart_source'     => $cart_source,
					'link'            => rest_url( sprintf( '/%s/%s', $this->namespace, 'session/' . $cart['cart_key'] ) ),
				);
			}

			return SCV2_Response::get_response( $sessions, $this->namespace, $this->rest_base );
		} catch ( \SCV2_Data_Exception $e ) {
			return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_carts_in_session()

} // END class
