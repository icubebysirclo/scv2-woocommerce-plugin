<?php
/**
 * Handle data for the current customers session.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SCV2_Session
 */
abstract class SCV2_Session {

	/**
	 * Customer ID.
	 */
	protected $_customer_id;

	/**
	 * Session Data.
	 */
	protected $_data = array();

	/**
	 * Dirty when the session needs saving.
	 */
	protected $_dirty = false;

	/**
	 * Stores cart hash.
	 */
	protected $_cart_hash;

	/**
	 * Init hooks and session data. Extended by child classes.
	 */
	public function init() {}

	/**
	 * Cleanup session data. Extended by child classes.
	 */
	public function cleanup_sessions() {}

	/**
	 * Magic get method.
	 */
	public function __get( $key ) {
		return $this->get( $key );
	}

	/**
	 * Magic set method.
	 */
	public function __set( $key, $value ) {
		$this->set( $key, $value );
	}

	/**
	 * Magic isset method.
	 */
	public function __isset( $key ) {
		return isset( $this->_data[ sanitize_title( $key ) ] );
	}

	/**
	 * Magic unset method.
	 */
	public function __unset( $key ) {
		if ( isset( $this->_data[ $key ] ) ) {
			unset( $this->_data[ $key ] );
			$this->_dirty = true;
		}
	}

	/**
	 * Get a session variable.
	 */
	public function get( $key, $default = null ) {
		$key = sanitize_key( $key );
		return isset( $this->_data[ $key ] ) ? maybe_unserialize( $this->_data[ $key ] ) : $default;
	}

	/**
	 * Set a session variable.
	 */
	public function set( $key, $value ) {
		if ( $value !== $this->get( $key ) ) {
			$this->_data[ sanitize_key( $key ) ] = maybe_serialize( $value );
			$this->_dirty                        = true;
		}
	}

	/**
	 * Get customer ID.
	 */
	public function get_customer_id() {
		return $this->_customer_id;
	}

	/**
	 * Set customer ID.
	 */
	public function set_customer_id( $customer_id ) {
		$this->_customer_id = $customer_id;
	}

	/**
	 * Get session data
	 */
	public function get_data() {
		return $this->_data;
	}

	/**
	 * Get cart hash
	 */
	public function get_cart_hash() {
		return $this->_cart_hash;
	}

}
