<?php
/**
 * SCV2 - WooCommerce Admin: Help Improve SCV2.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCV2_WC_Admin_Help_Improve_Note extends SCV2_WC_Admin_Notes {

	/**
	 * Name of the note for use in the database.
	 */
	const NOTE_NAME = 'scv2-wc-admin-help-improve';

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

		$args = array(
			'title'   => __( 'Help improve SCV2', 'cart-rest-api-for-woocommerce' ),
			'content' => __( 'I\'d love your input to shape the future of the SCV2 REST API together. Feel free to share any feedback, ideas or suggestions that you have.', 'cart-rest-api-for-woocommerce' ),
			'name'    => self::NOTE_NAME,
			'actions' => array(
				array(
					'name'   => 'scv2-share-feedback',
					'label'  => __( 'Share feedback', 'cart-rest-api-for-woocommerce' ),
					'url'    => 'https://github.com/co-cart/co-cart/issues/new?assignees=&labels=priority%3Alow%2C+enhancement&template=enhancement.md&title=ISBAT+...',
					'status' => $status,
				),
			),
		);

		return $args;
	} // END get_note_args()

} // END class

return new SCV2_WC_Admin_Help_Improve_Note();
