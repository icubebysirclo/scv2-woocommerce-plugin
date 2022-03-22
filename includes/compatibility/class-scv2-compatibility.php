<?php
/**
 * Extension Compatibility
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_Compatibility' ) ) {

	class SCV2_Compatibility {

		/**
		 * Constructor.
		 */
		public function __construct() {
			self::include_compatibility();
		}

		/**
		 * Load support for extension compatibility.
		 */
		public function include_compatibility() {
			include_once SCV2_ABSPATH . 'includes/compatibility/modules/class-scv2-advanced-shipping-packages.php'; // Advanced Shipping Packages.
			include_once SCV2_ABSPATH . 'includes/compatibility/modules/class-scv2-free-gift-coupons.php'; // Free Gift Coupons.
		}

	} // END class.

} // END if class exists.

return new SCV2_Compatibility();
