<?php
/**
 * Swift Checkout V2 Payment Method
 */
add_filter( 'woocommerce_payment_gateways', 'scv2_add_method_class' );

function scv2_add_method_class( $methods ) {
    $methods[] = 'SCV2_Payment_Method'; // your class name is here
    return $methods;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'scv2_init_method_class' );

function scv2_init_method_class() {

    class SCV2_Payment_Method extends WC_Payment_Gateway {

        /**
         * Class constructor
         */
        public function __construct() {
            $this->id = 'scv2';
            $this->icon = '';
            $this->method_title = 'Swift Checkout V2';
            $this->method_description = __("Payment Method for Swift Checkout V2", "scv2");

            $this->supports = array(
                'products'
            );

            $this->init_form_fields();

            $this->init_settings();
            $this->title = $this->get_option( 'title' );
            $this->enabled = $this->get_option( 'enabled' );

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        /**
         * Plugin options
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Enable',
                    'type'        => 'checkbox',
                    'default'     => 'yes'
                ),
                'title' => array(
                    'title'       => 'Title',
                    'type'        => 'text',
                    'default'     => 'Swift Checkout V2'
                )
            );
        }
    }
}