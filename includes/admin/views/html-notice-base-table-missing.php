<?php
/**
 * Admin View: Notice - Base table missing.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-info scv2-notice">
	<div class="scv2-notice-inner">
		<div class="scv2-notice-icon">
			<img src="<?php echo esc_url( SCV2_URL_PATH . '/assets/images/logo.jpg' ); ?>" alt="SCV2 Logo" />
		</div>

		<div class="scv2-notice-content">
			<h3><?php esc_html_e( 'Database table missing', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p>
				<?php
				$verify_db_tool_available = array_key_exists( 'scv2_verify_db_tables', WC_Admin_Status::get_tools() );
				$missing_tables           = get_option( 'scv2_schema_missing_tables' );
				if ( $verify_db_tool_available ) {
					echo wp_kses_post(
						sprintf(
						/* translators: %1%s: Missing table (separated by ",") %2$s: Link to check again */
							__( 'One table is required for SCV2 to function is missing and will not work as expected. Missing table: %1$s. <a href="%2$s">Check again.</a>', 'cart-rest-api-for-woocommerce' ),
							esc_html( implode( ', ', $missing_tables ) ),
							wp_nonce_url( admin_url( 'admin.php?page=wc-status&tab=tools&action=scv2_verify_db_tables' ), 'debug_action' )
						)
					);
				} else {
					echo wp_kses_post(
						sprintf(
						/* translators: %1%s: Missing table (separated by ",") */
							__( 'One table is required for SCV2 to function is missing and will not work as expected. Missing table: %1$s.', 'cart-rest-api-for-woocommerce' ),
							esc_html( implode( ', ', $missing_tables ) )
						)
					);
				}
				?>
			</p>
		</div>

		<div class="scv2-action">
			<a class="button button-primary scv2-button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'scv2-hide-notice', 'base_tables_missing', SCV2_Helpers::scv2_get_current_admin_url() ), 'scv2_hide_notices_nonce', '_scv2_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
