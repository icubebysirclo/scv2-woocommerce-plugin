<?php
/**
 * SCV2 Uninstall
 *
 * Uninstalling SCV2 deletes tables and options.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb, $wp_version;

wp_clear_scheduled_hook( 'scv2_cleanup_carts' );

/**
 * Only remove ALL plugin data and database table if SCV2_REMOVE_ALL_DATA constant is
 * set to true in user's wp-config.php. This is to prevent data loss when deleting the
 * plugin from the backend and to ensure only the site owner can perform this action.
 */
if ( defined( 'SCV2_REMOVE_ALL_DATA' ) && true === SCV2_REMOVE_ALL_DATA ) {
	// Drop Tables.
	require_once dirname( __FILE__ ) . '/includes/class-scv2-install.php';
	SCV2_Install::drop_tables();

	// Delete options.
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'scv2\_%';" );

	// Delete usermeta.
	$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'scv2\_%';" );

	// Delete sitemeta. Multi-site only!
	if ( is_multisite() ) {
		$wpdb->query( "DELETE FROM $wpdb->sitemeta WHERE meta_key LIKE 'scv2\_%';" );
	}

	require_once dirname( __FILE__ ) . '/includes/class-scv2-helpers.php';

	// Delete WooCommerce Admin Notes.
	if (
		class_exists( 'Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes' ) ||
		class_exists( 'Automattic\WooCommerce\Admin\Notes\Notes' )
	) {

		if ( SCV2_Helpers::is_wc_version_gte_4_8() ) {
			Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'scv2-wc-admin-activate-pro' );
			Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'scv2-wc-admin-do-with-products' );
			Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'scv2-wc-admin-help-improve' );
			Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'scv2-wc-admin-need-help' );
			Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'scv2-wc-admin-thanks-install' );
			Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'scv2-wc-admin-upgrade-pro' );
		} else {
			Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes::delete_notes_with_name( 'scv2-wc-admin-activate-pro' );
			Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes::delete_notes_with_name( 'scv2-wc-admin-do-with-products' );
			Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes::delete_notes_with_name( 'scv2-wc-admin-help-improve' );
			Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes::delete_notes_with_name( 'scv2-wc-admin-need-help' );
			Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes::delete_notes_with_name( 'scv2-wc-admin-thanks-install' );
			Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes::delete_notes_with_name( 'scv2-wc-admin-upgrade-pro' );
		}
	}

	// Clear any cached data that has been removed.
	wp_cache_flush();
}
