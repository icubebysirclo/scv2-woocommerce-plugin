<?php
/**
 * SCV2 - WooCommerce Admin: 6 things you can do SCV2 Products API.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCV2_WC_Admin_Do_With_Products_Note extends SCV2_WC_Admin_Notes {

	/**
	 * Name of the note for use in the database.
	 */
	const NOTE_NAME = 'scv2-wc-admin-do-with-products';

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

		// Don't add note if there are products.
		$query = new \WC_Product_Query(
			array(
				'limit'    => 1,
				'paginate' => true,
				'return'   => 'ids',
				'status'   => array( 'publish' ),
			)
		);

		$products = $query->get_products();
		$count    = $products->total;

		if ( $count <= 0 ) {
			return;
		}

		// Otherwise, create new note.
		self::create_new_note( $args );
	} // END add_note()

	/**
	 * Get note arguments.
	 */
	public static function get_note_args() {
		$type   = SCV2_Helpers::is_wc_version_gte_4_8() ? Automattic\WooCommerce\Admin\Notes\Note::E_WC_ADMIN_NOTE_MARKETING : Automattic\WooCommerce\Admin\Notes\WC_Admin_Note::E_WC_ADMIN_NOTE_MARKETING;
		$status = SCV2_Helpers::is_wc_version_gte_4_8() ? Automattic\WooCommerce\Admin\Notes\Note::E_WC_ADMIN_NOTE_UNACTIONED : Automattic\WooCommerce\Admin\Notes\WC_Admin_Note::E_WC_ADMIN_NOTE_UNACTIONED;

		$campaign_args = SCV2_Helpers::scv2_campaign(
			array(
				'utm_campaign' => 'wc-admin',
				'utm_content'  => 'wc-inbox',
			)
		);

		$args = array(
			'title'   => sprintf(
				/* translators: %s: SCV2 Products */
				__( '6 things you can do with %s', 'cart-rest-api-for-woocommerce' ),
				'SCV2 Products'
			),
			'content' => sprintf(
				/* translators: 1: %s: SCV2 Products, 2: SCV2 */
				__( 'Fetching your products via the REST API should be easy with no authentication issues. Learn more about the six things you can do with %1$s to help your development with %2$s.', 'cart-rest-api-for-woocommerce' ),
				'SCV2 Products',
				'SCV2'
			),
			'type'    => $type,
			'layout'  => 'thumbnail',
			'image'   => esc_url( SCV2_STORE_URL . 'wp-content/uploads/2020/03/rwmibqmoxry-128x214.jpg' ),
			'name'    => self::NOTE_NAME,
			'actions' => array(
				array(
					'name'    => 'scv2-learn-more-products',
					'label'   => __( 'Learn more', 'cart-rest-api-for-woocommerce' ),
					'url'     => SCV2_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( SCV2_STORE_URL . '6-things-you-can-do-with-scv2-products/' ) ) ),
					'status'  => $status,
					'primary' => true,
				),
			),
		);

		return $args;
	} // END get_note_args()

} // END class

return new SCV2_WC_Admin_Do_With_Products_Note();
