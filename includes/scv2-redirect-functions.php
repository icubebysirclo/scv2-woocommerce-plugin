<?php
/*
 * SETUP
 */
define("BRAND_ID_STAGING", "398809496132060018");
define("BRAND_ID_PRODUCTION", "398809496132060018");
define("SCV2_URL_STAGING", "https://scv2.gcp-staging.testingnow.me");
define("SCV2_URL_PRODUCTION", "https://checkout.getswift.asia");
define("SCV2_CUSTOMER_DASHBOARD_URL_STAGING", "https://scv2-dashboard.gcp-staging.testingnow.me");
define("SCV2_CUSTOMER_DASHBOARD_URL_PRODUCTION", "https://belanjaku.app");
define("PRIVATE_KEY", "TXAjwm8k53PJG9NacLbyZavvQB2qBh43");
define("PRODUCTION_MODE", 0);

global $brand_id;
global $base_url;
global $base_url_dashboard;

// Define production env
$brand_id = BRAND_ID_PRODUCTION;
$base_url = SCV2_URL_PRODUCTION;
$base_url_dashboard = SCV2_CUSTOMER_DASHBOARD_URL_PRODUCTION; 

// Define production env
if ( PRODUCTION_MODE == 0 ) {
    $brand_id = BRAND_ID_STAGING;
    $base_url = SCV2_URL_STAGING;
    $base_url_dashboard = SCV2_CUSTOMER_DASHBOARD_URL_STAGING;
}

/*
 * Redirect to SCV2
 */
add_action( 'woocommerce_before_checkout_form', 'proceed_to_swift_checkout_v2' );

function proceed_to_swift_checkout_v2() {
    // Define global woocommerce
    global $woocommerce;

    // Define brand id
    global $brand_id;

    // Define SCV2 base url
    global $base_url;

    // Get customer email
    $customer_email = $woocommerce->cart->get_customer()->get_email();

    // Check user logged in or not.
    $isLogin = (is_user_logged_in() ? true : false);

    // Customer ID used as the cart key by default.
    $cart_key = $woocommerce->session->get_customer_id();

    // Get cart cookie... if any.
    $cookie = $woocommerce->session->get_session_cookie();

    // If a cookie exist, override cart key.
    if ( $cookie ) {
        $cart_key = $cookie[0];
    }

    // Cart hash
    $woocommerce_cart_hash_key = '';
    $woocommerce_cart_hash_value = '';

    // Items in cart
    $woocommerce_items_in_cart_key = '';
    $woocommerce_items_in_cart_value = '';

    // SCV2 session
    $scv2_session_key = '';
    $scv2_session_value = '';

    // Get wp_scv2_session_
    foreach ( $_COOKIE as $key => $value ) {
        if ( strpos( $key, 'woocommerce_cart_hash') !== FALSE ) {
            $woocommerce_cart_hash_key = $key;
            $woocommerce_cart_hash_value = $value;
        }
        if ( strpos( $key, 'woocommerce_items_in_cart') !== FALSE ) {
            $woocommerce_items_in_cart_key = $key;
            $woocommerce_items_in_cart_value = $value;
        }
        if ( strpos( $key, 'wp_scv2_session_') !== FALSE ) {
            $scv2_session_key = $key;
            $scv2_session_value = $value;
        }
    }

    // URL encode cookie
    $woocommerce_cart_hash_cookie = $woocommerce_cart_hash_key.'='.urlencode($woocommerce_cart_hash_value);
    $woocommerce_items_in_cart_cookie = $woocommerce_items_in_cart_key.'='.urlencode($woocommerce_items_in_cart_value);
    $scv2_cookie = $scv2_session_key.'='.urlencode($scv2_session_value);
    $complete_cookie = $woocommerce_cart_hash_cookie.';'.$woocommerce_items_in_cart_cookie.';'.$scv2_cookie;


    // Logged in customer id
    $customer_id = "";
    if ( $isLogin ) {
        $customer_id = base64_encode($woocommerce->session->get_customer_id());
    }

    // Payload for encrypting
    $payload = [
        "ecp_token" => $customer_id,
        "brand_id" => $brand_id,
        "cart_id" => base64_encode($complete_cookie.'|'.$cart_key),
        "currency" => get_woocommerce_currency(),
        "email" => $customer_email,
        "isLogin" => $isLogin
    ];

    // Encrypting payload
    $encryptionMethod = "AES-256-CBC";
    $iv = substr(PRIVATE_KEY, 0, 16);
    $encrypted = urlencode(openssl_encrypt(json_encode($payload), $encryptionMethod, PRIVATE_KEY, 0, $iv));


    $redirect_url = $base_url.'/authentication?state='.$encrypted;

    wp_redirect($redirect_url);
}

/*
 * Hide shipping address and shipping method, so customer would not confused.
 */
add_filter( 'woocommerce_cart_needs_shipping', 'filter_cart_needs_shipping' );

function filter_cart_needs_shipping( $needs_shipping ) {
    // Hide shipping address and method
    if ( is_cart() ) {
        $needs_shipping = false;
    }

    return $needs_shipping;
}

/*
 * Hide coupon code input, so customer would not confused.
 */
add_filter( 'woocommerce_coupons_enabled', 'hide_coupon_field_on_cart' );

function hide_coupon_field_on_cart( $enabled ) {
    // Hide coupon code input
    if ( is_cart() ) {
        $enabled = false;
    }

    return $enabled;
}

/*
 * Add Payment Confirm nav, redirect to SCV2.
 */
add_filter( 'woocommerce_nav_menu_items', 'add_logo_nav_menu', 10, 2 );

function add_logo_nav_menu($items, $args){

$newitems = '<li><a title="logo" href="#">LOGO</a></li>';
    $newitems .= $items;

return $newitems;
}

/*
 * Hide Pay and Cancel button, so customer would not confused.
 */
add_filter('woocommerce_my_account_my_orders_actions', 'remove_pay_and_cancel_button', 10, 2);

function remove_pay_and_cancel_button($actions, $order) {
    // Hide pay button
    unset( $actions['pay'] );
    
    // Hide cancel button
    unset( $actions['cancel'] );

    return $actions;
}

/*
 * Add tracking button on order list, redirect to SCV2.
 */
add_filter('woocommerce_my_account_my_orders_actions', 'add_tracking_button', 10, 2);

function add_tracking_button($actions, $order) {
    // Define brand id
    global $brand_id;

    // Define SCV2 base url
    global $base_url;

    // If order status is processing or completed, show tracking button
    if ( $order->get_status() == 'processing' || $order->get_status() == 'completed' ) {
        $actions['tracking'] = [
            "url" => $base_url.'/track-order/'.base64_encode($order->get_id().'|'.$brand_id),
            "name" => "Tracking"
        ];
    }

    return $actions;
}

/*
 * Redirect to SCV2 Customer Dashboard
 */
add_action( 'woocommerce_before_edit_account_address_form', 'proceed_to_swift_checkout_v2_customer_dashboard' );

function proceed_to_swift_checkout_v2_customer_dashboard() {
    // Define SCV2 base url dashboard
    global $base_url_dashboard;

    wp_redirect($base_url_dashboard.'/dashboard/account');
}

/*
 * Add tracking button on order detail, redirect to SCV2.
 */
add_action('woocommerce_order_details_after_order_table', 'add_tracking_button_order_detail');

function add_tracking_button_order_detail( $order ) {
    // Define brand id
    global $brand_id;

    // Define SCV2 base url
    global $base_url;

    // If order status is processing or completed, show tracking button
    if ( $order->get_status() == 'processing' || $order->get_status() == 'completed' ) {
        $url = $base_url.'/track-order/'.base64_encode($order->get_id().'|'.$brand_id);

        // Render HTML
        echo '<p class="tracking">
                <a href="'.$url.'" class="button">Tracking</a>
            </p>';
    }
}

/*
 * Add payment confirm button on order detail, redirect to SCV2.
 */
add_action('woocommerce_order_details_after_order_table', 'add_payment_confirm_button_detail_order');

function add_payment_confirm_button_detail_order( $order ) {
    // Define SCV2 base url dashboard
    global $base_url_dashboard;

    // If order status is BANK TRANSFER, show payment confirm
    if ( $order->get_payment_method_title() == 'BANK_TRANSFER - BANK TRANSFER' ) {
        $url = $base_url_dashboard.'/confirmpayment?orderId='.$order->get_id().'';

        // Render HTML
        echo '<p class="payment-confirm">
                <a href="'.$url.'" class="button">Payment Confirm</a>
            </p>';
    }
}

/*
 * Display shipping meta data awb number on order detail.
 */
add_filter( 'woocommerce_get_order_item_totals', 'remove_paymeny_method_row_from_emails', 10, 3 );

function remove_paymeny_method_row_from_emails( $total_rows, $order, $tax_display ){
    // Get awb number from meta data
    $tracking_number_arr = array();
    foreach ( $order->get_shipping_methods() as $sm ) {
        foreach ( $sm->get_meta_data() as $md ) {
            if ( $md->key == 'AWB' ) {
                $tracking_number_arr[] = '<u>'.$md->value.'</u>';
            }
        }
    }

    // If empty, return not found
    if ( empty($tracking_number_arr) ) {
        $tracking_number = 'Not Found';
    } else {
        $tracking_number = implode (", ", $tracking_number_arr);
    }

    $total_rows['shipping'] = [
        'label' => $total_rows['shipping']['label'],
        'value' => $total_rows['shipping']['value'].'<br /><small>AWB: '.$tracking_number.'</small>'
    ];

    return $total_rows;
}
