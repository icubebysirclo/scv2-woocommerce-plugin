<?php
/**
 * This file is designed to be used to load as package NOT a WP plugin!
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
