<?php
/**
 * Handles support for JWT Auth plugin.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_Plugin_JWT_Auth' ) ) {

	/**
	 * JWT Authentication.
	 */
	class SCV2_Plugin_JWT_Auth {

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_filter( 'jwt_auth_whitelist', function( $endpoints ) {
				return array_merge( $endpoints, array(
					'/wp-json/scv2/v1/*',
					'/wp-json/scv2/v2/*',
				) );
			} );
		}

	} // END class.

} // END if class exists.

return new SCV2_Plugin_JWT_Auth();
