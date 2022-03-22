<?php
/**
 * Background Updater
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Background_Process', false ) ) {
	include_once WC_ABSPATH . '/abstracts/class-wc-background-process.php';
}

if ( ! class_exists( 'SCV2_Background_Updater' ) ) {

	/**
	 * SCV2_Background_Updater Class.
	 */
	class SCV2_Background_Updater extends WC_Background_Process {

		/**
		 * Initiate new background process.
		 */
		public function __construct() {
			// Uses unique prefix per blog so each blog has separate queue.
			$this->prefix = 'wp_' . get_current_blog_id();
			$this->action = 'scv2_updater';

			parent::__construct();
		}

		/**
		 * Dispatch updater.
		 */
		public function dispatch() {
			$dispatched = parent::dispatch();
			$logger     = new SCV2_Logger();

			if ( is_wp_error( $dispatched ) ) {
				$logger->error(
					/* translators: %s: dispatch error message */
					sprintf( __( 'Unable to dispatch SCV2 updater: %s', 'cart-rest-api-for-woocommerce' ), $dispatched->get_error_message() ),
					array( 'source' => 'scv2_db_updates' )
				);
			}
		}

		/**
		 * Handle cron health check.
		 */
		public function handle_cron_healthcheck() {
			if ( $this->is_process_running() ) {
				// Background process already running.
				return;
			}

			if ( $this->is_queue_empty() ) {
				// No data to process.
				$this->clear_scheduled_event();
				return;
			}

			$this->handle();
		}

		/**
		 * Schedule fallback event.
		 */
		protected function schedule_event() {
			if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
				wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
			}
		}

		/**
		 * Is the updater running?
		 */
		public function is_updating() {
			return false === $this->is_queue_empty();
		}

		/**
		 * Task
		 */
		protected function task( $callback ) {
			wc_maybe_define_constant( 'SCV2_UPDATING', true );

			$logger = new SCV2_Logger();

			include_once dirname( __FILE__ ) . '/scv2-update-functions.php';

			$result = false;

			if ( is_callable( $callback ) ) {
				/* translators: %s: callback function */
				$logger->info( sprintf( __( 'Running %s callback', 'cart-rest-api-for-woocommerce' ), $callback ), array( 'source' => 'scv2_db_updates' ) );
				$result = (bool) call_user_func( $callback, $this );

				if ( $result ) {
					/* translators: %s: callback function */
					$logger->info( sprintf( __( '%s callback needs to run again', 'cart-rest-api-for-woocommerce' ), $callback ), array( 'source' => 'scv2_db_updates' ) );
				} else {
					/* translators: %s: callback function */
					$logger->info( sprintf( __( 'Finished running %s callback', 'cart-rest-api-for-woocommerce' ), $callback ), array( 'source' => 'scv2_db_updates' ) );
				}
			} else {
				/* translators: %s: callback function */
				$logger->notice( sprintf( __( 'Could not find %s callback', 'cart-rest-api-for-woocommerce' ), $callback ), array( 'source' => 'scv2_db_updates' ) );
			}

			return $result ? $callback : false;
		}

		/**
		 * Complete
		 */
		protected function complete() {
			$logger = new SCV2_Logger();
			$logger->info( __( 'Data update complete', 'cart-rest-api-for-woocommerce' ), array( 'source' => 'scv2_db_updates' ) );
			SCV2_Install::update_db_version();
			parent::complete();
		}

		/**
		 * See if the batch limit has been exceeded.
		 */
		public function is_memory_exceeded() {
			return $this->memory_exceeded();
		}

	} // END class.

} // END if class exists.

return new SCV2_Background_Updater();
