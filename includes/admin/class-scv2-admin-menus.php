<?php
/**
 * SCV2 - Admin Menus.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_Admin_Menus' ) ) {

	class SCV2_Admin_Menus {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		} // END __construct()

		/**
		 * Add SCV2 to the menu and register WooCommerce admin bar.
		 */
		public function admin_menu() {
			$section = ! isset( $_GET['section'] ) ? 'getting-started' : trim( sanitize_key( wp_unslash( $_GET['section'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			switch ( $section ) {
				case 'getting-started':
					/* translators: %s: SCV2 */
					$title      = sprintf( esc_attr__( 'Getting Started with %s', 'cart-rest-api-for-woocommerce' ), 'SCV2' );
					$breadcrumb = esc_attr__( 'Getting Started', 'cart-rest-api-for-woocommerce' );
					break;
				default:
					$title      = apply_filters( 'scv2_page_title_' . strtolower( str_replace( '-', '_', $section ) ), 'SCV2' );
					$breadcrumb = apply_filters( 'scv2_page_wc_bar_breadcrumb_' . strtolower( str_replace( '-', '_', $section ) ), '' );
					break;
			}

			$page = admin_url( 'admin.php' );

			// Add SCV2 page.
			add_menu_page(
				$title,
				'SCV2',
				apply_filters( 'scv2_screen_capability', 'manage_options' ),
				'scv2',
				array( $this, 'scv2_page' ),
				'dashicons-cart'
			);

			// Register WooCommerce Admin Bar.
			if ( SCV2_Helpers::is_wc_version_gte( '4.0' ) && function_exists( 'wc_admin_connect_page' ) ) {
				wc_admin_connect_page(
					array(
						'id'        => 'scv2-getting-started',
						'screen_id' => 'toplevel_page_scv2',
						'title'     => array(
							esc_html__( 'SCV2', 'cart-rest-api-for-woocommerce' ),
							$breadcrumb,
						),
						'path'      => add_query_arg(
							array(
								'page'    => 'scv2',
								'section' => $section,
							),
							$page
						),
					)
				);
			}

			/**
			 * Moves SCV2 menu to the new WooCommerce Navigation Menu if it exists.
			 */
			if ( class_exists( '\Automattic\WooCommerce\Admin\Features\Navigation\Menu' ) && apply_filters( 'scv2_wc_navigation', true ) ) {
				// Add Category.
				Automattic\WooCommerce\Admin\Features\Navigation\Menu::add_plugin_category(
					array(
						'id'     => 'scv2-category',
						'title'  => 'SCV2',
						'parent' => 'woocommerce',
					)
				);

				// Add Page.
				Automattic\WooCommerce\Admin\Features\Navigation\Menu::add_plugin_item(
					array(
						'id'         => 'scv2',
						'title'      => esc_attr__( 'Getting Started', 'cart-rest-api-for-woocommerce' ),
						'capability' => apply_filters( 'scv2_screen_capability', 'manage_options' ),
						'url'        => 'scv2',
						'parent'     => 'scv2-category',
					)
				);
			}
		} // END admin_menu()

		/**
		 * SCV2 Page
		 */
		public static function scv2_page() {
			$section = ! isset( $_GET['section'] ) ? 'getting-started' : trim( sanitize_key( wp_unslash( $_GET['section'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			switch ( $section ) {
				case 'getting-started':
					self::getting_started_content();
					break;

				default:
					do_action( 'scv2_page_section_' . strtolower( str_replace( '-', '_', $section ) ) );
					break;
			}
		} // END scv2_page()

		/**
		 * Getting Started content.
		 */
		public static function getting_started_content() {
			include_once dirname( __FILE__ ) . '/views/html-getting-started.php';
		} // END getting_started_content()

	} // END class

} // END if class exists

return new SCV2_Admin_Menus();
