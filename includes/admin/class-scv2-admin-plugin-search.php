<?php
/**
 * Includes cards in the plugin search results when users
 * enter terms that match SCV2 add-ons or view all add-ons.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SCV2_Plugin_Search' ) ) {

	class SCV2_Plugin_Search {

		/**
		 * Singleton constructor.
		 */
		public static function init() {
			static $instance = null;

			if ( ! $instance ) {
				$instance = new SCV2_Plugin_Search();
			}

			return $instance;
		}

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'current_screen', array( $this, 'start' ) );
		} // END __construct()

		/**
		 * Add actions and filters only if this is the plugin installation screen.
		 */
		public function start( $screen ) {
			if ( 'plugin-install' === $screen->base ) {
				// Filters below inject plugin suggestion.
				add_action( 'admin_enqueue_scripts', array( $this, 'load_plugins_search_script' ) );
				add_filter( 'plugins_api_result', array( $this, 'inject_scv2_suggestion' ), 10, 3 );
				add_filter( 'plugin_install_action_links', array( $this, 'insert_related_links' ), 10, 2 );

				// Filters below are for SCV2s own plugin section.
				add_filter( 'plugins_api_result', array( $this, 'scv2_plugins' ), 10, 3 );
				add_filter( 'install_plugins_tabs', array( $this, 'plugins_tab' ) );
				add_filter( 'install_plugins_table_api_args_scv2', array( $this, 'plugin_list_args' ) );
				add_action( 'install_plugins_scv2', array( $this, 'scv2_plugin_dashboard' ) );
			}
		}

		/**
		 * Add SCV2 plugin tab.
		 */
		public function plugins_tab( $tabs ) {
			return array_merge(
				$tabs,
				array(
					'scv2' => 'SCV2',
				)
			);
		} // END plugins_tab()

		/**
		 * Set SCV2 tab args.
		 */
		public function plugin_list_args( $args ) {
			$installed_plugins = self::get_installed_plugins();

			$per_page = 30;

			$scv2_args = array(
				'page'              => isset( $_GET['paged'] ) ? max( 0, intval( $_GET['paged'] - 1 ) * $per_page ) : 0, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'per_page'          => $per_page,
				'author'            => 'scv2forwc',
				'installed_plugins' => array_keys( $installed_plugins ),
				// Send the locale to the API so it can provide context-sensitive results.
				'locale'            => get_user_locale(),
			);

			$args = wp_parse_args( $scv2_args, $args );

			return $args;
		} // END plugin_list_args()

		/**
		 * Return the list of known plugins.
		 */
		protected function get_installed_plugins() {
			$plugins = array();

			$plugin_info = get_site_transient( 'update_plugins' );

			if ( isset( $plugin_info->no_update ) ) {
				foreach ( $plugin_info->no_update as $plugin ) {
					$plugin->upgrade          = false;
					$plugins[ $plugin->slug ] = $plugin;
				}
			}

			if ( isset( $plugin_info->response ) ) {
				foreach ( $plugin_info->response as $plugin ) {
					$plugin->upgrade          = true;
					$plugins[ $plugin->slug ] = $plugin;
				}
			}

			return $plugins;
		} // END get_installed_plugins()

		/**
		 * Displays our own plugin dashboard on the plugin install page.
		 */
		public function scv2_plugin_dashboard() {
			?>
			<div class="scv2-plugin-install-dashboard">
				<p>
					<?php
					printf(
						/* translators: %1$s: http://getswift.asia/add-ons/, %2$s: http://getswift.asia/woocommerce-extensions/ */
						__( 'These plugins extend and expand the functionality of SCV2. You may learn more about each of the <a href="%1$s" target="_blank">SCV2 add-ons</a> and <a href="%2$s" target="_blank">WooCommerce extensions</a> from SCV2.xyz', 'cart-rest-api-for-woocommerce' ),
						esc_url( SCV2_STORE_URL . 'add-ons/' ),
						esc_url( SCV2_STORE_URL . 'woocommerce-extensions/' )
					);
					?>
				</p>

				<p>
					<?php print( esc_html__( 'Some of these plugins require a 3rd party plugin or extension to support it’s features. See plugin requirement at the bottom of each plugin card.', 'cart-rest-api-for-woocommerce' ) ); ?>
				</p>

			</div>
			<?php
			do_action( 'scv2_before_display_plugins_table' );

			display_plugins_table();

			do_action( 'scv2_after_display_plugins_table' );
		} // END scv2_plugin_dashboard()

		/**
		 * Load the search scripts and CSS for Plugin Search Suggestion and tweaks.
		 */
		public function load_plugins_search_script() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( SCV2_SLUG . '-plugin-search', SCV2_URL_PATH . '/assets/js/admin/plugin-search' . $suffix . '.js', array( 'jquery' ), SCV2_VERSION, true );
			wp_localize_script(
				SCV2_SLUG . '-plugin-search',
				'SCV2PluginSearch',
				array(
					'legend'      => sprintf(
						/* translators: %s: SCV2 */
						esc_html__(
							'This suggestion was made by %s, the awesome REST API plugin already installed on your site.',
							'cart-rest-api-for-woocommerce'
						),
						'SCV2'
					),
					'supportText' => esc_html__( 'Learn more about these suggestions.', 'cart-rest-api-for-woocommerce' ),
					'supportLink' => SCV2_STORE_URL . 'plugin-suggestions/',
				)
			);

			wp_register_style( SCV2_SLUG . '-plugin-search', SCV2_URL_PATH . '/assets/css/admin/plugin-search' . $suffix . '.css', array(), SCV2_VERSION );
			wp_enqueue_style( SCV2_SLUG . '-plugin-search' );
			wp_style_add_data( SCV2_SLUG . '-plugin-search', 'rtl', 'replace' );
			if ( $suffix ) {
				wp_style_add_data( SCV2_SLUG . '-plugin-search', 'suffix', '.min' );
			}
		} // END load_plugins_search_script()

		/**
		 * Get the plugin repo's data for SCV2 to populate the fields with.
		 */
		public static function get_scv2_plugin_data() {
			$data = get_transient( 'scv2_plugin_data' );

			if ( false === $data || is_wp_error( $data ) ) {
				$query_args = array(
					'slug'   => 'cart-rest-api-for-woocommerce',
					'is_ssl' => is_ssl(),
					'fields' => array(
						'short_description' => false,
						'sections'          => false,
						'versions'          => false,
						'reviews'           => true,
						'banners'           => false,
						'icons'             => true,
						'active_installs'   => true,
					),
				);

				$data = plugins_api( 'plugin_information', $query_args );

				set_transient( 'scv2_plugin_data', $data, DAY_IN_SECONDS );
			}

			return $data;
		} // END get_scv2_plugin_data()

		/**
		 * Create a list of SCV2 add-ons.
		 */
		public function get_addons_list() {
			$campaign_args = SCV2_Helpers::scv2_campaign(
				array(
					'utm_campaign' => 'install-plugin',
					'utm_content'  => 'plugin-suggestions',
				)
			);

			return array(
				'scv2-products'  => array(
					'name'              => 'SCV2 Products',
					'plugin'            => 'products',
					'search_terms'      => 'products, rest-api, reviews',
					'short_description' => esc_html__( 'Provides access to non-sensitive product information, categories, tags, attributes and even reviews from your store without the need to authenticate.', 'cart-rest-api-for-woocommerce' ),
					'logo'              => SCV2_URL_PATH . '/assets/images/logo.jpg',
					'requirement'       => 'SCV2',
					'info'              => array(
						'requires'     => '5.2',
						'tested'       => '5.6',
						'requires_php' => '7.2',
						'last_updated' => '',
					),
					'purchase'          => SCV2_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( SCV2_STORE_URL . 'pro/#pricing' ) ) ),
					'learn_more'        => SCV2_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( SCV2_STORE_URL . 'add-ons/products/' ) ) ),
					'third_party'       => false,
				),
				'scv2-acf'       => array(
					'name'              => 'Advanced Custom Fields',
					'plugin'            => 'acf',
					'search_terms'      => 'advanced, acf, fields, custom fields, meta, repeater',
					'short_description' => esc_html__( 'Returns all custom meta data saved for all products using Advanced Custom Fields.', 'cart-rest-api-for-woocommerce' ),
					'logo'              => SCV2_URL_PATH . '/assets/images/logo.jpg',
					'requirement'       => 'SCV2 Products',
					'info'              => array(
						'requires'     => '5.2',
						'tested'       => '5.6',
						'requires_php' => '7.2',
						'last_updated' => '',
					),
					'purchase'          => SCV2_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( SCV2_STORE_URL . 'pro/#pricing' ) ) ),
					'learn_more'        => SCV2_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( SCV2_STORE_URL . 'add-ons/advanced-custom-fields/' ) ) ),
					'third_party'       => false,
				),
				'scv2-yoast-seo' => array(
					'name'              => 'Yoast SEO',
					'plugin'            => 'yoast-seo',
					'search_terms'      => 'yoast, seo, xml sitemap, content analysis, readability, schema',
					'short_description' => esc_html__( 'Returns all Yoast SEO data for all products, product categories and tags.', 'cart-rest-api-for-woocommerce' ),
					'logo'              => SCV2_URL_PATH . '/assets/images/logo.jpg',
					'requirement'       => 'SCV2 Products',
					'info'              => array(
						'requires'     => '5.2',
						'tested'       => '5.6',
						'requires_php' => '7.2',
						'last_updated' => '',
					),
					'purchase'          => SCV2_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( SCV2_STORE_URL . 'pro/#pricing' ) ) ),
					'learn_more'        => SCV2_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( SCV2_STORE_URL . 'add-ons/yoast-seo/' ) ) ),
					'third_party'       => false,
				),
			);
		} // END get_addons_list()

		/**
		 * Create a list of SCV2 supported third party plugins.
		 */
		public function get_third_party_list() {
			return array(
				'woocommerce-name-your-price'            => array(
					'name'              => 'WooCommerce Name Your Price',
					'plugin'            => 'woocommerce-name-your-price',
					'author'            => 'Kathy Darling',
					'search_terms'      => 'nyp, name your price, pay what you want, product page feature, enhancements',
					'short_description' => esc_html__( 'Let customers pay what they want with Name Your Price', 'cart-rest-api-for-woocommerce' ),
					'logo'              => SCV2_URL_PATH . '/assets/images/plugin-suggestions/kia-logo.png',
					'requirement'       => false,
					'info'              => array(
						'requires'     => '4.4',
						'tested'       => '5.3',
						'requires_php' => '7.0',
						'last_updated' => '',
					),
					'learn_more'        => esc_url( 'https://woocommerce.com/products/name-your-price/' ),
					'third_party'       => true,
				),
				'woocommerce-mix-and-match-products'     => array(
					'name'              => 'WooCommerce Mix & Match Products',
					'plugin'            => 'woocommerce-mix-and-match-products',
					'author'            => 'Kathy Darling',
					'search_terms'      => 'nyp, name your price, pay what you want, product page feature, enhancements',
					'short_description' => esc_html__( 'Create mix and match products with WooCommerce, for creating cases of customer-selected products.', 'cart-rest-api-for-woocommerce' ),
					'logo'              => SCV2_URL_PATH . '/assets/images/plugin-suggestions/kia-logo.png',
					'requirement'       => false,
					'info'              => array(
						'requires'     => '4.4',
						'tested'       => '5.3',
						'requires_php' => '7.0',
						'last_updated' => '',
					),
					'learn_more'        => esc_url( 'https://woocommerce.com/products/woocommerce-mix-and-match-products/' ),
					'third_party'       => true,
				),
				'woocommerce-subscriptions'              => array(
					'name'              => 'WooCommerce Subscriptions',
					'plugin'            => 'woocommerce-subscriptions',
					'author'            => 'WooCommerce',
					'search_terms'      => 'subscription, product page feature, recurring payments, enhancements',
					'short_description' => esc_html__( 'Sell products and services with recurring payments in your WooCommerce store.', 'cart-rest-api-for-woocommerce' ),
					'logo'              => 'https://ps.w.org/woocommerce/assets/icon-128x128.png?rev=2366418',
					'requirement'       => sprintf(
						/* translators: %s: SCV2 */
						esc_html__( '%s Pro', 'cart-rest-api-for-woocommerce' ),
						'SCV2'
					),
					'plugin_does'       => esc_html__( 'Supported With', 'cart-rest-api-for-woocommerce' ),
					'info'              => array(
						'requires'     => '4.0',
						'tested'       => '5.5',
						'requires_php' => '7.0',
						'last_updated' => '',
					),
					'learn_more'        => esc_url( 'https://woocommerce.com/products/woocommerce-subscriptions/' ),
					'third_party'       => true,
				),
				'woocommerce-smart-coupons'              => array(
					'name'              => 'WooCommerce Smart Coupons',
					'plugin'            => 'woocommerce-smart-coupons',
					'author'            => 'StoreApps',
					'search_terms'      => 'coupon, credit, store credit, gift, certificate, voucher, discount, gift certificate, gift voucher, customer, self service',
					'short_description' => esc_html__( 'Grow your sales and customers using discounts, coupons, credits, vouchers, product giveaways, offers and promotions.', 'cart-rest-api-for-woocommerce' ),
					'logo'              => 'https://ps.w.org/woocommerce/assets/icon-128x128.png?rev=2366418',
					'requirement'       => sprintf(
						/* translators: %s: SCV2 */
						esc_html__( '%s Pro', 'cart-rest-api-for-woocommerce' ),
						'SCV2'
					),
					'plugin_does'       => esc_html__( 'Compatible With', 'cart-rest-api-for-woocommerce' ),
					'info'              => array(
						'requires'     => '4.0',
						'tested'       => '5.5',
						'requires_php' => '7.0',
						'last_updated' => '',
					),
					'learn_more'        => esc_url( 'https://woocommerce.com/products/smart-coupons/' ),
					'third_party'       => true,
				),
				'woocommerce-advanced-shipping-packages' => array(
					'name'              => 'WooCommerce Advanced Shipping Packages',
					'plugin'            => 'woocommerce-advanced-shipping-packages',
					'author'            => 'Jeroen Sormani',
					'search_terms'      => 'shipping, packages, split-packages, multiple shipping',
					'short_description' => esc_html__( 'Split your order into multiple shipping packages when you need it to, with the products you want to.', 'cart-rest-api-for-woocommerce' ),
					'logo'              => 'https://ps.w.org/woocommerce/assets/icon-128x128.png?rev=2366418',
					'requirement'       => false,
					'info'              => array(
						'requires'     => '4.0',
						'tested'       => '5.5',
						'requires_php' => '7.0',
						'last_updated' => '',
					),
					'learn_more'        => esc_url( 'https://woocommerce.com/products/woocommerce-advanced-shipping-packages/' ),
					'third_party'       => true,
				),
				'woocommerce-free-gift-coupons'          => array(
					'name'              => 'WooCommerce Free Gift Coupons',
					'plugin'            => 'woocommerce-free-gift-coupons',
					'author'            => 'Backcourt Development',
					'search_terms'      => 'coupon, give away, free items, gift items',
					'short_description' => esc_html__( 'Give away a free item(s) to any customer with the correct code.', 'cart-rest-api-for-woocommerce' ),
					'logo'              => 'https://ps.w.org/woocommerce/assets/icon-128x128.png?rev=2366418',
					'requirement'       => false,
					'info'              => array(
						'requires'     => '4.0',
						'tested'       => '5.5',
						'requires_php' => '7.0',
						'last_updated' => '',
					),
					'learn_more'        => esc_url( 'https://woocommerce.com/products/free-gift-coupons/' ),
					'third_party'       => true,
				),
			);
		} // END get_third_party_list()

		/**
		 * Returns both SCV2 addons and supported extensions.
		 */
		public function get_suggestions() {
			return array_merge( self::get_addons_list(), self::get_third_party_list() );
		} // END get_suggestions()

		/**
		 * Gets data to inject results.
		 */
		public function get_inject_data( $inject, $data ) {
			return array(
				/* translators: %1$s: Add-on name */
				'name'              => empty( $data['third_party'] ) ? sprintf( esc_html__( '%1$s Add-on', 'cart-rest-api-for-woocommerce' ), $data['name'] ) : $data['name'],
				'slug'              => empty( $data['third_party'] ) ? 'scv2-' . $data['plugin'] : $data['plugin'],
				'plugin'            => $data['plugin'],
				'version'           => '',
				'author'            => ! empty( $data['author'] ) ? esc_html( $data['author'] ) : 'SCV2',
				'author_profile'    => 'http://getswift.asia',
				'requires'          => ! empty( $data['info'] ) ? $data['info']['requires'] : $inject['requires'],
				'tested'            => ! empty( $data['info'] ) ? $data['info']['tested'] : $inject['tested'],
				'requires_php'      => ! empty( $data['info'] ) ? $data['info']['requires_php'] : $inject['requires_php'],
				'rating'            => $inject['rating'],
				'num_ratings'       => $inject['num_ratings'],
				'active_installs'   => $inject['active_installs'],
				'last_updated'      => ! empty( $data['last_updated'] ) ? $data['last_updated'] : $inject['last_updated'],
				'short_description' => $data['short_description'],
				'download_link'     => '',
				'icons'             => isset( $inject['icons'] ) ? $inject['icons'] : $data['logo'],
				'logo'              => array(
					'1x'  => esc_url( $data['logo'] ),
					'2x'  => esc_url( $data['logo'] ),
					'svg' => esc_url( $data['logo'] ),
				),
				'requirement'       => ! empty( $data['requirement'] ) ? $data['requirement'] : '',
				'plugin_does'       => ! empty( $data['plugin_does'] ) ? $data['plugin_does'] : esc_html__( 'Requires', 'cart-rest-api-for-woocommerce' ),
				'purchase'          => ! empty( $data['purchase'] ) ? esc_url( $data['purchase'] ) : '',
				'learn_more'        => ! empty( $data['learn_more'] ) ? esc_url( $data['learn_more'] ) : '',
				'third_party'       => $data['third_party'],
			);
		} // END get_inject_data()

		/**
		 * Filter plugin fetching API results to inject SCV2 add-ons.
		 */
		public function inject_scv2_suggestion( $result, $action, $args ) {
			// Return current results if we are not searching for suggestion.
			if ( empty( $args->search ) ) {
				return $result;
			}

			// Return current results if we are not on the first page of results.
			if ( ! isset( $result->info['page'] ) || 1 < $result->info['page'] ) {
				return $result;
			}

			// Get SCV2 plugin data.
			$inject = (array) self::get_scv2_plugin_data();

			// Return current results if failed to get plugin data.
			if ( is_wp_error( $inject ) ) {
				return $result;
			}

			$suggestions = self::get_suggestions();

			$show_addon = false;

			// Get each add-on and see if we should suggest it to the user.
			foreach ( $suggestions as $slug => $data ) {
				// Get prepared data to inject the results.
				$inject_data = self::get_inject_data( $inject, $data );

				// Override plugin slug to identify suggestion.
				$inject_data['slug'] = 'scv2-plugin-search';

				// Override card title and icon.
				$inject_data['name'] = '<h3>' . $inject_data['name'] . '</h3><strong>' . sprintf(
					/* translators: %s: Plugin author */
					esc_html__( 'by %s', 'cart-rest-api-for-woocommerce' ),
					$inject_data['author']
				) . '</strong>';
				$inject_data['icons'] = $inject_data['logo'];

				// Lowercase, trim, remove punctuation/special chars, decode url, remove 'cart-rest-api-for-woocommerce'.
				$normalized_term = $this->sanitize_search_term( $args->search );

				// Show if searched keywords matched any of the tags.
				if ( false !== stripos( $data['search_terms'] . ', ' . $data['name'], $normalized_term ) ) {
					$show_addon = true;
					break;
				}
			} // END foreach add-on

			// Inject single search result from list of suggestions to the bottom of the results.
			if ( $show_addon ) {
				array_push( $result->plugins, $inject_data );
			}

			// Return search results.
			return $result;
		} // END inject_scv2_suggestion()

		/**
		 * Filter plugin fetching API results to return SCV2 add-ons.
		 */
		public function scv2_plugins( $result, $action, $args ) {
			// If we are not browsing just SCV2 then return results.
			if ( ! isset( $args->author ) || 'scv2forwc' !== $args->author ) {
				return $result;
			}

			// Get SCV2 plugin data.
			$inject = (array) self::get_scv2_plugin_data();

			// Return current results if failed to get plugin data.
			if ( is_wp_error( $inject ) ) {
				return $result;
			}

			$suggestions = self::get_suggestions();

			// Get each add-on and see if we should suggest it to the user.
			foreach ( $suggestions as $slug => $data ) {
				// Get prepared data to inject the results.
				$inject_data = self::get_inject_data( $inject, $data );

				// Override card icon.
				$inject_data['icons'] = $inject_data['logo'];

				array_push( $result->plugins, $inject_data );
			} // END foreach add-on

			// Return search results.
			return $result;
		} // END scv2_plugins()

		/**
		 * Take a raw search query and return something a bit more standardized and
		 * easy to work with.
		 */
		private function sanitize_search_term( $term ) {
			$term = strtolower( urldecode( $term ) );

			// remove non-alpha/space chars.
			$term = preg_replace( '/[^a-z ]/', '', $term );

			// remove strings that don't help matches.
			$term = trim( str_replace( array( 'scv2', 'cart-rest-api-for-woocommerce', 'free', 'wordpress', 'woocommerce' ), '', $term ) );

			return $term;
		} // END sanitize_search_term()

		/**
		 * Returns allowed html tags.
		 */
		public function plugins_allowedtags() {
			return array(
				'a'       => array(
					'href'   => array(),
					'title'  => array(),
					'target' => array(),
				),
				'abbr'    => array( 'title' => array() ),
				'acronym' => array( 'title' => array() ),
				'code'    => array(),
				'pre'     => array(),
				'em'      => array(),
				'strong'  => array(),
				'ul'      => array(),
				'ol'      => array(),
				'li'      => array(),
				'p'       => array(),
				'br'      => array(),
			);
		} // END plugins_allowedtags()

		/**
		 * Put some more appropriate links on our custom result cards.
		 */
		public function insert_related_links( $links, $plugin ) {
			if ( isset( $_GET['tab'] ) && 'scv2' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$links = self::get_related_links( $links, $plugin );
			} elseif ( 'scv2-plugin-search' === $plugin['slug'] ) {
				$links = self::get_suggestion_links( $plugin );
			} else {
				return $links;
			}

			// Add link pointing to a relevant doc page in SCV2.xyz.
			if ( ! empty( $plugin['learn_more'] ) ) {
				$links['scv2-learn-more'] = '<a
					class="scv2-plugin-search__learn-more button"
					href="' . esc_url( $plugin['learn_more'] ) . '"
					target="_blank"
					data-addon="' . esc_attr( $plugin['plugin'] ) . '"
					data-track="learn_more"
					>' . esc_html__( 'Learn more', 'cart-rest-api-for-woocommerce' ) . ' <span class="dashicons dashicons-external"></span></a>';
			}

			foreach ( self::get_suggestions() as $key => $scv2_plugin ) {
				// Add plugin requirement.
				if ( $key === $plugin['slug'] && ! empty( $plugin['requirement'] ) ) {
					$links['scv2-requirement'] = '<div class="plugin-requirement">' . sprintf(
						/* translators: %4$s: plugin does, %3$s: requirement */
						esc_html__( '%1$sPlugin %4$s: %2$s%3$s', 'cart-rest-api-for-woocommerce' ),
						'<strong>',
						'</strong>',
						esc_html( $plugin['requirement'] ),
						$plugin['plugin_does']
					) . '</div>';
				}
			}

			return $links;
		} // END insert_related_links()

		/**
		 * Returns related links for each SCV2 plugin.
		 */
		public function get_related_links( $links, $plugin ) {
			return self::get_action_links( $links, $plugin );
		} // END get_related_links()

		/**
		 * Returns related links for suggested plugin.
		 */
		public function get_suggestion_links( $plugin ) {
			$links = array();

			return self::get_action_links( $links, $plugin );
		} // END get_suggestion_links()

		/**
		 * Returns action links.
		 */
		public function get_action_links( $links = array(), $plugin ) {
			$plugins_allowedtags = self::plugins_allowedtags();

			foreach ( self::get_suggestions() as $key => $scv2_plugin ) {
				if ( $key === $plugin['slug'] ) {
					$links = array(); // Reset links if plugin is not from WP.org.

					$title          = wp_kses( $plugin['name'], $plugins_allowedtags );
					$version        = wp_kses( $plugin['version'], $plugins_allowedtags );
					$name           = wp_strip_all_tags( $title . ' ' . $version );
					$requires_php   = isset( $plugin['requires_php'] ) ? $plugin['requires_php'] : null;
					$requires_wp    = isset( $plugin['requires'] ) ? $plugin['requires'] : null;
					$compatible_php = is_php_version_compatible( $requires_php );
					$compatible_wp  = is_wp_version_compatible( $requires_wp );
					$tested_wp      = ( empty( $plugin['tested'] ) || version_compare( get_bloginfo( 'version' ), $plugin['tested'], '<=' ) );

					if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) {
						$status = install_plugin_install_status( $plugin );

						switch ( $status['status'] ) {
							case 'install':
								if ( $status['url'] ) {
									if ( $compatible_php && $compatible_wp ) {
										if ( ! empty( $plugin['purchase'] ) ) {
											$links['scv2-purchase'] = sprintf(
												'<a class="scv2-plugin-primary button" data-slug="%s" href="%s" target="_blank" aria-label="%s" data-name="%s">%s</a>',
												esc_attr( $plugin['slug'] ),
												esc_url( $plugin['purchase'] ),
												/* translators: %s: Plugin name */
												esc_attr( sprintf( __( 'Purchase %s now', 'cart-rest-api-for-woocommerce' ), $name ) ),
												esc_attr( $name ),
												__( 'Purchase Now', 'cart-rest-api-for-woocommerce' )
											);
										}
									} else {
										$links['scv2-not-compatible'] = sprintf(
											'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
											__( 'Not Compatible', 'cart-rest-api-for-woocommerce' )
										);
									}
								}

								break;

							case 'update_available':
								if ( $status['url'] ) {
									if ( $compatible_php && $compatible_wp ) {
										$links['scv2-update-now'] = sprintf(
											'<a class="update-now button aria-button-if-js" data-plugin="%s" data-slug="%s" href="%s" aria-label="%s" data-name="%s">%s</a>',
											esc_attr( $status['file'] ),
											esc_attr( $plugin['slug'] ),
											esc_url( $status['url'] ),
											/* translators: %s: Plugin name */
											esc_attr( sprintf( __( 'Update %s now', 'cart-rest-api-for-woocommerce' ), $name ) ),
											esc_attr( $name ),
											__( 'Update Now', 'cart-rest-api-for-woocommerce' )
										);
									} else {
										$links['scv2-cannot-update'] = sprintf(
											'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
											__( 'Cannot Update', 'cart-rest-api-for-woocommerce' )
										);
									}
								}

								break;

							case 'latest_installed':
							case 'newer_installed':
								if ( is_plugin_active( $status['file'] ) ) {
									$links['scv2-active'] = sprintf(
										'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
										__( 'Installed & Active', 'cart-rest-api-for-woocommerce' )
									);
								} elseif ( current_user_can( 'activate_plugin', $status['file'] ) ) {
									if ( $compatible_php && $compatible_wp ) {
										$button_text = __( 'Activate', 'cart-rest-api-for-woocommerce' );
										/* translators: %s: Plugin name. */
										$button_label = __( 'Activate %s', 'cart-rest-api-for-woocommerce' );
										$activate_url = add_query_arg(
											array(
												'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $status['file'] ),
												'action'   => 'activate',
												'plugin'   => $status['file'],
											),
											network_admin_url( 'plugins.php' )
										);

										if ( is_network_admin() ) {
											$button_text = __( 'Network Activate', 'cart-rest-api-for-woocommerce' );
											/* translators: %s: Plugin name. */
											$button_label = __( 'Network Activate %s', 'cart-rest-api-for-woocommerce' );
											$activate_url = add_query_arg( array( 'networkwide' => 1 ), $activate_url );
										}

										$links['scv2-activate'] = sprintf(
											'<a href="%1$s" class="button activate-now button-primary" aria-label="%2$s">%3$s</a>',
											esc_url( $activate_url ),
											esc_attr( sprintf( $button_label, $plugin['name'] ) ),
											$button_text
										);
									} else {
										$links['scv2-not-compatible'] = sprintf(
											'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
											__( 'Not Compatible', 'cart-rest-api-for-woocommerce' )
										);
									}
								} else {
									$links['scv2-installed'] = sprintf(
										'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
										__( 'Installed', 'cart-rest-api-for-woocommerce' )
									);
								}

								break;

						} // END switch
					} // END if user can install or update plugins.
				} // END if plugin matches.
			} // END foreach scv2 plugin.

			return $links;
		} // END get_action_links()

	} // END class

} // END if class exists

/**
 * If "scv2_show_plugin_search" filter is set to false,
 * the plugin search suggestions will not show on the plugin install page.
 */
if ( is_admin() && SCV2_Helpers::is_scv2_ps_active() ) {
	SCV2_Plugin_Search::init();
}
