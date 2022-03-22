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
class SCV2_Count_Items_v2_Controller extends SCV2_Count_Items_Controller {

	/**
	 * Endpoint namespace.
	 */
	protected $namespace = 'scv2/v2';

	/**
	 * Route base.
	 */
	protected $rest_base = 'cart/items/count';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Count Items in Cart - scv2/v2/cart/items/count (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_cart_contents_count' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	} // register_routes()

	/**
	 * Get cart contents count.
	 */
	public static function get_cart_contents_count( $request = array(), $cart_contents = array() ) {
		$return        = ! empty( $request['return'] ) ? $request['return'] : '';
		$removed_items = isset( $request['removed_items'] ) ? $request['removed_items'] : false;

		$controller = new SCV2_Cart_V2_Controller();

		if ( empty( $cart_contents ) ) {
			// Return count for removed items in cart.
			if ( isset( $request['removed_items'] ) && is_bool( $request['removed_items'] ) && $request['removed_items'] ) {
				$count = array_sum( wp_list_pluck( $controller->get_cart_instance()->get_removed_cart_contents(), 'quantity' ) );
			} else {
				// Return count for items in cart.
				$count = $controller->get_cart_instance()->get_cart_contents_count();
			}
		} else {
			// Counts all items from the quantity variable.
			$count = array_sum( wp_list_pluck( $cart_contents, 'quantity' ) );
		}

		if ( 'numeric' !== $return && $count <= 0 ) {
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

	/**
	 * Get the schema for returning the item count, conforming to JSON Schema.
	 */
	public function get_item_schema() {
		$schema = array(
			'schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'SCV2 - ' . __( 'Count Items in Cart', 'cart-rest-api-for-woocommerce' ),
			'type'       => 'object',
			'properties' => array(
				'removed_items' => array(
					'required'    => false,
					'default'     => false,
					'description' => __( 'Returns count for removed items from the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
				),
			),
		);

		return $schema;
	} // END get_item_schema()

	/**
	 * Get the query params for counting items.
	 */
	public function get_collection_params() {
		$params = array(
			'return'        => array(
				'required'          => false,
				'default'           => 'numeric',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'removed_items' => array(
				'required' => false,
				'default'  => false,
				'type'     => 'boolean',
			),
		);

		return $params;
	} // END get_collection_params()

} // END class
