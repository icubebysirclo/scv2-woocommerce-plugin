<?php
/**
 * Admin View: Notice - Update
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$update_url = wp_nonce_url(
	add_query_arg( 'do_update_scv2', 'true', SCV2_Helpers::scv2_get_current_admin_url() ),
	'scv2_db_update',
	'scv2_db_update_nonce'
);
?>
<div class="notice notice-info scv2-notice">
	<div class="scv2-notice-inner">
		<div class="scv2-notice-icon">
			<img src="<?php echo esc_url( SCV2_URL_PATH . '/assets/images/logo.jpg' ); ?>" alt="SCV2 Logo" />
		</div>

		<div class="scv2-notice-content">
			<h3>
				<strong>
					<?php
					echo sprintf(
						/* translators: %s: SCV2 */
						esc_html__( '%s database update required', 'cart-rest-api-for-woocommerce' ),
						'SCV2'
					);
					?>
				</strong>
			</h3>
			<p>
				<?php
				echo sprintf(
					/* translators: %s: SCV2 */
					esc_html__( '%s has been updated! To keep things running smoothly, we have to update your database to the newest version.', 'cart-rest-api-for-woocommerce' ),
					'SCV2'
				);

				/* translators: 1: Link to docs 2: Close link. */
				printf( ' ' . esc_html__( 'The database update process runs in the background and may take a little while, so please be patient. Advanced users can alternatively update via %1$sWP CLI%2$s.', 'cart-rest-api-for-woocommerce' ), '<a href="' . esc_url( SCV2_STORE_URL . 'upgrading-the-database-using-wp-cli/' ) . '" target="_blank">', '</a>' );
				?>
			</p>
		</div>

		<div class="scv2-action">
			<a href="<?php echo esc_url( $update_url ); ?>" class="button button-primary scv2-button">
				<?php
				echo sprintf(
					/* translators: %s: SCV2 */
					esc_html__( 'Update %s Database', 'cart-rest-api-for-woocommerce' ),
					'SCV2'
				);
				?>
			</a>
			<span class="no-thanks"><a href="http://getswift.asia/how-to-update-scv2/" target="_blank">
				<?php esc_html_e( 'Learn more about updates', 'cart-rest-api-for-woocommerce' ); ?>
			</a></span>
		</div>
	</div>
</div>
