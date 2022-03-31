<?php
/**
 * SCV2 - Set billing address controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SCV2 REST API Set billing address v2 controller class.
 */
class SCV2_Set_Billing_Address_v2_Controller {

	/**
	 * Endpoint namespace.
	 */
	protected $namespace = 'scv2/v2';

	/**
	 * Route base.
	 */
	protected $rest_base = 'cart/set-billing-address';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Set billing address - scv2/v2/cart/set-billing-address (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'set_billing_address' ),
					'permission_callback' => '__return_true',

				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	} // register_routes()

	/**
	 * Set billing address.
	 */
	public function set_billing_address( $request = array() ) {
		// Auth
		if ( is_user_logged_in() ) {
			try {
				// Check cart_key
				if (! isset($request['cart_key']) ) {
					return SCV2_Response::get_error_response( 'Bad Request', __('Field `cart_key` must be define'), 400 );
				}

				// Get parameters
				$cart_key = $request['cart_key'];
				$first_name = ! isset( $request['first_name'] ) ? "" : $request['first_name'];
				$last_name = ! isset( $request['last_name'] ) ? "" : $request['last_name'];
				$company = ! isset( $request['company'] ) ? "" : $request['company'];
				$email = ! isset( $request['email'] ) ? "" : $request['email'];
				$phone = ! isset( $request['phone'] ) ? "" : $request['phone'];
				$country = ! isset( $request['country'] ) ? "" : $request['country'];
				$state = ! isset( $request['state'] ) ? "" : $request['state'];
				$postcode = ! isset( $request['postcode'] ) ? "" : $request['postcode'];
				$city = ! isset( $request['city'] ) ? "" : $request['city'];
				$address = ! isset( $request['address'] ) ? "" : $request['address'];

				// Call cart controller 
				$controller = new SCV2_Cart_V2_Controller();

				// Set billing address to session
				$controller->get_cart_instance()->get_customer()->set_billing_first_name( wc_clean($first_name) );
				$controller->get_cart_instance()->get_customer()->set_billing_last_name( wc_clean($last_name) );
				$controller->get_cart_instance()->get_customer()->set_billing_company( wc_clean($company) );
				$controller->get_cart_instance()->get_customer()->set_billing_email( wc_clean($email) );
				$controller->get_cart_instance()->get_customer()->set_billing_phone( wc_clean($phone) );
				$controller->get_cart_instance()->get_customer()->set_billing_country( wc_clean($country) );
				$controller->get_cart_instance()->get_customer()->set_billing_state( wc_clean($state) );
				$controller->get_cart_instance()->get_customer()->set_billing_postcode( wc_clean($postcode) );
				$controller->get_cart_instance()->get_customer()->set_billing_city( wc_clean($city) );
				$controller->get_cart_instance()->get_customer()->set_billing_address( wc_clean($address) );

				// Formatting data
				$cart_billing_address = array(
					'first_name' => wc_clean( $first_name ),
					'last_name' => wc_clean( $last_name ),
					'company' => wc_clean( $company ),
					'email' => wc_clean( $email ),
					'phone' => wc_clean( $phone ),
					'country' => wc_clean( $country ),
					'state' => wc_clean( $state ),
					'postcode' => wc_clean( $postcode ),
					'city' => wc_clean( $city ),
					'address' => wc_clean( $address ),
				);

				// Update cart_shipping
				global $wpdb;

				try {
				    $wpdb->update(
				    	$wpdb->prefix.'scv2_carts', 
				    	array('cart_billing_address' => maybe_serialize( $cart_billing_address )), 
				    	array('cart_key' => $cart_key)
				    );

				    // Success status
					$response = array(
						"status" => true
					);

					return SCV2_Response::get_response( $response, $this->namespace, $this->rest_base );
				} catch ( SCV2_Data_Exception $e ) {
					return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
				}
			} catch ( SCV2_Data_Exception $e ) {
				return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
			}
		}

		return SCV2_Response::get_error_response( 'Unauthorized', __('You shall not pass'), 401 );
	} // END set_billing_address()

} // END class
