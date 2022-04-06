<?php
/*
 * SETUP
 */
define("BRAND_ID", "398809496132060018");
define("SCV2_URL", "https://scv2.gcp-staging.testingnow.me");
define("PRIVATE_KEY", "TXAjwm8k53PJG9NacLbyZavvQB2qBh43");

/*
 * Redirect to SCV2
 */
add_action( 'woocommerce_before_checkout_form', 'proceed_to_swift_checkout_v2' );

function proceed_to_swift_checkout_v2() {
    // Define global woocommerce 
    global $woocommerce;

    // Get customer email
    $customer_email = $woocommerce->cart->get_customer()->get_email();

    // Check user logged in or not.
    $isLogin = (is_user_logged_in() ? true : false);

    // Customer ID used as the cart key by default.
    $cart_key = $woocommerce->session->get_customer_id();

    // Get cart cookie... if any.
    $cookie = $woocommerce->session->get_session_cookie();

    // Get wp_scv2_session_
    foreach ( $_COOKIE as $key => $value ) {
        if ( strpos( $key, 'wp_scv2_session_') !== FALSE ) {
            $scv2_session_key = $key;
            $scv2_session_value = $value;
        }
    }

    // URL encode wp_scv2_session_
    $scv2_cookie = $scv2_session_key.'='.urlencode($scv2_session_value);

    // If a cookie exist, override cart key.
    if ( $cookie ) {
        $cart_key = $cookie[0];
    }

    // Logged in customer id
    $customer_id = "";
    if ( $isLogin ) {
        $customer_id = base64_encode($woocommerce->session->get_customer_id());
    }

    // Payload for encrypting
    $payload = [
        "ecp_token" => $customer_id,
        "brand_id" => BRAND_ID,
        "cart_id" => base64_encode($scv2_cookie.'|'.$cart_key),
        "currency" => get_woocommerce_currency(),
        "email" => $customer_email,
        "isLogin" => $isLogin
    ];

    // Encrypting payload
    $encryptionMethod = "AES-256-CBC";
    $iv = substr(PRIVATE_KEY, 0, 16);
    $encrypted = urlencode(openssl_encrypt(json_encode($payload), $encryptionMethod, PRIVATE_KEY, 0, $iv));

    // redirecting to SCV2
    $redirect_url = SCV2_URL.'/authentication?state='.$encrypted;
    wp_redirect($redirect_url);
}

/*
 * Hide shipping address and shipping method, so customer would not confused.
 */
add_filter( 'woocommerce_cart_needs_shipping', 'filter_cart_needs_shipping' );

function filter_cart_needs_shipping( $needs_shipping ) {
    if ( is_cart() ) {
        $needs_shipping = false;
    }
    return $needs_shipping;
}
