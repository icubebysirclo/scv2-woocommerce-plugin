<?php
/**
 * Display notices in the WordPress admin for SCV2.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_Admin_Notices' ) ) {

	class SCV2_Admin_Notices {

		/**
		 * Activation date.
		 */
		public static $install_date;

		/**
		 * Stores notices.
		 */
		public static $notices = array();

		/**
		 * Array of notices - name => callback.
		 */
		private static $core_notices = array(
			'update_db'           => 'update_db_notice',
			'check_php'           => 'check_php_notice',
			'check_wp'            => 'check_wp_notice',
			'check_wc'            => 'check_woocommerce_notice',
			'plugin_review'       => 'plugin_review_notice',
			'check_beta'          => 'check_beta_notice',
			'upgrade_warning'     => 'upgrade_warning_notice',
			'base_tables_missing' => 'base_tables_missing_notice',
		);

		/**
		 * Constructor
		 */
		public function __construct() {
			self::$install_date = get_option( 'scv2_install_date', time() );
			self::$notices      = get_option( 'scv2_admin_notices', array() );

			add_action( 'switch_theme', array( $this, 'reset_admin_notices' ) );
			add_action( 'scv2_installed', array( $this, 'reset_admin_notices' ) );
			add_action( 'wp_loaded', array( $this, 'hide_notices' ) );
			add_action( 'init', array( $this, 'timed_notices' ) );

			if ( ! SCV2_Install::is_new_install() ) {
				add_action( 'shutdown', array( $this, 'store_notices' ) );
			}

			// If the current user has capabilities then add notices.
			if ( SCV2_Helpers::user_has_capabilities() ) {
				add_action( 'admin_print_styles', array( $this, 'add_notices' ) );
			}
		} // END __construct()

		/**
		 * Store notices to DB.
		 */
		public static function store_notices() {
			update_option( 'scv2_admin_notices', self::get_notices() );
		} // END store_notices()

		/**
		 * Get notices
		 */
		public static function get_notices() {
			return self::$notices;
		} // END get_notices()

		/**
		 * Remove all notices.
		 */
		public static function remove_all_notices() {
			self::$notices = array();
		} // END remove_all_notices()

		/**
		 * Reset notices for when new version of SCV2 is installed.
		 */
		public function reset_admin_notices() {
			self::add_notice( 'upgrade_warning' );
			self::add_notice( 'check_php' );
			self::add_notice( 'check_wp' );
			self::add_notice( 'check_wc' );
			self::add_notice( 'check_beta' );
		} // END reset_admin_notices()

		/**
		 * Show a notice.
		 */
		public static function add_notice( $name, $force_save = false ) {
			self::$notices = array_unique( array_merge( self::get_notices(), array( $name ) ) );

			if ( $force_save ) {
				// Adding early save to prevent more race conditions with notices.
				self::store_notices();
			}
		} // END add_notice()

		/**
		 * Remove a notice from being displayed.
		 */
		public static function remove_notice( $name, $force_save = false ) {
			$notices = self::get_notices();

			// Check that the notice exists before attempting to remove it.
			if ( in_array( $name, $notices ) ) {
				self::$notices = array_diff( $notices, array( $name ) );

				delete_option( 'scv2_admin_notice_' . $name );

				if ( $force_save ) {
					// Adding early save to prevent more conditions with notices.
					self::store_notices();
				}
			}
		} // END remove_notice()

		/**
		 * See if a notice is being shown.
		 */
		public function has_notice( $name ) {
			return in_array( $name, self::get_notices(), true );
		} // END has_notice()

		/**
		 * Hide a notice if the GET variable is set.
		 */
		public function hide_notices() {
			if ( isset( $_GET['scv2-hide-notice'] ) && isset( $_GET['_scv2_notice_nonce'] ) ) {
				if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_scv2_notice_nonce'] ) ), 'scv2_hide_notices_nonce' ) ) {
					wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'cart-rest-api-for-woocommerce' ) );
				}

				if ( ! SCV2_Helpers::user_has_capabilities() ) {
					wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'cart-rest-api-for-woocommerce' ) );
				}

				$hide_notice = sanitize_text_field( wp_unslash( $_GET['scv2-hide-notice'] ) );

				self::remove_notice( $hide_notice );

				update_user_meta( get_current_user_id(), 'dismissed_scv2_' . $hide_notice . '_notice', true );

				do_action( 'scv2_hide_' . $hide_notice . '_notice' );

				wp_safe_redirect( remove_query_arg( array( 'scv2-hide-notice', '_scv2_notice_nonce' ), SCV2_Helpers::scv2_get_current_admin_url() ) );
				exit;
			}
		} // END hide_notices()

		/**
		 * Add notices.
		 */
		public function add_notices() {
			// Prevent notices from loading on the frontend.
			if ( ! is_admin() ) {
				return;
			}

			$notices = self::get_notices();

			if ( empty( $notices ) ) {
				return;
			}

			// Notice should only show on specific pages.
			if ( ! SCV2_Helpers::is_scv2_admin_page() ) {
				return;
			}

			foreach ( $notices as $notice ) {
				if ( ! empty( self::$core_notices[ $notice ] ) && apply_filters( 'scv2_show_admin_notice', true, $notice ) ) {
					add_action( 'admin_notices', array( $this, self::$core_notices[ $notice ] ) );
				} else {
					add_action( 'admin_notices', array( $this, 'output_custom_notices' ) );
				}
			}
		} // END add_notices()

		/**
		 * Add a custom notice.
		 */
		public function add_custom_notice( $name, $notice_html ) {
			self::add_notice( $name );
			update_option( 'scv2_admin_notice_' . $name, wp_kses_post( $notice_html ) );
		} // END add_custom_notice()

		/**
		 * Output any stored custom notices.
		 */
		public function output_custom_notices() {
			$notices = self::get_notices();

			if ( ! empty( $notices ) ) {
				foreach ( $notices as $notice ) {
					if ( empty( self::$core_notices[ $notice ] ) ) {
						$notice_html = get_option( 'scv2_admin_notice_' . $notice );

						if ( $notice_html ) {
							include SCV2_ABSPATH . 'includes/admin/views/html-notice-custom.php';
						}
					}
				}
			}
		} // END output_custom_notices()

		/**
		 * Notice about base tables missing.
		 */
		public function base_tables_missing_notice() {
			$notice_dismissed = apply_filters(
				'scv2_hide_base_tables_missing_nag',
				get_user_meta( get_current_user_id(), 'dismissed_scv2_base_tables_missing_notice', true )
			);

			if ( $notice_dismissed ) {
				self::remove_notice( 'base_tables_missing' );
			}

			include_once SCV2_ABSPATH . 'includes/admin/views/html-notice-base-table-missing.php';
		} // END base_tables_missing_notice()

		/**
		 * Shows a notice asking the user for a review of SCV2.
		 */
		public function timed_notices() {
			// Was the plugin review notice dismissed?
			$hide_review_notice = get_user_meta( get_current_user_id(), 'dismissed_scv2_plugin_review_notice', true );

			// Check if we need to display the review plugin notice.
			if ( empty( $hide_review_notice ) ) {
				self::add_notice( 'plugin_review' );
			}
		} // END timed_notices()

		/**
		 * Shows an upgrade warning notice if the installed version is less
		 * than the new release coming soon.
		 */
		public function upgrade_warning_notice() {
			$version = strstr( SCV2_VERSION, '-', true );

			// If version returns empty then just set as the current plugin version.
			if ( empty( $version ) ) {
				$version = SCV2_VERSION;
			}

			if ( ! SCV2_Helpers::is_scv2_pre_release() && version_compare( $version, SCV2_NEXT_VERSION, '<' ) ) {
				include_once SCV2_ABSPATH . 'includes/admin/views/html-notice-upgrade-warning.php';
			}
		} // END upgrade_warning_notice()

		/**
		 * If we need to update the database, include a message with the DB update button.
		 */
		public static function update_db_notice() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( SCV2_Install::needs_db_update() ) {
				$next_scheduled_date = WC()->queue()->get_next( 'scv2_run_update_callback', null, 'scv2-db-updates' );

				if ( $next_scheduled_date || ! empty( $_GET['do_update_scv2'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					include SCV2_ABSPATH . 'includes/admin/views/html-notice-updating.php';
				} else {
					include SCV2_ABSPATH . 'includes/admin/views/html-notice-update.php';
				}
			} else {
				include SCV2_ABSPATH . 'includes/admin/views/html-notice-updated.php';
			}
		} // END update_db_notice()

		/**
		 * Checks the environment on loading WordPress, just in case the environment changes after activation.
		 */
		public function check_php_notice() {
			if ( ! SCV2_Helpers::is_environment_compatible() && is_plugin_active( plugin_basename( SCV2_FILE ) ) ) {
				SCV2::deactivate_plugin();

				include_once SCV2_ABSPATH . 'includes/admin/views/html-notice-requirement-php.php';
			}
		} // END check_php_notice()

		/**
		 * Checks that the WordPress version meets the plugin requirement before deciding
		 * to deactivate the plugin and show the WordPress requirement notice if it doesn't meet.
		 */
		public function check_wp_notice() {
			if ( ! SCV2_Helpers::is_wp_version_gte( SCV2::$required_wp ) ) {
				SCV2::deactivate_plugin();
				include_once SCV2_ABSPATH . 'includes/admin/views/html-notice-requirement-wp.php';
			}
		} // END check_wp_notice()

		/**
		 * Check WooCommerce Dependency.
		 */
		public function check_woocommerce_notice() {
			if ( ! defined( 'WC_VERSION' ) ) {
				// Deactivate plugin.
				SCV2::deactivate_plugin();

				// WooCommerce is Not Installed or Activated Notice.
				include_once SCV2_ABSPATH . 'includes/admin/views/html-notice-wc-not-installed.php';
			} elseif ( version_compare( WC_VERSION, SCV2::$required_woo, '<' ) ) {
				/**
				 * Displays a warning message if minimum version of WooCommerce check fails and
				 * provides an update button if the user has admin capabilities to update the plugin.
				 */
				include_once SCV2_ABSPATH . 'includes/admin/views/html-notice-required-wc.php';
			}
		} // END check_woocommerce_notice()

		/**
		 * Displays notice if user is testing pre-release version of the plugin.
		 */
		public function check_beta_notice() {
			// Is this version of SCV2 a pre-release?
			if ( SCV2_Helpers::is_scv2_pre_release() ) {
				include_once SCV2_ABSPATH . 'includes/admin/views/html-notice-trying-beta.php';
			}
		} // END check_beta_notice()

		/**
		 * Displays plugin review notice.
		 */
		public function plugin_review_notice() {
			// If it has been 2 weeks or more since activating the plugin then display the review notice.
			if ( ( intval( time() - self::$install_date ) ) > WEEK_IN_SECONDS * 2 ) {
				include_once SCV2_ABSPATH . 'includes/admin/views/html-notice-please-review.php';
			}
		} // END plugin_review_notice()

	} // END class.

} // END if class exists.

return new SCV2_Admin_Notices();
