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
			<div class="logo">
				<a href="<?php echo esc_url( SCV2_STORE_URL ); ?>" target="_blank">
					<img src="<?php echo esc_url( SCV2_URL_PATH . '/assets/images/logo.jpg' ); ?>" alt="SCV2 Logo" />
				</a>
			</div>

			<h1>
				<?php
				printf(
					/* translators: 1: SCV2 */
					esc_html__( 'Welcome to %s.', 'cart-rest-api-for-woocommerce' ),
					'SCV2'
				);
				?>
			</h1>
			<form action="options.php" method="post">
    <?php 
    settings_fields( 'nelio_example_plugin_settings' );
    do_settings_sections( 'nelio_example_plugin' );
    ?>
    <input
      type="submit"
      name="submit"
      class="button button-primary"
      value="<?php esc_attr_e( 'Save' ); ?>"
    />
  </form>
		</div>
	</div>
</div>
