<?php
/**
 * SCV2 - Remove Item controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Remove Item controller class.
 */
class SCV2_Remove_Item_v2_Controller extends SCV2_Cart_V2_Controller {

	/**
	 * Route base.
	 */
	protected $rest_base = 'cart/item';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Remove Item - scv2/v2/cart/item/6364d3f0f495b6ab9dcf8d3b5c6e0b01 (DELETE).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<item_key>[\w]+)',
			array(
				'args' => $this->get_collection_params(),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'remove_item' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	} // register_routes()

	/**
	 * Removes an Item in Cart.
	 */
	public function remove_item( $request = array() ) {
		try {
			$item_key = ! isset( $request['item_key'] ) ? '0' : sanitize_text_field( wp_unslash( wc_clean( $request['item_key'] ) ) );

			$item_key = $this->throw_missing_item_key( $item_key, 'remove' );

			// Checks to see if the cart contains item before attempting to remove it.
			if ( $this->get_cart_instance()->get_cart_contents_count() <= 0 && count( $this->get_cart_instance()->get_removed_cart_contents() ) <= 0 ) {
				$message = __( 'No items in cart.', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about no items in cart.
				 */
				$message = apply_filters( 'scv2_no_items_message', $message );

				throw new SCV2_Data_Exception( 'scv2_no_items', $message, 404 );
			}

			// Check item exists in cart before fetching the cart item data to update.
			$current_data = $this->get_cart_item( $item_key, 'remove' );

			$product = wc_get_product( $current_data['product_id'] );

			/* translators: %s: Item name. */
			$item_removed_title = apply_filters( 'scv2_cart_item_removed_title', $product ? sprintf( _x( '"%s"', 'Item name in quotes', 'cart-rest-api-for-woocommerce' ), $product->get_name() ) : __( 'Item', 'cart-rest-api-for-woocommerce' ), $current_data );

			// If item does not exist in cart return response.
			if ( empty( $current_data ) ) {
				$removed_contents = $this->get_cart_instance()->get_removed_cart_contents();

				// Check if the item has already been removed.
				if ( isset( $removed_contents[ $item_key ] ) ) {
					$product = wc_get_product( $removed_contents[ $item_key ]['product_id'] );

					/* translators: %s: Item name. */
					$item_already_removed_title = apply_filters( 'scv2_cart_item_already_removed_title', $product ? sprintf( _x( '"%s"', 'Item name in quotes', 'cart-rest-api-for-woocommerce' ), $product->get_name() ) : __( 'Item', 'cart-rest-api-for-woocommerce' ) );

					/* translators: %s: Item name. */
					$message = sprintf( __( '%s has already been removed from cart.', 'cart-rest-api-for-woocommerce' ), $item_already_removed_title );
				} else {
					/* translators: %s: Item name. */
					$message = sprintf( __( '%s does not exist in cart.', 'cart-rest-api-for-woocommerce' ), $item_removed_title );
				}

				/**
				 * Filters message about item removed from cart.
				 */
				$message = apply_filters( 'scv2_item_removed_message', $message );

				throw new SCV2_Data_Exception( 'scv2_item_not_in_cart', $message, 404 );
			}

			if ( $this->get_cart_instance()->remove_cart_item( $item_key ) ) {
				do_action( 'scv2_item_removed', $current_data );

				/**
				 * Calculates the cart totals now an item has been removed.
				 */
				$this->get_cart_instance()->calculate_totals();

				$response = $this->get_cart_contents( $request );

				/* translators: %s: Item name. */
				$message = sprintf( __( '%s has been removed from cart.', 'cart-rest-api-for-woocommerce' ), $item_removed_title );

				// Add notice.
				wc_add_notice( $message );

				// Was it requested to return status once item removed?
				if ( $request['return_status'] ) {
					/* translators: %s: Item name. */
					$response = $message;
				}

				return SCV2_Response::get_response( $response, $this->namespace, $this->rest_base );
			} else {
				$message = __( 'Unable to remove item from cart.', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about can not remove item.
				 */
				$message = apply_filters( 'scv2_can_not_remove_item_message', $message );

				throw new SCV2_Data_Exception( 'scv2_can_not_remove_item', $message, 403 );
			}
		} catch ( SCV2_Data_Exception $e ) {
			return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END remove_item()

	/**
	 * Get the query params for item.
	 */
	public function get_collection_params() {
		$controller = new SCV2_Cart_V2_Controller();

		$params = array_merge(
			$controller->get_collection_params(),
			array(
				'item_key'      => array(
					'description'       => __( 'Unique identifier for the item in the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg',
				),
				'return_status' => array(
					'description'       => __( 'Returns a message after removing item from cart.', 'cart-rest-api-for-woocommerce' ),
					'default'           => false,
					'type'              => 'boolean',
					'validate_callback' => 'rest_validate_request_arg',
				),
			)
		);

		return $params;
	} // END get_collection_params()

} // END class
