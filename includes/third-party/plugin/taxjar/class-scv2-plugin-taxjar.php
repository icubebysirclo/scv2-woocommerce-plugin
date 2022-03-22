<?php
/**
 * Handles support for TaxJar plugin.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_Plugin_TaxJar' ) ) {

	/**
	 * TaxJar.
	 */
	class SCV2_Plugin_TaxJar {

		/**
		 * Constructor.
		 */
		public function __construct() {
			if ( class_exists( 'WC_Taxjar' ) && version_compare( WC_Taxjar::$version, '3.2.5', '=>' ) ) {
				add_filter( 'taxjar_should_calculate_cart_tax', array( $this, 'maybe_calculate_tax' ) );
			}
		}

		/**
		 * Returns true to allow TaxJar to calculate totals
		 * when SCV2 API is requested.
		 */
		public function maybe_calculate_tax( $should_calculate ) {
			if ( SCV2_Authentication::is_rest_api_request() ) {
				$should_calculate = true;
			}

			return $should_calculate;
		}

	} // END class.

} // END if class exists.

return new SCV2_Plugin_TaxJar();
