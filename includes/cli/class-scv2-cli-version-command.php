<?php
/**
 * Returns the version of SCV2 via CLI.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCV2_CLI_Version_Command {

	/**
	 * Registers the version commands.
	 */
	public static function register_commands() {
		WP_CLI::add_command(
			'scv2 version', // Command.
			array( __CLASS__, 'version' ), // Callback.
			array( // Arguments.
				'shortdesc' => __( 'Returns the version of SCV2 installed.', 'cart-rest-api-for-woocommerce' ),
			)
		);

		WP_CLI::add_command(
			'scv2 db-version', // Command.
			array( __CLASS__, 'db_version' ), // Callback.
			array( // Arguments.
				'shortdesc' => __( 'Returns the database version of SCV2 installed.', 'cart-rest-api-for-woocommerce' ),
			)
		);
	}

	/**
	 * Returns the version of SCV2.
	 */
	public static function version() {
		global $wpdb;

		$wpdb->hide_errors();

		$current_version = get_option( 'scv2_version' );

		WP_CLI::log(
			WP_CLI::colorize(
				/* translators: 2: Version of SCV2 */
				'%y' . sprintf( __( '%1$s Version is %2$s', 'cart-rest-api-for-woocommerce' ), 'SCV2', $current_version )
			)
		);
	} // END version()

	/**
	 * Returns the database version of SCV2.
	 */
	public static function db_version() {
		global $wpdb;

		$wpdb->hide_errors();

		$db_version = get_option( 'scv2_db_version' );

		WP_CLI::log(
			WP_CLI::colorize(
				/* translators: 2: Database Version of SCV2 */
				'%y' . sprintf( __( '%1$s Database Version is %2$s', 'cart-rest-api-for-woocommerce' ), 'SCV2', $db_version )
			)
		);
	} // END db_version()

} // END class

new SCV2_CLI_Version_Command();
