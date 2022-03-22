<?php
/**
 * Allows you to update SCV2 via CLI.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCV2_CLI_Update_Command {

	/**
	 * Registers the update command.
	 */
	public static function register_commands() {
		WP_CLI::add_command(
			'scv2 update', // Command.
			array( __CLASS__, 'update' ), // Callback.
			array( // Arguments.
				'shortdesc' => __( 'Updates the SCV2 database.', 'cart-rest-api-for-woocommerce' ),
			)
		);
	}

	/**
	 * Runs all pending SCV2 database updates.
	 */
	public static function update() {
		global $wpdb;

		$wpdb->hide_errors();

		include_once SCV2_ABSPATH . 'includes/class-scv2-install.php';
		include_once SCV2_ABSPATH . 'includes/scv2-update-functions.php';

		$current_db_version = get_option( 'scv2_db_version' );
		$update_count       = 0;
		$callbacks          = SCV2_Install::get_db_update_callbacks();
		$callbacks_to_run   = array();

		foreach ( $callbacks as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					$callbacks_to_run[] = $update_callback;
				}
			}
		}

		if ( empty( $callbacks_to_run ) ) {
			// Ensure DB version is set to the current WC version to match WP-Admin update routine.
			SCV2_Install::update_db_version();

			/* translators: %s Database version number */
			WP_CLI::success( sprintf( __( 'No updates required. Database version is %s', 'cart-rest-api-for-woocommerce' ), get_option( 'scv2_db_version' ) ) );
			return;
		}

		/* translators: 1: Number of database updates 2: List of update callbacks */
		WP_CLI::log( sprintf( __( 'Found %1$d updates (%2$s)', 'cart-rest-api-for-woocommerce' ), count( $callbacks_to_run ), implode( ', ', $callbacks_to_run ) ) );

		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Updating database', 'cart-rest-api-for-woocommerce' ), count( $callbacks_to_run ) );

		foreach ( $callbacks_to_run as $update_callback ) {
			call_user_func( $update_callback );
			$result = false;
			while ( $result ) {
				$result = (bool) call_user_func( $update_callback );
			}
			$update_count ++;
			$progress->tick();
		}

		$progress->finish();

		/* translators: 1: Number of database updates performed 2: Database version number */
		WP_CLI::success( sprintf( __( '%1$d update functions completed. Database version is %2$s', 'cart-rest-api-for-woocommerce' ), absint( $update_count ), get_option( 'scv2_db_version' ) ) );
	} // END update()

} // END class
