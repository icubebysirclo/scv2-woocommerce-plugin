<?php
/**
 * SCV2 REST API logger.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SCV2 REST API logger class.
 */
class SCV2_Logger {

	public static $logger;

	/**
	 * Log issues or errors within SCV2.
	 */
	public static function log( $message, $type, $plugin = 'scv2-lite' ) {
		if ( ! class_exists( 'WC_Logger' ) ) {
			return;
		}

		if ( apply_filters( 'scv2_logging', true, $type, $plugin ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			if ( empty( self::$logger ) ) {
				self::$logger = wc_get_logger();
			}

			if ( 'scv2-lite' === $plugin ) {
				$log_entry = "\n" . '====Swift Checkout V2 Version: ' . SCV2_VERSION . '====' . "\n";
				$context   = array( 'source' => 'scv2-lite' );
			} elseif ( 'scv2-pro' === $plugin ) {
				$log_entry = "\n" . '====SCV2 Pro Version: ' . SCV2_PRO_VERSION . '====' . "\n";
				$context   = array( 'source' => 'scv2-pro' );
			} else {
				/* translators: %1$s: Log entry name, %2$s: log entry version */
				$log_entry = "\n" . sprintf( esc_html__( '====%1$s Version: %2$s====', 'cart-rest-api-for-woocommerce' ), apply_filters( 'scv2_log_entry_name', '', $plugin ), apply_filters( 'scv2_log_entry_version', '', $plugin ) ) . "\n";
				$context   = array( 'source' => apply_filters( 'scv2_log_entry_source', '' ) );
			}

			$log_time = date_i18n( get_option( 'date_format' ) . ' g:ia', current_time( 'timestamp' ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested

			$log_entry .= '====Start Log ' . $log_time . '====' . "\n" . $message . "\n";
			$log_entry .= '====End Log====' . "\n\n";

			switch ( $type ) {
				// Interesting events.
				case 'info':
					self::$logger->info( $log_entry, $context );
					break;
				// Normal but significant events.
				case 'notice':
					self::$logger->notice( $log_entry, $context );
					break;
				// Exceptional occurrences that are not errors.
				case 'warning':
					self::$logger->warning( $log_entry, $context );
					break;
				// Runtime errors that do not require immediate.
				case 'error':
					self::$logger->error( $log_entry, $context );
					break;
				// Critical conditions.
				case 'critical':
					self::$logger->critical( $log_entry, $context );
					break;
				// Action must be taken immediately.
				case 'alert':
					self::$logger->alert( $log_entry, $context );
					break;
				// System is unusable.
				case 'emergency':
					self::$logger->emergency( $log_entry, $context );
					break;
				// Detailed debug information.
				case 'debug':
				default:
					self::$logger->debug( $log_entry, $context );
					break;
			}
		}
	} // END log()

} // END class
