<?php
/**
 * Plugin Name: CL Database Tools
 * Plugin URI: https://tabernawp.com/
 * Description: Advanced database administration and optimization tools for WordPress
 * Version: 1.1.0
 * Author: Carlos Longarela
 * Author URI: https://tabernawp.com/
 * Author Email: carlos@longarela.eu
 * Text Domain: cl-db-tools
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Code is poetry but I am not a poet, sorry!' );
}

// Define plugin constants
define( 'CL_DB_TOOLS_VERSION', '1.1.0' );
define( 'CL_DB_TOOLS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CL_DB_TOOLS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CL_DB_TOOLS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Cache expiration time in seconds (default: 15 minutes)
// Can be customized in wp-config.php: define( 'CL_DB_TOOLS_CACHE_EXPIRATION', 30 * MINUTE_IN_SECONDS );
if ( ! defined( 'CL_DB_TOOLS_CACHE_EXPIRATION' ) ) {
	define( 'CL_DB_TOOLS_CACHE_EXPIRATION', 15 * MINUTE_IN_SECONDS );
}

// Maximum number of autoload options to display (default: 100, max: 250)
// Can be customized in wp-config.php: define( 'CL_DB_TOOLS_AUTOLOAD_LIMIT', 150 );
if ( ! defined( 'CL_DB_TOOLS_AUTOLOAD_LIMIT' ) ) {
	define( 'CL_DB_TOOLS_AUTOLOAD_LIMIT', 100 );
}

// Load main plugin class
require_once CL_DB_TOOLS_PLUGIN_DIR . 'includes/class-cl-db-tools.php';

// Initialize the plugin
CL_DB_Tools::get_instance();
