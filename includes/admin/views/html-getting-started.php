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
				Swift Checkout V2 Setup
			</h1>

			<div class="wrap">
				<form method="post" action="options.php">

				<?php settings_fields('custom_plugin_options_group'); ?>

				<!-- Setup Mode -->
				<u><h3>Mode</h3></u>
				<table class="form-table">
					<tr>
						<th>
							<label for="scv2_is_production">Is Production:</label>
						</th>
						<td>
							<select name="scv2_is_production" id="scv2_is_production">
								<?php $mode = array( false => 'Not selected', 1 => 'Yes',2 => 'No' );?>
                                <?php foreach ( $mode as $key => $value ) :?>
                                    <option value="<?php echo $key; ?>" <?php echo ( get_option('scv2_is_production') == $key) ? "selected='selected' disabled" : ""; ?>><?php echo $value; ?></option>
                                <?php endforeach; ?>
							</select>
						</td>
					</tr>
				</table>

				<!-- Production Setup -->
				<u><h3>Production</h3></u>
				<table class="form-table">
					<tr>
						<th>
							<label for="scv2_brand_id_production">Brand ID:</label>
						</th>
						<td>
							<input type = 'text' class="regular-text" id="scv2_brand_id_production" name="scv2_brand_id_production" value="<?php echo get_option('scv2_brand_id_production'); ?>">
						</td>
					</tr>
					<tr>
						<th>
							<label for="scv2_url_production">SCV2 URL:</label>
						</th>
						<td>
							<input type = 'text' class="regular-text" id="scv2_url_production" name="scv2_url_production" value="<?php echo get_option('scv2_url_production'); ?>">
						</td>
					</tr>
				</table>

				<!-- Staging Setup -->
				<u><h3>Staging</h3></u>
				<table class="form-table">
					<tr>
						<th>
							<label for="scv2_brand_id_staging">Brand ID:</label>
						</th>
						<td>
							<input type = 'text' class="regular-text" id="scv2_brand_id_staging" name="scv2_brand_id_staging" value="<?php echo get_option('scv2_brand_id_staging'); ?>">
						</td>
					</tr>
					<tr>
						<th>
							<label for="scv2_url_staging">SCV2 URL:</label>
						</th>
						<td>
							<input type = 'text' class="regular-text" id="scv2_url_staging" name="scv2_url_staging" value="<?php echo get_option('scv2_url_staging'); ?>">
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>

			</div>
		</div>
	</div>
</div>
