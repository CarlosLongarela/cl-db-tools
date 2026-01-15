<?php
/**
 * Database Optimizer class
 *
 * Executes optimization operations on the database
 *
 * @package CL_DB_Tools
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Code is poetry but I am not a poet, sorry!' );
}

/**
 * CL_DB_Optimizer class
 */
class CL_DB_Optimizer {

	/**
	 * Convert table to InnoDB
	 *
	 * @param string $table_name Table name.
	 * @return bool
	 */
	public static function convert_to_innodb( $table_name ) {
		global $wpdb;

		$query = CL_DB_Query_Builder::get_convert_to_innodb_query( $table_name );

		if ( false === $query ) {
			return false;
		}

		$result = $wpdb->query( $query );

		if ( false === $result ) {
			CL_DB_Security::log( "Error converting table {$table_name} to InnoDB: " . $wpdb->last_error, 'error' );
			return false;
		}

		// Clear cache after modification
		CL_DB_Analyzer::clear_cache();

		CL_DB_Security::log( "Successfully converted table {$table_name} to InnoDB", 'notice' );
		return true;
	}

	/**
	 * Convert all non-InnoDB tables to InnoDB
	 *
	 * @return array Results array with success and failed tables
	 */
	public static function convert_all_to_innodb() {
		$non_innodb_tables = CL_DB_Analyzer::get_non_innodb_tables();
		$results = array(
			'success' => array(),
			'failed' => array(),
		);

		if ( empty( $non_innodb_tables ) ) {
			return $results;
		}

		foreach ( $non_innodb_tables as $table ) {
			if ( self::convert_to_innodb( $table['table_name'] ) ) {
				$results['success'][] = $table['table_name'];
			} else {
				$results['failed'][] = $table['table_name'];
			}
		}

		return $results;
	}

	/**
	 * Update autoload value for an option
	 *
	 * @param string $option_name Option name.
	 * @param string $autoload_value Autoload value ('yes', 'on', 'no', or 'off').
	 * @return bool
	 */
	public static function update_autoload( $option_name, $autoload_value ) {
		global $wpdb;

		$query = CL_DB_Query_Builder::get_update_autoload_query( $option_name, $autoload_value );

		if ( false === $query ) {
			return false;
		}

		$result = $wpdb->query( $query );

		if ( false === $result ) {
			CL_DB_Security::log( "Error updating autoload for option {$option_name}: " . $wpdb->last_error, 'error' );
			return false;
		}

		// Clear option cache
		wp_cache_delete( $option_name, 'options' );

		// Clear analyzer cache
		CL_DB_Analyzer::clear_cache();

		CL_DB_Security::log( "Successfully updated autoload for option {$option_name} to {$autoload_value}", 'notice' );
		return true;
	}

	/**
	 * Delete an option
	 *
	 * @param string $option_name Option name.
	 * @return bool
	 */
	public static function delete_option( $option_name ) {
		global $wpdb;

		$query = CL_DB_Query_Builder::get_delete_option_query( $option_name );

		if ( false === $query ) {
			return false;
		}

		$result = $wpdb->query( $query );

		if ( false === $result ) {
			CL_DB_Security::log( "Error deleting option {$option_name}: " . $wpdb->last_error, 'error' );
			return false;
		}

		// Clear option cache
		wp_cache_delete( $option_name, 'options' );

		// Clear analyzer cache
		CL_DB_Analyzer::clear_cache();

		CL_DB_Security::log( "Successfully deleted option {$option_name}", 'notice' );
		return true;
	}

	/**
	 * Delete autosave posts
	 *
	 * @param int $keep_last Number of recent autosaves to keep.
	 * @return int|false Number of deleted posts or false on error
	 */
	public static function delete_autosaves( $keep_last = 0 ) {
		global $wpdb;

		$query = CL_DB_Query_Builder::get_delete_autosaves_query( $keep_last );
		$result = $wpdb->query( $query );

		if ( false === $result ) {
			CL_DB_Security::log( 'Error deleting autosaves: ' . $wpdb->last_error, 'error' );
			return false;
		}

		CL_DB_Security::log( "Successfully deleted {$result} autosave posts", 'notice' );
		return $result;
	}

	/**
	 * Delete orphaned postmeta
	 *
	 * @return int|false Number of deleted items or false on error
	 */
	public static function delete_orphaned_postmeta() {
		global $wpdb;

		$query = CL_DB_Query_Builder::get_delete_orphaned_postmeta_query();
		$result = $wpdb->query( $query );

		if ( false === $result ) {
			CL_DB_Security::log( 'Error deleting orphaned postmeta: ' . $wpdb->last_error, 'error' );
			return false;
		}

		CL_DB_Security::log( "Successfully deleted {$result} orphaned postmeta entries", 'notice' );
		return $result;
	}

	/**
	 * Delete orphaned usermeta
	 *
	 * @return int|false Number of deleted items or false on error
	 */
	public static function delete_orphaned_usermeta() {
		global $wpdb;

		$query = CL_DB_Query_Builder::get_delete_orphaned_usermeta_query();
		$result = $wpdb->query( $query );

		if ( false === $result ) {
			CL_DB_Security::log( 'Error deleting orphaned usermeta: ' . $wpdb->last_error, 'error' );
			return false;
		}

		CL_DB_Security::log( "Successfully deleted {$result} orphaned usermeta entries", 'notice' );
		return $result;
	}

	/**
	 * Delete expired transients
	 *
	 * @return int|false Number of deleted items or false on error
	 */
	public static function delete_expired_transients() {
		global $wpdb;

		$query = CL_DB_Query_Builder::get_delete_expired_transients_query();
		$result = $wpdb->query( $query );

		if ( false === $result ) {
			CL_DB_Security::log( 'Error deleting expired transients: ' . $wpdb->last_error, 'error' );
			return false;
		}

		CL_DB_Security::log( "Successfully deleted expired transients", 'notice' );
		return $result;
	}

	/**
	 * Delete all transients
	 *
	 * @return int|false Number of deleted items or false on error
	 */
	public static function delete_all_transients() {
		global $wpdb;

		$query = CL_DB_Query_Builder::get_delete_all_transients_query();
		$result = $wpdb->query( $query );

		if ( false === $result ) {
			CL_DB_Security::log( 'Error deleting all transients: ' . $wpdb->last_error, 'error' );
			return false;
		}

		CL_DB_Security::log( "Successfully deleted all transients", 'notice' );
		return $result;
	}

	/**
	 * Delete expired WooCommerce sessions
	 *
	 * @return int|false Number of deleted items or false on error
	 */
	public static function delete_expired_wc_sessions() {
		global $wpdb;

		if ( ! CL_DB_Analyzer::is_woocommerce_active() ) {
			return false;
		}

		$query = CL_DB_Query_Builder::get_delete_expired_wc_sessions_query();

		if ( false === $query ) {
			return false;
		}

		$result = $wpdb->query( $query );

		if ( false === $result ) {
			CL_DB_Security::log( 'Error deleting expired WC sessions: ' . $wpdb->last_error, 'error' );
			return false;
		}

		CL_DB_Security::log( "Successfully deleted {$result} expired WooCommerce sessions", 'notice' );
		return $result;
	}

	/**
	 * Delete orphaned WooCommerce order items
	 *
	 * @return int|false Number of deleted items or false on error
	 */
	public static function delete_orphaned_wc_order_items() {
		global $wpdb;

		if ( ! CL_DB_Analyzer::is_woocommerce_active() ) {
			return false;
		}

		$query = CL_DB_Query_Builder::get_delete_orphaned_wc_order_items_query();

		if ( false === $query ) {
			return false;
		}

		$result = $wpdb->query( $query );

		if ( false === $result ) {
			CL_DB_Security::log( 'Error deleting orphaned WC order items: ' . $wpdb->last_error, 'error' );
			return false;
		}

		CL_DB_Security::log( "Successfully deleted {$result} orphaned WooCommerce order items", 'notice' );
		return $result;
	}

	/**
	 * Delete orphaned WooCommerce order item meta
	 *
	 * @return int|false Number of deleted items or false on error
	 */
	public static function delete_orphaned_wc_order_itemmeta() {
		global $wpdb;

		if ( ! CL_DB_Analyzer::is_woocommerce_active() ) {
			return false;
		}

		$query = CL_DB_Query_Builder::get_delete_orphaned_wc_order_itemmeta_query();

		if ( false === $query ) {
			return false;
		}

		$result = $wpdb->query( $query );

		if ( false === $result ) {
			CL_DB_Security::log( 'Error deleting orphaned WC order item meta: ' . $wpdb->last_error, 'error' );
			return false;
		}

		CL_DB_Security::log( "Successfully deleted {$result} orphaned WooCommerce order item meta entries", 'notice' );
		return $result;
	}

	/**
	 * Optimize table
	 *
	 * @param string $table_name Table name.
	 * @return bool
	 */
	public static function optimize_table( $table_name ) {
		global $wpdb;

		$query = CL_DB_Query_Builder::get_optimize_table_query( $table_name );

		if ( false === $query ) {
			return false;
		}

		$result = $wpdb->query( $query );

		if ( false === $result ) {
			CL_DB_Security::log( "Error optimizing table {$table_name}: " . $wpdb->last_error, 'error' );
			return false;
		}

		// Clear cache after optimization
		CL_DB_Analyzer::clear_cache();

		CL_DB_Security::log( "Successfully optimized table {$table_name}", 'notice' );
		return true;
	}

	/**
	 * Optimize all tables
	 *
	 * @return array Results array with success and failed tables
	 */
	public static function optimize_all_tables() {
		$tables = CL_DB_Analyzer::get_tables_info();
		$results = array(
			'success' => array(),
			'failed' => array(),
		);

		if ( empty( $tables ) ) {
			return $results;
		}

		foreach ( $tables as $table ) {
			if ( self::optimize_table( $table['table_name'] ) ) {
				$results['success'][] = $table['table_name'];
			} else {
				$results['failed'][] = $table['table_name'];
			}
		}

		return $results;
	}

	/**
	 * Analyze table
	 *
	 * @param string $table_name Table name.
	 * @return bool
	 */
	public static function analyze_table( $table_name ) {
		global $wpdb;

		$query = CL_DB_Query_Builder::get_analyze_table_query( $table_name );

		if ( false === $query ) {
			return false;
		}

		$result = $wpdb->query( $query );

		if ( false === $result ) {
			CL_DB_Security::log( "Error analyzing table {$table_name}: " . $wpdb->last_error, 'error' );
			return false;
		}

		CL_DB_Security::log( "Successfully analyzed table {$table_name}", 'notice' );
		return true;
	}

	/**
	 * Repair table
	 *
	 * @param string $table_name Table name.
	 * @return bool
	 */
	public static function repair_table( $table_name ) {
		global $wpdb;

		$query = CL_DB_Query_Builder::get_repair_table_query( $table_name );

		if ( false === $query ) {
			return false;
		}

		$result = $wpdb->query( $query );

		if ( false === $result ) {
			CL_DB_Security::log( "Error repairing table {$table_name}: " . $wpdb->last_error, 'error' );
			return false;
		}

		CL_DB_Security::log( "Successfully repaired table {$table_name}", 'notice' );
		return true;
	}
}
