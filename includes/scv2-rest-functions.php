<?php
/**
 * SCV2 REST Functions.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wrapper for deprecated hook so we can apply some extra logic.
 */
function scv2_deprecated_hook( $hook, $version, $replacement = null, $message = null ) {
	if ( is_ajax() || SCV2_Authentication::is_rest_api_request() ) {
		do_action( 'deprecated_hook_run', $hook, $replacement, $version, $message );

		$message = empty( $message ) ? '' : ' ' . $message;
		/* translators: %1$s: filter name, %2$s: version */
		$log_string = sprintf( esc_html__( '%1$s is deprecated since version %2$s', 'cart-rest-api-for-woocommerce' ), $hook, $version );
		/* translators: %s: filter name */
		$log_string .= $replacement ? sprintf( esc_html__( '! Use %s instead.', 'cart-rest-api-for-woocommerce' ), $replacement ) : esc_html__( ' with no alternative available.', 'cart-rest-api-for-woocommerce' );

		SCV2_Logger::log( $log_string . $message, 'debug' );
	} else {
		_deprecated_hook( $hook, $version, $replacement, $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
} // END scv2_deprecated_hook()

/**
 * Wrapper for deprecated filter so we can apply some extra logic.
 */
function scv2_deprecated_filter( $filter, $args = array(), $version = '', $replacement = null, $message = null ) {
	if ( is_ajax() || SCV2_Authentication::is_rest_api_request() ) {
		do_action( 'deprecated_filter_run', $filter, $args, $replacement, $version, $message );

		$message = empty( $message ) ? '' : ' ' . $message;
		/* translators: %1$s: filter name, %2$s: version */
		$log_string = sprintf( esc_html__( '%1$s is deprecated since version %2$s', 'cart-rest-api-for-woocommerce' ), $filter, $version );
		/* translators: %s: filter name */
		$log_string .= $replacement ? sprintf( esc_html__( '! Use %s instead.', 'cart-rest-api-for-woocommerce' ), $replacement ) : esc_html__( ' with no alternative available.', 'cart-rest-api-for-woocommerce' );

		SCV2_Logger::log( $log_string . $message, 'debug' );
	} else {
		return apply_filters_deprecated( $filter, $args, $version, $replacement, $message );
	}
} // END scv2_deprecated_filter()

/**
 * Parses and formats a date for ISO8601/RFC3339.
 */
function scv2_prepare_date_response( $date, $utc = true ) {
	if ( is_numeric( $date ) ) {
		$date = new SCV2_DateTime( "@$date", new DateTimeZone( 'UTC' ) );
		$date->setTimezone( new DateTimeZone( wc_timezone_string() ) );
	} elseif ( is_string( $date ) ) {
		$date = new SCV2_DateTime( $date, new DateTimeZone( 'UTC' ) );
		$date->setTimezone( new DateTimeZone( wc_timezone_string() ) );
	}

	if ( ! is_a( $date, 'SCV2_DateTime' ) ) {
		return null;
	}

	// Get timestamp before changing timezone to UTC.
	return gmdate( 'Y-m-d\TH:i:s', $utc ? $date->getTimestamp() : $date->getOffsetTimestamp() );
} // END scv2_prepare_date_response()

/**
 * Returns image mime types users are allowed to upload via the API.
 */
function scv2_allowed_image_mime_types() {
	return apply_filters(
		'scv2_allowed_image_mime_types',
		array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'gif'          => 'image/gif',
			'png'          => 'image/png',
			'bmp'          => 'image/bmp',
			'tiff|tif'     => 'image/tiff',
			'ico'          => 'image/x-icon',
		)
	);
} // END scv2_allowed_image_mime_types()

/**
 * SCV2 upload directory.
 */
function scv2_upload_dir( $pathdata ) {
	if ( empty( $pathdata['subdir'] ) ) {
		$pathdata['path']   = $pathdata['path'] . '/scv2_uploads/' . md5( WC()->session->get_customer_id() );
		$pathdata['url']    = $pathdata['url'] . '/scv2_uploads/' . md5( WC()->session->get_customer_id() );
		$pathdata['subdir'] = '/scv2_uploads/' . md5( WC()->session->get_customer_id() );
	} else {
		$subdir             = '/scv2_uploads/' . md5( WC()->session->get_customer_id() );
		$pathdata['path']   = str_replace( $pathdata['subdir'], $subdir, $pathdata['path'] );
		$pathdata['url']    = str_replace( $pathdata['subdir'], $subdir, $pathdata['url'] );
		$pathdata['subdir'] = str_replace( $pathdata['subdir'], $subdir, $pathdata['subdir'] );
	}

	return apply_filters( 'scv2_upload_dir', $pathdata );
} // END scv2_upload_dir()

/**
 * Upload a file.
 */
function scv2_upload_file( $file ) {
	// wp_handle_upload function is part of wp-admin.
	if ( ! function_exists( 'wp_handle_upload' ) ) {
		include_once ABSPATH . 'wp-admin/includes/file.php';
	}

	include_once ABSPATH . 'wp-admin/includes/media.php';

	add_filter( 'upload_dir', 'scv2_upload_dir' );

	$upload = wp_handle_upload( $file, array( 'test_form' => false ) );

	remove_filter( 'upload_dir', 'scv2_upload_dir' );

	return $upload;
} // END scv2_upload_file()

/**
 * Upload image from URL.
 */
function scv2_upload_image_from_url( $image_url ) {
	$parsed_url = wp_parse_url( $image_url );

	// Check parsed URL.
	if ( ! $parsed_url || ! is_array( $parsed_url ) ) {
		/* translators: %s: image URL */
		return new WP_Error( 'scv2_invalid_image_url', sprintf( __( 'Invalid URL %s.', 'cart-rest-api-for-woocommerce' ), $image_url ), array( 'status' => 400 ) );
	}

	// Ensure url is valid.
	$image_url = esc_url_raw( $image_url );

	// download_url function is part of wp-admin.
	if ( ! function_exists( 'download_url' ) ) {
		include_once ABSPATH . 'wp-admin/includes/file.php';
	}

	$file_array         = array();
	$file_array['name'] = basename( current( explode( '?', $image_url ) ) );

	// Download file to temp location.
	$file_array['tmp_name'] = download_url( $image_url );

	// If error storing temporarily, return the error.
	if ( is_wp_error( $file_array['tmp_name'] ) ) {
		return new WP_Error(
			'scv2_invalid_remote_image_url',
			/* translators: %s: image URL */
			sprintf( __( 'Error getting remote image %s.', 'cart-rest-api-for-woocommerce' ), $image_url ) . ' '
			/* translators: %s: error message */
			. sprintf( __( 'Error: %s', 'cart-rest-api-for-woocommerce' ), $file_array['tmp_name']->get_error_message() ),
			array( 'status' => 400 )
		);
	}

	add_filter( 'upload_dir', 'scv2_upload_dir' );

	// Do the validation and storage stuff.
	$file = wp_handle_sideload(
		$file_array,
		array(
			'test_form' => false,
			'mimes'     => scv2_allowed_image_mime_types(),
		),
		current_time( 'Y/m' )
	);

	remove_filter( 'upload_dir', 'scv2_upload_dir' );

	if ( isset( $file['error'] ) ) {
		@unlink( $file_array['tmp_name'] ); // @codingStandardsIgnoreLine.

		/* translators: %s: error message */
		return new WP_Error( 'scv2_invalid_image', sprintf( __( 'Invalid image: %s', 'cart-rest-api-for-woocommerce' ), $file['error'] ), array( 'status' => 400 ) );
	}

	do_action( 'scv2_uploaded_image_from_url', $file, $image_url );

	return $file;
} // END scv2_upload_image_from_url()

/**
 * Set uploaded image as attachment.
 */
function scv2_set_uploaded_image_as_attachment( $upload, $id = 0 ) {
	$info    = wp_check_filetype( $upload['file'] );
	$title   = '';
	$content = '';

	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		include_once ABSPATH . 'wp-admin/includes/image.php';
	}

	$image_meta = wp_read_image_metadata( $upload['file'] );
	if ( $image_meta ) {
		if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) ) {
			$title = wc_clean( $image_meta['title'] );
		}
		if ( trim( $image_meta['caption'] ) ) {
			$content = wc_clean( $image_meta['caption'] );
		}
	}

	$attachment = array(
		'post_mime_type' => $info['type'],
		'guid'           => $upload['url'],
		'post_parent'    => $id,
		'post_title'     => $title ? $title : basename( $upload['file'] ),
		'post_content'   => $content,
	);

	$attachment_id = wp_insert_attachment( $attachment, $upload['file'], $id );
	if ( ! is_wp_error( $attachment_id ) ) {
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	}

	return $attachment_id;
} // END scv2_set_uploaded_image_as_attachment()

/**
 * Format the price with a currency symbol without HTML wrappers.
 */
function scv2_price_no_html( $price, $args = array() ) {
	$args = apply_filters(
		'scv2_price_args',
		wp_parse_args(
			$args,
			array(
				'ex_tax_label'       => false,
				'currency'           => '',
				'decimal_separator'  => wc_get_price_decimal_separator(),
				'thousand_separator' => wc_get_price_thousand_separator(),
				'decimals'           => wc_get_price_decimals(),
				'price_format'       => get_woocommerce_price_format(),
			)
		)
	);

	$original_price = $price;

	// Convert to float to avoid issues on PHP 8.
	$price = (float) $price;

	$unformatted_price = $price;
	$negative          = $price < 0;

	/**
	 * Filter raw price.
	 */
	$price = apply_filters( 'raw_woocommerce_price', $negative ? $price * -1 : $price, $original_price );

	/**
	 * Filter formatted price.
	 */
	$price = apply_filters( 'formatted_woocommerce_price', number_format( $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ), $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'], $original_price );

	if ( apply_filters( 'woocommerce_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
		$price = wc_trim_zeros( $price );
	}

	$formatted_price = ( $negative ? '-' : '' ) . sprintf( $args['price_format'], get_woocommerce_currency_symbol( $args['currency'] ), $price );
	$return          = $formatted_price;

	if ( $args['ex_tax_label'] && wc_tax_enabled() ) {
		$return .= ' ' . WC()->countries->ex_tax_or_vat();
	}

	$return = html_entity_decode( $return );

	/**
	 * Filters the string of price markup.
	 */
	return apply_filters( 'scv2_price_no_html', $return, $price, $args, $unformatted_price, $original_price );
} // END scv2_price_no_html()


/**
 * Add to cart messages.
 */
function scv2_add_to_cart_message( $products, $show_qty = false, $return = false ) {
	$titles = array();
	$count  = 0;

	if ( ! is_array( $products ) ) {
		$products = array( $products => 1 );
		$show_qty = false;
	}

	if ( ! $show_qty ) {
		$products = array_fill_keys( array_keys( $products ), 1 );
	}

	foreach ( $products as $product_id => $qty ) {
		/* translators: %s: product name */
		$titles[] = apply_filters( 'scv2_add_to_cart_qty_html', ( $qty > 1 ? $qty . ' &times; ' : '' ), $product_id ) . apply_filters( 'scv2_add_to_cart_item_name_in_quotes', sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'cart-rest-api-for-woocommerce' ), wp_strip_all_tags( get_the_title( $product_id ) ) ), $product_id );
		$count   += $qty;
	}

	$titles = array_filter( $titles );

	/* translators: %s: product name */
	$added_text = sprintf( _n( '%s has been added to your cart.', '%s have been added to your cart.', $count, 'cart-rest-api-for-woocommerce' ), wc_format_list_of_items( $titles ) );

	$message = apply_filters( 'scv2_add_to_cart_message_html', esc_html( $added_text ), $products, $show_qty );

	if ( $return ) {
		return $message;
	} else {
		wc_add_notice( $message, 'success' );
	}
} // END scv2_add_to_cart_message()
