<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('SCV2_Update_Plugins')) {

    class SCV2_Update_Plugins
    {

        public $plugin_slug;
        public $version;
        public $cache_key;
        public $cache_allowed;

        public function __construct()
        {
            if (defined('SCV2_DEV_MODE')) {
                add_filter('https_ssl_verify', '__return_false');
                add_filter('https_local_ssl_verify', '__return_false');
                add_filter('http_request_host_is_external', '__return_true');
            }

            $this->plugin_slug   = SCV2_SLUG;
            $this->version       = SCV2_VERSION;
            $this->cache_key     = 'scv2_plugins_updaterd';
            $this->cache_allowed = false;

            add_filter('plugins_api', [$this, 'info'], 20, 3);
            add_filter('site_transient_update_plugins', [$this, 'update']);
            add_action('upgrader_process_complete', [$this, 'purge'], 10, 2);
        }

        public function request()
        {
            $remote = get_transient($this->cache_key);
            // $remote = false;

            if (false === $remote || !$this->cache_allowed) {

                $remote = wp_remote_get(SCV2_RELEASE_URL);

                if (is_wp_error($remote) || 200 !== wp_remote_retrieve_response_code($remote) || empty(wp_remote_retrieve_body($remote))) {
                    return false;
                }

                set_transient($this->cache_key, $remote, MINUTE_IN_SECONDS);
            }

            $remote = json_decode(wp_remote_retrieve_body($remote));
            $result = $this->MapingDataFromGithubAPI($remote);
            return $result;
        }

        function info($response, $action, $args)
        {

            // do nothing if you're not getting plugin information right now
            if ('plugin_information' !== $action) {
                return $response;
            }

            // do nothing if it is not our plugin
            if (empty($args->slug) || $this->plugin_slug !== $args->slug) {
                return $response;
            }

            // get updates
            $remote = $this->request();

            if (!$remote) {
                return $response;
            }

            $response = new \stdClass();

            $response->name           = $remote->name;
            $response->slug           = $remote->slug;
            $response->version        = $remote->version;
            $response->download_link  = $remote->download_url;
            $response->trunk          = $remote->download_url;
            $response->last_updated   = $remote->last_updated;

            $response->sections = [
                'description'    => $remote->description,
                'changelog'    => $remote->changelog
            ];

            return $response;
        }

        public function update($transient)
        {

            if (empty($transient->checked)) {
                return $transient;
            }

            $remote = $this->request();
            if ($remote && version_compare($this->version, $remote->version, '<')) {
                $response              = new \stdClass();
                $response->slug        = $this->plugin_slug;
                $response->plugin      = "{$this->plugin_slug}/swift-checkout-v2.php";
                $response->new_version = $remote->version;
                $response->package     = $remote->download_url;

                $transient->response[$response->plugin] = $response;
            }

            return $transient;
        }

        public function purge($upgrader, $options)
        {

            if ($this->cache_allowed && 'update' === $options['action'] && 'plugin' === $options['type']) {
                // just clean the cache when new plugin version is installed
                delete_transient($this->cache_key);
            }
        }

        public function MapingDataFromGithubAPI($params)
        {
            $response                              = new \stdClass();
            $response->name                        = "Swift Checkout V2 - WooCommerce Plugin";
            $response->slug                        = $this->plugin_slug;
            $response->version                     = $params->tag_name;
            $response->download_url                = $params->assets[0]->browser_download_url;
            $response->last_updated                = $params->created_at;
            $response->description                 = "A <strong>quick checkout</strong> made for <strong>WooCommerce</strong>.";
            $response->changelog                   = $params->body;

            return $response;
        }
    }
}

return new SCV2_Update_Plugins();