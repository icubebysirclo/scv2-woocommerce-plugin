<?php
/**
 * Admin View: Custom Notices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-info scv2-notice">
	<div class="scv2-notice-inner">
		<div class="scv2-notice-content">
			<?php echo wp_kses_post( wpautop( $notice_html ) ); ?>
		</div>

		<div class="scv2-action">
			<a class="button button-primary scv2-button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'scv2-hide-notice', $notice, SCV2_Helpers::scv2_get_current_admin_url() ), 'scv2_hide_notices_nonce', '_scv2_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
