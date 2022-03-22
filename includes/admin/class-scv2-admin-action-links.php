<?php
/**
 * Adds links for SCV2 on the plugins page.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_Admin_Action_Links' ) ) {

	class SCV2_Admin_Action_Links {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_filter( 'plugin_action_links_' . plugin_basename( SCV2_FILE ), array( $this, 'plugin_action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 3 );
		} // END __construct()

		/**
		 * Plugin action links.
		 */
		public function plugin_action_links( $links ) {
			$page = admin_url( 'admin.php' );

			if ( current_user_can( 'manage_options' ) ) {
				$action_links = array(
					'getting-started' => '<a href="' . add_query_arg(
						array(
							'page'    => 'scv2',
							'section' => 'getting-started',
						),
						$page
						/* translators: %s: SCV2 */
					) . '" aria-label="' . sprintf( esc_attr__( 'Getting Started with %s', 'cart-rest-api-for-woocommerce' ), 'SCV2' ) . '" style="color: #9b6cc6; font-weight: 600;">' . esc_attr__( 'Getting Started', 'cart-rest-api-for-woocommerce' ) . '</a>',
				);

				return array_merge( $action_links, $links );
			}

			return $links;
		} // END plugin_action_links()

		/**
		 * Plugin row meta links
		 */
		public function plugin_row_meta( $metadata, $file, $data ) {
			if ( plugin_basename( SCV2_FILE ) === $file ) {
				/* translators: %s: URL to author */
				$metadata[1] = sprintf( __( 'Developed By %s', 'cart-rest-api-for-woocommerce' ), '<a href="' . $data['AuthorURI'] . '" aria-label="' . esc_attr__( 'View the developers site', 'cart-rest-api-for-woocommerce' ) . '">' . $data['Author'] . '</a>' );

				if ( ! SCV2_Helpers::is_scv2_pro_activated() ) {
					$campaign_args = SCV2_Helpers::scv2_campaign(
						array(
							'utm_content' => 'go-pro',
						)
					);
				} else {
					$campaign_args = SCV2_Helpers::scv2_campaign(
						array(
							'utm_content' => 'has-pro',
						)
					);
				}

				$campaign_args['utm_campaign'] = 'plugins-row';

				$row_meta = array(
					'docs'      => '<a href="' . SCV2_Helpers::build_shortlink( add_query_arg( $campaign_args, SCV2_DOCUMENTATION_URL ) ) . '" aria-label="' . sprintf(
						/* translators: %s: SCV2 */
						esc_attr__( 'View %s documentation', 'cart-rest-api-for-woocommerce' ),
						'SCV2'
					) . '" target="_blank">' . esc_attr__( 'Documentation', 'cart-rest-api-for-woocommerce' ) . '</a>',
					'translate' => '<a href="' . SCV2_Helpers::build_shortlink( add_query_arg( $campaign_args, SCV2_TRANSLATION_URL ) ) . '" aria-label="' . sprintf(
						/* translators: %s: SCV2 */
						esc_attr__( 'Translate %s', 'cart-rest-api-for-woocommerce' ),
						'SCV2'
					) . '" target="_blank">' . esc_attr__( 'Translate', 'cart-rest-api-for-woocommerce' ) . '</a>',
					'review'    => '<a href="' . SCV2_Helpers::build_shortlink( add_query_arg( $campaign_args, SCV2_REVIEW_URL ) ) . '" aria-label="' . sprintf(
						/* translators: %s: SCV2 */
						esc_attr__( 'Review %s on WordPress.org', 'cart-rest-api-for-woocommerce' ),
						'SCV2'
					) . '" target="_blank">' . esc_attr__( 'Leave a Review', 'cart-rest-api-for-woocommerce' ) . '</a>',
				);

				// Only show donate option if SCV2 Pro is not activated.
				if ( ! SCV2_Helpers::is_scv2_pro_activated() ) {
					$donate = array(
						'donate'   => '<a href="' . esc_url( 'https://www.buymeacoffee.com/sebastien' ) . '" aria-label="' . sprintf(
							/* translators: %s: SCV2 */
							esc_attr__( 'Make a donation for %s', 'cart-rest-api-for-woocommerce' ),
							'SCV2'
						) . '" target="_blank" style="color: #399141; font-weight: 600;">' . esc_attr__( 'Donate', 'cart-rest-api-for-woocommerce' ) . '</a>',
						'priority' => '<a href="' . SCV2_Helpers::build_shortlink( esc_url( SCV2_STORE_URL . 'product/14-day-priority-support/' ) ) . '" aria-label="' . sprintf(
							/* translators: %s: SCV2 */
							esc_attr__( 'Order priority support for %s', 'cart-rest-api-for-woocommerce' ),
							'SCV2'
						) . '" target="_blank" style="color: #9b6cc6; font-weight: 600;">' . esc_attr__( 'Priority Support', 'cart-rest-api-for-woocommerce' ) . '</a>',
					);

					$row_meta = array_merge( $donate, $row_meta );
				}

				// Only show upgrade option if SCV2 Pro is not activated.
				if ( ! SCV2_Helpers::is_scv2_pro_activated() ) {
					$store_url = SCV2_Helpers::build_shortlink( add_query_arg( $campaign_args, SCV2_STORE_URL . 'pro/' ) );

					/* translators: %s: SCV2 Pro */
					$row_meta['upgrade'] = sprintf( '<a href="%1$s" aria-label="' . sprintf( esc_attr__( 'Upgrade to %s', 'cart-rest-api-for-woocommerce' ), 'SCV2 Pro' ) . '" target="_blank" style="color: #c00; font-weight: 600;">%2$s</a>', esc_url( $store_url ), esc_attr__( 'Upgrade to Pro', 'cart-rest-api-for-woocommerce' ) );
				}

				$metadata = array_merge( $metadata, $row_meta );
			}

			return $metadata;
		} // END plugin_row_meta()

	} // END class

} // END if class exists

return new SCV2_Admin_Action_Links();
