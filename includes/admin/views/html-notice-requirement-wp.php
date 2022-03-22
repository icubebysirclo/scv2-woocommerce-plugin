<?php
/**
 * Admin View: WordPress Requirement Notice.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-error">
	<p>
		<?php
		echo sprintf(
			/* translators: 1: <strong>, 2: </strong>, 3: SCV2, 4: Required WordPress version number */
			__( 'Sorry, %1$s%3$s%2$s requires WordPress %4$s or higher. Please upgrade your WordPress setup.', 'cart-rest-api-for-woocommerce' ),
			'<strong>',
			'</strong>',
			esc_attr( 'SCV2' ),
			SCV2::$required_wp
		);
		?>
	</p>
</div>
