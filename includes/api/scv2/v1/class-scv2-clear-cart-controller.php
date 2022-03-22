<?php
/**
 * SCV2 - Clear Cart controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Clear Cart controller class.
 */
class SCV2_Clear_Cart_Controller extends SCV2_API_Controller {

	/**
	 * Route base.
	 */
	protected $rest_base = 'clear';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Clear Cart - scv2/v1/clear (POST)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'clear_cart' ),
				'permission_callback' => '__return_true',
			)
		);
	} // register_routes()

	/**
	 * Clear cart.
	 */
	public function clear_cart() {
		do_action( 'scv2_before_cart_emptied' );

		WC()->session->set( 'cart', array() );
		WC()->session->set( 'removed_cart_contents', array() );
		WC()->session->set( 'shipping_methods', array() );
		WC()->session->set( 'coupon_discount_totals', array() );
		WC()->session->set( 'coupon_discount_tax_totals', array() );
		WC()->session->set( 'applied_coupons', array() );
		WC()->session->set( 'total', array(
			'subtotal'            => 0,
			'subtotal_tax'        => 0,
			'shipping_total'      => 0,
			'shipping_tax'        => 0,
			'shipping_taxes'      => array(),
			'discount_total'      => 0,
			'discount_tax'        => 0,
			'cart_contents_total' => 0,
			'cart_contents_tax'   => 0,
			'cart_contents_taxes' => array(),
			'fee_total'           => 0,
			'fee_tax'             => 0,
			'fee_taxes'           => array(),
			'total'               => 0,
			'total_tax'           => 0,
		) );
		WC()->session->set( 'cart_fees', array() );

		/**
		 * If the user is authorized and `woocommerce_persistent_cart_enabled` filter is left enabled 
		 * then we will delete the persistent cart as well.
		 */
		if ( get_current_user_id() && apply_filters( 'woocommerce_persistent_cart_enabled', true ) ) {
			delete_user_meta( get_current_user_id(), '_woocommerce_persistent_cart_' . get_current_blog_id() );
		}

		do_action( 'scv2_cart_emptied' );

		if ( 0 === count( WC()->cart->get_cart() ) || 0 === count( WC()->session->get( 'cart' ) ) ) {
			do_action( 'scv2_cart_cleared' );

			$message = __( 'Cart is cleared.', 'cart-rest-api-for-woocommerce' );

			SCV2_Logger::log( $message, 'notice' );

			/**
			 * Filters message about the cart being cleared.
			 */
			$message = apply_filters( 'scv2_cart_cleared_message', $message );

			return $this->get_response( $message, $this->rest_base );
		} else {
			$message = __( 'Clearing the cart failed!', 'cart-rest-api-for-woocommerce' );

			SCV2_Logger::log( $message, 'error' );

			/**
			 * Filters message about the cart failing to clear.
			 */
			$message = apply_filters( 'scv2_clear_cart_failed_message', $message );

			return new WP_Error( 'scv2_clear_cart_failed', $message, array( 'status' => 404 ) );
		}
	} // END clear_cart()

} // END class
