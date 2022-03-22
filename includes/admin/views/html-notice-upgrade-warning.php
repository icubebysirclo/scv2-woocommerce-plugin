<?php
/**
 * Admin View: Upgrade Warning Notice.
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
			<h3><?php esc_html_e( 'Thank you for getting me this far!', 'cart-rest-api-for-woocommerce' ); ?></h3>

			<?php
			$campaign_args = array(
				'utm_medium'   => 'scv2-lite',
				'utm_source'   => 'plugins-page',
				'utm_campaign' => 'plugins-row',
				'utm_content'  => 'go-pro',
			);
			$store_url     = SCV2_Helpers::build_shortlink( add_query_arg( $campaign_args, SCV2_STORE_URL . 'pro/' ) );
			?>

			<p>
				<?php
				echo sprintf(
					/* translators: Nothing to translate. */
					__( 'Version %1$s%5$s%2$s of %3$s will be coming in the future and will provide a %1$sNEW and improved REST API%2$s plus %1$sNEW filters for developers%2$s. As this is a free plugin, ❤️ %6$sdonations%8$s or a 🛒 %7$spurchase of %4$s %8$s helps maintenance and support of these new improvements. If you like using %3$s and are able to contribute in either way, it would be greatly appreciated. 🙂 Thank you.', 'cart-rest-api-for-woocommerce' ),
					'<strong>',
					'</strong>',
					'SCV2',
					'SCV2 Pro',
					esc_attr( SCV2_NEXT_VERSION ),
					'<a href="https://www.buymeacoffee.com/sebastien" target="_blank">',
					'<a href="' . esc_url( $store_url ) . '" target="_blank">',
					'</a>'
				);
				?>
			</p>
		</div>

		<div class="scv2-action">
			<?php printf( '<a href="%1$s" class="button button-primary scv2-button" target="_blank">%2$s</a>', esc_url( SCV2_STORE_URL . 'contact/' ), esc_html__( 'Sign Up to Test', 'cart-rest-api-for-woocommerce' ) ); ?>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'scv2-hide-notice', 'upgrade_warning', SCV2_Helpers::scv2_get_current_admin_url() ), 'scv2_hide_notices_nonce', '_scv2_notice_nonce' ) ); ?>" class="no-thanks" aria-label="<?php echo esc_html__( 'Hide this notice forever.', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'Don\'t ask me again', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
