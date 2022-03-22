<?php
/**
 * SCV2 - WooCommerce Admin: Activate SCV2 Pro.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCV2_WC_Admin_Activate_Pro_Note extends SCV2_WC_Admin_Notes {

	/**
	 * Name of the note for use in the database.
	 */
	const NOTE_NAME = 'scv2-wc-admin-activate-pro';

	/**
	 * Name of the plugin slug.
	 */
	const PLUGIN_SLUG = 'scv2-pro';

	/**
	 * Name of the plugin file.
	 */
	const PLUGIN_FILE = 'scv2-pro/scv2-pro.php';

	/**
	 * Constructor
	 */
	public function __construct() {
		self::activate_plugin();
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

		// Check if SCV2 Pro is installed. If not true then don't create note.
		$is_plugin_installed = Automattic\WooCommerce\Admin\PluginsHelper::is_plugin_installed( self::PLUGIN_FILE );

		if ( ! $is_plugin_installed ) {
			return;
		}

		// Check if SCV2 Pro is activated. If true then don't create note.
		$pro_active = Automattic\WooCommerce\Admin\PluginsHelper::is_plugin_active( self::PLUGIN_FILE );

		if ( $pro_active ) {
			$data_store = \WC_Data_Store::load( 'admin-note' );

			// We already have this note? Then mark the note as actioned.
			$note_ids = $data_store->get_notes_with_name( self::NOTE_NAME );

			if ( ! empty( $note_ids ) ) {

				$note_id = array_pop( $note_ids );

				// Are we on WooCommerce 4.8 or greater.
				if ( SCV2_Helpers::is_wc_version_gte_4_8() ) {
					$note = Automattic\WooCommerce\Admin\Notes\Notes::get_note( $note_id );

					if ( Automattic\WooCommerce\Admin\Notes\Note::E_WC_ADMIN_NOTE_ACTIONED !== $note->get_status() ) {
						$note->set_status( Automattic\WooCommerce\Admin\Notes\Note::E_WC_ADMIN_NOTE_ACTIONED );
						$note->save();
					}
				} else {
					$note = Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes::get_note( $note_id );

					if ( Automattic\WooCommerce\Admin\Notes\WC_Admin_Note::E_WC_ADMIN_NOTE_ACTIONED !== $note->get_status() ) {
						$note->set_status( Automattic\WooCommerce\Admin\Notes\WC_Admin_Note::E_WC_ADMIN_NOTE_ACTIONED );
						$note->save();
					}
				}
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
		$status = SCV2_Helpers::is_wc_version_gte_4_8() ? Automattic\WooCommerce\Admin\Notes\Note::E_WC_ADMIN_NOTE_ACTIONED : Automattic\WooCommerce\Admin\Notes\WC_Admin_Note::E_WC_ADMIN_NOTE_ACTIONED;

		$args = array(
			'title'   => sprintf(
				/* translators: %s: SCV2 Pro */
				__( '%s is not Activated!', 'cart-rest-api-for-woocommerce' ),
				'SCV2 Pro'
			),
			'content' => sprintf(
				/* translators: %s: SCV2 Pro */
				__( 'You have %1$s installed but it\'s not activated yet. Activate %1$s to unlock the full cart experience and support for WooCommerce extensions like subscriptions now.', 'cart-rest-api-for-woocommerce' ),
				'SCV2 Pro'
			),
			'name'    => self::NOTE_NAME,
			'actions' => array(
				array(
					'name'    => 'activate-scv2-pro',
					'label'   => sprintf(
						/* translators: %s: SCV2 Pro */
						__( 'Activate %s', 'cart-rest-api-for-woocommerce' ),
						'SCV2 Pro'
					),
					'url'     => add_query_arg( array( 'action' => 'activate-scv2-pro' ), admin_url( 'plugins.php' ) ),
					'status'  => $status,
					'primary' => true,
				),
			),
		);

		return $args;
	} // END get_note_args()

	/**
	 * Activates SCV2 Pro when note is actioned.
	 */
	public function activate_plugin() {
		if ( ! isset( $_GET['action'] ) || 'activate-scv2-pro' !== $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$admin_url = add_query_arg(
			array(
				'action'        => 'activate',
				'plugin'        => self::PLUGIN_FILE,
				'plugin_status' => 'active',
			),
			admin_url( 'plugins.php' )
		);

		$activate_url = add_query_arg( '_wpnonce', wp_create_nonce( 'activate-plugin_' . self::PLUGIN_FILE ), $admin_url );

		wp_safe_redirect( $activate_url );
		exit;
	} // END activate_plugin()

} // END class

return new SCV2_WC_Admin_Activate_Pro_Note();
