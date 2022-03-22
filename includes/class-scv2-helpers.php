<?php
/**
 * SCV2 REST API helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SCV2 REST API helper class.
 */
class SCV2_Helpers {

	/**
	 * Cache 'gte' comparison results for WooCommerce version.
	 */
	private static $is_wc_version_gte = array();

	/**
	 * Cache 'gt' comparison results for WooCommerce version.
	 */
	private static $is_wc_version_gt = array();

	/**
	 * Cache 'lte' comparison results for WooCommerce version.
	 */
	private static $is_wc_version_lte = array();

	/**
	 * Cache 'lt' comparison results for WooCommerce version.
	 */
	private static $is_wc_version_lt = array();

	/**
	 * Cache 'gt' comparison results for WP version.
	 */
	private static $is_wp_version_gt = array();

	/**
	 * Cache 'gte' comparison results for WP version.
	 */
	private static $is_wp_version_gte = array();

	/**
	 * Cache 'lt' comparison results for WP version.
	 */
	private static $is_wp_version_lt = array();

	/**
	 * Helper method to get the version of the currently installed WooCommerce.
	 */
	private static function get_wc_version() {
		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	} // END get_wc_version()

	/**
	 * Returns true if the installed version of WooCommerce is 3.6 or greater.
	 */
	public static function is_wc_version_gte_3_6() {
		return self::is_wc_version_gte( '3.6' );
	} // END is_wc_version_gte_3_6()

	/**
	 * Returns true if the installed version of WooCommerce is 4.0 or greater.
	 */
	public static function is_wc_version_gte_4_0() {
		return self::is_wc_version_gte( '4.0' );
	} // END is_wc_version_gte_4_0()

	/**
	 * Returns true if the installed version of WooCommerce is 4.5 or greater.
	 */
	public static function is_wc_version_gte_4_5() {
		return self::is_wc_version_gte( '4.5' );
	} // END is_wc_version_gte_4_5()

	/**
	 * Returns true if the installed version of WooCommerce is lower than 4.5.
	 */
	public static function is_wc_version_lt_4_5() {
		return self::is_wc_version_lt( '4.5' );
	} // END is_wc_version_lt_4_5()

	/**
	 * Returns true if the installed version of WooCommerce is 4.8 or greater.
	 */
	public static function is_wc_version_gte_4_8() {
		return self::is_wc_version_gte( '4.8' );
	} // END is_wc_version_gte_4_5()

	/**
	 * Returns true if the installed version of WooCommerce is greater than or equal to $version.
	 */
	public static function is_wc_version_gte( $version ) {
		if ( ! isset( self::$is_wc_version_gte[ $version ] ) ) {
			self::$is_wc_version_gte[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>=' );
		}
		return self::$is_wc_version_gte[ $version ];
	} // END is_wc_version_gte()

	/**
	 * Returns true if the installed version of WooCommerce is greater than $version.
	 */
	public static function is_wc_version_gt( $version ) {
		if ( ! isset( self::$is_wc_version_gt[ $version ] ) ) {
			self::$is_wc_version_gt[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>' );
		}

		return self::$is_wc_version_gt[ $version ];
	} // END is_wc_version_gt()

	/**
	 * Returns true if the installed version of WooCommerce is lower than or equal to $version.
	 */
	public static function is_wc_version_lte( $version ) {
		if ( ! isset( self::$is_wc_version_lte[ $version ] ) ) {
			self::$is_wc_version_lte[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '<=' );
		}
		return self::$is_wc_version_lte[ $version ];
	} // END is_wc_version_lte()

	/**
	 * Returns true if the installed version of WooCommerce is less than $version.
	 */
	public static function is_wc_version_lt( $version ) {
		if ( ! isset( self::$is_wc_version_lt[ $version ] ) ) {
			self::$is_wc_version_lt[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '<' );
		}

		return self::$is_wc_version_lt[ $version ];
	} // END is_wc_version_lt()

	/**
	 * Returns true if the WooCommerce version does not meet SCV2 requirements.
	 */
	public static function is_not_wc_version_required() {
		if ( version_compare( self::get_wc_version(), SCV2::$required_woo, '<' ) ) {
			return true;
		}

		return false;
	} // END is_note_wc_version_required()

	/**
	 * Returns true if the installed version of WordPress is greater than $version.
	 */
	public static function is_wp_version_gt( $version ) {
		if ( ! isset( self::$is_wp_version_gt[ $version ] ) ) {
			global $wp_version;

			self::$is_wp_version_gt[ $version ] = $wp_version && version_compare( $wp_version, $version, '>' );
		}

		return self::$is_wp_version_gt[ $version ];
	} // END is_wp_version_gt()

	/**
	 * Returns true if the installed version of WordPress is greater than or equal to $version.
	 */
	public static function is_wp_version_gte( $version ) {
		if ( ! isset( self::$is_wp_version_gte[ $version ] ) ) {
			global $wp_version;

			self::$is_wp_version_gte[ $version ] = $wp_version && version_compare( $wp_version, $version, '>=' );
		}

		return self::$is_wp_version_gte[ $version ];
	} // END is_wp_version_gte()

	/**
	 * Returns true if the installed version of WordPress is less than $version.
	 */
	public static function is_wp_version_lt( $version ) {
		if ( ! isset( self::$is_wp_version_lt[ $version ] ) ) {
			global $wp_version;

			self::$is_wp_version_lt[ $version ] = $wp_version && version_compare( $wp_version, $version, '<' );
		}

		return self::$is_wp_version_lt[ $version ];
	} // END is_wp_version_lt()

	/**
	 * Helper method to get the version of the currently installed SCV2.
	 */
	public static function get_scv2_version() {
		return defined( 'SCV2_VERSION' ) && SCV2_VERSION ? SCV2_VERSION : null;
	} // END get_scv2_version()

	/**
	 * Returns true if SCV2 is a pre-release.
	 */
	public static function is_scv2_pre_release() {
		$version = self::get_scv2_version();

		if ( strpos( $version, 'beta' ) ||
			strpos( $version, 'rc' )
		) {
			return true;
		}

		return false;
	} // END is_scv2_pre_release()

	/**
	 * Returns true if SCV2 is a Beta release.
	 */
	public static function is_scv2_beta() {
		$version = self::get_scv2_version();

		if ( strpos( $version, 'beta' ) ) {
			return true;
		}

		return false;
	} // END is_scv2_beta()

	/**
	 * Returns true if SCV2 is a Release Candidate.
	 */
	public static function is_scv2_rc() {
		$version = self::get_scv2_version();

		if ( strpos( $version, 'rc' ) ) {
			return true;
		}

		return false;
	} // END is_scv2_rc()

	/**
	 * Checks if SCV2 Pro is installed.
	 */
	public static function is_scv2_pro_installed() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( 'scv2-pro/scv2-pro.php', $active_plugins ) || array_key_exists( 'scv2-pro/scv2-pro.php', $active_plugins ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
	} // END is_scv2_pro_installed()

	/**
	 * Check if SCV2 Pro is activated.
	 */
	public static function is_scv2_pro_activated() {
		if ( class_exists( 'SCV2_Pro' ) ) {
			return true;
		}

		return false;
	} // END is_scv2_pro_activated()

	/**
	 * These are the only screens SCV2 will focus
	 * on displaying notices or enqueue scripts/styles.
	 */
	public static function scv2_get_admin_screens() {
		return apply_filters(
			'scv2_admin_screens',
			array(
				'dashboard',
				'dashboard-network',
				'plugins',
				'plugins-network',
				'woocommerce_page_wc-status',
				'toplevel_page_scv2',
				'toplevel_page_scv2-network',
			)
		);
	} // END scv2_get_admin_screens()

	/**
	 * Returns true|false if the user is on a SCV2 page.
	 */
	public static function is_scv2_admin_page() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( ! in_array( $screen_id, self::scv2_get_admin_screens() ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			return false;
		}

		return true;
	} // END is_scv2_admin_page()

	/**
	 * Checks if the current user has the capabilities to install a plugin.
	 */
	public static function user_has_capabilities() {
		if ( current_user_can( apply_filters( 'scv2_install_capability', 'install_plugins' ) ) ) {
			return true;
		}

		// If the current user can not install plugins then return nothing!
		return false;
	} // END user_has_capabilities()

	/**
	 * Is SCV2 Plugin Suggestions active?
	 */
	public static function is_scv2_ps_active() {
		return apply_filters( 'scv2_show_plugin_search', true );
	} // END is_scv2_ps_active()

	/**
	 * Returns SCV2 Campaign for plugin identification.
	 */
	public static function scv2_campaign( $args = array() ) {
		$defaults = array(
			'utm_medium'   => 'scv2-lite',
			'utm_source'   => 'WordPress',
			'utm_campaign' => 'liteplugin',
			'utm_content'  => '',
		);

		$campaign = wp_parse_args( $args, $defaults );

		return $campaign;
	} // END scv2_campaign()

	/**
	 * Seconds to words.
	 */
	public static function scv2_seconds_to_words( $seconds ) {
		// Get the years.
		$years = ( intval( $seconds ) / YEAR_IN_SECONDS ) % 100;
		if ( $years > 1 ) {
			/* translators: %s: Number of years */
			return sprintf( __( '%s years', 'cart-rest-api-for-woocommerce' ), $years );
		} elseif ( $years > 0 ) {
			return __( 'a year', 'cart-rest-api-for-woocommerce' );
		}

		// Get the months.
		$months = ( intval( $seconds ) / MONTH_IN_SECONDS ) % 52;
		if ( $months > 1 ) {
			/* translators: %s: Number of months */
			return sprintf( __( '%s months', 'cart-rest-api-for-woocommerce' ), $months );
		} elseif ( $months > 0 ) {
			return __( '1 month', 'cart-rest-api-for-woocommerce' );
		}

		// Get the weeks.
		$weeks = ( intval( $seconds ) / WEEK_IN_SECONDS ) % 52;
		if ( $weeks > 1 ) {
			/* translators: %s: Number of weeks */
			return sprintf( __( '%s weeks', 'cart-rest-api-for-woocommerce' ), $weeks );
		} elseif ( $weeks > 0 ) {
			return __( 'a week', 'cart-rest-api-for-woocommerce' );
		}

		// Get the days.
		$days = ( intval( $seconds ) / DAY_IN_SECONDS ) % 7;
		if ( $days > 1 ) {
			/* translators: %s: Number of days */
			return sprintf( __( '%s days', 'cart-rest-api-for-woocommerce' ), $days );
		} elseif ( $days > 0 ) {
			return __( 'a day', 'cart-rest-api-for-woocommerce' );
		}

		// Get the hours.
		$hours = ( intval( $seconds ) / HOUR_IN_SECONDS ) % 24;
		if ( $hours > 1 ) {
			/* translators: %s: Number of hours */
			return sprintf( __( '%s hours', 'cart-rest-api-for-woocommerce' ), $hours );
		} elseif ( $hours > 0 ) {
			return __( 'an hour', 'cart-rest-api-for-woocommerce' );
		}

		// Get the minutes.
		$minutes = ( intval( $seconds ) / MINUTE_IN_SECONDS ) % 60;
		if ( $minutes > 1 ) {
			/* translators: %s: Number of minutes */
			return sprintf( __( '%s minutes', 'cart-rest-api-for-woocommerce' ), $minutes );
		} elseif ( $minutes > 0 ) {
			return __( 'a minute', 'cart-rest-api-for-woocommerce' );
		}

		// Get the seconds.
		$seconds = intval( $seconds ) % 60;
		if ( $seconds > 1 ) {
			/* translators: %s: Number of seconds */
			return sprintf( __( '%s seconds', 'cart-rest-api-for-woocommerce' ), $seconds );
		} elseif ( $seconds > 0 ) {
			return __( 'a second', 'cart-rest-api-for-woocommerce' );
		}
	} // END scv2_seconds_to_words()

	/**
	 * Check how long SCV2 has been active for.
	 */
	public static function scv2_active_for( $seconds = '' ) {
		if ( empty( $seconds ) ) {
			return true;
		}

		// Getting install timestamp.
		$scv2_installed = get_option( 'scv2_install_date', false );

		if ( false === $scv2_installed ) {
			return false;
		}

		return ( ( time() - $scv2_installed ) >= $seconds );
	} // END scv2_active_for()

	/**
	 * Get current admin page URL.
	 */
	public static function scv2_get_current_admin_url() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );

		if ( ! $uri ) {
			return '';
		}

		return remove_query_arg( array( 'scv2-hide-notice', '_scv2_notice_nonce' ), admin_url( $uri ) );
	} // END scv2_get_current_admin_url()

	/**
	 * Determines if the server environment is compatible with this plugin.
	 */
	public static function is_environment_compatible() {
		return version_compare( PHP_VERSION, SCV2::$required_php, '>=' );
	} // END is_environment_compatible()

	/**
	 * Gets the message for display when the environment is incompatible with this plugin.
	 */
	public static function get_environment_message() {
		/* translators: 1: SCV2, 2: Required PHP version */
		return sprintf( esc_html__( 'The minimum PHP version required for %1$s is %2$s. You are running %3$s.', 'cart-rest-api-for-woocommerce' ), 'SCV2', SCV2::$required_php, self::get_php_version() );
	} // END get_environment_message()

	/**
	 * Collects the additional data necessary for the shortlink.
	 */
	protected static function collect_additional_shortlink_data() {
		$memory = WP_MEMORY_LIMIT;

		if ( function_exists( 'wc_let_to_num' ) ) {
			$memory = wc_let_to_num( $memory );
		}

		if ( function_exists( 'memory_get_usage' ) ) {
			$system_memory = @ini_get( 'memory_limit' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

			if ( function_exists( 'wc_let_to_num' ) ) {
				$system_memory = wc_let_to_num( $system_memory );
			}

			$memory = max( $memory, $system_memory );
		}

		// WordPress 5.5+ environment type specification.
		// 'production' is the default in WP, thus using it as a default here, too.
		$environment_type = 'production';
		if ( function_exists( 'wp_get_environment_type' ) ) {
			$environment_type = wp_get_environment_type();
		}

		return array(
			'php_version'      => self::get_php_version(),
			'wp_version'       => self::get_wordpress_version(),
			'wc_version'       => self::get_wc_version(),
			'scv2_version'   => self::get_scv2_version(),
			'days_active'      => self::get_days_active(),
			'debug_mode'       => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Yes' : 'No',
			'memory_limit'     => size_format( $memory ),
			'user_language'    => self::get_user_language(),
			'multisite'        => is_multisite() ? 'Yes' : 'No',
			'environment_type' => $environment_type,
		);
	} // END collect_additional_shortlink_data()

	/**
	 * Builds a URL to use in the plugin as shortlink.
	 */
	public static function build_shortlink( $url ) {
		return add_query_arg( self::collect_additional_shortlink_data(), $url );
	} // END build_shortlink()

	/**
	 * Gets the current site's PHP version, without the extra info.
	 */
	private static function get_php_version() {
		$version = explode( '.', PHP_VERSION );

		return (int) $version[0] . '.' . (int) $version[1];
	} // END get_php_version()

	/**
	 * Gets the current site's WordPress version.
	 */
	protected static function get_wordpress_version() {
		return $GLOBALS['wp_version'];
	} // END get_wordpress_version()

	/**
	 * Gets the number of days the plugin has been active.
	 */
	private static function get_days_active() {
		$date_activated = get_option( 'scv2_install_date', time() );
		$datediff       = ( time() - $date_activated );
		$days           = (int) round( $datediff / DAY_IN_SECONDS );

		return $days;
	} // END get_days_active()

	/**
	 * Gets the user's language.
	 */
	private static function get_user_language() {
		if ( function_exists( 'get_user_locale' ) ) {
			return get_user_locale();
		}

		return false;
	} // END get_user_language()

	/**
	 * Checks if SCV2 is white labelled.
	 */
	public static function is_white_labelled() {
		if ( ! defined( 'SCV2_WHITE_LABEL' ) || false === SCV2_WHITE_LABEL ) {
			return false;
		}

		return true;
	} // END is_white_labelled()

} // END class

return new SCV2_Helpers();
