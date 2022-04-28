<?php
/**
 * Swift Checkout V2 Shipping Method
 */
if (in_array("woocommerce/woocommerce.php", apply_filters("active_plugins", get_option("active_plugins")))) {

    function scv2_shipping_method()
    {
        if (!class_exists("SCV2_Shipping_Method")) {

            class SCV2_Shipping_Method extends WC_Shipping_Method
            {
                public function __construct()
                {
                    $this->id = "scv2";
                    $this->method_title = __("Swift Checkout V2", "scv2");
                    $this->method_description = __("Shipping Method for Swift Checkout V2", "scv2");

                    // Contreis availability
                    $this->availability = "including";
                    $this->countries = ["ID"];

                    $this->init();

                    $this->enabled = isset($this->settings["enabled"]) ? $this->settings["enabled"] : "yes";
                    $this->title = isset($this->settings["title"]) ? $this->settings["title"] : __("Swift Checkout V2", "scv2");
                }

                /**
                 Load the settings API
                 */
                function init()
                {
                    $this->init_form_fields();
                    $this->init_settings();
                    add_action("woocommerce_update_options_shipping_" . $this->id, [$this, "process_admin_options"]);
                }

                function init_form_fields()
                {
                    $this->form_fields = [
                        "enabled" => [
                            "title" => __("Enable", "scv2"),
                            "type" => "checkbox",
                            "default" => "yes",
                        ],
                        "title" => [
                            "title" => __("Title", "scv2"),
                            "type" => "text",
                            "default" => __("Swift Checkout V2", "scv2"),
                        ],
                    ];
                }

                public function calculate_shipping($package = array())
                {
                    $cost = 0;

                    $rate = [
                        "id" => $this->id,
                        "label" => $this->title,
                        "cost" => $cost,
                    ];

                    $this->add_rate($rate);
                }
            }
        }
    }

    add_action("woocommerce_shipping_init", "scv2_shipping_method");

    function add_scv2_shipping_method($methods)
    {
        $methods[] = "SCV2_Shipping_Method";
        return $methods;
    }

    add_filter("woocommerce_shipping_methods", "add_scv2_shipping_method");

    function scv2_validate_order($posted)
    {
        $packages = WC()->shipping->get_packages();
        $chosen_methods = WC()->session->get("chosen_shipping_methods");

        if (is_array($chosen_methods) && in_array("scv2", $chosen_methods)) {
            foreach ($packages as $i => $package) {
                if ($chosen_methods[$i] != "scv2") {
                    continue;
                }
            }
        }
    }

    add_action("woocommerce_review_order_before_cart_contents", "scv2_validate_order", 10);

    add_action("woocommerce_after_checkout_validation", "scv2_validate_order", 10);

    // Set default shipping method when add to cart
    add_action( 'woocommerce_before_cart', 'set_default_chosen_shipping_method', 5 );

    function set_default_chosen_shipping_method(){
        if( count( WC()->session->get('shipping_for_package_0')['rates'] ) > 0 ){
            foreach( WC()->session->get('shipping_for_package_0')['rates'] as $rate_id =>$rate)
                if($rate->method_id == 'scv2'){
                    $default_rate_id = array( $rate_id );
                    break;
                }

            WC()->session->set('chosen_shipping_methods', $default_rate_id );
        }
    }
}
