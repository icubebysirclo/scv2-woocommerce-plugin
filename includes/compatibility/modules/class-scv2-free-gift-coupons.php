<?php
/**
 * Handles support for Free Gift Coupons extension.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Free_Gift_Coupons' ) ) {
	return;
}

if ( ! class_exists( 'SCV2_FGC_Compatibility' ) ) {

	/**
	 * Free Gift Coupons Support.
	 */
	class SCV2_FGC_Compatibility {

		/**
		 * Constructor.
		 */
		public function __construct() {
			// Validate quantity on update cart in case sneaky folks mess with the markup.
			add_filter( 'scv2_update_cart_validation', array( $this, 'update_cart_validation' ), 10, 4 );

			// Display as Free! in cart and in orders.
			add_filter( 'scv2_cart_item_price', array( 'WC_Free_Gift_Coupons', 'cart_item_price' ), 10, 2 );
			add_filter( 'scv2_cart_item_subtotal', array( 'WC_Free_Gift_Coupons', 'cart_item_price' ), 10, 2 );
		}

		/**
		 * Update cart validation.
		 */
		public static function update_cart_validation( $passed_validation, $cart_item_key, $values, $quantity ) {
			try {
				if ( ! empty( $values['free_gift'] ) ) {
					// Has an initial FGC quantity.
					if ( ! empty( $values['fgc_quantity'] ) && $quantity !== $values['fgc_quantity'] ) {
						/* translators: %s Product title. */
						$error_message = sprintf( __( 'You are not allowed to modify the quantity of your %s gift.', 'cart-rest-api-for-woocommerce' ), $values['data']->get_name() );

						throw new SCV2_Data_Exception( 'scv2_fgc_update_quantity', $error_message, 404 );
					}
				}

				return $passed_validation;
			} catch ( SCV2_Data_Exception $e ) {
				return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
			}
		} // END update_cart_validation()

	} // END class.

} // END if class exists.

return new SCV2_FGC_Compatibility();
