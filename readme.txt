=== CL Database Tools ===
Contributors: carloslongarela
Donate link: https://tabernawp.com/donaciones/
Tags: database, optimization, cleanup, performance, mysql
Requires at least: 5.8
Tested up to: 6.7
Stable tag: 1.0.6
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Advanced database administration and optimization tools for WordPress. Analyze, clean and optimize your database directly from the admin panel.

== Description ==

CL Database Tools provides a comprehensive suite of database administration and optimization tools directly within your WordPress admin panel. Perfect for developers, site administrators, and anyone who wants to keep their WordPress database clean and optimized.

= Features =

* **Database Overview** - View complete database statistics including size, tables, rows, and engine information
* **Table Management** - Optimize individual tables or all tables at once
* **InnoDB Conversion** - Convert MyISAM tables to InnoDB for better performance
* **Autoload Options** - Analyze and manage autoloaded options to improve site performance
* **Posts Cleanup** - Delete auto-drafts and revisions with option to keep recent ones
* **Orphaned Data Cleanup** - Find and remove orphaned postmeta and usermeta entries
* **WooCommerce Support** - Clean expired WooCommerce sessions (when WooCommerce is active)
* **Transients Management** - View and delete expired transients
* **SQL Query Display** - View the actual SQL queries being executed
* **Copy Queries** - Copy any query to clipboard for use in external tools

= Security =

* All operations require `manage_options` capability
* Nonce verification on all AJAX requests
* Prepared statements for all database queries
* Input sanitization and output escaping
* Backup warnings before destructive operations

= Requirements =

* WordPress 5.8 or higher
* PHP 7.4 or higher
* MySQL 5.6 or higher / MariaDB 10.0 or higher

== Installation ==

1. Upload the `cl-db-tools` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Tools > Database Tools to access the plugin

== Frequently Asked Questions ==

= Is it safe to use these tools? =

Yes, but always make a backup before performing any optimization or cleanup operations. The plugin displays warnings before any destructive action.

= Will this plugin slow down my site? =

No. The plugin only runs when you access it from the admin panel. It uses transient caching to minimize database queries.

= Does it work with WooCommerce? =

Yes! When WooCommerce is active, additional options appear to manage WooCommerce sessions.

= Can I undo changes? =

No. Database changes cannot be undone. Always make a backup before performing operations.

= Why should I convert tables to InnoDB? =

InnoDB offers better performance, crash recovery, and supports transactions. It's the recommended engine for WordPress.

== Screenshots ==

1. Database Overview - View database statistics and connection information
2. Tables Management - Optimize tables and convert to InnoDB
3. Autoload Options - Manage autoloaded options
4. Posts Cleanup - Clean auto-drafts and revisions
5. Orphaned Data - Find and remove orphaned metadata
6. WooCommerce Tools - Manage WooCommerce sessions (when active)

== Changelog ==

= 1.0.6 =
* Added "Created" column to tables list showing table creation date
* Added tooltip explaining that this date may change after OPTIMIZE or ALTER operations
* UI improvements with new tooltip component

= 1.0.5 =
* Added readme.txt for WordPress.org repository submission
* Updated changelog documentation

= 1.0.4 =
* Refactored main plugin class to separate file
* Added plugin information panel with toggle button
* Code organization improvements

= 1.0.3 =
* Added plugin info toggle button in header
* UI improvements for plugin information display

= 1.0.2 =
* Security improvements with $wpdb->prepare() on all queries
* Added esc_html() to dynamic content in messages
* Updated direct access protection message
* Code standards compliance improvements

= 1.0.1 =
* Initial improvements and bug fixes

= 1.0.0 =
* Initial release
* Database overview and statistics
* Table optimization and InnoDB conversion
* Autoload options management
* Posts cleanup (auto-drafts and revisions)
* Orphaned postmeta and usermeta cleanup
* WooCommerce sessions management
* Expired transients cleanup
* SQL query display and copy functionality

== Upgrade Notice ==

= 1.0.6 =
Added table creation date column with informative tooltip. UI improvements.

= 1.0.5 =
Added readme.txt for WordPress.org submission. No functional changes.

= 1.0.4 =
Code refactoring and UI improvements. No database changes required.

= 1.0.2 =
Security improvements. Recommended update for all users.

= 1.0.0 =
Initial release.

== Additional Information ==

= Contributing =

Contributions are welcome! Please feel free to submit a Pull Request.

= Support =

For support questions, please use the WordPress.org support forums.

= Credits =

Developed by Carlos Longarela at [TabernaWP](https://tabernawp.com/).
