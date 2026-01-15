<?php
/**
 * AJAX Handler class
 *
 * Handles all AJAX requests from the admin interface
 *
 * @package CL_DB_Tools
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Code is poetry but I am not a poet, sorry!' );
}

/**
 * CL_DB_Ajax_Handler class
 */
class CL_DB_Ajax_Handler {

	/**
	 * Instance of this class
	 *
	 * @var CL_DB_Ajax_Handler
	 */
	private static $instance = null;

	/**
	 * Get instance of this class
	 *
	 * @return CL_DB_Ajax_Handler
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		// Register AJAX handlers
		add_action( 'wp_ajax_cl_db_execute_query', array( $this, 'execute_query' ) );
		add_action( 'wp_ajax_cl_db_convert_innodb', array( $this, 'convert_to_innodb' ) );
		add_action( 'wp_ajax_cl_db_convert_all_innodb', array( $this, 'convert_all_to_innodb' ) );
		add_action( 'wp_ajax_cl_db_update_autoload', array( $this, 'update_autoload' ) );
		add_action( 'wp_ajax_cl_db_delete_option', array( $this, 'delete_option' ) );
		add_action( 'wp_ajax_cl_db_delete_autosaves', array( $this, 'delete_autosaves' ) );
		add_action( 'wp_ajax_cl_db_delete_orphaned_postmeta', array( $this, 'delete_orphaned_postmeta' ) );
		add_action( 'wp_ajax_cl_db_delete_orphaned_usermeta', array( $this, 'delete_orphaned_usermeta' ) );
		add_action( 'wp_ajax_cl_db_delete_wc_sessions', array( $this, 'delete_wc_sessions' ) );
		add_action( 'wp_ajax_cl_db_delete_expired_transients', array( $this, 'delete_expired_transients' ) );
		add_action( 'wp_ajax_cl_db_delete_orphaned_wc_order_items', array( $this, 'delete_orphaned_wc_order_items' ) );
		add_action( 'wp_ajax_cl_db_delete_orphaned_wc_order_itemmeta', array( $this, 'delete_orphaned_wc_order_itemmeta' ) );
		add_action( 'wp_ajax_cl_db_optimize_table', array( $this, 'optimize_table' ) );
		add_action( 'wp_ajax_cl_db_optimize_all_tables', array( $this, 'optimize_all_tables' ) );
	}

	/**
	 * Verify AJAX request
	 *
	 * @return bool
	 */
	private function verify_request() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! CL_DB_Security::verify_request( $nonce ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed.', 'cl-db-tools' ),
				)
			);
			return false;
		}

		return true;
	}

	/**
	 * Execute generic query (for read-only operations)
	 *
	 * @return void
	 */
	public function execute_query() {
		$this->verify_request();

		$action = isset( $_POST['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['action_type'] ) ) : '';

		// Map actions to appropriate methods
		$method_map = array(
			'get_database_size' => array( 'CL_DB_Analyzer', 'get_database_size' ),
			'get_tables_info' => array( 'CL_DB_Analyzer', 'get_tables_info' ),
			'get_non_innodb' => array( 'CL_DB_Analyzer', 'get_non_innodb_tables' ),
			'get_autoload' => array( 'CL_DB_Analyzer', 'get_autoload_info' ),
			'get_autosaves' => array( 'CL_DB_Analyzer', 'get_autosave_info' ),
			'get_orphaned_postmeta' => array( 'CL_DB_Analyzer', 'get_orphaned_postmeta_info' ),
			'get_orphaned_usermeta' => array( 'CL_DB_Analyzer', 'get_orphaned_usermeta_info' ),
			'get_expired_transients' => array( 'CL_DB_Analyzer', 'get_expired_transients_info' ),
		);

		if ( isset( $method_map[ $action ] ) && is_callable( $method_map[ $action ] ) ) {
			$result = call_user_func( $method_map[ $action ] );

			wp_send_json_success(
				array(
					'message' => __( 'Query executed successfully.', 'cl-db-tools' ),
					'data' => $result,
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid action.', 'cl-db-tools' ),
				)
			);
		}
	}

	/**
	 * Convert table to InnoDB
	 *
	 * @return void
	 */
	public function convert_to_innodb() {
		$this->verify_request();

		$table_name = isset( $_POST['table'] ) ? sanitize_text_field( wp_unslash( $_POST['table'] ) ) : '';

		if ( empty( $table_name ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Table name is required.', 'cl-db-tools' ),
				)
			);
		}

		$result = CL_DB_Optimizer::convert_to_innodb( $table_name );

		if ( $result ) {
			wp_send_json_success(
				array(
					'message' => sprintf(
						/* translators: %s: table name */
						__( 'Table %s successfully converted to InnoDB.', 'cl-db-tools' ),
						esc_html( $table_name )
					),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators: %s: table name */
						__( 'Failed to convert table %s to InnoDB.', 'cl-db-tools' ),
						esc_html( $table_name )
					),
				)
			);
		}
	}

	/**
	 * Convert all non-InnoDB tables to InnoDB
	 *
	 * @return void
	 */
	public function convert_all_to_innodb() {
		$this->verify_request();

		$results = CL_DB_Optimizer::convert_all_to_innodb();

		$success_count = count( $results['success'] );
		$failed_count = count( $results['failed'] );

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: 1: number of successful conversions, 2: number of failed conversions */
					__( 'Conversion complete. Success: %1$d, Failed: %2$d', 'cl-db-tools' ),
					$success_count,
					$failed_count
				),
				'data' => $results,
			)
		);
	}

	/**
	 * Update autoload value for an option
	 *
	 * @return void
	 */
	public function update_autoload() {
		$this->verify_request();

		$option_name = isset( $_POST['option'] ) ? sanitize_text_field( wp_unslash( $_POST['option'] ) ) : '';
		$autoload_value = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : 'no';

		if ( empty( $option_name ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Option name is required.', 'cl-db-tools' ),
				)
			);
		}

		$result = CL_DB_Optimizer::update_autoload( $option_name, $autoload_value );

		if ( $result ) {
			wp_send_json_success(
				array(
					'message' => sprintf(
						/* translators: %s: option name */
						__( 'Autoload updated for option %s.', 'cl-db-tools' ),
						esc_html( $option_name )
					),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to update autoload.', 'cl-db-tools' ),
				)
			);
		}
	}

	/**
	 * Delete an option
	 *
	 * @return void
	 */
	public function delete_option() {
		$this->verify_request();

		$option_name = isset( $_POST['option'] ) ? sanitize_text_field( wp_unslash( $_POST['option'] ) ) : '';

		if ( empty( $option_name ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Option name is required.', 'cl-db-tools' ),
				)
			);
		}

		$result = CL_DB_Optimizer::delete_option( $option_name );

		if ( $result ) {
			wp_send_json_success(
				array(
					'message' => sprintf(
						/* translators: %s: option name */
						__( 'Option %s deleted successfully.', 'cl-db-tools' ),
						esc_html( $option_name )
					),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to delete option.', 'cl-db-tools' ),
				)
			);
		}
	}

	/**
	 * Delete autosave posts
	 *
	 * @return void
	 */
	public function delete_autosaves() {
		$this->verify_request();

		$keep_last = isset( $_POST['keep_last'] ) ? absint( $_POST['keep_last'] ) : 0;

		$result = CL_DB_Optimizer::delete_autosaves( $keep_last );

		if ( false !== $result ) {
			wp_send_json_success(
				array(
					'message' => sprintf(
						/* translators: %d: number of deleted posts */
						__( 'Successfully deleted %d autosave posts.', 'cl-db-tools' ),
						$result
					),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to delete autosaves.', 'cl-db-tools' ),
				)
			);
		}
	}

	/**
	 * Delete orphaned postmeta
	 *
	 * @return void
	 */
	public function delete_orphaned_postmeta() {
		$this->verify_request();

		$result = CL_DB_Optimizer::delete_orphaned_postmeta();

		if ( false !== $result ) {
			wp_send_json_success(
				array(
					'message' => sprintf(
						/* translators: %d: number of deleted entries */
						__( 'Successfully deleted %d orphaned postmeta entries.', 'cl-db-tools' ),
						$result
					),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to delete orphaned postmeta.', 'cl-db-tools' ),
				)
			);
		}
	}

	/**
	 * Delete orphaned usermeta
	 *
	 * @return void
	 */
	public function delete_orphaned_usermeta() {
		$this->verify_request();

		$result = CL_DB_Optimizer::delete_orphaned_usermeta();

		if ( false !== $result ) {
			wp_send_json_success(
				array(
					'message' => sprintf(
						/* translators: %d: number of deleted entries */
						__( 'Successfully deleted %d orphaned usermeta entries.', 'cl-db-tools' ),
						$result
					),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to delete orphaned usermeta.', 'cl-db-tools' ),
				)
			);
		}
	}

	/**
	 * Delete expired WooCommerce sessions
	 *
	 * @return void
	 */
	public function delete_wc_sessions() {
		$this->verify_request();

		$result = CL_DB_Optimizer::delete_expired_wc_sessions();

		if ( false !== $result ) {
			wp_send_json_success(
				array(
					'message' => sprintf(
						/* translators: %d: number of deleted sessions */
						__( 'Successfully deleted %d expired WooCommerce sessions.', 'cl-db-tools' ),
						$result
					),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to delete WooCommerce sessions.', 'cl-db-tools' ),
				)
			);
		}
	}

	/**
	 * Delete expired transients
	 *
	 * @return void
	 */
	public function delete_expired_transients() {
		$this->verify_request();

		$result = CL_DB_Optimizer::delete_expired_transients();

		if ( false !== $result ) {
			wp_send_json_success(
				array(
					'message' => __( 'Successfully deleted expired transients.', 'cl-db-tools' ),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to delete expired transients.', 'cl-db-tools' ),
				)
			);
		}
	}

	/**
	 * Delete orphaned WooCommerce order items
	 *
	 * @return void
	 */
	public function delete_orphaned_wc_order_items() {
		$this->verify_request();

		$result = CL_DB_Optimizer::delete_orphaned_wc_order_items();

		if ( false !== $result ) {
			wp_send_json_success(
				array(
					'message' => sprintf(
						/* translators: %d: number of deleted items */
						__( 'Successfully deleted %d orphaned WooCommerce order items.', 'cl-db-tools' ),
						$result
					),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to delete orphaned WooCommerce order items.', 'cl-db-tools' ),
				)
			);
		}
	}

	/**
	 * Delete orphaned WooCommerce order item meta
	 *
	 * @return void
	 */
	public function delete_orphaned_wc_order_itemmeta() {
		$this->verify_request();

		$result = CL_DB_Optimizer::delete_orphaned_wc_order_itemmeta();

		if ( false !== $result ) {
			wp_send_json_success(
				array(
					'message' => sprintf(
						/* translators: %d: number of deleted items */
						__( 'Successfully deleted %d orphaned WooCommerce order item meta entries.', 'cl-db-tools' ),
						$result
					),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to delete orphaned WooCommerce order item meta.', 'cl-db-tools' ),
				)
			);
		}
	}

	/**
	 * Optimize table
	 *
	 * @return void
	 */
	public function optimize_table() {
		$this->verify_request();

		$table_name = isset( $_POST['table'] ) ? sanitize_text_field( wp_unslash( $_POST['table'] ) ) : '';

		if ( empty( $table_name ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Table name is required.', 'cl-db-tools' ),
				)
			);
		}

		$result = CL_DB_Optimizer::optimize_table( $table_name );

		if ( $result ) {
			wp_send_json_success(
				array(
					'message' => sprintf(
						/* translators: %s: table name */
						__( 'Table %s optimized successfully.', 'cl-db-tools' ),
						esc_html( $table_name )
					),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to optimize table.', 'cl-db-tools' ),
				)
			);
		}
	}

	/**
	 * Optimize all tables
	 *
	 * @return void
	 */
	public function optimize_all_tables() {
		$this->verify_request();

		$results = CL_DB_Optimizer::optimize_all_tables();

		$success_count = count( $results['success'] );
		$failed_count = count( $results['failed'] );

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: 1: number of successful optimizations, 2: number of failed optimizations */
					__( 'Optimization complete. Success: %1$d, Failed: %2$d', 'cl-db-tools' ),
					$success_count,
					$failed_count
				),
				'data' => $results,
			)
		);
	}
}
