<?php
/**
 * SCV2 - Admin.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_Admin' ) ) {

	class SCV2_Admin {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'includes' ) );
			add_action( 'current_screen', array( $this, 'conditional_includes' ) );
		} // END __construct()

		/**
		 * Include any classes we need within admin.
		 */
		public function includes() {
			include_once SCV2_ABSPATH . 'includes/admin/class-scv2-admin-assets.php';           // Admin Assets.
			include_once SCV2_ABSPATH . 'includes/admin/class-scv2-admin-menus.php';            // Admin Menus.
			// include_once SCV2_ABSPATH . 'includes/admin/class-scv2-admin-notices.php';          // Plugin Notices.
			// include_once SCV2_ABSPATH . 'includes/admin/class-scv2-admin-plugin-search.php';    // Plugin Search.
			// include_once SCV2_ABSPATH . 'includes/admin/class-scv2-wc-admin-notices.php';       // WooCommerce Admin Notices.
			include_once SCV2_ABSPATH . 'includes/admin/class-scv2-wc-admin-system-status.php'; // WooCommerce System Status.
		} // END includes()

		/**
		 * Include admin files conditionally.
		 */
		public function conditional_includes() {
			$screen = get_current_screen();

			if ( ! $screen ) {
				return;
			}

			switch ( $screen->id ) {

				case 'plugins':
					// include_once SCV2_ABSPATH . 'includes/admin/class-scv2-admin-action-links.php';         // Plugin Action Links.
					// include_once SCV2_ABSPATH . 'includes/admin/class-scv2-admin-plugin-screen-update.php'; // Plugin Update.
					include_once SCV2_ABSPATH . 'includes/admin/class-scv2-admin-update-plugin.php'; // Custom Update Plugins.
					break;
			}
		} // END conditional_includes()

	} // END class

} // END if class exists

return new SCV2_Admin();
