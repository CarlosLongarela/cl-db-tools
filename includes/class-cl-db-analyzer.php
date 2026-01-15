<?php
/**
 * Database Analyzer class
 *
 * Analyzes database and retrieves information
 *
 * @package CL_DB_Tools
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Code is poetry but I am not a poet, sorry!' );
}

/**
 * CL_DB_Analyzer class
 */
class CL_DB_Analyzer {

	/**
	 * WordPress core table suffixes (without prefix)
	 *
	 * These are the default tables created by a standard WordPress installation.
	 * This list may need to be updated if WordPress adds new core tables.
	 *
	 * @var array
	 */
	private static $wp_core_tables = array(
		'commentmeta',
		'comments',
		'links',
		'options',
		'postmeta',
		'posts',
		'term_relationships',
		'term_taxonomy',
		'termmeta',
		'terms',
		'usermeta',
		'users',
	);

	/**
	 * Check if a table is a WordPress core table
	 *
	 * @param string $table_name Full table name including prefix.
	 * @return bool
	 */
	public static function is_wp_core_table( $table_name ) {
		global $wpdb;

		foreach ( self::$wp_core_tables as $core_table ) {
			if ( $table_name === $wpdb->prefix . $core_table ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get WordPress core table suffixes
	 *
	 * @return array
	 */
	public static function get_wp_core_tables() {
		return self::$wp_core_tables;
	}

	/**
	 * Get all tables information
	 *
	 * @param bool $force Force refresh cache.
	 * @return array|false
	 */
	public static function get_tables_info( $force = false ) {
		$cache_key = 'cl_db_tools_tables_info';

		if ( ! $force ) {
			$cached = get_transient( $cache_key );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		global $wpdb;

		$query = CL_DB_Query_Builder::get_tables_info_query();
		$results = $wpdb->get_results( $query, ARRAY_A );

		if ( $wpdb->last_error ) {
			CL_DB_Security::log( 'Error getting tables info: ' . $wpdb->last_error, 'error' );
			return false;
		}

		// Cache using configured expiration time
		set_transient( $cache_key, $results, CL_DB_TOOLS_CACHE_EXPIRATION );

		return $results;
	}

	/**
	 * Get database size information
	 *
	 * @param bool $force Force refresh cache.
	 * @return object|false
	 */
	public static function get_database_size( $force = false ) {
		$cache_key = 'cl_db_tools_database_size';

		if ( ! $force ) {
			$cached = get_transient( $cache_key );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		global $wpdb;

		$query = CL_DB_Query_Builder::get_database_size_query();
		$result = $wpdb->get_row( $query );

		if ( $wpdb->last_error ) {
			CL_DB_Security::log( 'Error getting database size: ' . $wpdb->last_error, 'error' );
			return false;
		}

		// Cache using configured expiration time
		set_transient( $cache_key, $result, CL_DB_TOOLS_CACHE_EXPIRATION );

		return $result;
	}

	/**
	 * Get tables not using InnoDB engine
	 *
	 * @return array|false
	 */
	public static function get_non_innodb_tables() {
		global $wpdb;

		$query = CL_DB_Query_Builder::get_non_innodb_tables_query();
		$results = $wpdb->get_results( $query, ARRAY_A );

		if ( $wpdb->last_error ) {
			CL_DB_Security::log( 'Error getting non-InnoDB tables: ' . $wpdb->last_error, 'error' );
			return false;
		}

		return $results;
	}

	/**
	 * Get autoload options information
	 *
	 * @param bool $force Force refresh cache.
	 * @return array
	 */
	public static function get_autoload_info( $force = false ) {
		$cache_key = 'cl_db_tools_autoload_info';

		if ( ! $force ) {
			$cached = get_transient( $cache_key );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		global $wpdb;

		$total_query = CL_DB_Query_Builder::get_autoload_total_query();
		$total = $wpdb->get_row( $total_query );

		$options_query = CL_DB_Query_Builder::get_autoload_options_query();
		$options = $wpdb->get_results( $options_query, ARRAY_A );

		$result = array(
			'total' => $total,
			'options' => $options,
		);

		// Cache using configured expiration time
		set_transient( $cache_key, $result, CL_DB_TOOLS_CACHE_EXPIRATION );

		return $result;
	}

	/**
	 * Get autosave posts information
	 *
	 * @return array
	 */
	public static function get_autosave_info() {
		global $wpdb;

		$count_query = CL_DB_Query_Builder::get_autosave_count_query();
		$count = $wpdb->get_var( $count_query );

		$posts_query = CL_DB_Query_Builder::get_autosave_posts_query();
		$posts = $wpdb->get_results( $posts_query, ARRAY_A );

		return array(
			'count' => $count,
			'posts' => $posts,
		);
	}

	/**
	 * Get orphaned postmeta information
	 *
	 * @return array
	 */
	public static function get_orphaned_postmeta_info() {
		global $wpdb;

		$count_query = CL_DB_Query_Builder::get_orphaned_postmeta_count_query();
		$count = $wpdb->get_var( $count_query );

		$items_query = CL_DB_Query_Builder::get_orphaned_postmeta_query();
		$items = $wpdb->get_results( $items_query, ARRAY_A );

		return array(
			'count' => $count,
			'items' => $items,
		);
	}

	/**
	 * Get orphaned usermeta information
	 *
	 * @return array
	 */
	public static function get_orphaned_usermeta_info() {
		global $wpdb;

		$count_query = CL_DB_Query_Builder::get_orphaned_usermeta_count_query();
		$count = $wpdb->get_var( $count_query );

		$items_query = CL_DB_Query_Builder::get_orphaned_usermeta_query();
		$items = $wpdb->get_results( $items_query, ARRAY_A );

		return array(
			'count' => $count,
			'items' => $items,
		);
	}

	/**
	 * Get expired transients information
	 *
	 * @return array
	 */
	public static function get_expired_transients_info() {
		global $wpdb;

		$query = CL_DB_Query_Builder::get_expired_transients_query();
		$transients = $wpdb->get_results( $query, ARRAY_A );

		return array(
			'count' => count( $transients ),
			'items' => $transients,
		);
	}

	/**
	 * Get all transients information
	 *
	 * @return array
	 */
	public static function get_all_transients_info() {
		global $wpdb;

		$count_query = CL_DB_Query_Builder::get_all_transients_count_query();
		$count = $wpdb->get_var( $count_query );

		$items_query = CL_DB_Query_Builder::get_all_transients_query();
		$items = $wpdb->get_results( $items_query, ARRAY_A );

		return array(
			'count' => $count,
			'items' => $items,
		);
	}

	/**
	 * Get orphaned WooCommerce order items information
	 *
	 * @return array|false
	 */
	public static function get_orphaned_wc_order_items_info() {
		global $wpdb;

		$count_query = CL_DB_Query_Builder::get_orphaned_wc_order_items_count_query();

		if ( false === $count_query ) {
			return false;
		}

		$count = $wpdb->get_var( $count_query );

		$items_query = CL_DB_Query_Builder::get_orphaned_wc_order_items_query();
		$items = $wpdb->get_results( $items_query, ARRAY_A );

		return array(
			'count' => $count,
			'items' => $items,
		);
	}

	/**
	 * Get orphaned WooCommerce order item meta information
	 *
	 * @return array|false
	 */
	public static function get_orphaned_wc_order_itemmeta_info() {
		global $wpdb;

		$count_query = CL_DB_Query_Builder::get_orphaned_wc_order_itemmeta_count_query();

		if ( false === $count_query ) {
			return false;
		}

		$count = $wpdb->get_var( $count_query );

		$items_query = CL_DB_Query_Builder::get_orphaned_wc_order_itemmeta_query();
		$items = $wpdb->get_results( $items_query, ARRAY_A );

		return array(
			'count' => $count,
			'items' => $items,
		);
	}

	/**
	 * Get WooCommerce sessions information
	 *
	 * @return array|false
	 */
	public static function get_wc_sessions_info() {
		global $wpdb;

		$query = CL_DB_Query_Builder::get_wc_sessions_query();

		if ( false === $query ) {
			return false;
		}

		$sessions = $wpdb->get_results( $query, ARRAY_A );

		$expired_count = 0;
		$current_time = time();

		foreach ( $sessions as $session ) {
			if ( $session['session_expiry'] < $current_time ) {
				$expired_count++;
			}
		}

		return array(
			'total' => count( $sessions ),
			'expired' => $expired_count,
			'sessions' => $sessions,
		);
	}

	/**
	 * Format bytes to human readable format
	 *
	 * @param int $bytes Bytes to format.
	 * @param int $precision Decimal precision.
	 * @return string
	 */
	public static function format_bytes( $bytes, $precision = 2 ) {
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );

		$bytes = max( $bytes, 0 );
		$pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow = min( $pow, count( $units ) - 1 );

		$bytes /= pow( 1024, $pow );

		return round( $bytes, $precision ) . ' ' . $units[ $pow ];
	}

	/**
	 * Check if WooCommerce is active
	 *
	 * @return bool
	 */
	public static function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Clear all cached data
	 *
	 * Should be called after any database modification
	 *
	 * @return void
	 */
	public static function clear_cache() {
		$cache_keys = array(
			'cl_db_tools_tables_info',
			'cl_db_tools_database_size',
			'cl_db_tools_autoload_info',
		);

		foreach ( $cache_keys as $key ) {
			delete_transient( $key );
		}

		CL_DB_Security::log( 'Cache cleared after database modification', 'notice' );
	}
}
