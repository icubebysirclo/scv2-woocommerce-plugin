<?php
/**
 * Handles product validation.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_Product_Validation' ) ) {

	class SCV2_Product_Validation {

		/**
		 * Constructor.
		 */
		public function __construct() {
			// Prevent certain product types from being added to the cart.
			add_filter( 'scv2_add_to_cart_handler_external', array( $this, 'product_not_allowed_to_add' ), 0, 1 );
			add_filter( 'scv2_add_to_cart_handler_grouped', array( $this, 'product_not_allowed_to_add' ), 0, 2 );

			// Prevent password products being added to the cart.
			add_filter( 'scv2_add_to_cart_validation', array( $this, 'protected_product_add_to_cart' ), 10, 2 );

			// Prevents variations that are not purchasable from being added to the cart. @since 2.7.2.
			add_filter( 'scv2_add_to_cart_validation', array( $this, 'variation_not_purchasable' ), 10, 5 );

			// Correct product name for missing variation attributes.
			add_filter( 'scv2_product_name', array( $this, 'validate_variation_product_name' ), 0, 3 );
			add_filter( 'scv2_item_added_product_name', array( $this, 'validate_variation_product_name' ), 0, 3 );
		}

		/**
		 * Error response for product types that are not allowed to be added to the cart.
		 */
		public function product_not_allowed_to_add( $product_data, $request = array() ) {
			try {
				$route = '';

				if ( ! empty( $request ) ) {
					$route = $request->get_route();
				}

				if ( ! empty( $route ) && ( false === strpos( $route, 'scv2/v2/add-item' ) ) && $product_data->get_type() === 'grouped' ) {
					/* translators: %1$s: product type, %2$s: api route */
					$message = sprintf( __( 'You cannot use this route to add "%1$s" products to the cart. Please use %2$s instead.', 'cart-rest-api-for-woocommerce' ), $product_data->get_type(), str_replace( 'add-item', 'add-items', $route ) );
				} else {
					/* translators: %1$s: product name, %2$s: product type */
					$message = sprintf( __( 'You cannot add "%1$s" to your cart as it is an "%2$s" product.', 'cart-rest-api-for-woocommerce' ), $product_data->get_name(), $product_data->get_type() );
				}

				/**
				 * Filters message about product type that cannot be added to the cart.
				 */
				$message = apply_filters( 'scv2_cannot_add_product_type_to_cart_message', $message, $product_data );

				throw new SCV2_Data_Exception( 'scv2_cannot_add_product_type_to_cart', $message, 403 );
			} catch ( SCV2_Data_Exception $e ) {
				return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
			}
		} // END product_not_allowed_to_add()

		/**
		 * Validates the product name for a variable product.
		 *
		 * If variation details are missing then return the product title instead.
		 */
		public function validate_variation_product_name( $product_name, $_product, $cart_item ) {
			if ( $_product->is_type( 'variation' ) ) {
				$product            = wc_get_product( $_product->get_parent_id() );
				$default_attributes = $product->get_default_attributes();

				if ( empty( $cart_item['variation'] ) && empty( $default_attributes ) ) {
					return $_product->get_title();
				}
			}

			return $product_name;
		} // END validate_variation_product_name()

		/**
		 * Prevent password protected products being added to the cart.
		 */
		public function protected_product_add_to_cart( $passed, $product_id ) {
			if ( post_password_required( $product_id ) ) {
				$passed = false;

				$product = wc_get_product( $product_id );

				/* translators: %s: product name */
				SCV2_Logger::log( sprintf( __( 'Product "%s" is protected and cannot be purchased.', 'cart-rest-api-for-woocommerce' ), $product->get_name() ), 'error' );
			}
			return $passed;
		} // END protected_product_add_to_cart()

		/**
		 * Prevents variations that are not purchasable from being added to the cart.
		 */
		public function variation_not_purchasable( $passed, $product_id, $quantity, $variation_id, $variation ) {
			$product = wc_get_product( $product_id );

			if ( ! empty( $variation ) ) {
				$data_store   = \WC_Data_Store::load( 'product' );
				$variation_id = $data_store->find_matching_product_variation( $product, $variation );
				$product      = wc_get_product( $variation_id );

				if ( $variation_id > 0 && ! $product->is_purchasable() ) {
					$passed = false;

					/* translators: %s: product name */
					SCV2_Logger::log( sprintf( __( 'Variation for "%s" is not purchasable.', 'cart-rest-api-for-woocommerce' ), $product->get_name() ), 'error' );
				}
			}

			return $passed;
		} // END variation_not_purchasable()

	} // END class.

} // END if class exists.

return new SCV2_Product_Validation();
