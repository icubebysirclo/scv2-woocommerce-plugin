<?php
/**
 * SCV2 - Autoloader.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_Autoloader' ) ) {

	class SCV2_Autoloader {

		/**
		 * Path to the includes directory.
		 */
		private $include_path = '';

		/**
		 * The Constructor.
		 */
		public function __construct() {
			if ( function_exists( '__autoload' ) ) {
				spl_autoload_register( '__autoload' );
			}

			spl_autoload_register( array( $this, 'autoload' ) );

			$this->include_path = untrailingslashit( plugin_dir_path( SCV2_FILE ) ) . '/includes/';
		}

		/**
		 * Take a class name and turn it into a file name.
		 */
		private function get_file_name_from_class( $class ) {
			return 'class-' . str_replace( '_', '-', $class ) . '.php';
		} // END get_file_name_from_class()

		/**
		 * Include a class file.
		 */
		private function load_file( $path ) {
			if ( $path && is_readable( $path ) ) {
				include_once $path;
				return true;
			}
			return false;
		} // END load_file()

		/**
		 * Auto-load SCV2 classes on demand to reduce memory consumption.
		 */
		public function autoload( $class ) {
			$class = strtolower( $class );

			if ( 0 !== strpos( $class, 'scv2_' ) ) {
				return;
			}

			$file = $this->get_file_name_from_class( $class );
			$path = '';

			if ( 0 === strpos( $class, 'scv2_admin' ) ) {
				$path = $this->include_path . 'admin/';
			} elseif ( 0 === strpos( $class, 'scv2_wc_admin_notes_' ) ) {
				$path = $this->include_path . 'admin/notes/';
			}

			if ( empty( $path ) || ! $this->load_file( $path . $file ) ) {
				$this->load_file( $this->include_path . $file );
			}
		} // END autoload()

	} // END class.

} // END if class exists.

new SCV2_Autoloader();
