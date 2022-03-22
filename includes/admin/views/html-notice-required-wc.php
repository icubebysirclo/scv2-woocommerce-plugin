<?php
/**
 * Admin View: Required WooCommerce Notice.
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
			<h3><?php echo esc_html__( 'Update Required!', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p>
				<?php
				echo sprintf(
					/* translators: 1: SCV2, 2: WooCommerce, 3: Required WooCommerce version */
					esc_html__( '%1$s requires at least %2$s v%3$s or higher.', 'cart-rest-api-for-woocommerce' ),
					'SCV2',
					'WooCommerce',
					esc_attr( SCV2::$required_woo )
				);
				?>
			</p>
		</div>

		<?php if ( current_user_can( 'update_plugins' ) ) { ?>
		<div class="scv2-action">
			<?php
			$upgrade_url = wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'upgrade-plugin',
						'plugin' => 'woocommerce',
					),
					self_admin_url( 'update.php' )
				),
				'upgrade-plugin_woocommerce'
			);
			$upgrade_url = wp_nonce_url( add_query_arg( 'scv2-hide-notice', 'check_wc', $upgrade_url ), 'scv2_hide_notices_nonce', '_scv2_notice_nonce' );
			?>
			<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary scv2-button" aria-label="<?php echo esc_html__( 'Update WooCommerce', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'Update WooCommerce', 'cart-rest-api-for-woocommerce' ); ?></a>
			<a class="no-thanks" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'scv2-hide-notice', 'check_wc', esc_url( SCV2_Helpers::scv2_get_current_admin_url() ) ), 'scv2_hide_notices_nonce', '_scv2_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
		<?php } ?>
	</div>
</div>
