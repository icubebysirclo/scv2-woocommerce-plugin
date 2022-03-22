<?php
/**
 * SCV2 - Item controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API View individual item controller class.
 */
class SCV2_Item_v2_Controller extends SCV2_Item_Controller {

	/**
	 * Endpoint namespace.
	 */
	protected $namespace = 'scv2/v2';

	/**
	 * Route base.
	 */
	protected $rest_base = 'cart/item';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Get Item - scv2/v2/cart/item/6364d3f0f495b6ab9dcf8d3b5c6e0b01 (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<item_key>[\w]+)',
			array(
				'args' => $this->get_collection_params(),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'view_item' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	} // register_routes()

	/**
	 * View Item in Cart.
	 */
	public function view_item( $request = array() ) {
		try {
			$item_key = ! isset( $request['item_key'] ) ? '' : sanitize_text_field( wp_unslash( wc_clean( $request['item_key'] ) ) );

			$controller = new SCV2_Cart_V2_Controller();

			$cart_contents = ! $controller->get_cart_instance()->is_empty() ? array_filter( $controller->get_cart_instance()->get_cart() ) : array();

			$item = $controller->get_items( $cart_contents );

			$item = isset( $item[ $item_key ] ) ? $item[ $item_key ] : false;

			// If item is not found, throw exception error.
			if ( ! $item ) {
				throw new SCV2_Data_Exception( 'scv2_item_not_found', __( 'Item specified was not found in cart.', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			return SCV2_Response::get_response( $item, $this->namespace, $this->rest_base );
		} catch ( SCV2_Data_Exception $e ) {
			return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END view_item()

	/**
	 * Get the query params for item.
	 */
	public function get_collection_params() {
		$controller = new SCV2_Cart_V2_Controller();

		$params = array_merge(
			$controller->get_collection_params(),
			array(
				'item_key' => array(
					'description'       => __( 'Unique identifier for the item in the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg',
				),
			)
		);

		return $params;
	} // END get_collection_params()

} // END class
