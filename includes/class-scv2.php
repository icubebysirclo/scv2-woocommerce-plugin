<?php
/**
 * SCV2 core setup.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main SCV2 class.
 */
final class SCV2 {

	/**
	 * Plugin Version
	 */
	public static $version = '1.0.0';

	/**
	 * SCV2 Database Schema version.
	 */
	public static $db_version = '1.0.0';

	/**
	 * Required WordPress Version
	 */
	public static $required_wp = '5.4';

	/**
	 * Required WooCommerce Version
	 */
	public static $required_woo = '4.3';

	/**
	 * Required PHP Version

	 */
	public static $required_php = '7.3';

	/**
	 * Initiate SCV2.
	 */
	public static function init() {
		self::setup_constants();
		self::includes();
		self::include_extension_compatibility();
		self::include_third_party();

		// Install SCV2 upon activation.
		register_activation_hook( SCV2_FILE, array( __CLASS__, 'install_scv2' ) );

		// Setup SCV2 Session Handler.
		add_filter( 'woocommerce_session_handler', array( __CLASS__, 'session_handler' ) );

		// Setup WooCommerce and SCV2.
		add_action( 'woocommerce_loaded', array( __CLASS__, 'woocommerce' ) );
		add_action( 'woocommerce_loaded', array( __CLASS__, 'background_updater' ) );

		// Load translation files.
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ), 0 );

		// Load REST API.
		add_action( 'init', array( __CLASS__, 'load_rest_api' ) );

		// Initialize action.
		do_action( 'scv2_init' );
	} // END init()

	/**
	 * Setup Constants
	 */
	public static function setup_constants() {
		self::define( 'SCV2_ABSPATH', dirname( SCV2_FILE ) . '/' );
		self::define( 'SCV2_PLUGIN_BASENAME', plugin_basename( SCV2_FILE ) );
		self::define( 'SCV2_VERSION', self::$version );
		self::define( 'SCV2_DB_VERSION', self::$db_version );
		self::define( 'SCV2_SLUG', 'cart-rest-api-for-woocommerce' );
		self::define( 'SCV2_URL_PATH', untrailingslashit( plugins_url( '/', SCV2_FILE ) ) );
		self::define( 'SCV2_FILE_PATH', untrailingslashit( plugin_dir_path( SCV2_FILE ) ) );
		self::define( 'SCV2_CART_CACHE_GROUP', 'scv2_cart_id' );
		self::define( 'SCV2_STORE_URL', 'http://getswift.asia/' );
		self::define( 'SCV2_PLUGIN_URL', 'https://wordpress.org/plugins/cart-rest-api-for-woocommerce/' );
		self::define( 'SCV2_SUPPORT_URL', 'https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce' );
		self::define( 'SCV2_REVIEW_URL', 'https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/reviews/' );
		self::define( 'SCV2_DOCUMENTATION_URL', 'https://docs.scv2.xyz' );
		self::define( 'SCV2_TRANSLATION_URL', 'https://translate.scv2.xyz/projects/cart-rest-api-for-woocommerce/' );
		self::define( 'SCV2_NEXT_VERSION', '1.0.0' );
	} // END setup_constants()

	/**
	 * Define constant if not already set.
	 */
	private static function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	} // END define()

	/**
	 * Return the name of the package.
	 */
	public static function get_name() {
		return 'SCV2';
	} // END get_name()

	/**
	 * Return the version of the package.
	 */
	public static function get_version() {
		return self::$version;
	} // END get_version()

	/**
	 * Return the path to the package.
	 */
	public static function get_path() {
		return dirname( __DIR__ );
	} // END get_path()

	/**
	 * Includes required core files.
	 */
	public static function includes() {
		// Class autoloader.
		include_once SCV2_ABSPATH . 'includes/class-scv2-autoloader.php';

		// Abstracts.
		include_once SCV2_ABSPATH . 'includes/abstracts/abstract-scv2-session.php';

		// Core classes.
		include_once SCV2_ABSPATH . 'includes/class-scv2-api.php';
		include_once SCV2_ABSPATH . 'includes/class-scv2-authentication.php';
		include_once SCV2_ABSPATH . 'includes/class-scv2-helpers.php';
		include_once SCV2_ABSPATH . 'includes/class-scv2-install.php';
		include_once SCV2_ABSPATH . 'includes/class-scv2-logger.php';
		include_once SCV2_ABSPATH . 'includes/class-scv2-response.php';
		include_once SCV2_ABSPATH . 'includes/class-scv2-cart-formatting.php';
		include_once SCV2_ABSPATH . 'includes/class-scv2-cart-validation.php';
		include_once SCV2_ABSPATH . 'includes/class-scv2-product-validation.php';
		include_once SCV2_ABSPATH . 'includes/class-scv2-session.php';

		// Redirect functions.
		include_once SCV2_ABSPATH . 'includes/scv2-redirect-functions.php';

		// REST API functions.
		include_once SCV2_ABSPATH . 'includes/scv2-rest-functions.php';

		// WP-CLI.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			include_once SCV2_ABSPATH . 'includes/class-scv2-cli.php';
		}

		/**
		 * Load backend features only if SCV2_WHITE_LABEL constant is
		 * NOT set or IS set to false in user's wp-config.php file.
		 */
		if (
			! defined( 'SCV2_WHITE_LABEL' ) || false === SCV2_WHITE_LABEL &&
			is_admin() || ( defined( 'WP_CLI' ) && WP_CLI )
		) {
			include_once SCV2_ABSPATH . 'includes/admin/class-scv2-admin.php';
		} else {
			include_once SCV2_ABSPATH . 'includes/admin/class-scv2-wc-admin-system-status.php';
		}
	} // END includes()

	/**
	 * SCV2 Background Updater.
	 */
	public static function background_updater() {
		include_once SCV2_ABSPATH . 'includes/class-scv2-background-updater.php';
	} // END background_updater()

	/**
	 * Include extension compatibility.
	 */
	public static function include_extension_compatibility() {
		include_once SCV2_ABSPATH . 'includes/compatibility/class-scv2-compatibility.php';
	} // END include_extension_compatibility()

	/**
	 * Include third party support.
	 */
	public static function include_third_party() {
		include_once SCV2_ABSPATH . 'includes/third-party/class-scv2-third-party.php';
	} // END include_third_party()

	/**
	 * Install SCV2 upon activation.
	 */
	public static function install_scv2() {
		self::activation_check();

		SCV2_Install::install();
	} // END install_scv2()

	/**
	 * Checks the server environment and other factors and deactivates the plugin if necessary.
	 */
	public static function activation_check() {
		if ( ! SCV2_Helpers::is_environment_compatible() ) {
			self::deactivate_plugin();
			/* translators: %1$s: SCV2, %2$s: Environment message */
			wp_die( sprintf( esc_html__( '%1$s could not be activated. %2$s', 'cart-rest-api-for-woocommerce' ), 'SCV2', SCV2_Helpers::get_environment_message() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( SCV2_Helpers::is_scv2_pro_installed() && defined( 'SCV2_PACKAGE_VERSION' ) && version_compare( SCV2_VERSION, SCV2_PACKAGE_VERSION, '>=' ) ) {
			self::deactivate_plugin();
			/* translators: %1$s: Swift Checkout V2, %2$s: SCV2 Pro */
			wp_die( sprintf( esc_html__( '%1$s is not required as it is already packaged within %2$s', 'cart-rest-api-for-woocommerce' ), 'Swift Checkout V2', 'SCV2 Pro' ) );
		}
	} // END activation_check()

	/**
	 * Deactivates the plugin if the environment is not ready.
	 */
	public static function deactivate_plugin() {
		deactivate_plugins( plugin_basename( SCV2_FILE ) );

		if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	} // END deactivate_plugin()

	/**
	 * Load REST API.
	 */
	public static function load_rest_api() {
		include_once SCV2_ABSPATH . 'includes/class-scv2-rest-api.php';
	} // END load_rest_api()

	/**
	 * Filters the session handler to replace with our own.
	 */
	public static function session_handler( $handler ) {
		if ( class_exists( 'WC_Session' ) ) {
			include_once SCV2_ABSPATH . 'includes/class-scv2-session-handler.php';
			$handler = 'SCV2_Session_Handler';
		}

		return $handler;
	} // END session_handler()

	/**
	 * Includes WooCommerce tweaks.
	 */
	public static function woocommerce() {
		include_once SCV2_ABSPATH . 'includes/class-scv2-woocommerce.php';
	} // END woocommerce()

	/**
	 * Load the plugin translations if any ready.
	 */
	public static function load_plugin_textdomain() {
		if ( function_exists( 'determine_locale' ) ) {
			$locale = determine_locale();
		} else {
			$locale = is_admin() ? get_user_locale() : get_locale();
		}

		$locale = apply_filters( 'plugin_locale', $locale, SCV2_SLUG );

		unload_textdomain( SCV2_SLUG );
		load_textdomain( SCV2_SLUG, WP_LANG_DIR . '/cart-rest-api-for-woocommerce/cart-rest-api-for-woocommerce-' . $locale . '.mo' );
		load_plugin_textdomain( SCV2_SLUG, false, plugin_basename( dirname( SCV2_FILE ) ) . '/languages' );
	} // END load_plugin_textdomain()

} // END class
