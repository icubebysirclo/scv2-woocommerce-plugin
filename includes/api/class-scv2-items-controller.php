<?php
/**
 * SCV2 - Items controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API View Items controller class.
 */
class SCV2_Items_v2_Controller extends SCV2_Item_Controller {

	/**
	 * Endpoint namespace.
	 */
	protected $namespace = 'scv2/v2';

	/**
	 * Route base.
	 */
	protected $rest_base = 'cart/items';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Get Items - scv2/v2/cart/items (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'view_items' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	} // register_routes()

	/**
	 * Returns all items in the cart.
	 */
	public function view_items() {
		$controller = new SCV2_Cart_V2_Controller();

		$cart_contents = ! $controller->get_cart_instance()->is_empty() ? array_filter( $controller->get_cart_instance()->get_cart() ) : array();

		$items = $controller->get_items( $cart_contents );

		// Return message should the cart be empty.
		if ( empty( $cart_contents ) ) {
			$items = esc_html__( 'No items in the cart.', 'cart-rest-api-for-woocommerce' );
		}

		return SCV2_Response::get_response( $items, $this->namespace, $this->rest_base );
	} // END view_items()

} // END class
