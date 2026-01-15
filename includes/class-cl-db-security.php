<?php
/**
 * Security helper class
 *
 * Handles all security-related functionality including nonces, capabilities, and SQL injection prevention
 *
 * @package CL_DB_Tools
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Code is poetry but I am not a poet, sorry!' );
}

/**
 * CL_DB_Security class
 */
class CL_DB_Security {

	/**
	 * Required capability to access plugin features
	 *
	 * @var string
	 */
	const REQUIRED_CAPABILITY = 'manage_options';

	/**
	 * Nonce action name
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'cl_db_tools_action';

	/**
	 * Nonce field name
	 *
	 * @var string
	 */
	const NONCE_FIELD = 'cl_db_tools_nonce';

	/**
	 * Check if current user has required capability
	 *
	 * @return bool
	 */
	public static function current_user_can() {
		return current_user_can( self::REQUIRED_CAPABILITY );
	}

	/**
	 * Verify nonce and user capability
	 *
	 * @param string $nonce Nonce value to verify.
	 * @return bool
	 */
	public static function verify_request( $nonce ) {
		if ( ! self::current_user_can() ) {
			return false;
		}

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Create nonce field for forms
	 *
	 * @param bool $echo Whether to echo or return the field.
	 * @return string
	 */
	public static function nonce_field( $echo = true ) {
		return wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD, true, $echo );
	}

	/**
	 * Get nonce value
	 *
	 * @return string
	 */
	public static function create_nonce() {
		return wp_create_nonce( self::NONCE_ACTION );
	}

	/**
	 * Sanitize table name
	 *
	 * Ensures table name is safe for use in SQL queries
	 *
	 * @param string $table_name Table name to sanitize.
	 * @return string|false Sanitized table name or false if invalid
	 */
	public static function sanitize_table_name( $table_name ) {
		global $wpdb;

		// Remove any non-alphanumeric characters except underscore
		$table_name = preg_replace( '/[^a-zA-Z0-9_]/', '', $table_name );

		// Verify table exists in WordPress database
		$existing_tables = $wpdb->get_col( 'SHOW TABLES' );

		if ( ! in_array( $table_name, $existing_tables, true ) ) {
			return false;
		}

		return $table_name;
	}

	/**
	 * Sanitize option name
	 *
	 * @param string $option_name Option name to sanitize.
	 * @return string Sanitized option name
	 */
	public static function sanitize_option_name( $option_name ) {
		return sanitize_key( $option_name );
	}

	/**
	 * Sanitize integer value
	 *
	 * @param mixed $value Value to sanitize.
	 * @return int
	 */
	public static function sanitize_int( $value ) {
		return absint( $value );
	}

	/**
	 * Sanitize array of integers
	 *
	 * @param array $values Array of values to sanitize.
	 * @return array
	 */
	public static function sanitize_int_array( $values ) {
		if ( ! is_array( $values ) ) {
			return array();
		}

		return array_map( 'absint', $values );
	}

	/**
	 * Escape SQL LIKE pattern
	 *
	 * @param string $pattern Pattern to escape.
	 * @return string
	 */
	public static function esc_like( $pattern ) {
		global $wpdb;
		return $wpdb->esc_like( $pattern );
	}

	/**
	 * Check if action requires backup confirmation
	 *
	 * @param string $action Action being performed.
	 * @return bool
	 */
	public static function requires_backup_confirmation( $action ) {
		$destructive_actions = array(
			'delete_autoload',
			'update_autoload',
			'delete_autosaves',
			'delete_orphaned_postmeta',
			'delete_orphaned_usermeta',
			'delete_wc_sessions',
			'delete_transients',
			'optimize_tables',
			'convert_to_innodb',
		);

		return in_array( $action, $destructive_actions, true );
	}

	/**
	 * Log security event
	 *
	 * @param string $message Log message.
	 * @param string $level Log level (notice, warning, error).
	 */
	public static function log( $message, $level = 'notice' ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( sprintf( '[CL DB Tools] [%s] %s', strtoupper( $level ), $message ) );
		}
	}
}
