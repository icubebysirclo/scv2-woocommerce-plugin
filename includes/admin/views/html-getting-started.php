<?php
/**
 * Admin View: Getting Started.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$campaign_args = SCV2_Helpers::scv2_campaign(
	array(
		'utm_content' => 'getting-started',
	)
);
?>
<div class="wrap scv2 getting-started">

	<div class="container">
		<div class="content">
			<div class="logo" style="background-color: #ffffff; padding: 40px; margin: 0px;">
				<a href="<?php echo esc_url( SCV2_STORE_URL ); ?>" target="_blank">
					<img src="<?php echo esc_url( SCV2_URL_PATH . '/assets/images/logo.png' ); ?>" alt="SCV2 Logo" />
				</a>
			</div>

			<h1 style="text-align: center;">
				<?php
				printf(
					/* translators: 1: SCV2 */
					esc_html__( 'Welcome to %s', 'cart-rest-api-for-woocommerce' ),
					'Swift Checkout V2'
				);
				?>
			</h1>
		</div>
	</div>
</div>
