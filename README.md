# CL Database Tools

Advanced database administration and optimization tools for WordPress.

## Description

CL Database Tools is a powerful WordPress plugin that provides comprehensive database management and optimization capabilities. It allows you to analyze, monitor, and optimize your WordPress database with ease.

## Features

### Database Overview
- View total database size, data size, and index size
- Display all tables with detailed information
- Monitor table engines and sizes
- Track creation and update dates

### Table Management
- Convert tables from MyISAM to InnoDB engine
- Optimize individual tables or all tables at once
- View table structure and statistics
- Monitor index sizes and data distribution

### Autoload Options Management
- View all autoloaded options and their sizes
- Identify heavy autoloaded options
- Disable autoload for specific options
- Delete unnecessary options
- Display total autoload size

### Posts Cleanup
- View and delete auto-draft posts
- Manage post revisions
- Keep X most recent autosaves
- Clean up old revisions

### Orphaned Data Cleanup
- Find and delete orphaned postmeta (meta data without associated posts)
- Find and delete orphaned usermeta (meta data without associated users)
- View detailed information about orphaned entries

### WooCommerce Optimizations
- Delete expired WooCommerce sessions
- View session statistics
- Clean up WooCommerce-specific data

### Transients Management
- View expired transients
- Delete expired transients
- Optimize transient storage

### Security Features
- Nonce verification for all operations
- Capability checks (requires `manage_options`)
- SQL injection prevention
- Backup confirmation for destructive operations
- Secure table name sanitization

### Query Display
- View SQL query for each operation
- Copy queries to clipboard with one click
- Execute queries directly from WordPress admin
- Understand what each operation does

## Installation

1. Upload the `cl-db-tools` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to Tools > Database Tools to access the plugin

## Usage

### Important: Always Backup First

**Before performing any database optimization**, always create a complete backup of your database. The plugin will prompt you to confirm that you have a backup before executing destructive operations.

### Accessing the Plugin

1. Go to WordPress Admin Dashboard
2. Navigate to **Tools > Database Tools**
3. Choose the appropriate tab for your task

### Tabs Overview

#### Overview Tab
- View database statistics
- Monitor overall database health
- Quick access to database size information

#### Tables Tab
- View all database tables
- Optimize individual tables
- Convert tables to InnoDB engine
- Monitor table sizes and row counts

#### Autoload Options Tab
- Review autoloaded options
- Disable autoload for heavy options
- Delete unnecessary options
- Improve site performance by reducing autoload size

#### Posts Cleanup Tab
- Clean up auto-draft posts
- Manage post revisions
- Keep only recent autosaves

#### Orphaned Data Tab
- Find orphaned postmeta entries
- Find orphaned usermeta entries
- Clean up database by removing orphaned data

#### WooCommerce Tab (if WooCommerce is active)
- Manage WooCommerce sessions
- Delete expired sessions
- Clean up expired transients

#### Optimizations Tab
- Optimize all tables at once
- Reclaim unused space
- Improve database performance

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher (InnoDB support recommended)
- User capability: `manage_options`

## Frequently Asked Questions

### Is it safe to use this plugin?

Yes, but **always create a backup before performing any optimization**. The plugin includes multiple safety checks and confirmations for destructive operations.

### Will this plugin slow down my site?

No. The plugin only runs in the WordPress admin area and does not affect frontend performance.

### Can I undo changes made by this plugin?

Most operations are irreversible (like deleting data). This is why **creating a backup before use is essential**.

### What is autoload and why should I manage it?

Autoload options are loaded on every WordPress page request. Heavy autoloaded data can slow down your site. This plugin helps identify and manage these options.

### What is InnoDB and why should I convert tables?

InnoDB is a modern database engine that provides better performance, reliability, and support for transactions compared to older engines like MyISAM. Converting to InnoDB is recommended for most WordPress sites.

### Can I see the SQL query before executing it?

Yes! Every operation shows the SQL query that will be executed. You can copy it to run in your preferred database management tool (like phpMyAdmin) or execute it directly from the plugin.

## Changelog

### 1.0.0
- Initial release
- Database overview and statistics
- Table management and optimization
- Autoload options management
- Posts and revisions cleanup
- Orphaned data cleanup
- WooCommerce optimizations
- Transients management
- Query display and copy functionality
- Comprehensive security features

## Author

**Carlos Longarela**
- Website: [TabernaWP](https://tabernawp.com/)
- Email: carlos@longarela.eu

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support, please visit [TabernaWP](https://tabernawp.com/) or contact carlos@longarela.eu

## Credits

Developed by Carlos Longarela with attention to WordPress coding standards and security best practices.
