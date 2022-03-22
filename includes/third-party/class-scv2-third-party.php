<?php
/**
 * Handles support for Third Party.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_Third_Party' ) ) {

	/**
	 * Third Party Support.
	 */
	class SCV2_Third_Party {

		/**
		 * Constructor.
		 */
		public function __construct() {
			self::include_hosts();
			self::include_plugins();
		}

		/**
		 * Load support for third-party hosts.
		 */
		public function include_hosts() {
			include_once SCV2_ABSPATH . 'includes/third-party/hosting/pantheon/class-scv2-host-pantheon.php'; // Pantheon.io.
		}

		/**
		 * Load support for third-party plugins.
		 */
		public function include_plugins() {
			include_once SCV2_ABSPATH . 'includes/third-party/plugin/jwt-auth-by-useful-team/class-scv2-plugin-jwt-auth.php'; // JWT Auth.
			include_once SCV2_ABSPATH . 'includes/third-party/plugin/taxjar/class-scv2-plugin-taxjar.php'; // TaxJar.
		}

	} // END class.

} // END if class exists.

return new SCV2_Third_Party();
