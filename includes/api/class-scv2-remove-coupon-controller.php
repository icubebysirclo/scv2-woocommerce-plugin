<?php
/**
 * SCV2 - Remove coupon controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SCV2 REST API Remove coupon v2 controller class.
 */
class SCV2_Remove_Coupon_v2_Controller {

	/**
	 * Endpoint namespace.
	 */
	protected $namespace = 'scv2/v2';

	/**
	 * Route base.
	 */
	protected $rest_base = 'cart/remove-coupon';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Remove coupon - scv2/v2/cart/remove-coupon (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'remove_coupon' ),
					'permission_callback' => '__return_true',

				)
			)
		);
	} // register_routes()

	/**
	 * Remove coupon.
	 */
	public function remove_coupon( $request = array() ) {
		// Auth
		try {
			// Check cart_key
			if (! isset($request['cart_key']) ) {
				return SCV2_Response::get_error_response( 'Bad Request', __('Field `cart_key` must be define'), 400 );
			}

			// Define global
			global $woocommerce;

			// Get parameters
			$cart_key = $request['cart_key'];

			global $wpdb;

			// Get cart_coupons
			$cart_coupons = $wpdb->get_var( 
		    	$wpdb->prepare("
		    		SELECT cart_coupons 
		    		FROM {$wpdb->prefix}scv2_carts 
		    		WHERE cart_key = %s", $cart_key 
		    	) 
		    );

			// Unserialize data
    		$cart_coupons = maybe_unserialize( $cart_coupons );

    		// Update cart_coupons
    		if (! empty( $cart_coupons ) ) {

    			// remove discount
    			foreach ($cart_coupons as $item) {
					WC()->cart->remove_coupon( $item["coupon_code"] );
				}

    			$cart_coupons = '';
    			try {
				    $wpdb->update(
				    	$wpdb->prefix.'scv2_carts', 
				    	array('cart_coupons' => maybe_serialize( $cart_coupons )), 
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
    		}
			return SCV2_Response::get_error_response( 'Failed', __('No coupon applied'), 400 );
		} catch ( SCV2_Data_Exception $e ) {
			return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}

	} // END set_shipping_method()

} // END class
