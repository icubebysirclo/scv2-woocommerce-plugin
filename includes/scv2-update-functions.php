<?php
/**
 * SCV2 Updates
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update SCV2 session database structure.
 */
function scv2_update_300_db_structure() {
	global $wpdb;

	$source_exists = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->prefix}scv2_carts WHERE key_name = 'cart_created'" );

	if ( is_null( $source_exists ) ) {
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}scv2_carts ADD `cart_created` BIGINT UNSIGNED NOT NULL AFTER `cart_value`" );
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}scv2_carts ADD `cart_source` VARCHAR(200) NOT NULL AFTER `cart_expiry`" );
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}scv2_carts ADD `cart_hash` VARCHAR(200) NOT NULL AFTER `cart_source`" );
	}
}

/**
 * Update database version to 1.0.0
 */
function scv2_update_300_db_version() {
	SCV2_Install::update_db_version( '1.0.0' );
}
