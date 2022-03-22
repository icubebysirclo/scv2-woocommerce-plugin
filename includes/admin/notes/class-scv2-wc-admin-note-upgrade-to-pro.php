<?php
/**
 * SCV2 - WooCommerce Admin: Upgrade to SCV2 Pro.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCV2_WC_Admin_Upgrade_Pro_Note extends SCV2_WC_Admin_Notes {

	/**
	 * Name of the note for use in the database.
	 */
	const NOTE_NAME = 'scv2-wc-admin-upgrade-pro';

	/**
	 * Constructor
	 */
	public function __construct() {
		self::add_note( self::NOTE_NAME, 30 * DAY_IN_SECONDS );
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

		// Prevent note being created if SCV2 Pro is installed.
		if ( SCV2_Helpers::is_scv2_pro_activated() ) {
			if ( SCV2_Helpers::is_wc_version_gte_4_8() ) {
				Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( $note_name );
			} else {
				Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes::delete_notes_with_name( $note_name );
			}

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
			'title'   => __( 'Ready to take your headless store to the next level?', 'cart-rest-api-for-woocommerce' ),
			'content' => sprintf(
				/* translators: %s: SCV2 Pro. */
				esc_attr__( 'Upgrade to %s and unlock more cart features and supported WooCommerce extensions.', 'cart-rest-api-for-woocommerce' ),
				'SCV2 Pro'
			),
			'name'    => self::NOTE_NAME,
			'actions' => array(
				array(
					'name'    => 'scv2-pro-learn-more',
					'label'   => __( 'Learn more', 'cart-rest-api-for-woocommerce' ),
					'url'     => SCV2_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( SCV2_STORE_URL . 'pro/' ) ) ),
					'status'  => $status,
					'primary' => true,
				),
			),
		);

		return $args;
	} // END get_note_args()

} // END class

return new SCV2_WC_Admin_Upgrade_Pro_Note();
