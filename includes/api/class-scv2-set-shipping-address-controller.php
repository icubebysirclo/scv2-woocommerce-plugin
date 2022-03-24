<?php
/**
 * SCV2 - Add Item controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SCV2 REST API Add Item v2 controller class.
 */
class SCV2_Set_Shipping_Address_v2_Controller {

	/**
	 * Endpoint namespace.
	 */
	protected $namespace = 'scv2/v2';

	/**
	 * Route base.
	 */
	protected $rest_base = 'cart/set-shipping-address';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Add Item - scv2/v2/cart/set-shipping-address (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'set_shipping_address' ),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	} // register_routes()

	/**
	 * Set shipping address.
	 */
	public function set_shipping_address( $request = array() ) {
		try {
			$first_name = ! isset( $request['first_name'] ) ? "" : $request['first_name'];
			$last_name 	= ! isset( $request['last_name'] ) ? "" : $request['last_name'];
			$company 	= ! isset( $request['company'] ) ? "" : $request['company'];
			$email 		= ! isset( $request['email'] ) ? "" : $request['email'];
			$phone 		= ! isset( $request['phone'] ) ? "" : $request['phone'];
			$country 	= ! isset( $request['country'] ) ? "" : $request['country'];
			$state 		= ! isset( $request['state'] ) ? "" : $request['state'];
			$postcode 	= ! isset( $request['postcode'] ) ? "" : $request['postcode'];
			$city 		= ! isset( $request['city'] ) ? "" : $request['city'];
			$address 	= ! isset( $request['address'] ) ? "" : $request['address'];

			// Call cart controller 
			$controller = new SCV2_Cart_V2_Controller();

			// Set shipping address 
			$controller->get_cart_instance()->get_customer()->set_shipping_first_name( wc_clean($first_name) );
			$controller->get_cart_instance()->get_customer()->set_shipping_last_name( wc_clean($last_name) );
			$controller->get_cart_instance()->get_customer()->set_shipping_company( wc_clean($company) );
			$controller->get_cart_instance()->get_customer()->set_shipping_country( wc_clean($country) );
			$controller->get_cart_instance()->get_customer()->set_shipping_state( wc_clean($state) );
			$controller->get_cart_instance()->get_customer()->set_shipping_postcode( wc_clean($postcode) );
			$controller->get_cart_instance()->get_customer()->set_shipping_city( wc_clean($city) );
			$controller->get_cart_instance()->get_customer()->set_shipping_address( wc_clean($address) );

			// Set email and phone on billing
			$controller->get_cart_instance()->get_customer()->set_billing_email( wc_clean($email) );
			$controller->get_cart_instance()->get_customer()->set_billing_phone( wc_clean($phone) );

			return true;
		} catch ( SCV2_Data_Exception $e ) {
			return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END set_shipping_address()

} // END class
