<?php
/**
 * Manages SCV2 plugin update notices.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_Plugins_Screen_Updates' ) ) {

	class SCV2_Plugins_Screen_Updates {

		/**
		 * The upgrade notice shown inline.
		 */
		protected $upgrade_notice = '';

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'in_plugin_update_message-' . plugin_basename( SCV2_FILE ), array( $this, 'in_plugin_update_message' ), 10, 2 );

			// Add after_plugin_row... action for SCV2.
			add_action( 'after_plugin_row_' . plugin_basename( SCV2_FILE ), array( $this, 'plugin_row' ), 11, 2 );
		} // END __construct()

		/**
		 * Show plugin changes on the plugins screen.
		 */
		public function in_plugin_update_message( $args, $response ) {
			$this->upgrade_notice = $this->get_upgrade_notice( $response->new_version );

			if ( ! empty( $this->upgrade_notice ) ) {
				echo '</p></div><div class="notice inline notice-scv2">' .
				/* translators: %s: SCV2 */
				'<p class="cart"><strong>' . sprintf( esc_html__( '%s Upgrade Notice', 'cart-rest-api-for-woocommerce' ), 'SCV2' ) . '</strong></p>' .
				'<p>' . wp_kses_post( $this->upgrade_notice ) . '</p>' .
				'</div>';
			}
		} // END in_plugin_update_message()

		/**
		 * Get the upgrade notice from WordPress.org.
		 */
		protected function get_upgrade_notice( $version ) {
			/**
			 * If WordPress version is 5.5 or greater then return nothing.
			 * This is because of WordPress auto-update support.
			 */
			if ( SCV2_Helpers::is_wp_version_gte( '5.5' ) ) {
				return '';
			}

			$transient_name = 'scv2_readme_upgrade_notice_' . $version;
			$upgrade_notice = get_transient( $transient_name );

			if ( false === $upgrade_notice ) {
				$response = wp_safe_remote_get( esc_url_raw( 'https://plugins.svn.wordpress.org/' . SCV2_SLUG . '/trunk/readme.txt' ) );

				if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
					$upgrade_notice = $this->parse_update_notice( $response['body'], $version );
					if ( ! empty( $upgrade_notice ) ) {
						set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
					}
				}
			}

			return $upgrade_notice;
		} // END get_upgrade_notice()

		/**
		 * Parse update notice from readme file.
		 */
		private function parse_update_notice( $content, $new_version ) {
			$version_parts     = explode( '.', $new_version );
			$check_for_notices = array(
				$version_parts[0] . '.0', // Major.
				$version_parts[0] . '.0.0', // Major.
				$version_parts[0] . '.' . $version_parts[1], // Minor.
				$version_parts[0] . '.' . $version_parts[1] . '.' . $version_parts[2], // Patch.
			);
			$notice_regexp     = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( $new_version ) . '\s*=|$)~Uis';
			$upgrade_notice    = '';

			foreach ( $check_for_notices as $check_version ) {
				if ( version_compare( SCV2_VERSION, $check_version, '>' ) ) {
					continue;
				}

				$matches = null;

				if ( preg_match( $notice_regexp, $content, $matches ) ) {
					$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );

					if ( version_compare( trim( $matches[1] ), $check_version, '=' ) ) {
						foreach ( $notices as $index => $line ) {
							$upgrade_notice .= preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line );
						}
					}
					break;
				}
			}

			return wp_kses_post( $upgrade_notice );
		} // END parse_update_notice()

		/**
		 * Displays a notice under the plugin row for SCV2.
		 */
		public function plugin_row( $file, $plugin_data ) {
			$plugins_allowedtags = array(
				'a'       => array(
					'href'  => array(),
					'title' => array(),
				),
				'abbr'    => array( 'title' => array() ),
				'acronym' => array( 'title' => array() ),
				'code'    => array(),
				'em'      => array(),
				'strong'  => array(),
			);

			$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
			$plugin_name   = wp_kses( $plugin_data['Name'], $plugins_allowedtags );

			if ( is_network_admin() || ! is_multisite() ) {
				if ( is_network_admin() ) {
					$active_class = is_plugin_active_for_network( $file ) ? ' active' : '';
				} else {
					$active_class = is_plugin_active( $file ) ? ' active' : '';
				}

				$notice_type = 'notice-scv2';

				// Only show the plugin notice if this version of SCV2 is not a pre-release or is lower than the version mentioned in the notice.
				$version = strstr( SCV2_VERSION, '-', true );

				// If version returns empty then just set as the current plugin version.
				if ( empty( $version ) ) {
					$version = SCV2_VERSION;
				}

				if ( SCV2_Helpers::is_scv2_pre_release() || version_compare( SCV2_NEXT_VERSION, $version, '<=' ) ) {
					return;
				}

				echo '<tr class="plugin-update-tr' . esc_attr( $active_class ) . ' scv2-row-notice" id="' . esc_attr( 'cart-rest-api-for-woocommerce-update' ) . '" data-slug="cart-rest-api-for-woocommerce" data-plugin="' . esc_attr( $file ) . '"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="notice inline ' . esc_attr( $notice_type ) . '"><p class="cart">';

				printf(
					/* translators: 1: plugin name, 2: version mentioned, 3: details URL */
					__( 'Because of the great feedback %1$s users have provided, <strong>%1$s v%2$s</strong> will be introducing a new and improved API in the future. I am in need of testers and your feedback. <a href="%3$s" target="_blank">Sign Up to Test</a>.', 'cart-rest-api-for-woocommerce' ),
					esc_attr( $plugin_name ),
					esc_attr( SCV2_NEXT_VERSION ),
					esc_url( SCV2_STORE_URL . 'contact/' )
				);

				echo '</p></div></td></tr>';
			}
		} // END plugin_row()

	} // END class

} // END if class exists

return new SCV2_Plugins_Screen_Updates();
