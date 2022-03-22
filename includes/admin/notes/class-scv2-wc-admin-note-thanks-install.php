<?php
/**
 * SCV2 - WooCommerce Admin: Thanks for Installing
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCV2_WC_Admin_Thanks_Install_Note extends SCV2_WC_Admin_Notes {

	/**
	 * Name of the note for use in the database.
	 */
	const NOTE_NAME = 'scv2-wc-admin-thanks-install';

	/**
	 * Constructor
	 */
	public function __construct() {
		self::add_note( self::NOTE_NAME );
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

		$data_store = \WC_Data_Store::load( 'admin-note' );

		// We already have this note? Then don't create it again.
		$note_ids = $data_store->get_notes_with_name( self::NOTE_NAME );
		if ( ! empty( $note_ids ) ) {
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
			'title'   => sprintf(
				/* translators: %s: SCV2 */
				esc_attr__( 'Thank you for installing %s!', 'cart-rest-api-for-woocommerce' ),
				'SCV2'
			),
			'content' => __( 'Now you are ready to start developing your headless store. Visit the documentation site to learn how to access the API, view examples and find many action hooks and filters and more.', 'cart-rest-api-for-woocommerce' ),
			'name'    => self::NOTE_NAME,
			'actions' => array(
				array(
					'name'    => 'scv2-view-documentation',
					'label'   => __( 'View Documentation', 'cart-rest-api-for-woocommerce' ),
					'url'     => SCV2_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( 'https://docs.scv2.xyz' ) ) ),
					'status'  => $status,
					'primary' => true,
				),
			),
		);

		return $args;
	} // END get_note_args()

} // END class

return new SCV2_WC_Admin_Thanks_Install_Note();
