<?php
/**
 * SCV2 Data Exception Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data exception class.
 */
class SCV2_Data_Exception extends Exception {

	/**
	 * Sanitized error code.
	 */
	public $error_code;

	/**
	 * Error extra data.
	 */
	public $additional_data = array();

	/**
	 * Setup exception.
	 */
	public function __construct( $error_code, $message, $http_status_code = 400, $additional_data = array() ) {
		$this->error_code      = $error_code;
		$this->additional_data = array_filter( (array) $additional_data );

		SCV2_Logger::log( $message, 'error' );

		parent::__construct( $message, $http_status_code );
	}

	/**
	 * Returns the error code.
	 */
	public function getErrorCode() {
		return $this->error_code;
	}

	/**
	 * Returns additional error data.
	 */
	public function getAdditionalData() {
		return $this->additional_data;
	}

}
