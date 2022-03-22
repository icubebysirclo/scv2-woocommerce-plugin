<?php
/**
 * SCV2 - WooCommerce Admin: Need Help?
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCV2_WC_Admin_Need_Help_Note extends SCV2_WC_Admin_Notes {

	/**
	 * Name of the note for use in the database.
	 */
	const NOTE_NAME = 'scv2-wc-admin-need-help';

	/**
	 * Constructor
	 */
	public function __construct() {
		self::add_note( self::NOTE_NAME, 8 * DAY_IN_SECONDS );
	}

	/**
	 * Add note.
	 */
	public static function add_note( $note_name = '', $seconds = '', $source = 'scv2' ) {
		parent::add_note( $note_name, $seconds, $source );

		$args = self::get_note_args();

		// If no arguments return then we cant create a note.
		if ( is_array( $args ) && empty( $args ) ) {
			return;
		}

		// Otherwise, create new note.
		self::create_new_note( $args );
	} // END add_note()

	/**
	 * Get note arguments.
	 */
	public static function get_note_args() {
		$status = SCV2_Helpers::is_wc_version_gte_4_8() ? Automattic\WooCommerce\Admin\Notes\Note::E_WC_ADMIN_NOTE_UNACTIONED : Automattic\WooCommerce\Admin\Notes\WC_Admin_Note::E_WC_ADMIN_NOTE_UNACTIONED;

		$campaign_args = SCV2_Helpers::scv2_campaign(
			array(
				'utm_campaign' => 'wc-admin',
				'utm_content'  => 'wc-inbox',
			)
		);

		$args = array(
			'title'   => __( 'Need help with SCV2?', 'cart-rest-api-for-woocommerce' ),
			'content' => __( 'You can ask a question on the support forum, discuss with other SCV2 developers in the Slack community or get priority support.', 'cart-rest-api-for-woocommerce' ),
			'name'    => self::NOTE_NAME,
			'actions' => array(
				array(
					'name'    => 'scv2-learn-more-support',
					'label'   => __( 'Learn more', 'cart-rest-api-for-woocommerce' ),
					'url'     => SCV2_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( SCV2_STORE_URL . 'support/' ) ) ),
					'status'  => $status,
					'primary' => true,
				),
			),
		);

		return $args;
	} // END get_note_args()

} // END class

return new SCV2_WC_Admin_Need_Help_Note();
