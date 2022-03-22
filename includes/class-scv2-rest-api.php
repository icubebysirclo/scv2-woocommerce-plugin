<?php
/**
 * SCV2 Server
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SCV2 REST API class.
 */
class SCV2_REST_API {

	/**
	 * Setup class.
	 */
	public function __construct() {
		// REST API was included starting WordPress 4.4.
		if ( ! class_exists( 'WP_REST_Server' ) ) {
			return;
		}

		// If WooCommerce does not exists then do nothing!
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$this->maybe_load_cart();
		$this->rest_api_includes();

		// Hook into WordPress ready to init the REST API as needed.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 10 );

		// Prevent SCV2 from being cached with WP REST API Cache plugin (https://wordpress.org/plugins/wp-rest-api-cache/).
		add_filter( 'rest_cache_skip', array( $this, 'prevent_cache' ), 10, 2 );

		// Sends the cart key to the header.
		add_filter( 'rest_authentication_errors', array( $this, 'scv2_key_header' ), 20, 1 );
	} // END __construct()

	/**
	 * Register REST API routes.
	 */
	public function register_rest_routes() {
		foreach ( $this->get_rest_namespaces() as $namespace => $controllers ) {
			foreach ( $controllers as $controller_name => $controller_class ) {
				if ( class_exists( $controller_class ) ) {
					$this->controllers[ $namespace ][ $controller_name ] = new $controller_class();
					$this->controllers[ $namespace ][ $controller_name ]->register_routes();
				}
			}
		}
	}

	/**
	 * Get API namespaces - new namespaces should be registered here.
	 */
	protected function get_rest_namespaces() {
		return apply_filters(
			'scv2_rest_api_get_rest_namespaces',
			array(
				'wc/v2'     => $this->get_legacy_controller(),
				'scv2/v1' => $this->get_v1_controllers(),
				'scv2/v2' => $this->get_v2_controllers(),
			)
		);
	}

	/**
	 * List of controllers in the wc/v2 namespace.
	 */
	protected function get_legacy_controller() {
		return array(
			'wc-rest-cart' => 'WC_REST_Cart_Controller',
		);
	}

	/**
	 * List of controllers in the scv2/v1 namespace.
	 */
	protected function get_v1_controllers() {
		return array(
			'scv2-v1-cart'        => 'SCV2_API_Controller',
			'scv2-v1-add-item'    => 'SCV2_Add_Item_Controller',
			'scv2-v1-calculate'   => 'SCV2_Calculate_Controller',
			'scv2-v1-clear-cart'  => 'SCV2_Clear_Cart_Controller',
			'scv2-v1-count-items' => 'SCV2_Count_Items_Controller',
			'scv2-v1-item'        => 'SCV2_Item_Controller',
			'scv2-v1-logout'      => 'SCV2_Logout_Controller',
			'scv2-v1-totals'      => 'SCV2_Totals_Controller',
		);
	}

	/**
	 * List of controllers in the scv2/v2 namespace.
	 */
	protected function get_v2_controllers() {
		return array(
			'scv2-v2-store'             => 'SCV2_Store_V2_Controller',
			'scv2-v2-cart'              => 'SCV2_Cart_V2_Controller',
			'scv2-v2-cart-add-item'     => 'SCV2_Add_Item_v2_Controller',
			'scv2-v2-cart-add-items'    => 'SCV2_Add_Items_v2_Controller',
			'scv2-v2-cart-item'         => 'SCV2_Item_v2_Controller',
			'scv2-v2-cart-items'        => 'SCV2_Items_v2_Controller',
			'scv2-v2-cart-items-count'  => 'SCV2_Count_Items_v2_Controller',
			'scv2-v2-cart-update-item'  => 'SCV2_Update_Item_v2_Controller',
			'scv2-v2-cart-remove-item'  => 'SCV2_Remove_Item_v2_Controller',
			'scv2-v2-cart-restore-item' => 'SCV2_Restore_Item_v2_Controller',
			'scv2-v2-cart-calculate'    => 'SCV2_Calculate_v2_Controller',
			'scv2-v2-cart-clear'        => 'SCV2_Clear_Cart_v2_Controller',
			'scv2-v2-cart-totals'       => 'SCV2_Totals_v2_Controller',
			'scv2-v2-login'             => 'SCV2_Login_v2_Controller',
			'scv2-v2-logout'            => 'SCV2_Logout_v2_Controller',
			'scv2-v2-session'           => 'SCV2_Session_V2_Controller',
			'scv2-v2-sessions'          => 'SCV2_Sessions_V2_Controller',
		);
	}

	/**
	 * Loads the cart, session and notices should it be required.
	 */
	private function maybe_load_cart() {
		if ( SCV2_Authentication::is_rest_api_request() ) {
			// WooCommerce is greater than v3.6 or less than v4.5.
			if ( SCV2_Helpers::is_wc_version_gte_3_6() && SCV2_Helpers::is_wc_version_lt_4_5() ) {
				require_once WC_ABSPATH . 'includes/wc-cart-functions.php';
				require_once WC_ABSPATH . 'includes/wc-notice-functions.php';

				// Initialize session.
				$this->initialize_session();

				// Initialize cart.
				$this->initialize_cart();
			}

			// WooCommerce is greater than v4.5 or equal.
			if ( SCV2_Helpers::is_wc_version_gte_4_5() ) {
				if ( is_null( WC()->cart ) && function_exists( 'wc_load_cart' ) ) {
					wc_load_cart();
				}
			}

			// Identify if user has switched.
			if ( $this->has_user_switched() ) {
				$this->user_switched();
			}
		}
	} // END maybe_load_cart()

	/**
	 * If the current customer ID in session does not match,
	 * then the user has switched.
	 */
	protected function has_user_switched() {
		if ( ! WC()->session instanceof SCV2_Session_Handler ) {
			return;
		}

		// Get cart cookie... if any.
		$cookie = WC()->session->get_session_cookie();

		// Current user ID. If user is NOT logged in then the customer is a guest.
		$current_user_id = strval( get_current_user_id() );

		// Does a cookie exist?
		if ( $cookie ) {
			$customer_id = $cookie[0];

			// If the user is logged in and does not match ID in cookie then user has switched.
			if ( $customer_id !== $current_user_id && 0 !== $current_user_id ) {
				/* translators: %1$s is previous ID, %2$s is current ID. */
				SCV2_Logger::log( sprintf( __( 'User has changed! Was %1$s before and is now %2$s', 'cart-rest-api-for-woocommerce' ), $customer_id, $current_user_id ), 'info' );

				return true;
			}
		}

		return false;
	} // END has_user_switched()

	/**
	 * Allows something to happen if a user has switched.
	 */
	public function user_switched() {
		do_action( 'scv2_user_switched' );
	} // END user_switched()

	/**
	 * Initialize session.
	 */
	public function initialize_session() {
		// SCV2 session handler class.
		$session_class = 'SCV2_Session_Handler';

		if ( is_null( WC()->session ) || ! WC()->session instanceof $session_class ) {
			// Prefix session class with global namespace if not already namespaced.
			if ( false === strpos( $session_class, '\\' ) ) {
				$session_class = '\\' . $session_class;
			}

			// Initialize new session.
			WC()->session = new $session_class();
			WC()->session->init();
		}
	} // END initialize_session()

	/**
	 * Initialize cart.
	 */
	public function initialize_cart() {
		if ( is_null( WC()->customer ) || ! WC()->customer instanceof WC_Customer ) {
			$customer_id = strval( get_current_user_id() );

			WC()->customer = new WC_Customer( $customer_id, true );

			// Customer should be saved during shutdown.
			add_action( 'shutdown', array( WC()->customer, 'save' ), 10 );
		}

		if ( is_null( WC()->cart ) || ! WC()->cart instanceof WC_Cart ) {
			WC()->cart = new WC_Cart();
		}
	} // END initialize_cart()

	/**
	 * Include SCV2 REST API controllers.
	 */
	public function rest_api_includes() {
		// Only include Legacy REST API if WordPress is v5.4.2 or lower.
		if ( SCV2_Helpers::is_wp_version_lt( '5.4.2' ) ) {
			// Legacy - WC Cart REST API v2 controller.
			include_once dirname( __FILE__ ) . '/api/legacy/wc-v2/class-wc-rest-cart-controller.php';
		}

		// SCV2 REST API v1 controllers.
		include_once dirname( __FILE__ ) . '/api/scv2/v1/class-scv2-controller.php';
		include_once dirname( __FILE__ ) . '/api/scv2/v1/class-scv2-add-item-controller.php';
		include_once dirname( __FILE__ ) . '/api/scv2/v1/class-scv2-clear-cart-controller.php';
		include_once dirname( __FILE__ ) . '/api/scv2/v1/class-scv2-calculate-controller.php';
		include_once dirname( __FILE__ ) . '/api/scv2/v1/class-scv2-count-controller.php';
		include_once dirname( __FILE__ ) . '/api/scv2/v1/class-scv2-item-controller.php';
		include_once dirname( __FILE__ ) . '/api/scv2/v1/class-scv2-logout-controller.php';
		include_once dirname( __FILE__ ) . '/api/scv2/v1/class-scv2-totals-controller.php';

		// SCV2 REST API v2 controllers.
		include_once dirname( __FILE__ ) . '/api/class-scv2-store-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-scv2-cart-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-scv2-add-item-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-scv2-add-items-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-scv2-item-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-scv2-items-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-scv2-clear-cart-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-scv2-calculate-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-scv2-count-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-scv2-update-item-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-scv2-remove-item-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-scv2-restore-item-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-scv2-login-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-scv2-logout-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-scv2-totals-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-scv2-session-controller.php';
		include_once dirname( __FILE__ ) . '/api/class-scv2-sessions-controller.php';

		do_action( 'scv2_rest_api_controllers' );
	} // rest_api_includes()

	/**
	 * Prevents SCV2 from being cached.
	 */
	public function prevent_cache( $skip, $request_uri ) {
		$rest_prefix = trailingslashit( rest_get_url_prefix() );

		if ( strpos( $request_uri, $rest_prefix . 'scv2/' ) !== false ) {
			return true;
		}

		return $skip;
	} // END prevent_cache()

	/**
	 * Sends the cart key to the header if a cart exists.
	 */
	public function scv2_key_header( $result ) {
		if ( ! empty( $result ) ) {
			return $result;
		}

		// Check that the SCV2 session handler has loaded.
		if ( ! WC()->session instanceof SCV2_Session_Handler ) {
			return $result;
		}

		// Customer ID used as the cart key by default.
		$cart_key = WC()->session->get_customer_id();

		// Get cart cookie... if any.
		$cookie = WC()->session->get_session_cookie();

		// If a cookie exist, override cart key.
		if ( $cookie ) {
			$cart_key = $cookie[0];
		}

		// Check if we requested to load a specific cart.
		$cart_key = isset( $_REQUEST['cart_key'] ) ? trim( sanitize_key( wp_unslash( $_REQUEST['cart_key'] ) ) ) : $cart_key; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Send cart key in the header if it's not empty or ZERO.
		if ( ! empty( $cart_key ) && '0' !== $cart_key ) {
			rest_get_server()->send_header( 'X-SCV2-API', $cart_key );
		}

		return true;
	} // END scv2_key_header()

} // END class

return new SCV2_REST_API();
