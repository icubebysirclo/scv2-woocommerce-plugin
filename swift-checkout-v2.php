<?php
/**
 * Plugin Name: Swift Checkout V2
 * Description: A <strong>quick checkout</strong> made for <strong>WooCommerce</strong>.
 * Author:      ICUBE By Sirclo
 * Author URI:  http://getswift.asia
 * Version:     2.0.0
 * Domain Path: /languages/
 * Requires at least: 5.4
 * Requires PHP: 7.3
 * WC requires at least: 4.3
 * WC tested up to: 5.9
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'SCV2_FILE' ) ) {
	define( 'SCV2_FILE', __FILE__ );
}

// Include the main SCV2 class.
if ( ! class_exists( 'SCV2', false ) ) {
	include_once untrailingslashit( plugin_dir_path( SCV2_FILE ) ) . '/includes/class-scv2.php';
}

/**
 * Returns the main instance of SCV2 and only runs if it does not already exists.
 */
if ( ! function_exists( 'SCV2' ) ) {
	/**
	 * Initialize SCV2.
	 */
	function SCV2() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		return SCV2::init();
	}

	SCV2();
}
