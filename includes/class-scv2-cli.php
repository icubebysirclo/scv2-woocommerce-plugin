<?php
/**
 * Enables SCV2, via the command line.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Should WP-CLI not exist, just return to prevent the plugin from crashing.
if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

if ( ! class_exists( 'SCV2_CLI' ) ) {

	/**
	 * CLI class.
	 */
	class SCV2_CLI {

		/**
		 * Load required files and hooks to make the CLI work.
		 */
		public function __construct() {
			$this->includes();
			$this->hooks();
		}

		/**
		 * Load command files.
		 */
		private function includes() {
			require_once SCV2_ABSPATH . 'includes/cli/class-scv2-cli-update-command.php';
			require_once SCV2_ABSPATH . 'includes/cli/class-scv2-cli-version-command.php';
		}

		/**
		 * Sets up and hooks WP CLI to SCV2 CLI code.
		 */
		private function hooks() {
			WP_CLI::add_hook( 'after_wp_load', 'SCV2_CLI_Version_Command::register_commands' );
			WP_CLI::add_hook( 'after_wp_load', 'SCV2_CLI_Update_Command::register_commands' );
		}

	} // END class

} // END if class exists

new SCV2_CLI();
