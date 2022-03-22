<?php
/**
 * SCV2 - Totals controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Totals controller class.
 */
class SCV2_Totals_v2_Controller extends SCV2_Cart_V2_Controller {

	/**
	 * Route base.
	 */
	protected $rest_base = 'cart/totals';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Get Cart Totals - scv2/v2/cart/totals (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_totals' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'html' => array(
						'required'          => false,
						'default'           => false,
						'description'       => __( 'Returns the totals pre-formatted.', 'cart-rest-api-for-woocommerce' ),
						'type'              => 'boolean',
						'validate_callback' => 'rest_validate_request_arg',
					),
				),
			)
		);
	} // register_routes()

	/**
	 * Returns all calculated totals.
	 */
	public function get_totals( $request = array() ) {
		try {
			$pre_formatted = isset( $request['html'] ) ? $request['html'] : false;

			$controller = new SCV2_Cart_V2_Controller();

			$totals            = $controller->get_cart_instance()->get_totals();
			$totals_calculated = false;

			if ( ! empty( $totals['total'] ) ) {
				$totals_calculated = true;
			}

			if ( ! $totals_calculated ) {
				$message = esc_html__( 'This cart either has no items or was not calculated.', 'cart-rest-api-for-woocommerce' );

				throw new SCV2_Data_Exception( 'scv2_cart_totals_empty', $message, 404 );
			}

			$ignore_convert = array(
				'shipping_taxes',
				'cart_contents_taxes',
				'fee_taxes',
			);

			// Was it requested to have the totals preformatted?
			if ( $pre_formatted ) {
				$new_totals = array();

				foreach ( $totals as $type => $total ) {
					if ( in_array( $type, $ignore_convert ) ) {
						$new_totals[ $type ] = $total;
					} else {
						if ( is_string( $total ) ) {
							$new_totals[ $type ] = scv2_price_no_html( $total );
						} else {
							$new_totals[ $type ] = scv2_price_no_html( strval( $total ) );
						}
					}
				}

				$totals = $new_totals;
			}

			return SCV2_Response::get_response( $totals, $this->namespace, $this->rest_base );
		} catch ( SCV2_Data_Exception $e ) {
			return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_totals()

} // END class
