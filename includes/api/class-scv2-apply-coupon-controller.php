<?php
/**
 * SCV2 - Apply coupon controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SCV2 REST API Apply coupon v2 controller class.
 */
class SCV2_Apply_Coupon_v2_Controller {

	/**
	 * Endpoint namespace.
	 */
	protected $namespace = 'scv2/v2';

	/**
	 * Route base.
	 */
	protected $rest_base = 'cart/apply-coupon';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Apply coupon - scv2/v2/cart/apply-coupon (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'apply_coupon' ),
					'permission_callback' => '__return_true',

				)
			)
		);
	} // register_routes()

	/**
	 * Apply coupon.
	 */
	public function apply_coupon( $request = array() ) {
		// Auth
		// if ( is_user_logged_in() ) {
			try {
				// Check cart_key
				if (! isset($request['cart_key']) ) {
					return SCV2_Response::get_error_response( 'Bad Request', __('Field `cart_key` must be define'), 400 );
				}

				// Check coupon_code
				if (! isset($request['coupon_code']) ) {
					return SCV2_Response::get_error_response( 'Bad Request', __('Field `coupon_code` must be define'), 400 );
				}

				// Define global
				global $woocommerce;

				// Get parameters
				$cart_key = $request['cart_key'];
				$coupon_code = $request['coupon_code'];

				// Create an instance of WC_Coupon
				$coupon = new WC_Coupon( $coupon_code );

				// Check coupon to make determine if its valid or not
			    if( ! $coupon->is_valid() ) {
			        return SCV2_Response::get_error_response( 'Failed', __('Coupon does not exist'), 400 );
			    }

				if ( ! empty( $coupon_code ) && ! WC()->cart->has_discount( $coupon_code ) ){
					// apply the coupon discount
					WC()->cart->add_discount( $coupon_code );

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

					// Checking cart has coupon or not
					if ( empty( $cart_coupons ) ) {
						// Insert new coupon
						$cart_coupons[] = $new_coupons;
					} else {
					    // Unserialize data
		    			$cart_coupons = maybe_unserialize( $cart_coupons );

		    			// Insert new coupon
					    if ( !in_array( $new_coupons, $cart_coupons, true ) ) {
					        array_push( $cart_coupons, $new_coupons );
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
				return SCV2_Response::get_error_response( 'Failed', __('Coupon already applied'), 400 );
			} catch ( SCV2_Data_Exception $e ) {
				return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
			}
		// }

		// return SCV2_Response::get_error_response( 'Unauthorized', __('You shall not pass'), 401 );
	} // END set_shipping_method()

} // END class
