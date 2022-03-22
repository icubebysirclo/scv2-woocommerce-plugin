<?php
/**
 * Handles support for Advanced Shipping Packages extension.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_ASP_Compatibility' ) ) {

	class SCV2_ASP_Compatibility {

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_filter( 'scv2_shipping_package_name', array( $this, 'scv2_asp_shipping_package_name' ), 10, 3 );
		}

		/**
		 * Name shipping packages.
		 */
		public function scv2_asp_shipping_package_name( $name, $i, $package ) {
			if ( is_numeric( $i ) && 'shipping_package' === get_post_type( $i ) ) {
				$name = get_post_meta( $i, '_name', true );
			}

			// Default package name.
			if ( 0 === $i ) {
				$name = get_option( 'advanced_shipping_packages_default_package_name', '' );
			}

			return $name;
		}

	} // END class.

} // END if class exists.

return new SCV2_ASP_Compatibility();
