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
		// if ( is_user_logged_in() ) {
			try {
				// Check cart_key
				if (! isset($request['cart_key']) ) {
					return SCV2_Response::get_error_response( 'Bad Request', __('Field `cart_key` must be define'), 400 );
				}

				// Define global
				global $woocommerce;

				// Get parameters
				$cart_key = $request['cart_key'];

				// Get applied coupon code
				$coupon_code = WC()->cart->get_applied_coupons();

				// print_r($coupon_code[0]);die();

				if ( $coupon_code ) {
					$coupon_code = $coupon_code[0];
				}

				// Create an instance of WC_Coupon
				$coupon = new WC_Coupon( $coupon_code );

				// Check coupon to make determine if its valid or not
			    if( ! $coupon->id && ! isset( $coupon->id ) ) {
			        return SCV2_Response::get_error_response( 'Failed', __('Coupon does not exist'), 400 );
			    }

				if ( ! empty( $coupon_code ) && WC()->cart->has_discount( $coupon_code ) ){
					// remove the coupon discount
					WC()->cart->remove_coupon( $coupon_code );

					// Formatting data
					$new_coupons = array(
						'coupon_code' => $coupon_code
					);

					// Update cart_coupons
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

					// Checking cart has coupon or not
		    		if ( count( $cart_coupons ) == 1) {
						$cart_coupons = '';
					} else {
						foreach ($cart_coupons as $key => $value) {
							if ($value['coupon_code'] == $coupon_code) {
								unset($cart_coupons[$key]);
							}
						}
					}

					// Update cart_coupons
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
		// }

		// return SCV2_Response::get_error_response( 'Unauthorized', __('You shall not pass'), 401 );
	} // END set_shipping_method()

} // END class
