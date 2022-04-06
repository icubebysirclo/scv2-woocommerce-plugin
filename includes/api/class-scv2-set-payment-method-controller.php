<?php
/**
 * SCV2 - Set payment method controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SCV2 REST API Set payment method v2 controller class.
 */
class SCV2_Set_Payment_Method_v2_Controller {

	/**
	 * Endpoint namespace.
	 */
	protected $namespace = 'scv2/v2';

	/**
	 * Route base.
	 */
	protected $rest_base = 'cart/set-payment-method';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Set payment method - scv2/v2/cart/set-payment-method (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'set_payment_method' ),
					'permission_callback' => '__return_true',

				)
			)
		);
	} // register_routes()

	/**
	 * Set payment method.
	 */
	public function set_payment_method( $request = array() ) {
		// Auth
		// if ( is_user_logged_in() ) {
			try {
				// Check cart_key
				if (! isset($request['cart_key']) ) {
					return SCV2_Response::get_error_response( 'Bad Request', __('Field `cart_key` must be define'), 400 );
				}

				// Get parameters
				$cart_key = $request['cart_key'];
				$payment_provider = ! isset( $request['payment_provider'] ) ? "" : $request['payment_provider'];
				$payment_service = ! isset( $request['payment_service'] ) ? "" : $request['payment_service'];

				// Formatting data
				$cart_payment = array(
					'method_id' => 'scv2',
					'method_title' => wc_clean( $payment_provider.' - '.$payment_service ),
				);

				// Update cart_payment
				global $wpdb;

				try {
				    $wpdb->update(
				    	$wpdb->prefix.'scv2_carts', 
				    	array('cart_payment' => maybe_serialize( $cart_payment )), 
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
		// }

		// return SCV2_Response::get_error_response( 'Unauthorized', __('You shall not pass'), 401 );
	} // END set_payment_method()
	
} // END class
