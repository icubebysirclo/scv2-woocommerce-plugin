<?php
/**
 * SCV2 - Calculate controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Calculate controller class.
 */
class SCV2_Calculate_Controller extends SCV2_API_Controller {

	/**
	 * Route base.
	 */
	protected $rest_base = 'calculate';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Calculate Cart Total - scv2/v1/calculate (POST)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'calculate_totals' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'return' => array(
						'required'          => false,
						'default'           => false,
						'description'       => __( 'Returns the cart totals once calculated.', 'cart-rest-api-for-woocommerce' ),
						'type'              => 'boolean',
						'validate_callback' => 'rest_validate_request_arg',
					),
				),
			)
		);
	} // register_routes()

	/**
	 * Calculate Cart Totals.
	 */
	public function calculate_totals( $data = array() ) {
		WC()->cart->calculate_totals();

		// Was it requested to return all totals once calculated?
		if ( $data['return'] ) {
			return SCV2_Totals_Controller::get_totals( $data );
		}

		$message = __( 'Cart totals have been calculated.', 'cart-rest-api-for-woocommerce' );

		SCV2_Logger::log( $message, 'notice' );

		/**
		 * Filters message about cart totals have been calculated.
		 */
		$message = apply_filters( 'scv2_totals_calculated_message', $message );

		return $this->get_response( $message, $this->rest_base );
	} // END calculate_totals()

} // END class
