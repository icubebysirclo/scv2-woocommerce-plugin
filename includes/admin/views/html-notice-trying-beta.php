<?php
/**
 * Admin View: Trying Beta Notice.
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
			<h3>
			<?php
			echo sprintf(
				/* translators: %s: SCV2 */
				esc_html__( 'Thank you for trying out v%s', 'cart-rest-api-for-woocommerce' ),
				esc_attr( strstr( SCV2_VERSION, '-', true ) )
			);

			if ( SCV2_Helpers::is_scv2_beta() ) {
				echo sprintf(
					/* translators: %s: SCV2 */
					esc_html__( ', a beta release of %s!', 'cart-rest-api-for-woocommerce' ),
					'SCV2'
				);
			}

			if ( SCV2_Helpers::is_scv2_rc() ) {
				echo sprintf(
					/* translators: %s: SCV2 */
					esc_html__( ', a release candidate of %s!', 'cart-rest-api-for-woocommerce' ),
					'SCV2'
				);
			}
			?>
			</h3>
			<p><?php echo esc_html__( 'If you have any questions or any feedback at all, please let me know. Any little bit you\'re willing to share helps the development of SCV2.', 'cart-rest-api-for-woocommerce' ); ?></p>
		</div>

		<div class="scv2-action">
			<?php
				/* translators: 1: Feedback URL, 2: SCV2, 3: Button Text */
				printf( '<a href="%1$s" class="button button-primary scv2-button" aria-label="' . esc_html__( 'Give Feedback for %2$s', 'cart-rest-api-for-woocommerce' ) . '" target="_blank">%3$s</a>',
					esc_url( SCV2_STORE_URL . 'feedback/?wpf674_3=SCV2 v' . SCV2_VERSION ),
					'SCV2',
					esc_html__( 'Give Feedback', 'cart-rest-api-for-woocommerce' )
				);
				?>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'scv2-hide-notice', 'check_beta', SCV2_Helpers::scv2_get_current_admin_url() ), 'scv2_hide_notices_nonce', '_scv2_notice_nonce' ) ); ?>" class="no-thanks" aria-label="<?php echo esc_html__( 'Hide this notice forever.', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'Don\'t ask me again', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
