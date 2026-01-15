/**
 * CL Database Tools - Admin JavaScript
 *
 * @package CL_DB_Tools
 */

(function($) {
	'use strict';

	const CLDBTools = {
		/**
		 * Initialize
		 */
		init: function() {
			this.setupInfoToggle();
			this.setupTabs();
			this.restoreActiveTab();
			this.setupCopyButtons();
			this.setupExecuteButtons();
			this.setupActionButtons();
		},

		/**
		 * Setup plugin info toggle
		 */
		setupInfoToggle: function() {
			$(document).on('click', '.cl-db-info-toggle', function(e) {
				e.preventDefault();

				const $button = $(this);
				const $infoPanel = $('#cl-db-plugin-info');
				const isExpanded = $button.attr('aria-expanded') === 'true';

				// Toggle aria-expanded attribute
				$button.attr('aria-expanded', !isExpanded);

				// Toggle panel visibility
				if (isExpanded) {
					$infoPanel.attr('hidden', true);
				} else {
					$infoPanel.removeAttr('hidden');
				}
			});
		},

		/**
		 * Setup tab navigation
		 */
		setupTabs: function() {
			$('.nav-tab').on('click', function(e) {
				e.preventDefault();

				const targetId = $(this).attr('href');

				// Save active tab to localStorage
				localStorage.setItem('clDbToolsActiveTab', targetId);

				// Update active tab
				$('.nav-tab').removeClass('nav-tab-active');
				$(this).addClass('nav-tab-active');

				// Update active content
				$('.tab-content').removeClass('active');
				$(targetId).addClass('active');
			});
		},

		/**
		 * Restore previously active tab after page reload
		 */
		restoreActiveTab: function() {
			const activeTab = localStorage.getItem('clDbToolsActiveTab');

			if (activeTab && $(activeTab).length) {
				// Remove default active states
				$('.nav-tab').removeClass('nav-tab-active');
				$('.tab-content').removeClass('active');

				// Activate saved tab
				$('.nav-tab[href="' + activeTab + '"]').addClass('nav-tab-active');
				$(activeTab).addClass('active');
			}
		},

		/**
		 * Setup copy query buttons
		 */
		setupCopyButtons: function() {
			$(document).on('click', '.cl-copy-query', function(e) {
				e.preventDefault();

				const query = $(this).data('query');

				CLDBTools.copyToClipboard(query);
			});
		},

		/**
		 * Setup execute query buttons
		 */
		setupExecuteButtons: function() {
			$(document).on('click', '.cl-execute-query', function(e) {
				e.preventDefault();

				const $button = $(this);
				const action = $button.data('action');
				const param = $button.data('param') || '';
				const requiresBackup = $button.data('requires-backup') || false;

				if (requiresBackup && !CLDBTools.confirmBackup()) {
					return;
				}

				CLDBTools.executeAction(action, param, $button);
			});
		},

		/**
		 * Setup action buttons
		 */
		setupActionButtons: function() {
			// Optimize table
			$(document).on('click', '.cl-optimize-table', function(e) {
				e.preventDefault();

				const $button = $(this);
				const table = $button.data('table');

				if (!confirm(clDbTools.i18n.confirmBackup)) {
					return;
				}

				CLDBTools.ajaxRequest('cl_db_optimize_table', { table: table }, $button);
			});

			// Convert to InnoDB
			$(document).on('click', '.cl-convert-innodb', function(e) {
				e.preventDefault();

				const $button = $(this);
				const table = $button.data('table');

				if (!confirm(clDbTools.i18n.confirmBackup)) {
					return;
				}

				CLDBTools.ajaxRequest('cl_db_convert_innodb', { table: table }, $button);
			});

			// Convert all to InnoDB
			$(document).on('click', '.cl-convert-all-innodb', function(e) {
				e.preventDefault();

				const $button = $(this);

				if (!confirm(clDbTools.i18n.confirmBackup)) {
					return;
				}

				CLDBTools.ajaxRequest('cl_db_convert_all_innodb', {}, $button);
			});

			// Update autoload
			$(document).on('click', '.cl-update-autoload', function(e) {
				e.preventDefault();

				const $button = $(this);
				const option = $button.data('option');
				const value = $button.data('value');

				if (!confirm(clDbTools.i18n.confirmBackup)) {
					return;
				}

				CLDBTools.ajaxRequest('cl_db_update_autoload', { option: option, value: value }, $button);
			});

			// Delete option
			$(document).on('click', '.cl-delete-option', function(e) {
				e.preventDefault();

				const $button = $(this);
				const option = $button.data('option');

				if (!confirm(clDbTools.i18n.confirmBackup + '\n\n' + clDbTools.i18n.confirmDelete)) {
					return;
				}

				CLDBTools.ajaxRequest('cl_db_delete_option', { option: option }, $button);
			});

			// Delete autosaves
			$(document).on('click', '.cl-delete-autosaves', function(e) {
				e.preventDefault();

				const $button = $(this);
				const keepLast = $('#keep-autosaves').val() || 0;

				if (!confirm(clDbTools.i18n.confirmBackup + '\n\n' + clDbTools.i18n.confirmDelete)) {
					return;
				}

				CLDBTools.ajaxRequest('cl_db_delete_autosaves', { keep_last: keepLast }, $button);
			});

			// Delete orphaned postmeta
			$(document).on('click', '.cl-delete-orphaned-postmeta', function(e) {
				e.preventDefault();

				const $button = $(this);

				if (!confirm(clDbTools.i18n.confirmBackup + '\n\n' + clDbTools.i18n.confirmDelete)) {
					return;
				}

				CLDBTools.ajaxRequest('cl_db_delete_orphaned_postmeta', {}, $button);
			});

			// Delete orphaned usermeta
			$(document).on('click', '.cl-delete-orphaned-usermeta', function(e) {
				e.preventDefault();

				const $button = $(this);

				if (!confirm(clDbTools.i18n.confirmBackup + '\n\n' + clDbTools.i18n.confirmDelete)) {
					return;
				}

				CLDBTools.ajaxRequest('cl_db_delete_orphaned_usermeta', {}, $button);
			});

			// Delete WooCommerce sessions
			$(document).on('click', '.cl-delete-wc-sessions', function(e) {
				e.preventDefault();

				const $button = $(this);

				if (!confirm(clDbTools.i18n.confirmBackup)) {
					return;
				}

				CLDBTools.ajaxRequest('cl_db_delete_wc_sessions', {}, $button);
			});

			// Delete expired transients
			$(document).on('click', '.cl-delete-expired-transients', function(e) {
				e.preventDefault();

				const $button = $(this);

				if (!confirm(clDbTools.i18n.confirmBackup)) {
					return;
				}

				CLDBTools.ajaxRequest('cl_db_delete_expired_transients', {}, $button);
			});

			// Delete all transients
			$(document).on('click', '.cl-delete-all-transients', function(e) {
				e.preventDefault();

				const $button = $(this);

				if (!confirm(clDbTools.i18n.confirmBackup)) {
					return;
				}

				CLDBTools.ajaxRequest('cl_db_delete_all_transients', {}, $button);
			});

			// Delete orphaned WooCommerce order items
			$(document).on('click', '.cl-delete-orphaned-wc-order-items', function(e) {
				e.preventDefault();

				const $button = $(this);

				if (!confirm(clDbTools.i18n.confirmBackup)) {
					return;
				}

				CLDBTools.ajaxRequest('cl_db_delete_orphaned_wc_order_items', {}, $button);
			});

			// Delete orphaned WooCommerce order item meta
			$(document).on('click', '.cl-delete-orphaned-wc-order-itemmeta', function(e) {
				e.preventDefault();

				const $button = $(this);

				if (!confirm(clDbTools.i18n.confirmBackup)) {
					return;
				}

				CLDBTools.ajaxRequest('cl_db_delete_orphaned_wc_order_itemmeta', {}, $button);
			});

			// Optimize all tables
			$(document).on('click', '.cl-optimize-all-tables', function(e) {
				e.preventDefault();

				const $button = $(this);

				if (!confirm(clDbTools.i18n.confirmBackup)) {
					return;
				}

				CLDBTools.ajaxRequest('cl_db_optimize_all_tables', {}, $button);
			});
		},

		/**
		 * Execute action
		 */
		executeAction: function(action, param, $button) {
			this.ajaxRequest('cl_db_execute_query', { action_type: action, param: param }, $button);
		},

		/**
		 * Make AJAX request
		 */
		ajaxRequest: function(action, data, $button) {
			const originalText = $button.text();

			$button.addClass('loading').prop('disabled', true);

			$.ajax({
				url: clDbTools.ajaxUrl,
				type: 'POST',
				data: {
					action: action,
					nonce: clDbTools.nonce,
					...data
				},
				success: function(response) {
					if (response.success) {
						CLDBTools.showMessage(response.data.message || clDbTools.i18n.success, 'success');

						// Show query results if available
						if (action === 'cl_db_execute_query' && response.data.data) {
							CLDBTools.showQueryResults(response.data.data, $button);
						}

						// Reload page after 1.5 seconds for operations that modify data
						if (action !== 'cl_db_execute_query') {
							setTimeout(function() {
								location.reload();
							}, 1500);
						}
					} else {
						CLDBTools.showMessage(response.data.message || clDbTools.i18n.error, 'error');
					}
				},
				error: function(xhr, status, error) {
					CLDBTools.showMessage(clDbTools.i18n.error + ': ' + error, 'error');
				},
				complete: function() {
					$button.removeClass('loading').prop('disabled', false).text(originalText);
				}
			});
		},

		/**
		 * Show query results in a formatted box
		 */
		showQueryResults: function(data, $button) {
			// Remove any existing results in this query box
			const $queryBox = $button.closest('.cl-db-query-box');
			$queryBox.find('.cl-db-query-results').remove();

			// Create results container
			const $results = $('<div class="cl-db-query-results"></div>');
			const $resultsContent = $('<pre class="cl-db-query-results-content"></pre>');

			// Format the data as JSON with indentation
			const formattedData = JSON.stringify(data, null, 2);
			$resultsContent.text(formattedData);

			$results.append($resultsContent);
			$queryBox.append($results);
		},

		/**
		 * Copy text to clipboard
		 */
		copyToClipboard: function(text) {
			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard.writeText(text).then(
					function() {
						CLDBTools.showCopyFeedback(clDbTools.i18n.copied);
					},
					function() {
						CLDBTools.fallbackCopyToClipboard(text);
					}
				);
			} else {
				CLDBTools.fallbackCopyToClipboard(text);
			}
		},

		/**
		 * Fallback copy to clipboard method
		 */
		fallbackCopyToClipboard: function(text) {
			const $temp = $('<textarea>');
			$('body').append($temp);
			$temp.val(text).select();

			try {
				document.execCommand('copy');
				CLDBTools.showCopyFeedback(clDbTools.i18n.copied);
			} catch (err) {
				CLDBTools.showCopyFeedback(clDbTools.i18n.copyFailed);
			}

			$temp.remove();
		},

		/**
		 * Show copy feedback message
		 */
		showCopyFeedback: function(message) {
			const $feedback = $('<div class="cl-db-copy-feedback">' + message + '</div>');
			$('body').append($feedback);

			setTimeout(function() {
				$feedback.addClass('fadeout');
				setTimeout(function() {
					$feedback.remove();
				}, 300);
			}, 2000);
		},

		/**
		 * Show message
		 */
		showMessage: function(message, type) {
			const $message = $('<div class="cl-db-message ' + type + '">' + message + '</div>');
			$('.cl-db-tools h1').after($message);

			setTimeout(function() {
				$message.fadeOut(function() {
					$(this).remove();
				});
			}, 5000);
		},

		/**
		 * Confirm backup
		 */
		confirmBackup: function() {
			return confirm(clDbTools.i18n.confirmBackup);
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		CLDBTools.init();
	});

})(jQuery);
