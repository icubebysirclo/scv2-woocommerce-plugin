<?php
/**
 * SCV2 - Add Items controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SCV2 REST API Add Items v2 controller class.
 */
class SCV2_Add_Items_v2_Controller extends SCV2_Add_Item_Controller {

	/**
	 * Endpoint namespace.
	 */
	protected $namespace = 'scv2/v2';

	/**
	 * Route base.
	 */
	protected $rest_base = 'cart/add-items';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Add Items - scv2/v2/cart/add-items (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'add_items_to_cart' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	} // register_routes()

	/**
	 * Add other bundled or grouped products to Cart.
	 */
	public function add_items_to_cart( $request = array() ) {
		try {
			$product_id = ! isset( $request['id'] ) ? 0 : wc_clean( wp_unslash( $request['id'] ) );
			$items      = isset( $request['quantity'] ) && is_array( $request['quantity'] ) ? wp_unslash( $request['quantity'] ) : array();

			$controller = new SCV2_Cart_V2_Controller();

			// Filters additional requested data.
			$request = $controller->filter_request_data( $request );

			// Validate product ID before continuing and return correct product ID if different.
			$product_id = $this->validate_product_id( $product_id );

			// The product we are attempting to add to the cart.
			$adding_to_cart = wc_get_product( $product_id );
			$adding_to_cart = $controller->validate_product_for_cart( $adding_to_cart );

			// Add to cart handlers.
			$add_items_to_cart_handler = apply_filters( 'scv2_add_items_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

			if ( has_filter( 'scv2_add_items_to_cart_handler_' . $add_items_to_cart_handler ) ) {
				$was_added_to_cart = apply_filters( 'scv2_add_items_to_cart_handler_' . $add_items_to_cart_handler, $adding_to_cart, $request ); // Custom handler.
			} else {
				$was_added_to_cart = $this->add_to_cart_handler_grouped( $product_id, $items, $request );
			}

			if ( ! is_wp_error( $was_added_to_cart ) ) {
				// Was it requested to return the items details after being added?
				if ( isset( $request['return_items'] ) && is_bool( $request['return_items'] ) && $request['return_items'] ) {
					$response = array();

					foreach ( $was_added_to_cart as $id => $item ) {
						$response[] = $controller->get_item( $item['data'], $item, $item['key'], true );
					}
				} else {
					$response = $controller->get_cart_contents( $request );
				}

				return SCV2_Response::get_response( $response, $this->namespace, $this->rest_base );
			}

			return $was_added_to_cart;
		} catch ( SCV2_Data_Exception $e ) {
			return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END add_items_to_cart()

	/**
	 * Handle adding grouped product to the cart.
	 */
	public function add_to_cart_handler_grouped( $product_id, $items, $request ) {
		try {
			$controller = new SCV2_Cart_V2_Controller();

			$was_added_to_cart = false;
			$added_to_cart     = array();

			if ( ! empty( $items ) ) {
				$quantity_set = false;

				foreach ( $items as $item => $quantity ) {
					$quantity = wc_stock_amount( $quantity );

					if ( $quantity <= 0 ) {
						continue;
					}

					$quantity_set = true;

					// Product validation.
					$product_to_add = $controller->validate_product( $item, $quantity, 0, array(), array(), 'grouped', $request );

					/**
					 * If validation failed then return error response.
					 */
					if ( is_wp_error( $product_to_add ) ) {
						return $product_to_add;
					}

					// Suppress total recalculation until finished.
					remove_action( 'woocommerce_add_to_cart', array( WC()->cart, 'calculate_totals' ), 20, 0 );

					// Add item to cart once validation is passed.
					$item_added = $this->add_item_to_cart( $product_to_add );

					if ( false !== $item_added ) {
						$was_added_to_cart      = true;
						$added_to_cart[ $item ] = $item_added;
					}

					add_action( 'woocommerce_add_to_cart', array( WC()->cart, 'calculate_totals' ), 20, 0 );
				}

				if ( ! $was_added_to_cart && ! $quantity_set ) {
					throw new SCV2_Data_Exception( 'scv2_grouped_product_failed', __( 'Please choose the quantity of items you wish to add to your cart.', 'cart-rest-api-for-woocommerce' ), 404 );
				} elseif ( $was_added_to_cart ) {
					scv2_add_to_cart_message( $added_to_cart );

					// Calculate totals now all items in the group has been added to cart.
					$controller->get_cart_instance()->calculate_totals();

					return $added_to_cart;
				}
			} else {
				throw new SCV2_Data_Exception( 'scv2_grouped_product_empty', __( 'Please choose a product to add to your cart.', 'cart-rest-api-for-woocommerce' ), 404 );
			}
		} catch ( SCV2_Data_Exception $e ) {
			return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END add_to_cart_handler_grouped()

	/**
	 * Get the schema for adding items, conforming to JSON Schema.
	 */
	public function get_item_schema() {
		$schema = array(
			'schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'SCV2 - ' . __( 'Add Items', 'cart-rest-api-for-woocommerce' ),
			'type'       => 'object',
			'properties' => array(
				'id'           => array(
					'required'    => true,
					'description' => __( 'Unique identifier for the container product ID.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
				),
				'quantity'     => array(
					'required'    => true,
					'description' => __( 'List of items and quantity in the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
				),
				'return_items' => array(
					'required'    => false,
					'default'     => false,
					'description' => __( 'Returns the items details once added.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
				),
			),
		);

		$schema['properties'] = apply_filters( 'scv2_add_items_schema', $schema['properties'], $this->rest_base );

		return $schema;
	} // END get_item_schema()

	/**
	 * Get the query params for adding items.
	 */
	public function get_collection_params() {
		$controller = new SCV2_Cart_V2_Controller();

		$params = array_merge(
			$controller->get_collection_params(),
			array(
				'id'           => array(
					'description'       => __( 'Unique identifier for the container product ID.', 'cart-rest-api-for-woocommerce' ),
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg',
				),
				'quantity'     => array(
					'required'          => true,
					'description'       => __( 'List of items and quantity in the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'              => 'object',
					'validate_callback' => 'rest_validate_request_arg',
				),
				'return_items' => array(
					'description' => __( 'Returns the items details once added.', 'cart-rest-api-for-woocommerce' ),
					'default'     => false,
					'type'        => 'boolean',
				),
			)
		);

		return $params;
	} // END get_collection_params()

} // END class
