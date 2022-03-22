<?php
/**
 * SCV2 - Item controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Item controller class.
 */
class SCV2_Item_Controller extends SCV2_API_Controller {

	/**
	 * Route base.
	 */
	protected $rest_base = 'item';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Update, Remove or Restore Item - scv2/v1/item (GET, POST, DELETE)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'args' => $this->get_collection_params(),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'restore_item' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'quantity' => array(
							'default'           => 1,
							'type'              => 'float',
							'validate_callback' => function( $value, $request, $param ) {
								return is_numeric( $value );
							},
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'remove_item' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	} // register_routes()

	/**
	 * Remove Item in Cart.
	 */
	public function remove_item( $data = array() ) {
		$cart_item_key = ! isset( $data['cart_item_key'] ) ? '0' : sanitize_text_field( wp_unslash( wc_clean( $data['cart_item_key'] ) ) );

		// Checks to see if the cart is empty before attempting to remove item.
		if ( WC()->cart->is_empty() ) {
			$message = __( 'No items in cart.', 'cart-rest-api-for-woocommerce' );

			SCV2_Logger::log( $message, 'error' );

			/**
			 * Filters message about no items in cart.
			 */
			$message = apply_filters( 'scv2_no_items_message', $message );

			return new WP_Error( 'scv2_no_items', $message, array( 'status' => 404 ) );
		}

		if ( $cart_item_key != '0' ) {
			// Check item exists in cart before fetching the cart item data to update.
			$current_data = $this->get_cart_item( $cart_item_key, 'remove' );

			// If item does not exist in cart return response.
			if ( empty( $current_data ) ) {
				$message = __( 'Item specified does not exist in cart.', 'cart-rest-api-for-woocommerce' );

				SCV2_Logger::log( $message, 'error' );

				/**
				 * Filters message about item not in cart.
				 */
				$message = apply_filters( 'scv2_item_not_in_cart_message', $message, 'remove' );

				return new WP_Error( 'scv2_item_not_in_cart', $message, array( 'status' => 404 ) );
			}

			if ( WC()->cart->remove_cart_item( $cart_item_key ) ) {
				do_action( 'scv2_item_removed', $current_data );

				/**
				 * Calculates the cart totals now an item has been removed.
				 */
				WC()->cart->calculate_totals();

				// Was it requested to return the whole cart once item removed?
				if ( $data['return_cart'] ) {
					$cart_contents = $this->get_cart_contents( $data );

					return new WP_REST_Response( $cart_contents, 200 );
				}

				return $this->get_response( __( 'Item has been removed from cart.', 'cart-rest-api-for-woocommerce' ), $this->rest_base );
			} else {
				$message = __( 'Unable to remove item from cart.', 'cart-rest-api-for-woocommerce' );

				SCV2_Logger::log( $message, 'error' );

				/**
				 * Filters message about can not remove item.
				 */
				$message = apply_filters( 'scv2_can_not_remove_item_message', $message );

				return new WP_Error( 'scv2_can_not_remove_item', $message, array( 'status' => 403 ) );
			}
		} else {
			$message = __( 'Cart item key is required!', 'cart-rest-api-for-woocommerce' );

			SCV2_Logger::log( $message, 'error' );

			/**
			 * Filters message about cart item key required.
			 */
			$message = apply_filters( 'scv2_cart_item_key_required_message', $message, 'remove' );

			return new WP_Error( 'scv2_cart_item_key_required', $message, array( 'status' => 404 ) );
		}
	} // END remove_item()

	/**
	 * Restore Item in Cart.
	 */
	public function restore_item( $data = array() ) {
		$cart_item_key = ! isset( $data['cart_item_key'] ) ? '0' : sanitize_text_field( wp_unslash( wc_clean( $data['cart_item_key'] ) ) );

		if ( $cart_item_key != '0' ) {
			if ( WC()->cart->restore_cart_item( $cart_item_key ) ) {
				$current_data = $this->get_cart_item( $cart_item_key, 'restore' ); // Fetches the cart item data once it is restored.

				do_action( 'scv2_item_restored', $current_data );

				/**
				 * Calculates the cart totals now an item has been restored.
				 */
				WC()->cart->calculate_totals();

				// Was it requested to return the whole cart once item restored?
				if ( $data['return_cart'] ) {
					$cart_contents = $this->get_cart_contents( $data );

					return new WP_REST_Response( $cart_contents, 200 );
				}

				return $this->get_response( __( 'Item has been restored to the cart.', 'cart-rest-api-for-woocommerce' ), $this->rest_base );
			} else {
				$message = __( 'Unable to restore item to the cart.', 'cart-rest-api-for-woocommerce' );

				SCV2_Logger::log( $message, 'error' );

				/**
				 * Filters message about can not restore item.
				 */
				$message = apply_filters( 'scv2_can_not_restore_item_message', $message );

				return new WP_Error( 'scv2_can_not_restore_item', $message, array( 'status' => 403 ) );
			}
		} else {
			$message = __( 'Cart item key is required!', 'cart-rest-api-for-woocommerce' );

			SCV2_Logger::log( $message, 'error' );

			/**
			 * Filters message about cart item key required.
			 */
			$message = apply_filters( 'scv2_cart_item_key_required_message', $message, 'restore' );

			return new WP_Error( 'scv2_cart_item_key_required', $message, array( 'status' => 404 ) );
		}
	} // END restore_item()

	/**
	 * Update Item in Cart.
	 */
	public function update_item( $data = array() ) {
		$cart_item_key = ! isset( $data['cart_item_key'] ) ? '0' : sanitize_text_field( wp_unslash( wc_clean( $data['cart_item_key'] ) ) );
		$quantity      = ! isset( $data['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $data['quantity'] ) );

		// Allows removing of items if quantity is zero should for example the item was with a product bundle.
		if ( $quantity === 0 ) {
			return $this->remove_item( $data );
		}

		$this->validate_quantity( $quantity );

		if ( $cart_item_key != '0' ) {
			// Check item exists in cart before fetching the cart item data to update.
			$current_data = $this->get_cart_item( $cart_item_key, 'container' );

			// If item does not exist in cart return response.
			if ( empty( $current_data ) ) {
				$message = __( 'Item specified does not exist in cart.', 'cart-rest-api-for-woocommerce' );

				SCV2_Logger::log( $message, 'error' );

				/**
				 * Filters message about cart item key required.
				 */
				$message = apply_filters( 'scv2_item_not_in_cart_message', $message, 'update' );

				return new WP_Error( 'scv2_item_not_in_cart', $message, array( 'status' => 404 ) );
			}

			$stock = $this->has_enough_stock( $current_data, $quantity ); // Checks if the item has enough stock before updating.

			/**
			 * Return error if stock is not enough.
			 */
			if ( is_wp_error( $stock ) ) {
				return $stock;
			}

			/**
			 * Update cart validation.
			 */
			$passed_validation = apply_filters( 'scv2_update_cart_validation', true, $cart_item_key, $current_data, $quantity );

			/**
			 * If validation returned an error return error response.
			 */
			if ( is_wp_error( $passed_validation ) ) {
				return $passed_validation;
			}

			// Return error if product is_sold_individually.
			if ( $current_data['data']->is_sold_individually() && $quantity > 1 ) {
				/* translators: %s Product name. */
				$message = sprintf( __( 'You can only have 1 %s in your cart.', 'cart-rest-api-for-woocommerce' ), $current_data['data']->get_name() );

				SCV2_Logger::log( $message, 'error' );

				/**
				 * Filters message about product not being allowed to increase quantity.
				 */
				$message = apply_filters( 'scv2_can_not_increase_quantity_message', $message, $current_data['data'] );

				return new WP_Error( 'scv2_can_not_increase_quantity', $message, array( 'status' => 403 ) );
			}

			// Only update cart item quantity if passed validation.
			if ( $passed_validation ) {
				if ( WC()->cart->set_quantity( $cart_item_key, $quantity ) ) {
					$new_data = $this->get_cart_item( $cart_item_key, 'update' );

					$product_id   = ! isset( $new_data['product_id'] ) ? 0 : absint( wp_unslash( $new_data['product_id'] ) );
					$variation_id = ! isset( $new_data['variation_id'] ) ? 0 : absint( wp_unslash( $new_data['variation_id'] ) );

					$product_data = wc_get_product( $variation_id ? $variation_id : $product_id );

					if ( $quantity != $current_data['quantity'] ) {
						do_action( 'scv2_item_quantity_changed', $cart_item_key, $new_data );

						/**
						 * Calculates the cart totals if an item has changed it's quantity.
						 */
						WC()->cart->calculate_totals();
					}
				} else {
					$message = __( 'Unable to update item quantity in cart.', 'cart-rest-api-for-woocommerce' );

					SCV2_Logger::log( $message, 'error' );

					/**
					 * Filters message about can not update item.
					 */
					$message = apply_filters( 'scv2_can_not_update_item_message', $message );

					return new WP_Error( 'scv2_can_not_update_item', $message, array( 'status' => 403 ) );
				}

				// Was it requested to return the whole cart once item updated?
				if ( $data['return_cart'] ) {
					$cart_contents = $this->get_cart_contents( $data );

					return new WP_REST_Response( $cart_contents, 200 );
				}

				$response = array();

				// Return response based on product quantity increment.
				if ( $quantity > $current_data['quantity'] ) {
					/* translators: 1: product name, 2: new quantity */
					$response = array(
						'message'  => sprintf( __( 'The quantity for "%1$s" has increased to "%2$s".', 'cart-rest-api-for-woocommerce' ), $product_data->get_name(), $new_data['quantity'] ),
						'quantity' => $new_data['quantity'],
					);
				} elseif ( $quantity < $current_data['quantity'] ) {
					/* translators: 1: product name, 2: new quantity */
					$response = array(
						'message'  => sprintf( __( 'The quantity for "%1$s" has decreased to "%2$s".', 'cart-rest-api-for-woocommerce' ), $product_data->get_name(), $new_data['quantity'] ),
						'quantity' => $new_data['quantity'],
					);
				} else {
					/* translators: %s: product name */
					$response = array(
						'message'  => sprintf( __( 'The quantity for "%s" has not changed.', 'cart-rest-api-for-woocommerce' ), $product_data->get_name() ),
						'quantity' => $quantity,
					);
				}

				$response = apply_filters( 'scv2_update_item', $response, $new_data, $quantity, $product_data );

				return $this->get_response( $response, $this->rest_base );
			}
		} else {
			$message = __( 'Cart item key is required!', 'cart-rest-api-for-woocommerce' );

			SCV2_Logger::log( $message, 'error' );

			/**
			 * Filters message about cart item key required.
			 */
			$message = apply_filters( 'scv2_cart_item_key_required_message', $message, 'update' );

			return new WP_Error( 'scv2_cart_item_key_required', $message, array( 'status' => 404 ) );
		}
	} // END update_item()

	/**
	 * Get the query params for item.
	 */
	public function get_collection_params() {
		$params = array(
			'cart_item_key' => array(
				'description'       => __( 'Unique identifier for the item in the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'return_cart'   => array(
				'description'       => __( 'Returns the whole cart to reduce API requests.', 'cart-rest-api-for-woocommerce' ),
				'default'           => false,
				'type'              => 'boolean',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	} // END get_collection_params()

} // END class
