<?php
/**
 * SCV2 - Restore Item controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Restore Item controller class.
 */
class SCV2_Restore_Item_v2_Controller extends SCV2_Cart_V2_Controller {

	/**
	 * Route base.
	 */
	protected $rest_base = 'cart/item';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Restore Item - scv2/v2/cart/item/6364d3f0f495b6ab9dcf8d3b5c6e0b01 (PUT).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<item_key>[\w]+)',
			array(
				'args' => $this->get_collection_params(),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'restore_item' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	} // register_routes()

	/**
	 * Restores an Item in Cart.
	 */
	public function restore_item( $request = array() ) {
		try {
			$item_key = ! isset( $request['item_key'] ) ? '0' : sanitize_text_field( wp_unslash( wc_clean( $request['item_key'] ) ) );

			$item_key = $this->throw_missing_item_key( $item_key, 'restore' );

			// Check item removed from cart before fetching the cart item data.
			$current_data = $this->get_cart_instance()->get_removed_cart_contents();

			// If item does not exist in cart return response.
			if ( empty( $current_data ) ) {
				$restored_item = $this->get_cart_item( $item_key, 'restore' );

				// Check if the item has already been restored.
				if ( isset( $restored_item ) ) {
					$product = wc_get_product( $restored_item['product_id'] );

					/* translators: %s: Item name. */
					$item_already_restored_title = apply_filters( 'scv2_cart_item_already_restored_title', $product ? sprintf( _x( '"%s"', 'Item name in quotes', 'cart-rest-api-for-woocommerce' ), $product->get_name() ) : __( 'Item', 'cart-rest-api-for-woocommerce' ) );

					/* translators: %s: Item name. */
					$message = sprintf( __( '%s has already been restored to the cart.', 'cart-rest-api-for-woocommerce' ), $item_already_restored_title );
				} else {
					$message = __( 'Item does not exist in cart.', 'cart-rest-api-for-woocommerce' );
				}

				/**
				 * Filters message about item already restored to cart.
				 */
				$message = apply_filters( 'scv2_item_restored_message', $message );

				throw new SCV2_Data_Exception( 'scv2_item_restored_to_cart', $message, 404 );
			}

			if ( $this->get_cart_instance()->restore_cart_item( $item_key ) ) {
				$restored_item = $this->get_cart_item( $item_key, 'restore' ); // Fetches the cart item data once it is restored.

				do_action( 'scv2_item_restored', $restored_item );

				/**
				 * Calculates the cart totals now an item has been restored.
				 */
				$this->get_cart_instance()->calculate_totals();

				// Get cart contents.
				$response = $this->get_cart_contents( $request );

				// Was it requested to return just the restored item?
				if ( $request['return_item'] ) {
					$response = $this->get_item( $restored_item['data'], $restored_item, $restored_item['key'], true );
				}

				return SCV2_Response::get_response( $response, $this->namespace, $this->rest_base );
			} else {
				$message = __( 'Unable to restore item to the cart.', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about can not restore item.
				 */
				$message = apply_filters( 'scv2_can_not_restore_item_message', $message );

				throw new SCV2_Data_Exception( 'scv2_can_not_restore_item', $message, 403 );
			}
		} catch ( SCV2_Data_Exception $e ) {
			return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END restore_item()

	/**
	 * Get the query params for restoring an item.
	 */
	public function get_collection_params() {
		$controller = new SCV2_Cart_V2_Controller();

		$params = array_merge(
			$controller->get_collection_params(),
			array(
				'item_key'    => array(
					'description'       => __( 'Unique identifier for the item in the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg',
				),
				'return_item' => array(
					'description'       => __( 'Returns the item details once restored.', 'cart-rest-api-for-woocommerce' ),
					'default'           => false,
					'type'              => 'boolean',
					'validate_callback' => 'rest_validate_request_arg',
				),
			)
		);

		return $params;
	} // END get_collection_params()

} // END class
