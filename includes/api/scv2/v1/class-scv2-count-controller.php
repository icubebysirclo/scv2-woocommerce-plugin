<?php
/**
 * SCV2 - Count Items controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Count Items controller class.
 */
class SCV2_Count_Items_Controller extends SCV2_API_Controller {

	/**
	 * Route base.
	 */
	protected $rest_base = 'count-items';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Count Items in Cart - scv2/v1/count-items (GET)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_cart_contents_count' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'return' => array(
						'default' => 'numeric',
						'type'    => 'string',
					),
				),
			)
		);
	} // register_routes()

	/**
	 * Count items.
	 */
	public static function count_items( $data = array(), $cart_contents = array() ) {
		if ( empty( $cart_contents ) ) {
			$count = WC()->cart->get_cart_contents_count();
		} else {
			// Counts all items from the quantity variable.
			$count = array_sum( wp_list_pluck( $cart_contents, 'quantity' ) );
		}

		return $count;
	}

	/**
	 * Get cart contents count.
	 */
	public static function get_cart_contents_count( $data = array(), $cart_contents = array() ) {
		$return = ! empty( $data['return'] ) ? $data['return'] : '';
		$count  = self::count_items( $data, $cart_contents );

		if ( $return != 'numeric' && $count <= 0 ) {
			$message = __( 'There are no items in the cart!', 'cart-rest-api-for-woocommerce' );

			SCV2_Logger::log( $message, 'notice' );

			/**
			 * Filters message about no items in the cart.
			 */
			$message = apply_filters( 'scv2_no_items_in_cart_message', $message );

			return new WP_REST_Response( $message, 200 );
		}

		return $count;
	} // END get_cart_contents_count()

} // END class
