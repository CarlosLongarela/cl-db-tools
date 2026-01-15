<?php
/**
 * Main plugin class
 *
 * Handles plugin initialization and dependency loading
 *
 * @package CL_DB_Tools
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Code is poetry but I am not a poet, sorry!' );
}

/**
 * CL_DB_Tools class
 */
class CL_DB_Tools {

	/**
	 * Instance of this class
	 *
	 * @var CL_DB_Tools
	 */
	private static $instance = null;

	/**
	 * Get instance of this class
	 *
	 * @return CL_DB_Tools
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
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load required dependencies
	 */
	private function load_dependencies() {
		// Core files
		require_once CL_DB_TOOLS_PLUGIN_DIR . 'includes/class-cl-db-security.php';
		require_once CL_DB_TOOLS_PLUGIN_DIR . 'includes/class-cl-db-query-builder.php';
		require_once CL_DB_TOOLS_PLUGIN_DIR . 'includes/class-cl-db-analyzer.php';
		require_once CL_DB_TOOLS_PLUGIN_DIR . 'includes/class-cl-db-optimizer.php';

		// Admin files
		if ( is_admin() ) {
			require_once CL_DB_TOOLS_PLUGIN_DIR . 'admin/class-cl-db-admin.php';
			require_once CL_DB_TOOLS_PLUGIN_DIR . 'admin/class-cl-db-ajax-handler.php';
		}
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		if ( is_admin() ) {
			CL_DB_Admin::get_instance();
			CL_DB_Ajax_Handler::get_instance();
		}
	}

	/**
	 * Load plugin textdomain for translations
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'cl-db-tools',
			false,
			dirname( CL_DB_TOOLS_PLUGIN_BASENAME ) . '/languages'
		);
	}
}
