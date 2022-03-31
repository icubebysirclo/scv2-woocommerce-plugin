<?php
/*
 * SETUP
 */
define("BRAND_ID", "398809496132060018");
define("SCV2_URL", "https://scv2.gcp-staging.testingnow.me");
define("PRIVATE_KEY", "TXAjwm8k53PJG9NacLbyZavvQB2qBh43");
define("ECP_TOKEN", "ck_449950aa1b5f86b2cb0de9660be50ee22757b479|cs_1b55f9d32b6185a6daea1c8bd99f00a801b6de7f");

/*
 * Redirect to SCV2
 */
add_action( 'woocommerce_before_checkout_form', 'proceed_to_swift_checkout_v2' );

function proceed_to_swift_checkout_v2() {
    // Define global woocommerce 
    global $woocommerce;

    // Get customer email
    $customerEmail = $woocommerce->cart->get_customer()->get_email();

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

    // Payload for encrypting
    $payload = [
        "ecp_token" => ECP_TOKEN,
        "brand_id" => BRAND_ID,
        "cart_id" => $cart_key,
        "currency" => get_woocommerce_currency(),
        "email" => $customerEmail,
        "isLogin" => $isLogin
    ];

    // print_r(json_encode($payload));die();

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
