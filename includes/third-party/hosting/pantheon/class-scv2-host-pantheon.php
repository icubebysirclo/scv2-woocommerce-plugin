<?php
/**
 * Handles support for Pantheon host.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_Host_Pantheon' ) ) {

	/**
	 * Host: Pantheon.
	 */
	class SCV2_Host_Pantheon {

		/**
		 * Constructor.
		 */
		public function __construct() {
			if ( isset( $_SERVER['PANTHEON_ENVIRONMENT'] ) ) {
				add_filter( 'scv2_cookie', array( $this, 'pantheon_scv2_cookie_name' ) );
			}
		}

		/**
		 * Returns a new cookie name so SCV2 does not get
		 * cached for guest customers on the frontend.
		 */
		public function pantheon_scv2_cookie_name() {
			return 'wp-scv2pantheon';
		}

	} // END class.

} // END if class exists.

return new SCV2_Host_Pantheon();
