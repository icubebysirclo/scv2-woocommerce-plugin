<?php
/**
 * Admin View: Plugin Review Notice.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user = wp_get_current_user(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

$time = SCV2_Helpers::scv2_seconds_to_words( time() - self::$install_date );
?>
<div class="notice notice-info scv2-notice">
	<div class="scv2-notice-inner">
		<div class="scv2-notice-icon">
			<img src="<?php echo esc_url( SCV2_URL_PATH . '/assets/images/logo.jpg' ); ?>" alt="SCV2 Logo" />
		</div>

		<div class="scv2-notice-content">
			<h3>
				<?php
				printf(
					/* translators: 1: Display name of current user. 2: SCV2 */
					esc_html__( 'Hi %1$s, are you enjoying %2$s?', 'cart-rest-api-for-woocommerce' ),
					esc_html( $current_user->display_name ),
					'SCV2'
				);
				?>
			</h3>
			<p>
				<?php
				printf(
					/* translators: 1: SCV2 2: Time since installed. */
					esc_html__( 'You have been using %1$s for %2$s now! Mind leaving a review and let me know know what you think of the plugin? I\'d really appreciate it!', 'cart-rest-api-for-woocommerce' ), 'SCV2', esc_html( $time )
				);
				?>
			</p>
		</div>

		<div class="scv2-action">
			<?php printf( '<a href="%1$s" class="button button-primary scv2-button" aria-label="' . esc_html__( 'Leave a Review', 'cart-rest-api-for-woocommerce' ) . '" target="_blank">%2$s</a>', esc_url( SCV2_REVIEW_URL . '?rate=5#new-post' ), esc_html__( 'Leave a Review', 'cart-rest-api-for-woocommerce' ) ); ?>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'scv2-hide-notice', 'plugin_review', SCV2_Helpers::scv2_get_current_admin_url() ), 'scv2_hide_notices_nonce', '_scv2_notice_nonce' ) ); ?>" class="no-thanks" aria-label="<?php echo esc_html__( 'Hide this notice forever.', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'No thank you / I already have', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
