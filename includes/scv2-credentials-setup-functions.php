<?php
/**
 * Swift Checkout V2 Credentials Setup
 */
add_action('admin_init', 'custom_plugin_register_settings');

function custom_plugin_register_settings() {
	// Register setup
	register_setting('custom_plugin_options_group', 'scv2_is_production');
	register_setting('custom_plugin_options_group', 'scv2_brand_id_production');
	register_setting('custom_plugin_options_group', 'scv2_url_production');
	register_setting('custom_plugin_options_group', 'scv2_brand_id_staging');
	register_setting('custom_plugin_options_group', 'scv2_url_staging');
}