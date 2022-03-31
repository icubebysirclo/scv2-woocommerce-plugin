<?php
/**
 * SCV2 - Set shipping method controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SCV2 REST API Set shipping method v2 controller class.
 */
class SCV2_Set_Shipping_Method_v2_Controller {

	/**
	 * Endpoint namespace.
	 */
	protected $namespace = 'scv2/v2';

	/**
	 * Route base.
	 */
	protected $rest_base = 'cart/set-shipping-method';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Set shipping method - scv2/v2/cart/set-shipping-method (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'set_shipping_method' ),
					'permission_callback' => '__return_true',

				)
			)
		);
	} // register_routes()

	/**
	 * Set shipping method.
	 */
	public function set_shipping_method( $request = array() ) {
		// Auth
		if ( is_user_logged_in() ) {
			try {
				// Check cart_key
				if (! isset($request['cart_key']) ) {
					return SCV2_Response::get_error_response( 'Bad Request', __('Field `cart_key` must be define'), 400 );
				}

				// Get parameters
				$cart_key = $request['cart_key'];
				$shipping_provider = ! isset( $request['shipping_provider'] ) ? "" : $request['shipping_provider'];
				$shipping_service = ! isset( $request['shipping_service'] ) ? "" : $request['shipping_service'];
				$shipping_cost 	= ! isset( $request['shipping_cost'] ) ? "" : $request['shipping_cost'];

				// Formatting data
				$cart_shipping = array(
					'method_id' => 'scv2',
					'method_title' => wc_clean( $shipping_provider.' - '.$shipping_service ),
					'total' => $shipping_cost 
				);

				// Update cart_shipping
				global $wpdb;

				try {
				    $wpdb->update(
				    	$wpdb->prefix.'scv2_carts', 
				    	array('cart_shipping' => maybe_serialize( $cart_shipping )), 
				    	array('cart_key' => $cart_key)
				    );

				    // Success status
					$response = array(
						"status" => true
					);

					return SCV2_Response::get_response( $response, $this->namespace, $this->rest_base );
				} catch ( SCV2_Data_Exception $e ) {
					return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
				}
			} catch ( SCV2_Data_Exception $e ) {
				return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
			}
		}

		return SCV2_Response::get_error_response( 'Unauthorized', __('You shall not pass'), 401 );
	} // END set_shipping_method()

	protected function calculate_totals() {

	}

} // END class
