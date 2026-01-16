<?php
/**
 * Query Builder class
 *
 * Builds all SQL queries used by the plugin
 *
 * @package CL_DB_Tools
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Code is poetry but I am not a poet, sorry!' );
}

/**
 * CL_DB_Query_Builder class
 */
class CL_DB_Query_Builder {

	/**
	 * Get query to show all database tables with details
	 *
	 * @return string
	 */
	public static function get_tables_info_query() {
		global $wpdb;

		return $wpdb->prepare(
			"SELECT
				TABLE_NAME as 'table_name',
				ENGINE as 'engine',
				TABLE_ROWS as 'rows',
				AVG_ROW_LENGTH as 'avg_row_length',
				DATA_LENGTH as 'data_length',
				INDEX_LENGTH as 'index_length',
				DATA_FREE as 'data_free',
				CREATE_TIME as 'create_time',
				UPDATE_TIME as 'update_time',
				TABLE_COLLATION as 'collation'
			FROM information_schema.TABLES
			WHERE TABLE_SCHEMA = %s
			ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC",
			DB_NAME
		);
	}

	/**
	 * Get query to show database size
	 *
	 * @return string
	 */
	public static function get_database_size_query() {
		global $wpdb;

		return $wpdb->prepare(
			"SELECT
				SUM(DATA_LENGTH + INDEX_LENGTH) as 'total_size',
				SUM(DATA_LENGTH) as 'data_size',
				SUM(INDEX_LENGTH) as 'index_size',
				SUM(DATA_FREE) as 'free_size'
			FROM information_schema.TABLES
			WHERE TABLE_SCHEMA = %s",
			DB_NAME
		);
	}

	/**
	 * Get query to show tables not using InnoDB
	 *
	 * @return string
	 */
	public static function get_non_innodb_tables_query() {
		global $wpdb;

		return $wpdb->prepare(
			"SELECT
				TABLE_NAME as 'table_name',
				ENGINE as 'engine'
			FROM information_schema.TABLES
			WHERE TABLE_SCHEMA = %s
			AND ENGINE != 'InnoDB'
			ORDER BY TABLE_NAME",
			DB_NAME
		);
	}

	/**
	 * Get query to convert table to InnoDB
	 *
	 * @param string $table_name Table name.
	 * @return string|false
	 */
	public static function get_convert_to_innodb_query( $table_name ) {
		$table_name = CL_DB_Security::sanitize_table_name( $table_name );

		if ( false === $table_name ) {
			return false;
		}

		return "ALTER TABLE `{$table_name}` ENGINE=InnoDB";
	}

	/**
	 * Get query to show autoload options with sizes
	 *
	 * @param int $limit Maximum number of results (default 100).
	 * @return string
	 */
	public static function get_autoload_options_query( $limit = 100 ) {
		global $wpdb;

		$limit = absint( $limit );
		if ( $limit <= 0 ) {
			$limit = 100;
		}

		return $wpdb->prepare(
			"SELECT
				option_name,
				LENGTH(option_value) as 'size',
				autoload
			FROM {$wpdb->options}
			WHERE autoload IN ('yes', 'on')
			ORDER BY LENGTH(option_value) DESC
			LIMIT %d",
			$limit
		);
	}

	/**
	 * Get query to show total autoload size
	 *
	 * @return string
	 */
	public static function get_autoload_total_query() {
		global $wpdb;

		return "SELECT
				COUNT(*) as 'count',
				SUM(LENGTH(option_value)) as 'total_size'
			FROM {$wpdb->options}
			WHERE autoload IN ('yes', 'on')";
	}

	/**
	 * Get query to update autoload value
	 *
	 * @param string $option_name Option name.
	 * @param string $autoload_value New autoload value ('yes', 'on', or 'no', 'off').
	 * @return string|false
	 */
	public static function get_update_autoload_query( $option_name, $autoload_value ) {
		global $wpdb;

		$option_name = CL_DB_Security::sanitize_option_name( $option_name );
		$autoload_value = in_array( $autoload_value, array( 'yes', 'on' ), true ) ? 'on' : 'off';

		return $wpdb->prepare(
			"UPDATE {$wpdb->options} SET autoload = %s WHERE option_name = %s",
			$autoload_value,
			$option_name
		);
	}

	/**
	 * Get query to delete an option
	 *
	 * @param string $option_name Option name.
	 * @return string|false
	 */
	public static function get_delete_option_query( $option_name ) {
		global $wpdb;

		$option_name = CL_DB_Security::sanitize_option_name( $option_name );

		return $wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name = %s",
			$option_name
		);
	}

	/**
	 * Get query to show autosave posts
	 *
	 * @param int $limit Maximum number of results (default 100).
	 * @return string
	 */
	public static function get_autosave_posts_query( $limit = 100 ) {
		global $wpdb;

		$limit = absint( $limit );
		if ( $limit <= 0 ) {
			$limit = 100;
		}

		return $wpdb->prepare(
			"SELECT
				ID,
				post_parent,
				post_title,
				post_date,
				post_modified
			FROM {$wpdb->posts}
			WHERE post_status = 'auto-draft'
			OR (post_status = 'inherit' AND post_type = 'revision')
			ORDER BY post_modified DESC
			LIMIT %d",
			$limit
		);
	}

	/**
	 * Get query to count autosave posts
	 *
	 * @return string
	 */
	public static function get_autosave_count_query() {
		global $wpdb;

		return $wpdb->prepare(
			"SELECT COUNT(*) as 'count'
			FROM {$wpdb->posts}
			WHERE post_status = %s
			OR (post_status = %s AND post_type = %s)",
			'auto-draft',
			'inherit',
			'revision'
		);
	}

	/**
	 * Get query to delete autosave posts
	 *
	 * @param int $keep_last Number of recent autosaves to keep (0 to delete all).
	 * @return string
	 */
	public static function get_delete_autosaves_query( $keep_last = 0 ) {
		global $wpdb;
		$keep_last = CL_DB_Security::sanitize_int( $keep_last );

		if ( $keep_last > 0 ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $keep_last is sanitized with absint()
			return $wpdb->prepare(
				"DELETE FROM {$wpdb->posts}
				WHERE ID NOT IN (
					SELECT ID FROM (
						SELECT ID
						FROM {$wpdb->posts}
						WHERE post_status = %s
						OR (post_status = %s AND post_type = %s)
						ORDER BY post_modified DESC
						LIMIT %d
					) as keeper
				)
				AND (
					post_status = %s
					OR (post_status = %s AND post_type = %s)
				)",
				'auto-draft',
				'inherit',
				'revision',
				$keep_last,
				'auto-draft',
				'inherit',
				'revision'
			);
		}

		return $wpdb->prepare(
			"DELETE FROM {$wpdb->posts}
			WHERE post_status = %s
			OR (post_status = %s AND post_type = %s)",
			'auto-draft',
			'inherit',
			'revision'
		);
	}

	/**
	 * Get query to show orphaned postmeta
	 *
	 * @param int $limit Maximum number of results (default 100).
	 * @return string
	 */
	public static function get_orphaned_postmeta_query( $limit = 100 ) {
		global $wpdb;

		$limit = absint( $limit );
		if ( $limit <= 0 ) {
			$limit = 100;
		}

		return $wpdb->prepare(
			"SELECT
				pm.meta_id,
				pm.post_id,
				pm.meta_key,
				LENGTH(pm.meta_value) as 'size'
			FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.ID IS NULL
			ORDER BY pm.meta_id
			LIMIT %d",
			$limit
		);
	}

	/**
	 * Get query to count orphaned postmeta
	 *
	 * @return string
	 */
	public static function get_orphaned_postmeta_count_query() {
		global $wpdb;

		// No user input, but using prepare for consistency and PHPCS compliance.
		return "SELECT COUNT(*) as 'count'
			FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.ID IS NULL";
	}

	/**
	 * Get query to delete orphaned postmeta
	 *
	 * @return string
	 */
	public static function get_delete_orphaned_postmeta_query() {
		global $wpdb;

		return "DELETE pm FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.ID IS NULL";
	}

	/**
	 * Get query to show orphaned usermeta
	 *
	 * @param int $limit Maximum number of results (default 100).
	 * @return string
	 */
	public static function get_orphaned_usermeta_query( $limit = 100 ) {
		global $wpdb;

		$limit = absint( $limit );
		if ( $limit <= 0 ) {
			$limit = 100;
		}

		return $wpdb->prepare(
			"SELECT
				um.umeta_id,
				um.user_id,
				um.meta_key,
				LENGTH(um.meta_value) as 'size'
			FROM {$wpdb->usermeta} um
			LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
			WHERE u.ID IS NULL
			ORDER BY um.umeta_id
			LIMIT %d",
			$limit
		);
	}

	/**
	 * Get query to count orphaned usermeta
	 *
	 * @return string
	 */
	public static function get_orphaned_usermeta_count_query() {
		global $wpdb;

		return "SELECT COUNT(*) as 'count'
			FROM {$wpdb->usermeta} um
			LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
			WHERE u.ID IS NULL";
	}

	/**
	 * Get query to delete orphaned usermeta
	 *
	 * @return string
	 */
	public static function get_delete_orphaned_usermeta_query() {
		global $wpdb;

		return "DELETE um FROM {$wpdb->usermeta} um
			LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
			WHERE u.ID IS NULL";
	}

	/**
	 * Get query to show expired transients
	 *
	 * @param int $limit Maximum number of results (default 100).
	 * @return string
	 */
	public static function get_expired_transients_query( $limit = 100 ) {
		global $wpdb;

		$limit = absint( $limit );
		if ( $limit <= 0 ) {
			$limit = 100;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe
		return "SELECT
				option_name,
				LENGTH(option_value) as 'size'
			FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_timeout_%'
			AND option_value < UNIX_TIMESTAMP()
			ORDER BY option_value
			LIMIT " . absint( $limit );
	}

	/**
	 * Get query to delete expired transients
	 *
	 * @return string
	 */
	public static function get_delete_expired_transients_query() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe
		return "DELETE a, b FROM {$wpdb->options} a
			LEFT JOIN {$wpdb->options} b ON b.option_name = REPLACE(a.option_name, '_transient_timeout_', '_transient_')
			WHERE a.option_name LIKE '_transient_timeout_%'
			AND a.option_value < UNIX_TIMESTAMP()";
	}

	/**
	 * Get query to show all transients
	 *
	 * @param int $limit Maximum number of results (default 100).
	 * @return string
	 */
	public static function get_all_transients_query( $limit = 100 ) {
		global $wpdb;

		$limit = absint( $limit );
		if ( $limit <= 0 ) {
			$limit = 100;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe
		return "SELECT
				option_name,
				LENGTH(option_value) as 'size'
			FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_%'
			AND option_name NOT LIKE '_transient_timeout_%'
			ORDER BY LENGTH(option_value) DESC
			LIMIT " . absint( $limit );
	}

	/**
	 * Get query to count all transients
	 *
	 * @return string
	 */
	public static function get_all_transients_count_query() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe
		return "SELECT COUNT(*) as 'count'
			FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_%'
			AND option_name NOT LIKE '_transient_timeout_%'";
	}

	/**
	 * Get query to delete all transients
	 *
	 * @return string
	 */
	public static function get_delete_all_transients_query() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe
		return "DELETE FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_%'";
	}

	/**
	 * Get query to show WooCommerce sessions
	 *
	 * @return string|false
	 */
	public static function get_wc_sessions_query() {
		global $wpdb;

		// Check if WooCommerce sessions table exists
		$table_name = $wpdb->prefix . 'woocommerce_sessions';
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

		if ( ! $table_exists ) {
			return false;
		}

		// Table name is constructed from $wpdb->prefix which is safe.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return "SELECT
				session_id,
				session_expiry,
				FROM_UNIXTIME(session_expiry) as 'expiry_date'
			FROM `{$table_name}`
			ORDER BY session_expiry DESC";
	}

	/**
	 * Get query to delete expired WooCommerce sessions
	 *
	 * @return string|false
	 */
	public static function get_delete_expired_wc_sessions_query() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'woocommerce_sessions';
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

		if ( ! $table_exists ) {
			return false;
		}

		// Table name is constructed from $wpdb->prefix which is safe.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return "DELETE FROM `{$table_name}`
			WHERE session_expiry < UNIX_TIMESTAMP()";
	}

	/**
	 * Get query to show orphaned WooCommerce order item meta
	 *
	 * @param int $limit Maximum number of results (default 100).
	 * @return string|false
	 */
	public static function get_orphaned_wc_order_itemmeta_query( $limit = 100 ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'woocommerce_order_itemmeta';
		$items_table = $wpdb->prefix . 'woocommerce_order_items';
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

		if ( ! $table_exists ) {
			return false;
		}

		$limit = absint( $limit );
		if ( $limit <= 0 ) {
			$limit = 100;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->prepare(
			"SELECT
				oim.meta_id,
				oim.order_item_id,
				oim.meta_key,
				LENGTH(oim.meta_value) as 'size'
			FROM `{$table_name}` oim
			LEFT JOIN `{$items_table}` oi ON oim.order_item_id = oi.order_item_id
			WHERE oi.order_item_id IS NULL
			ORDER BY oim.meta_id
			LIMIT %d",
			$limit
		);
	}

	/**
	 * Get query to count orphaned WooCommerce order item meta
	 *
	 * @return string|false
	 */
	public static function get_orphaned_wc_order_itemmeta_count_query() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'woocommerce_order_itemmeta';
		$items_table = $wpdb->prefix . 'woocommerce_order_items';
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

		if ( ! $table_exists ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return "SELECT COUNT(*) as 'count'
			FROM `{$table_name}` oim
			LEFT JOIN `{$items_table}` oi ON oim.order_item_id = oi.order_item_id
			WHERE oi.order_item_id IS NULL";
	}

	/**
	 * Get query to delete orphaned WooCommerce order item meta
	 *
	 * @return string|false
	 */
	public static function get_delete_orphaned_wc_order_itemmeta_query() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'woocommerce_order_itemmeta';
		$items_table = $wpdb->prefix . 'woocommerce_order_items';
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

		if ( ! $table_exists ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return "DELETE oim FROM `{$table_name}` oim
			LEFT JOIN `{$items_table}` oi ON oim.order_item_id = oi.order_item_id
			WHERE oi.order_item_id IS NULL";
	}

	/**
	 * Get query to show orphaned WooCommerce order items (items without orders)
	 *
	 * @param int $limit Maximum number of results (default 100).
	 * @return string|false
	 */
	public static function get_orphaned_wc_order_items_query( $limit = 100 ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'woocommerce_order_items';
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

		if ( ! $table_exists ) {
			return false;
		}

		$limit = absint( $limit );
		if ( $limit <= 0 ) {
			$limit = 100;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->prepare(
			"SELECT
				oi.order_item_id,
				oi.order_item_name,
				oi.order_item_type,
				oi.order_id
			FROM `{$table_name}` oi
			LEFT JOIN {$wpdb->posts} p ON oi.order_id = p.ID
			WHERE p.ID IS NULL
			ORDER BY oi.order_item_id
			LIMIT %d",
			$limit
		);
	}

	/**
	 * Get query to count orphaned WooCommerce order items
	 *
	 * @return string|false
	 */
	public static function get_orphaned_wc_order_items_count_query() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'woocommerce_order_items';
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

		if ( ! $table_exists ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return "SELECT COUNT(*) as 'count'
			FROM `{$table_name}` oi
			LEFT JOIN {$wpdb->posts} p ON oi.order_id = p.ID
			WHERE p.ID IS NULL";
	}

	/**
	 * Get query to delete orphaned WooCommerce order items
	 *
	 * @return string|false
	 */
	public static function get_delete_orphaned_wc_order_items_query() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'woocommerce_order_items';
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

		if ( ! $table_exists ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return "DELETE oi FROM `{$table_name}` oi
			LEFT JOIN {$wpdb->posts} p ON oi.order_id = p.ID
			WHERE p.ID IS NULL";
	}

	/**
	 * Get query to optimize table
	 *
	 * @param string $table_name Table name.
	 * @return string|false
	 */
	public static function get_optimize_table_query( $table_name ) {
		$table_name = CL_DB_Security::sanitize_table_name( $table_name );

		if ( false === $table_name ) {
			return false;
		}

		return "OPTIMIZE TABLE `{$table_name}`";
	}

	/**
	 * Get query to analyze table
	 *
	 * @param string $table_name Table name.
	 * @return string|false
	 */
	public static function get_analyze_table_query( $table_name ) {
		$table_name = CL_DB_Security::sanitize_table_name( $table_name );

		if ( false === $table_name ) {
			return false;
		}

		return "ANALYZE TABLE `{$table_name}`";
	}

	/**
	 * Get query to repair table
	 *
	 * @param string $table_name Table name.
	 * @return string|false
	 */
	public static function get_repair_table_query( $table_name ) {
		$table_name = CL_DB_Security::sanitize_table_name( $table_name );

		if ( false === $table_name ) {
			return false;
		}

		return "REPAIR TABLE `{$table_name}`";
	}
}
