<?php
/**
 * SCV2 API
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SCV2 API class.
 */
class SCV2_API {

	/**
	 * Setup class.
	 */
	public function __construct() {
		// Add query vars.
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		// Register API endpoint.
		add_action( 'init', array( $this, 'add_endpoint' ), 0 );

		// Handle scv2 endpoint requests.
		add_action( 'parse_request', array( $this, 'handle_api_requests' ), 0 );
	} // END __construct()

	/**
	 * Add new query vars.
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'scv2';

		return $vars;
	} // END add_query_vars()

	/**
	 * Add rewrite endpoint.
	 */
	public static function add_endpoint() {
		add_rewrite_endpoint( 'scv2', EP_ALL );
	} // END add_endpoint()

	/**
	 * API request - Trigger any API requests.
	 */
	public function handle_api_requests() {
		global $wp;

		if ( ! empty( $_GET['scv2'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$wp->query_vars['scv2'] = trim( sanitize_key( wp_unslash( $_GET['scv2'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// SCV2 endpoint requests.
		if ( ! empty( $wp->query_vars['scv2'] ) ) {

			// Buffer, we won't want any output here.
			ob_start();

			// No cache headers.
			wc_nocache_headers();

			// Clean the API request.
			$api_request = strtolower( wc_clean( $wp->query_vars['scv2'] ) );

			// Trigger generic action before request hook.
			do_action( 'scv2_api_request', $api_request );

			// Is there actually something hooked into this API request? If not trigger 400 - Bad request.
			status_header( has_action( 'scv2_api_' . $api_request ) ? 200 : 400 );

			// Trigger an action which plugins can hook into to fulfill the request.
			do_action( 'scv2_api_' . $api_request );

			// Done, clear buffer and exit.
			ob_end_clean();
			die( '-1' );
		}
	} // END handle_api_requests()

} // END class

return new SCV2_API();
