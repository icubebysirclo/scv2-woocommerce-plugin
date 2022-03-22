<?php
/**
 * Admin View: Notice - Updated.
 */

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
			<h3><?php esc_html_e( 'Database updated', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p>
				<?php
				echo sprintf(
					/* translators: %s: SCV2 */
					esc_html__( '%s database update complete. Thank you for updating to the latest version!', 'cart-rest-api-for-woocommerce' ),
					'SCV2'
				);
				?>
			</p>
		</div>

		<div class="scv2-action">
			<a class="button button-primary scv2-button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'scv2-hide-notice', 'update_db', remove_query_arg( 'do_update_scv2', SCV2_Helpers::scv2_get_current_admin_url() ) ), 'scv2_hide_notices_nonce', '_scv2_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
