<?php
/**
 * Admin interface class
 *
 * Handles the admin menu and page rendering
 *
 * @package CL_DB_Tools
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Code is poetry but I am not a poet, sorry!' );
}

/**
 * CL_DB_Admin class
 */
class CL_DB_Admin {

	/**
	 * Instance of this class
	 *
	 * @var CL_DB_Admin
	 */
	private static $instance = null;

	/**
	 * Get instance of this class
	 *
	 * @return CL_DB_Admin
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
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_management_page(
			__( 'Database Tools', 'cl-db-tools' ),
			__( 'Database Tools', 'cl-db-tools' ),
			CL_DB_Security::REQUIRED_CAPABILITY,
			'cl-db-tools',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'tools_page_cl-db-tools' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'cl-db-tools-admin',
			CL_DB_TOOLS_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			CL_DB_TOOLS_VERSION
		);

		wp_enqueue_script(
			'cl-db-tools-admin',
			CL_DB_TOOLS_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			CL_DB_TOOLS_VERSION,
			true
		);

		wp_localize_script(
			'cl-db-tools-admin',
			'clDbTools',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce' => CL_DB_Security::create_nonce(),
				'i18n' => array(
					'confirmBackup' => __( 'WARNING: This action will modify your database. Have you made a backup?', 'cl-db-tools' ),
					'confirmDelete' => __( 'Are you sure you want to delete these items?', 'cl-db-tools' ),
					'copied' => __( 'Query copied to clipboard!', 'cl-db-tools' ),
					'copyFailed' => __( 'Failed to copy query', 'cl-db-tools' ),
					'processing' => __( 'Processing...', 'cl-db-tools' ),
					'success' => __( 'Operation completed successfully', 'cl-db-tools' ),
					'error' => __( 'An error occurred', 'cl-db-tools' ),
				),
			)
		);
	}

	/**
	 * Render admin page
	 */
	public function render_admin_page() {
		if ( ! CL_DB_Security::current_user_can() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'cl-db-tools' ) );
		}

		$plugin_data = array(
			'name'        => __( 'CL Database Tools', 'cl-db-tools' ),
			'version'     => CL_DB_TOOLS_VERSION,
			'author'      => 'Carlos Longarela',
			'author_uri'  => 'https://tabernawp.com/',
			'description' => __( 'Advanced database administration and optimization tools for WordPress', 'cl-db-tools' ),
			'updated'     => '2026-01-15',
		);
		?>
		<div class="wrap cl-db-tools">
			<h1>
				<?php echo esc_html( get_admin_page_title() ); ?>
				<button type="button" class="cl-db-info-toggle" aria-expanded="false" aria-controls="cl-db-plugin-info" title="<?php esc_attr_e( 'Plugin information', 'cl-db-tools' ); ?>">
					<span class="dashicons dashicons-info-outline"></span>
				</button>
			</h1>

			<div id="cl-db-plugin-info" class="cl-db-plugin-info" hidden>
				<table class="cl-db-plugin-info-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Plugin', 'cl-db-tools' ); ?></th>
						<td><?php echo esc_html( $plugin_data['name'] ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Version', 'cl-db-tools' ); ?></th>
						<td><code><?php echo esc_html( $plugin_data['version'] ); ?></code></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Last updated', 'cl-db-tools' ); ?></th>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $plugin_data['updated'] ) ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Author', 'cl-db-tools' ); ?></th>
						<td>
							<a href="<?php echo esc_url( $plugin_data['author_uri'] ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( $plugin_data['author'] ); ?>
							</a>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Description', 'cl-db-tools' ); ?></th>
						<td><?php echo esc_html( $plugin_data['description'] ); ?></td>
					</tr>
				</table>
			</div>

			<div class="cl-db-notice">
				<p>
					<strong><?php esc_html_e( 'Important:', 'cl-db-tools' ); ?></strong>
					<?php esc_html_e( 'Always make a backup of your database before performing any optimization operations.', 'cl-db-tools' ); ?>
				</p>
			</div>

			<div class="nav-tab-wrapper">
				<a href="#tab-overview" class="nav-tab nav-tab-active"><?php esc_html_e( 'Overview', 'cl-db-tools' ); ?></a>
				<a href="#tab-tables" class="nav-tab"><?php esc_html_e( 'Tables', 'cl-db-tools' ); ?></a>
				<a href="#tab-autoload" class="nav-tab"><?php esc_html_e( 'Autoload Options', 'cl-db-tools' ); ?></a>
				<a href="#tab-posts" class="nav-tab"><?php esc_html_e( 'Posts Cleanup', 'cl-db-tools' ); ?></a>
				<a href="#tab-orphaned" class="nav-tab"><?php esc_html_e( 'Orphaned Data', 'cl-db-tools' ); ?></a>
				<?php if ( CL_DB_Analyzer::is_woocommerce_active() ) : ?>
					<a href="#tab-woocommerce" class="nav-tab"><?php esc_html_e( 'WooCommerce', 'cl-db-tools' ); ?></a>
				<?php endif; ?>
			</div>

			<div id="tab-overview" class="tab-content active">
				<?php $this->render_overview_tab(); ?>
			</div>

			<div id="tab-tables" class="tab-content">
				<?php $this->render_tables_tab(); ?>
			</div>

			<div id="tab-autoload" class="tab-content">
				<?php $this->render_autoload_tab(); ?>
			</div>

			<div id="tab-posts" class="tab-content">
				<?php $this->render_posts_tab(); ?>
			</div>

			<div id="tab-orphaned" class="tab-content">
				<?php $this->render_orphaned_tab(); ?>
			</div>

			<?php if ( CL_DB_Analyzer::is_woocommerce_active() ) : ?>
				<div id="tab-woocommerce" class="tab-content">
					<?php $this->render_woocommerce_tab(); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render overview tab
	 */
	private function render_overview_tab() {
		global $wpdb;

		$db_size     = CL_DB_Analyzer::get_database_size();
		$tables_info = CL_DB_Analyzer::get_tables_info();
		$non_innodb  = CL_DB_Analyzer::get_non_innodb_tables();

		// Get database connection info.
		$db_version     = $wpdb->get_var( 'SELECT VERSION()' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$db_charset     = $wpdb->charset;
		$db_collate     = $wpdb->collate;
		$db_server_info = $wpdb->db_server_info();

		// Determine database type.
		$db_type = __( 'MySQL', 'cl-db-tools' );
		if ( stripos( $db_server_info, 'mariadb' ) !== false ) {
			$db_type = __( 'MariaDB', 'cl-db-tools' );
		}

		// Calculate total rows.
		$total_rows = 0;
		foreach ( $tables_info as $table ) {
			$total_rows += (int) $table['rows'];
		}

		?>
		<div class="cl-db-section">
			<h2><?php esc_html_e( 'Database Connection', 'cl-db-tools' ); ?></h2>

			<table class="widefat cl-db-overview-table">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Database Name', 'cl-db-tools' ); ?></th>
						<td><code><?php echo esc_html( DB_NAME ); ?></code></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Database User', 'cl-db-tools' ); ?></th>
						<td><code><?php echo esc_html( DB_USER ); ?></code></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Database Host', 'cl-db-tools' ); ?></th>
						<td><code><?php echo esc_html( DB_HOST ); ?></code></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Database Type', 'cl-db-tools' ); ?></th>
						<td><?php echo esc_html( $db_type ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Server Version', 'cl-db-tools' ); ?></th>
						<td><?php echo esc_html( $db_version ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Server Info', 'cl-db-tools' ); ?></th>
						<td><?php echo esc_html( $db_server_info ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Charset', 'cl-db-tools' ); ?></th>
						<td><code><?php echo esc_html( $db_charset ); ?></code></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Collation', 'cl-db-tools' ); ?></th>
						<td><code><?php echo esc_html( ! empty( $db_collate ) ? $db_collate : __( 'Default', 'cl-db-tools' ) ); ?></code></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Table Prefix', 'cl-db-tools' ); ?></th>
						<td><code><?php echo esc_html( $wpdb->prefix ); ?></code></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="cl-db-section">
			<h2><?php esc_html_e( 'Database Statistics', 'cl-db-tools' ); ?></h2>

			<div class="cl-db-stats">
				<div class="cl-db-stat-box">
					<h3><?php esc_html_e( 'Total Size', 'cl-db-tools' ); ?></h3>
					<p class="cl-db-stat-value"><?php echo esc_html( CL_DB_Analyzer::format_bytes( $db_size->total_size ) ); ?></p>
				</div>

				<div class="cl-db-stat-box">
					<h3><?php esc_html_e( 'Data Size', 'cl-db-tools' ); ?></h3>
					<p class="cl-db-stat-value"><?php echo esc_html( CL_DB_Analyzer::format_bytes( $db_size->data_size ) ); ?></p>
				</div>

				<div class="cl-db-stat-box">
					<h3><?php esc_html_e( 'Index Size', 'cl-db-tools' ); ?></h3>
					<p class="cl-db-stat-value"><?php echo esc_html( CL_DB_Analyzer::format_bytes( $db_size->index_size ) ); ?></p>
				</div>

				<div class="cl-db-stat-box">
					<h3><?php esc_html_e( 'Tables', 'cl-db-tools' ); ?></h3>
					<p class="cl-db-stat-value"><?php echo esc_html( number_format_i18n( count( $tables_info ) ) ); ?></p>
				</div>

				<div class="cl-db-stat-box">
					<h3><?php esc_html_e( 'Total Rows', 'cl-db-tools' ); ?></h3>
					<p class="cl-db-stat-value"><?php echo esc_html( number_format_i18n( $total_rows ) ); ?></p>
				</div>

				<div class="cl-db-stat-box">
					<h3><?php esc_html_e( 'Non-InnoDB Tables', 'cl-db-tools' ); ?></h3>
					<p class="cl-db-stat-value"><?php echo esc_html( count( $non_innodb ) ); ?></p>
				</div>
			</div>

			<div class="cl-db-query-display">
				<h3><?php esc_html_e( 'Database Size Query', 'cl-db-tools' ); ?></h3>
				<?php $this->render_query_box( CL_DB_Query_Builder::get_database_size_query(), 'get_database_size' ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render tables tab
	 */
	private function render_tables_tab() {
		$tables_info = CL_DB_Analyzer::get_tables_info();
		$non_innodb = CL_DB_Analyzer::get_non_innodb_tables();

		?>
		<div class="cl-db-section">
			<h2><?php esc_html_e( 'Database Tables', 'cl-db-tools' ); ?></h2>

			<div class="cl-db-query-display">
				<h3><?php esc_html_e( 'Tables Information Query', 'cl-db-tools' ); ?></h3>
				<?php $this->render_query_box( CL_DB_Query_Builder::get_tables_info_query(), 'get_tables_info' ); ?>
			</div>

			<p>
				<button class="button button-primary cl-optimize-all-tables" data-requires-backup="true">
					<?php esc_html_e( 'Optimize All Tables', 'cl-db-tools' ); ?>
				</button>
			</p>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Table Name', 'cl-db-tools' ); ?></th>
						<th><?php esc_html_e( 'Engine', 'cl-db-tools' ); ?></th>
						<th><?php esc_html_e( 'Rows', 'cl-db-tools' ); ?></th>
						<th><?php esc_html_e( 'Data Size', 'cl-db-tools' ); ?></th>
						<th><?php esc_html_e( 'Index Size', 'cl-db-tools' ); ?></th>
						<th><?php esc_html_e( 'Total Size', 'cl-db-tools' ); ?></th>
						<th>
							<?php esc_html_e( 'Created', 'cl-db-tools' ); ?>
							<span class="cl-db-tooltip" aria-label="<?php esc_attr_e( 'This date may change after OPTIMIZE TABLE or ALTER TABLE operations', 'cl-db-tools' ); ?>">
								<span class="dashicons dashicons-info-outline"></span>
							</span>
						</th>
						<th><?php esc_html_e( 'Actions', 'cl-db-tools' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $tables_info as $table ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $table['table_name'] ); ?></strong></td>
							<td><?php echo esc_html( $table['engine'] ); ?></td>
							<td><?php echo esc_html( number_format_i18n( $table['rows'] ) ); ?></td>
							<td><?php echo esc_html( CL_DB_Analyzer::format_bytes( $table['data_length'] ) ); ?></td>
							<td><?php echo esc_html( CL_DB_Analyzer::format_bytes( $table['index_length'] ) ); ?></td>
							<td><?php echo esc_html( CL_DB_Analyzer::format_bytes( $table['data_length'] + $table['index_length'] ) ); ?></td>
							<td>
								<?php
								if ( ! empty( $table['create_time'] ) ) {
									echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $table['create_time'] ) ) );
								} else {
									echo '<span class="cl-db-na">' . esc_html__( 'N/A', 'cl-db-tools' ) . '</span>';
								}
								?>
							</td>
							<td>
								<button class="button button-small cl-optimize-table" data-table="<?php echo esc_attr( $table['table_name'] ); ?>">
									<?php esc_html_e( 'Optimize', 'cl-db-tools' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ( ! empty( $non_innodb ) ) : ?>
				<div class="cl-db-section">
					<h3><?php esc_html_e( 'Convert to InnoDB', 'cl-db-tools' ); ?></h3>
					<p><?php esc_html_e( 'The following tables are not using the InnoDB engine:', 'cl-db-tools' ); ?></p>

					<div class="cl-db-query-display">
						<?php $this->render_query_box( CL_DB_Query_Builder::get_non_innodb_tables_query(), 'get_non_innodb' ); ?>
					</div>

					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Table Name', 'cl-db-tools' ); ?></th>
								<th><?php esc_html_e( 'Current Engine', 'cl-db-tools' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'cl-db-tools' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $non_innodb as $table ) : ?>
								<tr>
									<td><strong><?php echo esc_html( $table['table_name'] ); ?></strong></td>
									<td><?php echo esc_html( $table['engine'] ); ?></td>
									<td>
										<?php
										$query = CL_DB_Query_Builder::get_convert_to_innodb_query( $table['table_name'] );
										$this->render_query_box( $query, 'convert_innodb_' . $table['table_name'], $table['table_name'] );
										?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<p>
						<button class="button button-primary cl-convert-all-innodb" data-requires-backup="true">
							<?php esc_html_e( 'Convert All to InnoDB', 'cl-db-tools' ); ?>
						</button>
					</p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render autoload tab
	 */
	private function render_autoload_tab() {
		$autoload_info = CL_DB_Analyzer::get_autoload_info();

		?>
		<div class="cl-db-section">
			<h2><?php esc_html_e( 'Autoload Options', 'cl-db-tools' ); ?></h2>

			<div class="cl-db-stats">
				<div class="cl-db-stat-box">
					<h3><?php esc_html_e( 'Total Autoload Options', 'cl-db-tools' ); ?></h3>
					<p class="cl-db-stat-value"><?php echo esc_html( number_format_i18n( $autoload_info['total']->count ) ); ?></p>
				</div>

				<div class="cl-db-stat-box">
					<h3><?php esc_html_e( 'Total Autoload Size', 'cl-db-tools' ); ?></h3>
					<p class="cl-db-stat-value"><?php echo esc_html( CL_DB_Analyzer::format_bytes( $autoload_info['total']->total_size ) ); ?></p>
				</div>
			</div>

			<div class="cl-db-query-display">
				<h3><?php esc_html_e( 'Autoload Options Query', 'cl-db-tools' ); ?></h3>
				<?php $this->render_query_box( CL_DB_Query_Builder::get_autoload_options_query(), 'get_autoload' ); ?>
			</div>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Option Name', 'cl-db-tools' ); ?></th>
						<th><?php esc_html_e( 'Size', 'cl-db-tools' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'cl-db-tools' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $autoload_info['options'] as $option ) : ?>
						<tr>
							<td><code><?php echo esc_html( $option['option_name'] ); ?></code></td>
							<td><?php echo esc_html( CL_DB_Analyzer::format_bytes( $option['size'] ) ); ?></td>
							<td>
								<button class="button button-small cl-update-autoload" data-option="<?php echo esc_attr( $option['option_name'] ); ?>" data-value="no" data-requires-backup="true">
									<?php esc_html_e( 'Disable Autoload', 'cl-db-tools' ); ?>
								</button>
								<button class="button button-small button-link-delete cl-delete-option" data-option="<?php echo esc_attr( $option['option_name'] ); ?>" data-requires-backup="true">
									<?php esc_html_e( 'Delete', 'cl-db-tools' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render posts cleanup tab
	 */
	private function render_posts_tab() {
		$autosave_info = CL_DB_Analyzer::get_autosave_info();

		?>
		<div class="cl-db-section">
			<h2><?php esc_html_e( 'Autosaves and Revisions Cleanup', 'cl-db-tools' ); ?></h2>

			<div class="cl-db-stats">
				<div class="cl-db-stat-box">
					<h3><?php esc_html_e( 'Total Autosaves', 'cl-db-tools' ); ?></h3>
					<p class="cl-db-stat-value"><?php echo esc_html( number_format_i18n( $autosave_info['count'] ) ); ?></p>
				</div>
			</div>

			<div class="cl-db-query-display">
				<h3><?php esc_html_e( 'Autosaves Query', 'cl-db-tools' ); ?></h3>
				<?php $this->render_query_box( CL_DB_Query_Builder::get_autosave_posts_query(), 'get_autosaves' ); ?>
			</div>

			<div class="cl-db-action-box">
				<h3><?php esc_html_e( 'Delete Autosaves', 'cl-db-tools' ); ?></h3>
				<p>
					<label for="keep-autosaves">
						<?php esc_html_e( 'Keep last:', 'cl-db-tools' ); ?>
						<input type="number" id="keep-autosaves" value="0" min="0" step="1" style="width: 80px;">
						<?php esc_html_e( 'autosaves (0 = delete all)', 'cl-db-tools' ); ?>
					</label>
				</p>
				<p>
					<button class="button button-primary cl-delete-autosaves" data-requires-backup="true">
						<?php esc_html_e( 'Delete Autosaves', 'cl-db-tools' ); ?>
					</button>
				</p>

				<div class="cl-db-query-display">
					<?php $this->render_query_box( CL_DB_Query_Builder::get_delete_autosaves_query( 0 ), 'delete_autosaves' ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render orphaned data tab
	 */
	private function render_orphaned_tab() {
		$orphaned_postmeta = CL_DB_Analyzer::get_orphaned_postmeta_info();
		$orphaned_usermeta = CL_DB_Analyzer::get_orphaned_usermeta_info();

		?>
		<div class="cl-db-section">
			<h2><?php esc_html_e( 'Orphaned Postmeta', 'cl-db-tools' ); ?></h2>

			<div class="cl-db-stats">
				<div class="cl-db-stat-box">
					<h3><?php esc_html_e( 'Orphaned Postmeta Entries', 'cl-db-tools' ); ?></h3>
					<p class="cl-db-stat-value"><?php echo esc_html( number_format_i18n( $orphaned_postmeta['count'] ) ); ?></p>
				</div>
			</div>

			<div class="cl-db-query-display">
				<h3><?php esc_html_e( 'Find Orphaned Postmeta Query', 'cl-db-tools' ); ?></h3>
				<?php $this->render_query_box( CL_DB_Query_Builder::get_orphaned_postmeta_query(), 'get_orphaned_postmeta' ); ?>
			</div>

			<?php if ( $orphaned_postmeta['count'] > 0 ) : ?>
				<p>
					<button class="button button-primary cl-delete-orphaned-postmeta" data-requires-backup="true">
						<?php esc_html_e( 'Delete Orphaned Postmeta', 'cl-db-tools' ); ?>
					</button>
				</p>

				<div class="cl-db-query-display">
					<h3><?php esc_html_e( 'Delete Orphaned Postmeta Query', 'cl-db-tools' ); ?></h3>
					<?php $this->render_query_box( CL_DB_Query_Builder::get_delete_orphaned_postmeta_query(), 'delete_orphaned_postmeta' ); ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="cl-db-section">
			<h2><?php esc_html_e( 'Orphaned Usermeta', 'cl-db-tools' ); ?></h2>

			<div class="cl-db-stats">
				<div class="cl-db-stat-box">
					<h3><?php esc_html_e( 'Orphaned Usermeta Entries', 'cl-db-tools' ); ?></h3>
					<p class="cl-db-stat-value"><?php echo esc_html( number_format_i18n( $orphaned_usermeta['count'] ) ); ?></p>
				</div>
			</div>

			<div class="cl-db-query-display">
				<h3><?php esc_html_e( 'Find Orphaned Usermeta Query', 'cl-db-tools' ); ?></h3>
				<?php $this->render_query_box( CL_DB_Query_Builder::get_orphaned_usermeta_query(), 'get_orphaned_usermeta' ); ?>
			</div>

			<?php if ( $orphaned_usermeta['count'] > 0 ) : ?>
				<p>
					<button class="button button-primary cl-delete-orphaned-usermeta" data-requires-backup="true">
						<?php esc_html_e( 'Delete Orphaned Usermeta', 'cl-db-tools' ); ?>
					</button>
				</p>

				<div class="cl-db-query-display">
					<h3><?php esc_html_e( 'Delete Orphaned Usermeta Query', 'cl-db-tools' ); ?></h3>
					<?php $this->render_query_box( CL_DB_Query_Builder::get_delete_orphaned_usermeta_query(), 'delete_orphaned_usermeta' ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render WooCommerce tab
	 */
	private function render_woocommerce_tab() {
		$wc_sessions = CL_DB_Analyzer::get_wc_sessions_info();
		$expired_transients = CL_DB_Analyzer::get_expired_transients_info();

		?>
		<div class="cl-db-section">
			<h2><?php esc_html_e( 'WooCommerce Sessions', 'cl-db-tools' ); ?></h2>

			<?php if ( false !== $wc_sessions ) : ?>
				<div class="cl-db-stats">
					<div class="cl-db-stat-box">
						<h3><?php esc_html_e( 'Total Sessions', 'cl-db-tools' ); ?></h3>
						<p class="cl-db-stat-value"><?php echo esc_html( number_format_i18n( $wc_sessions['total'] ) ); ?></p>
					</div>

					<div class="cl-db-stat-box">
						<h3><?php esc_html_e( 'Expired Sessions', 'cl-db-tools' ); ?></h3>
						<p class="cl-db-stat-value"><?php echo esc_html( number_format_i18n( $wc_sessions['expired'] ) ); ?></p>
					</div>
				</div>

				<?php if ( $wc_sessions['expired'] > 0 ) : ?>
					<p>
						<button class="button button-primary cl-delete-wc-sessions" data-requires-backup="true">
							<?php esc_html_e( 'Delete Expired Sessions', 'cl-db-tools' ); ?>
						</button>
					</p>

					<div class="cl-db-query-display">
						<?php $this->render_query_box( CL_DB_Query_Builder::get_delete_expired_wc_sessions_query(), 'delete_wc_sessions' ); ?>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<p><?php esc_html_e( 'WooCommerce sessions table not found.', 'cl-db-tools' ); ?></p>
			<?php endif; ?>
		</div>

		<div class="cl-db-section">
			<h2><?php esc_html_e( 'Expired Transients', 'cl-db-tools' ); ?></h2>

			<div class="cl-db-stats">
				<div class="cl-db-stat-box">
					<h3><?php esc_html_e( 'Expired Transients', 'cl-db-tools' ); ?></h3>
					<p class="cl-db-stat-value"><?php echo esc_html( number_format_i18n( $expired_transients['count'] ) ); ?></p>
				</div>
			</div>

			<div class="cl-db-query-display">
				<?php $this->render_query_box( CL_DB_Query_Builder::get_expired_transients_query(), 'get_expired_transients' ); ?>
			</div>

			<?php if ( $expired_transients['count'] > 0 ) : ?>
				<p>
					<button class="button button-primary cl-delete-expired-transients" data-requires-backup="true">
						<?php esc_html_e( 'Delete Expired Transients', 'cl-db-tools' ); ?>
					</button>
				</p>

				<div class="cl-db-query-display">
					<?php $this->render_query_box( CL_DB_Query_Builder::get_delete_expired_transients_query(), 'delete_expired_transients' ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render query box with copy and execute buttons
	 *
	 * @param string $query SQL query.
	 * @param string $action Action identifier.
	 * @param string $param Optional parameter (e.g., table name).
	 */
	private function render_query_box( $query, $action, $param = '' ) {
		if ( false === $query ) {
			return;
		}

		// Clean up query formatting: remove extra indentation
		$lines = explode( "\n", trim( $query ) );
		$lines = array_map( 'trim', $lines );
		$lines = array_filter( $lines ); // Remove empty lines
		$clean_query = implode( "\n", $lines );

		$requires_backup = CL_DB_Security::requires_backup_confirmation( $action );
		?>
		<div class="cl-db-query-box">
			<pre class="cl-db-query-content"><?php echo esc_html( $clean_query ); ?></pre>
			<div class="cl-db-query-actions">
				<button class="button cl-copy-query" data-query="<?php echo esc_attr( $clean_query ); ?>">
					<?php esc_html_e( 'Copy Query', 'cl-db-tools' ); ?>
				</button>
				<button class="button button-primary cl-execute-query" data-action="<?php echo esc_attr( $action ); ?>" data-param="<?php echo esc_attr( $param ); ?>" <?php echo $requires_backup ? 'data-requires-backup="true"' : ''; ?>>
					<?php esc_html_e( 'Execute Query', 'cl-db-tools' ); ?>
				</button>
			</div>
		</div>
		<?php
	}
}
