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
class SCV2_Calculate_v2_Controller extends SCV2_Calculate_Controller {

	/**
	 * Endpoint namespace.
	 */
	protected $namespace = 'scv2/v2';

	/**
	 * Route base.
	 */
	protected $rest_base = 'cart/calculate';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Calculate Cart Total - scv2/v2/cart/calculate (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'calculate_totals' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'return_totals' => array(
						'required'          => false,
						'default'           => false,
						'description'       => __( 'Returns the cart totals once calculated if requested.', 'cart-rest-api-for-woocommerce' ),
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
	public function calculate_totals( $request = array() ) {
		try {
			$controller = new SCV2_Cart_V2_Controller();

			$controller->get_cart_instance()->calculate_totals();

			// Was it requested to return all totals once calculated?
			if ( isset( $request['return_totals'] ) && is_bool( $request['return_totals'] ) && $request['return_totals'] ) {
				$response = SCV2_Totals_Controller::get_totals( $request );
			}

			scv2_deprecated_filter( 'scv2_totals_calculated_message', array(), '1.0.0', null, null );

			// Get cart contents.
			$response = $controller->get_cart_contents( $request );

			return SCV2_Response::get_response( $response, $this->namespace, $this->rest_base );
		} catch ( SCV2_Data_Exception $e ) {
			return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END calculate_totals()

} // END class
