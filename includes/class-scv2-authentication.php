<?php
/**
 * Handles REST API authentication.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_Authentication' ) ) {

	class SCV2_Authentication {

		/**
		 * Authentication error.
		 */
		protected $error = null;

		/**
		 * Logged in user data.
		 */
		protected $user = null;

		/**
		 * Current auth method.
		 */
		protected $auth_method = '';

		/**
		 * Constructor.
		 */
		public function __construct() {
			// Check that we are only authenticating for our API.
			if ( $this->is_rest_api_request() ) {
				// Authenticate user.
				add_filter( 'determine_current_user', array( $this, 'authenticate' ), 15 );
				add_filter( 'rest_authentication_errors', array( $this, 'authentication_fallback' ) );

				// Triggers saved cart after login and updates user activity.
				add_filter( 'rest_authentication_errors', array( $this, 'scv2_user_logged_in' ), 10 );

				// Check authentication errors.
				add_filter( 'rest_authentication_errors', array( $this, 'check_authentication_error' ), 15 );

				// Disable cookie authentication REST check.
				if ( is_ssl() || $this->is_wp_environment_local() ) {
					remove_filter( 'rest_authentication_errors', 'rest_cookie_check_errors', 100 );
				}

				// Check API permissions.
				add_filter( 'rest_pre_dispatch', array( $this, 'check_api_permissions' ), 10, 3 );

				// Allow all cross origin requests.
				add_action( 'rest_api_init', array( $this, 'allow_all_cors' ), 15 );
			}
		}

		/**
		 * Triggers saved cart after login and updates user activity.
		 */
		public function scv2_user_logged_in( $error ) {
			global $current_user;

			if ( $current_user->ID > 0 ) {
				wc_update_user_last_active( $current_user->ID );
				update_user_meta( $current_user->ID, '_woocommerce_load_saved_cart_after_login', 1 );
			}

			return $error;
		} // END scv2_user_logged_in()

		/**
		 * Returns true if we are making a REST API request for SCV2.
		 */
		public static function is_rest_api_request() {
			if ( empty( $_SERVER['REQUEST_URI'] ) ) {
				return false;
			}

			$rest_prefix         = trailingslashit( rest_get_url_prefix() );
			$request_uri         = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			$is_rest_api_request = ( false !== strpos( $request_uri, $rest_prefix . 'scv2/' ) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			return apply_filters( 'scv2_is_rest_api_request', $is_rest_api_request );
		} // END is_rest_api_request()

		/**
		 * Authenticate user.
		 */
		public function authenticate( $user_id ) {
			// Do not authenticate twice.
			if ( ! empty( $user_id ) ) {
				return $user_id;
			}

			if ( is_ssl() || $this->is_wp_environment_local() ) {
				$user_id = $this->perform_basic_authentication();
			}

			/**
			 * Should you need to authenticate as another user instead of the one returned.
			 */
			$user_id = apply_filters( 'scv2_authenticate', $user_id, is_ssl() );

			return $user_id;
		} // END authenticate()

		/**
		 * Authenticate the user if authentication wasn't performed during the
		 * determine_current_user action.
		 */
		public function authentication_fallback( $error ) {
			if ( ! empty( $error ) ) {
				// Another plugin has already declared a failure.
				return $error;
			}

			if ( empty( $this->error ) && empty( $this->auth_method ) && empty( $this->user ) && 0 === get_current_user_id() ) {
				// Authentication hasn't occurred during `determine_current_user`, so check auth.
				$user_id = $this->authenticate( false );

				if ( ! empty( $user_id ) ) {
					wp_set_current_user( $user_id );
					return true;
				}
			}
			return $error;
		} // END authentication_fallback()

		/**
		 * Check for authentication error.
		 */
		public function check_authentication_error( $error ) {
			// Pass through other errors.
			if ( ! empty( $error ) ) {
				return $error;
			}

			return $this->get_error();
		} // END check_authentication_error()

		/**
		 * Set authentication error.
		 */
		protected function set_error( $error ) {
			// Reset user.
			$this->user = null;

			$this->error = $error;
		} // END set_error()

		/**
		 * Get authentication error.
		 */
		protected function get_error() {
			return $this->error;
		} // END get_error()

		/**
		 * Basic Authentication.
		 */
		private function perform_basic_authentication() {
			$this->auth_method = 'basic_auth';

			// Check that we're trying to authenticate via headers.
			if ( ! empty( $_SERVER['PHP_AUTH_USER'] ) && ! empty( $_SERVER['PHP_AUTH_PW'] ) ) {
				$username = trim( sanitize_user( $_SERVER['PHP_AUTH_USER'] ) );
				$password = trim( sanitize_text_field( $_SERVER['PHP_AUTH_PW'] ) );

				// Check if the username provided was an email address and get the username if true.
				if ( is_email( $_SERVER['PHP_AUTH_USER'] ) ) {
					$user     = get_user_by( 'email', $_SERVER['PHP_AUTH_USER'] );
					$username = $user->user_login;
				}
			} elseif ( ! empty( $_REQUEST['username'] ) && ! empty( $_REQUEST['password'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				// Fallback to check if the username and password was passed via URL.
				$username = trim( sanitize_user( $_REQUEST['username'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$password = trim( sanitize_text_field( $_REQUEST['password'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				// Check if the username provided was an email address and get the username if true.
				if ( is_email( $_REQUEST['username'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$user     = get_user_by( 'email', $_REQUEST['username'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$username = $user->user_login; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}
			}

			// Only authenticate if a username and password is available to check.
			if ( ! empty( $username ) && ! empty( $password ) ) {
				$this->user = wp_authenticate( $username, $password );
			} else {
				return false;
			}

			if ( is_wp_error( $this->user ) ) {
				$this->set_error( new WP_Error( 'scv2_authentication_error', __( 'Authentication is invalid.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 401 ) ) );

				return false;
			}

			return $this->user->ID;
		} // END perform_basic_authentication()

		/**
		 * Checks the WordPress environment to see if we are running SCV2 locally.
		 */
		protected function is_wp_environment_local() {
			if ( function_exists( 'wp_get_environment_type' ) ) {
				if ( 'local' === wp_get_environment_type() || 'development' === wp_get_environment_type() ) {
					return true;
				}
			}

			return false;
		} // END is_wp_environment_local()

		/**
		 * Allow all cross origin header requests.
		 */
		public function allow_all_cors() {
			// If not enabled via filter then return.
			if ( apply_filters( 'scv2_disable_all_cors', true ) ) {
				return;
			}

			// Remove the default cors server headers.
			remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );

			// Adds new cors server headers.
			add_filter( 'rest_pre_serve_request', array( $this, 'cors_headers' ), 0, 4 );
		} // END allow_all_cors()

		/**
		 * Cross Origin headers.
		 */
		public function cors_headers( $served, $result, $request, $server ) {
			if ( strpos( $request->get_route(), 'scv2/' ) !== false ) {
				$origin = get_http_origin();

				// Requests from file:// and data: URLs send "Origin: null".
				if ( 'null' !== $origin ) {
					$origin = esc_url_raw( $origin );
				}

				header( 'Access-Control-Allow-Origin: ' . apply_filters( 'scv2_allow_origin', $origin ) );
				header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
				header( 'Access-Control-Allow-Credentials: true' );
				header( 'Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With' );
				header( 'Access-Control-Expose-Headers: X-SCV2-API' );
			}

			return $served;
		} // END cors_headers()

		/**
		 * Check for permission to access API.
		 */
		public function check_api_permissions( $result, $server, $request ) {
			$method = $request->get_method();
			$path   = $request->get_route();
			$prefix = 'scv2/';

			/**
			 * Should the developer choose to restrict any of SCV2's API routes for any method.
			 * They can set the requested API and method to enforce authentication by not allowing it permission to the public.
			 */
			$api_not_allowed = apply_filters( 'scv2_api_permission_check_' . strtolower( $method ), array() );

			try {
				// If no user is logged in then just return.
				if ( ! is_user_logged_in() ) {
					switch ( $method ) {
						case 'GET':
							foreach ( $api_not_allowed as $route ) {
								if ( preg_match( '!^/' . $prefix . $route . '(?:$|/)!', $path ) ) {
									/* translators: 1: permission method, 2: api route */
									throw new SCV2_Data_Exception( 'scv2_rest_permission_error', sprintf( __( 'Permission to %1$s %2$s is only permitted if the user is authenticated.', 'cart-rest-api-for-woocommerce' ), 'READ', $path ), 401 );
								}
							}
							break;
						case 'POST':
						case 'PUT':
						case 'PATCH':
						case 'DELETE':
							foreach ( $api_not_allowed as $route ) {
								if ( preg_match( '!^/' . $prefix . $route . '(?:$|/)!', $path ) ) {
									/* translators: 1: permission method, 2: api route */
									throw new SCV2_Data_Exception( 'scv2_rest_permission_error', sprintf( __( 'Permission to %1$s %2$s is only permitted if the user is authenticated.', 'cart-rest-api-for-woocommerce' ), 'WRITE', $path ), 401 );
								}
							}
							break;
						case 'OPTIONS':
							return true;

						default:
							/* translators: %s: api route */
							throw new SCV2_Data_Exception( 'scv2_rest_permission_error', sprintf( __( 'Unknown request method for %s.', 'cart-rest-api-for-woocommerce' ), $path ), 401 );
					}
				}

				// Return previous result if nothing has changed.
				return $result;
			} catch ( SCV2_Data_Exception $e ) {
				return SCV2_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
			}
		} // END check_permissions()

	} // END class.

} // END if class exists.

return new SCV2_Authentication();
