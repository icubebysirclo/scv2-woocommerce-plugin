<?php
/**
 * Manages SCV2 dashboard assets.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_Admin_Assets' ) ) {

	class SCV2_Admin_Assets {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Registers and enqueue Stylesheets.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );

			// Adds admin body classes.
			add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
		} // END __construct()

		/**
		 * Registers and enqueue Stylesheets.
		 */
		public function admin_styles() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';
			$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			if ( in_array( $screen_id, SCV2_Helpers::scv2_get_admin_screens() ) ) {
				wp_register_style( SCV2_SLUG . '_admin', SCV2_URL_PATH . '/assets/css/admin/scv2' . $suffix . '.css', array(), SCV2_VERSION );
				wp_enqueue_style( SCV2_SLUG . '_admin' );
				wp_style_add_data( SCV2_SLUG . '_admin', 'rtl', 'replace' );
			}
			if ( $suffix ) {
				wp_style_add_data( SCV2_SLUG . '_admin', 'suffix', '.min' );
			}
		} // END admin_styles()

		/**
		 * Adds admin body class for SCV2 page.
		 */
		public function admin_body_class( $classes ) {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			// Add body class for SCV2 page.
			if ( 'toplevel_page_scv2' === $screen_id || 'toplevel_page_scv2-network' === $screen_id ) {
				$classes = ' scv2 ';
			}

			// Add special body class for plugin install page.
			if ( 'plugin-install' === $screen_id || 'plugin-install-network' === $screen_id ) {
				if ( isset( $_GET['tab'] ) && 'scv2' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$classes = ' scv2-plugin-install ';
				}
			}

			return $classes;
		} // END admin_body_class()

	} // END class

} // END if class exists

return new SCV2_Admin_Assets();
