<?php
/**
 * SCV2 - Installation related functions and actions.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_Install' ) ) {

	class SCV2_Install {

		/**
		 * DB updates and callbacks that need to be run per version.
		 */
		private static $db_updates = array(
			'1.0.0' => array(
				'scv2_update_300_db_structure',
				'scv2_update_300_db_version',
			),
		);

		/**
		 * Constructor.
		 */
		public static function init() {
			// Checks version of SCV2 and install/update if needed.
			add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
			add_action( 'init', array( __CLASS__, 'manual_database_update' ), 20 );
			add_action( 'scv2_run_update_callback', array( __CLASS__, 'run_update_callback' ) );
			add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );

			// Redirect to Getting Started page once activated.
			add_action( 'activated_plugin', array( __CLASS__, 'redirect_getting_started' ), 10, 2 );

			// Drop tables when MU blog is deleted.
			add_filter( 'wpmu_drop_tables', array( __CLASS__, 'wpmu_drop_tables' ) );
		} // END init()

		/**
		 * Check plugin version and run the updater if necessary.
		 */
		public static function check_version() {
			if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( get_option( 'scv2_version' ), SCV2_VERSION, '<' ) && current_user_can( 'install_plugins' ) ) {
				self::install();
				do_action( 'scv2_updated' );
			}
		} // END check_version()

		/**
		 * Perform a manual database update when triggered by WooCommerce System Tools.
		 */
		public static function manual_database_update() {
			$blog_id = get_current_blog_id();

			add_action( 'wp_' . $blog_id . '_scv2_updater_cron', array( __CLASS__, 'run_manual_database_update' ) );
		} // END manual_database_update()

		/**
		 * Run manual database update.
		 */
		public static function run_manual_database_update() {
			self::update();
		} // END run_manual_database_update()

		/**
		 * Run an update callback when triggered by ActionScheduler.
		 */
		public static function run_update_callback( $callback ) {
			include_once dirname( __FILE__ ) . '/scv2-update-functions.php';

			if ( is_callable( $callback ) ) {
				self::run_update_callback_start( $callback );
				$result = (bool) call_user_func( $callback );
				self::run_update_callback_end( $callback, $result );
			}
		} // END run_update_callback()

		/**
		 * Triggered when a callback will run.
		 */
		protected static function run_update_callback_start( $callback ) {
			define( 'SCV2_UPDATING', true );
		} // END run_update_callback_start()

		/**
		 * Triggered when a callback has ran.
		 */
		protected static function run_update_callback_end( $callback, $result ) {
			if ( $result ) {
				WC()->queue()->add(
					'scv2_run_update_callback',
					array(
						'update_callback' => $callback,
					),
					'scv2-db-updates'
				);
			}
		} // END run_update_callback_end()

		/**
		 * Install actions when a update button is clicked within the admin area.
		 */
		public static function install_actions() {
			if ( ! empty( $_GET['do_update_scv2'] ) ) {
				check_admin_referer( 'scv2_db_update', 'scv2_db_update_nonce' );
				self::update();
				SCV2_Admin_Notices::add_notice( 'update_db', true );
			}
		} // END install_actions()

		/**
		 * Install SCV2.
		 */
		public static function install() {
			if ( ! is_blog_installed() ) {
				return;
			}

			if ( ! version_compare( get_option( 'scv2_version' ), SCV2_VERSION, '<' ) ) {
				return;
			}

			// Check if we are not already running this routine.
			if ( 'yes' === get_transient( 'scv2_installing' ) ) {
				return;
			}

			// If we made it till here nothing is running yet, lets set the transient now for five minutes.
			set_transient( 'scv2_installing', 'yes', MINUTE_IN_SECONDS * 5 );
			if ( ! defined( 'SCV2_INSTALLING' ) ) {
				define( 'SCV2_INSTALLING', true );
			}

			// Remove all admin notices.
			self::remove_admin_notices();

			// Install database tables.
			self::create_tables();
			self::verify_base_tables();

			// Creates cron jobs.
			self::create_cron_jobs();

			// Create files.
			self::create_files();

			// Set activation date.
			self::set_install_date();

			// Update plugin version.
			self::update_version();

			// Maybe update database version.
			self::maybe_update_db_version();

			delete_transient( 'scv2_installing' );

			do_action( 'scv2_installed' );
		} // END install()

		/**
		 * Check if all the base tables are present.
		 */
		public static function verify_base_tables( $modify_notice = true, $execute = false ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			if ( $execute ) {
				self::create_tables();
			}

			$queries        = dbDelta( self::get_schema(), false );
			$missing_tables = array();

			foreach ( $queries as $table_name => $result ) {
				if ( "Created table $table_name" === $result ) {
					$missing_tables[] = $table_name;
				}
			}

			if ( 0 < count( $missing_tables ) ) {
				if ( $modify_notice ) {
					SCV2_Admin_Notices::add_notice( 'base_tables_missing' );
				}

				update_option( 'scv2_schema_missing_tables', $missing_tables );
			} else {
				if ( $modify_notice ) {
					SCV2_Admin_Notices::remove_notice( 'base_tables_missing' );
				}

				delete_option( 'scv2_schema_missing_tables' );
			}

			return $missing_tables;
		} // END verify_base_tables()

		/**
		 * Reset any notices added to admin.
		 */
		private static function remove_admin_notices() {
			if ( ! class_exists( 'SCV2_Admin_Notices' ) ) {
				include_once SCV2_ABSPATH . 'includes/admin/class-scv2-admin-notices.php';
			}
			SCV2_Admin_Notices::remove_all_notices();
		} // END remove_admin_notices()

		/**
		 * Is this a brand new SCV2 install?
		 */
		public static function is_new_install() {
			return is_null( get_option( 'scv2_version', null ) );
		} // END is_new_install()

		/**
		 * Is a Database update needed?
		 */
		public static function needs_db_update() {
			$current_db_version = get_option( 'scv2_db_version', null );
			$updates            = self::get_db_update_callbacks();
			$update_versions    = array_keys( $updates );
			usort( $update_versions, 'version_compare' );

			return ! is_null( $current_db_version ) && version_compare( $current_db_version, end( $update_versions ), '<' );
		} // END needs_db_update()

		/**
		 * See if we need to show or run database updates during install.
		 */
		private static function maybe_update_db_version() {
			if ( self::needs_db_update() ) {
				if ( apply_filters( 'scv2_enable_auto_update_db', false ) ) {
					self::update();
				} else {
					SCV2_Admin_Notices::add_notice( 'update_db', true );
				}
			} else {
				self::update_db_version();
			}
		} // END maybe_update_db_version()

		/**
		 * Update plugin version to current.
		 */
		private static function update_version() {
			update_option( 'scv2_version', SCV2_VERSION );
		} // END update_version()

		/**
		 * Get list of DB update callbacks.
		 */
		public static function get_db_update_callbacks() {
			return self::$db_updates;
		} // END get_db_update_callbacks()

		/**
		 * Push all needed DB updates to the queue for processing.
		 */
		private static function update() {
			$current_db_version = get_option( 'scv2_db_version' );
			$loop               = 0;

			foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {
				if ( version_compare( $current_db_version, $version, '<' ) ) {
					foreach ( $update_callbacks as $update_callback ) {
						WC()->queue()->schedule_single(
							time() + $loop,
							'scv2_run_update_callback',
							array(
								'update_callback' => $update_callback,
							),
							'scv2-db-updates'
						);
						$loop++;
					}
				}
			}
		} // END update()

		/**
		 * Update DB version to current.
		 */
		public static function update_db_version( $version = null ) {
			delete_option( 'scv2_db_version' );
			add_option( 'scv2_db_version', is_null( $version ) ? SCV2_DB_VERSION : $version );
		} // END update_db_version()

		/**
		 * Set the time the plugin was installed.
		 */
		public static function set_install_date() {
			add_option( 'scv2_install_date', time() );
		} // END set_install_date()

		/**
		 * Redirects to the Getting Started page upon plugin activation.
		 */
		public static function redirect_getting_started( $plugin, $network_activation ) {
			// Prevent redirect if plugin name does not match or multiple plugins are being activated.
			if ( plugin_basename( SCV2_FILE ) !== $plugin || isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}

			$page = admin_url( 'admin.php' );

			$getting_started = add_query_arg(
				array(
					'page'    => 'scv2',
					'section' => 'getting-started',
				),
				$page
			);

			/**
			 * Should SCV2 be installed via WP-CLI,
			 * display a link to the Getting Started page.
			 */
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				WP_CLI::log(
					WP_CLI::colorize(
						/* translators: %1$s: message, %2$s: URL, %3$s: SCV2 */
						'%y' . sprintf( '🎉 %1$s %2$s', __( 'Get started with %3$s here:', 'cart-rest-api-for-woocommerce' ), $getting_started, 'SCV2' ) . '%n'
					)
				);
				return;
			}

			wp_safe_redirect( $getting_started );
			exit;
		} // END redirect_getting_started()

		/**
		 * Create cron jobs (clear them first).
		 */
		private static function create_cron_jobs() {
			wp_clear_scheduled_hook( 'scv2_cleanup_carts' );

			wp_schedule_event( time() + ( 6 * HOUR_IN_SECONDS ), 'twicedaily', 'scv2_cleanup_carts' );
		} // END create_cron_jobs()

		/**
		 * Creates database tables which the plugin needs to function.
		 * WARNING: If you are modifying this method, make sure that its safe to call regardless of the state of database.
		 *
		 * This is called from `install` method and is executed in-sync when SCV2 is installed or updated.
		 * This can also be called optionally from `verify_base_tables`.
		 */
		private static function create_tables() {
			global $wpdb;

			$wpdb->hide_errors();

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			dbDelta( self::get_schema() );
		} // END create_tables()

		/**
		 * Get Table schema.
		 * @global $wpdb
		 * @return string
		 */
		private static function get_schema() {
			global $wpdb;

			$collate = $wpdb->has_cap( 'collation' ) ? $wpdb->get_charset_collate() : '';

			$tables = "CREATE TABLE {$wpdb->prefix}scv2_carts (
				cart_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				cart_key char(42) NOT NULL,
				cart_value longtext NOT NULL,
				cart_billing_address longtext NOT NULL,
				cart_shipping_address longtext NOT NULL,
				cart_shipping longtext NOT NULL,
				cart_payment longtext NOT NULL,
				cart_coupons longtext NOT NULL,
				cart_totals longtext NOT NULL,
				cart_created BIGINT UNSIGNED NOT NULL,
				cart_expiry BIGINT UNSIGNED NOT NULL,
				cart_source varchar(200) NOT NULL,
				cart_hash varchar(200) NOT NULL,
				PRIMARY KEY  (cart_id),
				UNIQUE KEY cart_key (cart_key)
			) $collate;";

			return $tables;
		} // END get_schema()

		/**
		 * Return a list of SCV2 tables. Used to make sure all SCV2 tables
		 * are dropped when uninstalling the plugin in a single site
		 * or multi site environment.
		 */
		public static function get_tables() {
			global $wpdb;

			$tables = array(
				"{$wpdb->prefix}scv2_carts",
			);

			return $tables;
		} // END get_tables()

		/**
		 * Drop SCV2 tables.
		 */
		public static function drop_tables() {
			global $wpdb;

			$tables = self::get_tables();

			foreach ( $tables as $table ) {
				$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}
		} // END drop_tables()

		/**
		 * Uninstall tables when MU blog is deleted.
		 */
		public static function wpmu_drop_tables( $tables ) {
			return array_merge( $tables, self::get_tables() );
		} // END wpmu_drop_tables()

		/**
		 * Create files/directories.
		 */
		private static function create_files() {
			// Bypass if filesystem is read-only and/or non-standard upload system is used.
			if ( apply_filters( 'scv2_install_skip_create_files', false ) ) {
				return;
			}

			// Install files and folders for uploading files and prevent hotlinking.
			$upload_dir = wp_get_upload_dir();

			$files = array(
				array(
					'base'    => $upload_dir['basedir'] . '/scv2_uploads',
					'file'    => 'index.html',
					'content' => '',
				),
				array(
					'base'    => $upload_dir['basedir'] . '/scv2_uploads',
					'file'    => '.htaccess',
					'content' => 'deny from all',
				),
			);

			foreach ( $files as $file ) {
				if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
					$file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'wb' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen

					if ( $file_handle ) {
						fwrite( $file_handle, $file['content'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
						fclose( $file_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
					}
				}
			}
		} // create_files()

	} // END class.

} // END if class exists.

SCV2_Install::init();
