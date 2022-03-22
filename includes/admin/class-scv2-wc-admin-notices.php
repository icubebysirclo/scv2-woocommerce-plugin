<?php
/**
 * SCV2 - WooCommerce Admin Notices.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_WC_Admin_Notes' ) ) {

	class SCV2_WC_Admin_Notes {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'include_notes' ), 20 );
		}

		/**
		 * Include the notes to create.
		 */
		public function include_notes() {
			// Don't include notes if WC v4.0 or greater is not installed.
			if ( ! SCV2_Helpers::is_wc_version_gte( '4.0' ) ) {
				return;
			}

			// Don't include notes if WC Admin does not exist.
			if (
				! class_exists( 'Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes' ) ||
				! class_exists( 'Automattic\WooCommerce\Admin\Notes\WC_Admin_Note' ) ||
				! class_exists( 'Automattic\WooCommerce\Admin\Notes\Notes' ) ||
				! class_exists( 'Automattic\WooCommerce\Admin\Notes\Note' )
			) {
				return;
			}

			include_once dirname( __FILE__ ) . '/notes/class-scv2-wc-admin-note-activate-pro.php';
			include_once dirname( __FILE__ ) . '/notes/class-scv2-wc-admin-note-do-with-products.php';
			include_once dirname( __FILE__ ) . '/notes/class-scv2-wc-admin-note-help-improve.php';
			include_once dirname( __FILE__ ) . '/notes/class-scv2-wc-admin-note-need-help.php';
			include_once dirname( __FILE__ ) . '/notes/class-scv2-wc-admin-note-thanks-install.php';
			include_once dirname( __FILE__ ) . '/notes/class-scv2-wc-admin-note-upgrade-to-pro.php';
		} // END include_notes()

		/**
		 * Add note.
		 */
		public static function add_note( $note_name = '', $seconds = '', $source = 'scv2' ) {
			// Don't show the note if SCV2 has not been active long enough.
			if ( ! SCV2_Helpers::scv2_active_for( $seconds ) ) {
				return;
			}
		} // END add_note()

		/**
		 * Create a new note.
		 */
		public static function create_new_note( $args = array() ) {
			if ( ! class_exists( 'WC_Data_Store' ) ) {
				return;
			}

			if ( ! is_array( $args ) ) {
				return;
			}

			// Type of note.
			$type = SCV2_Helpers::is_wc_version_gte_4_8() ? Automattic\WooCommerce\Admin\Notes\Note::E_WC_ADMIN_NOTE_INFORMATIONAL : Automattic\WooCommerce\Admin\Notes\WC_Admin_Note::E_WC_ADMIN_NOTE_INFORMATIONAL;

			// Default arguments.
			$default_args = array(
				'name'    => '',
				'title'   => '',
				'content' => '',
				'type'    => $type,
				'source'  => 'scv2',
				'icon'    => 'plugins',
				'layout'  => 'plain',
				'image'   => '',
				'actions' => array(),
			);

			foreach ( $args['actions'] as $key => $action ) {
				$default_args['actions'][ $key ] = array(
					'name'    => 'scv2-' . $key,
					'label'   => '',
					'url'     => '',
					'status'  => '',
					'primary' => '',
				);
			}

			// Parse incoming $args into an array and merge it with $default_args.
			$args = wp_parse_args( $args, $default_args );

			if ( empty( $args['name'] ) || empty( $args['title'] ) || empty( $args['content'] ) || empty( $args['type'] ) ) {
				return;
			}

			// First, see if we've already created this note so we don't do it again.
			$data_store = \WC_Data_Store::load( 'admin-note' );
			$note_ids   = $data_store->get_notes_with_name( $args['name'] );
			if ( ! empty( $note_ids ) ) {
				return;
			}

			// Are we are on WooCommerce 4.8 or greater.
			if ( SCV2_Helpers::is_wc_version_gte_4_8() ) {
				$note = new Automattic\WooCommerce\Admin\Notes\Note();
			} else {
				$note = new Automattic\WooCommerce\Admin\Notes\WC_Admin_Note();
			}

			$note->set_name( $args['name'] );
			$note->set_title( $args['title'] );
			$note->set_content( $args['content'] );
			$note->set_content_data( (object) array() );
			$note->set_type( $args['type'] );

			if ( method_exists( $note, 'set_layout' ) ) {
				$note->set_layout( $args['layout'] );
			}

			if ( ! method_exists( $note, 'set_image' ) ) {
				$note->set_icon( $args['icon'] );
			}

			if ( method_exists( $note, 'set_image' ) ) {
				$note->set_image( $args['image'] );
			}

			if ( isset( $args['source'] ) ) {
				$note->set_source( $args['source'] );
			}

			// Create each action button for the note.
			foreach ( $args['actions'] as $key => $action ) {
				$note->add_action( $action['name'], $action['label'], empty( $action['url'] ) ? false : $action['url'], empty( $action['status'] ) ? Automattic\WooCommerce\Admin\Notes\WC_Admin_Note::E_WC_ADMIN_NOTE_UNACTIONED : $action['status'], empty( $action['primary'] ) ? false : $action['primary'] );
			}

			// Save note.
			$note->save();

			return $note;
		} // END create_new_note()

	} // END class

} // END if class exists

return new SCV2_WC_Admin_Notes();
