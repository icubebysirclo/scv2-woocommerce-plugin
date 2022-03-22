<?php
/**
 * SCV2 REST API Store controller.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SCV2 REST API Store v2 controller class.
 */
class SCV2_Store_V2_Controller extends SCV2_API_Controller {

	/**
	 * Endpoint namespace.
	 */
	protected $namespace = 'scv2/v2';

	/**
	 * Route base.
	 */
	protected $rest_base = 'store';

	/**
	 * Register the routes for index.
	 */
	public function register_routes() {
		// Get Cart - scv2/v2 (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_store' ),
				'permission_callback' => '__return_true',
			)
		);
	} // register_routes()

	/**
	 * Retrieves the store index.
	 */
	public function get_store( $request ) {
		// General store data.
		$available = array(
			'version'         => SCV2_VERSION,
			'title'           => get_option( 'blogname' ),
			'description'     => get_option( 'blogdescription' ),
			'home_url'        => home_url(),
			'language'        => get_bloginfo( 'language' ),
			'gmt_offset'      => get_option( 'gmt_offset' ),
			'timezone_string' => get_option( 'timezone_string' ),
			'store_address'   => $this->get_store_address(),
			'routes'          => $this->get_routes(),
		);

		$response = new WP_REST_Response( $available );

		$response->add_link( 'help', 'https://docs.scv2.xyz/' );

		/**
		 * Filters the API store index data.
		 */
		return apply_filters( 'scv2_store_index', $response );
	} // END get_store()

	/**
	 * Returns the store address.
	 */
	public function get_store_address() {
		return apply_filters(
			'scv2_store_address',
			array(
				'address'   => get_option( 'woocommerce_store_address' ),
				'address_2' => get_option( 'woocommerce_store_address_2' ),
				'city'      => get_option( 'woocommerce_store_city' ),
				'country'   => get_option( 'woocommerce_default_country' ),
				'postcode'  => get_option( 'woocommerce_store_postcode' ),
			)
		);
	} // END get_store_address()

	/**
	 * Returns the list of all public SCV2 API routes.
	 */
	public function get_routes() {
		$prefix = trailingslashit( home_url() . '/' . rest_get_url_prefix() . '/scv2/v2/' );

		return apply_filters(
			'scv2_routes',
			array(
				'cart'             => $prefix . 'cart',
				'cart-add-item'    => $prefix . 'cart/add-item',
				'cart-add-items'   => $prefix . 'cart/add-items',
				'cart-item'        => $prefix . 'cart/item',
				'cart-items'       => $prefix . 'cart/items',
				'cart-items-count' => $prefix . 'cart/items/count',
				'cart-calculate'   => $prefix . 'cart/calculate',
				'cart-clear'       => $prefix . 'cart/clear',
				'cart-totals'      => $prefix . 'cart/totals',
				'login'            => $prefix . 'login',
				'logout'           => $prefix . 'logout',
			)
		);
	} // END get_routes()

} // END class
