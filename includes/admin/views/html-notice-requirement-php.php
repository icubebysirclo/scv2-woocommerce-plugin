<?php
/**
 * Admin View: PHP Requirement Notice.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-error">
	<p><?php echo SCV2_Helpers::get_environment_message(); ?></p>
</div>
