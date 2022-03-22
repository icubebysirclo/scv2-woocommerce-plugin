<?php
/**
 * Admin View: Notice - Updating
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pending_actions_url = admin_url( 'admin.php?page=wc-status&tab=action-scheduler&s=scv2_run_update&status=pending' );
$cron_disabled       = ! defined( 'DISABLE_WP_CRON' ) ? false : true;
$cron_cta            = $cron_disabled ? __( 'You can manually run queued updates here.', 'cart-rest-api-for-woocommerce' ) : __( 'View progress &rarr;', 'cart-rest-api-for-woocommerce' );
?>
<div class="notice notice-info scv2-notice">
	<p>
		<strong>
			<?php
			echo sprintf(
				/* translators: %s: SCV2 */
				esc_html__( '%s database update', 'cart-rest-api-for-woocommerce' ),
				'SCV2'
			);
			?>
		</strong><br>
		<?php
		echo sprintf(
			/* translators: %s: SCV2 */
			esc_html__( '%s is updating the database in the background. The database update process may take a little while, so please be patient.', 'cart-rest-api-for-woocommerce' ),
			'SCV2'
		);
		?>
		<?php
		if ( $cron_disabled ) {
			echo '<br>' . esc_html__( 'Note: WP CRON has been disabled on your install which may prevent this update from completing.', 'cart-rest-api-for-woocommerce' );
		}
		?>
		&nbsp;<a href="<?php echo esc_url( $pending_actions_url ); ?>"><?php echo esc_html( $cron_cta ); ?></a>
	</p>
</div>
