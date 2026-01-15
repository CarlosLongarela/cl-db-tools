<?php
/**
 * Uninstall script
 *
 * Fired when the plugin is uninstalled
 *
 * @package CL_DB_Tools
 */

// Exit if accessed directly or not in uninstall context
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Clean up plugin data on uninstall
 *
 * This plugin does not store any options or data in the database.
 * If we add any options in future versions, they should be deleted here.
 */

// Example cleanup (currently no options stored):
// delete_option( 'cl_db_tools_settings' );
// delete_site_option( 'cl_db_tools_network_settings' );

// Clear any cached data
wp_cache_flush();
