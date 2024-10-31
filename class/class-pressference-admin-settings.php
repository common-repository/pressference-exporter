<?php

if (!defined('ABSPATH')) {
	exit;
}

if (class_exists('Pressference_Admin_Settings', false)) {
	return new Pressference_Admin_Settings();
}

class Pressference_Admin_Settings {
	public function __construct() {
		$this->exporter_handling = new Pressference_Exporter_Handling();

		add_action('admin_init', array($this, 'admin_settings'));
		add_action('admin_footer-pf-exporter_page_pf-settings', array($this, 'footer_scripts'));
	}

	public function admin_settings() {
		register_setting('pf_general', 'pf-settings');
		register_setting('pf_csv', 'pf-csv-settings');
		register_setting('pf_email', 'pf-email-settings');

		add_settings_section('general_setting', __('', PRESSFERENCE_TEXT_DOMAIN), [$this, 'general_setting_section_callback'], 'pf-settings');
		add_settings_section('general_action_setting', __('', PRESSFERENCE_TEXT_DOMAIN), [$this, 'general_action_setting_section_callback'], 'pf-settings');
		add_settings_section('csv_general_setting', __('', PRESSFERENCE_TEXT_DOMAIN), [$this, 'csv_general_setting_section_callback'], 'pf-csv-settings');
		add_settings_section('email_setting', __('', PRESSFERENCE_TEXT_DOMAIN), [$this, 'email_setting_section_callback'], 'pf-email-settings');

		add_settings_field('export_filename', __('Export Filename', PRESSFERENCE_TEXT_DOMAIN), array($this, 'export_filename_field_callback'), 'pf-settings', 'general_setting', [
				'label_for' => 'document_name',
				'class' => 'encoding-field regular-text'
			]);
		add_settings_field('export_file_path', __('Export File Path', PRESSFERENCE_TEXT_DOMAIN), array($this, 'export_file_path_field_callback'), 'pf-settings', 'general_setting', [
				'label_for' => 'document_path',
				'class' => 'encoding-field regular-text'
			]);
		add_settings_field('script_timeout', __('Script Timeout', PRESSFERENCE_TEXT_DOMAIN), array($this, 'script_timeout_field_callback'), 'pf-settings', 'general_setting', [
				'label_for' => 'script_timeout',
				'class' => 'input-select'
			]);
		add_settings_field('export_pre_sheet', __('Export Separately', PRESSFERENCE_TEXT_DOMAIN), array($this, 'export_per_sheet_field_callback'), 'pf-settings', 'general_setting', [
				'label_for' => 'export_per_sheet',
				'class' => 'export-order-per-sheet'
			]);
		add_settings_field('date_format', __('Date Format', PRESSFERENCE_TEXT_DOMAIN), array($this, 'date_format_field_callback'), 'pf-settings', 'general_setting', [
				'label_for' => 'date_format',
				'class' => 'date-format'
			]);
		add_settings_field('update_order_status_exported', __('Change Order Status After Export', PRESSFERENCE_TEXT_DOMAIN), array($this, 'update_order_status_exported_field_callback'), 'pf-settings', 'general_action_setting', [
				'label_for' => 'update_order_status_exported',
				'class' => 'update-order-status-exported'
			]);

		add_settings_field('field_delimiter', __('Field Delimiter', PRESSFERENCE_TEXT_DOMAIN), array($this, 'delimiter_field_callback'), 'pf-csv-settings', 'csv_general_setting', [
				'label_for' => 'field_delimiter',
				'class' => 'field-delimiter'
			]);
		add_settings_field('header_formatting', __('Header Formatting', PRESSFERENCE_TEXT_DOMAIN), array($this, 'header_formatting_field_callback'), 'pf-csv-settings', 'csv_general_setting', [
				'label_for' => 'header_formatting',
				'class' => 'header-formatting'
			]);

		add_settings_field('email_recipient', __('Recipient(s) <span class="dashicons dashicons-editor-help pf-tooltip pf-tip" title="use comma as separator for multiple recipient."></span>', PRESSFERENCE_TEXT_DOMAIN), array($this, 'email_recipient_field_callback'), 'pf-email-settings', 'email_setting', [
				'label_for' => '',
				'class' => 'regular-text'
			]);
		add_settings_field('email_subject', __('Subject', PRESSFERENCE_TEXT_DOMAIN), array($this, 'email_subject_field_callback'), 'pf-email-settings', 'email_setting', [
				'label_for' => '',
				'class' => 'regular-text'
			]);
		add_settings_field('email_content', __('Content <span class="dashicons dashicons-editor-help pf-tooltip pf-tip" title="support html tags."></span>', PRESSFERENCE_TEXT_DOMAIN), array($this, 'email_content_field_callback'), 'pf-email-settings', 'email_setting', [
				'label_for' => '',
				'class' => ''
			]);
	}

	// Section callback
	public function general_setting_section_callback($args) {
		$html = '<h5>General Configuration</h5>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	public function general_action_setting_section_callback($args) {
		$html = '<h5>Actions</h5>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	public function csv_general_setting_section_callback($args) {}

	public function csv_event_setting_section_callback($args) {
		$html = '<h5>Events</h5>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	public function email_setting_section_callback($args) {
		$html = '<h5>Export by Email</h5>
			<p>Export by email can be configure from profile</p>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	// Field callback
	public function export_filename_field_callback($args) {
		$filename = get_option('pf_exporter_filename');

		$html = '<input type="text" name="filename" class="input-field '.$args['class'].'" id="'.$args['label_for'].'" autocomplete="off" value="'.esc_html($filename).'" />
			<div class="available-structure-tags">
				<p class="description">Usable tag: </p>
				<ul class="tagging">
					<li>
						<button type="button" class="button button-secondary" aria-pressed="false" data-label="date format Y-m-d">%date%</button>
					</li>
					<li>
						<button type="button" class="button button-secondary" aria-pressed="false" data-label="time in format (h:i:s)">%time%</button>
					</li>
					<li>
						<button type="button" class="button button-secondary" aria-pressed="false" data-label="export type (order, product, customers, etc)">%type%</button>
					</li>
					<li>
						<button type="button" class="button button-secondary" aria-pressed="false" data-label="store name">%store_name%</button>
					</li>
				</ul>
			</div>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	public function export_file_path_field_callback($args) {
		$html = '<label>&lt;application_root&gt;/wp-content/plugins/pf-exporter/export-file/</label>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	public function character_encoding_field_callback($args) {
		$encoding = get_option( 'pf_exporter_character_encoding' );

		$encoding_selection = [
			"ascii" => "ASCII",
			'utf-8' => "UTF-8"
		];

		$html = '<select name="character_encoding" class="input-select">';
		foreach ($encoding_selection as $k=>$v) {
			$html .= '<option value="'.$k.'" '.($encoding == $k ? 'selected="selected"' : '').'>'.$v.'</option>';
		}
		$html .= '</select>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	public function script_timeout_field_callback($args) {
		$script_timeout = get_option('pf_exporter_script_timeout');

		$script_timeout_selection = [
			"600" => "10 minute",
			"1800" => "30 minute",
			"3600" => "1 hour",
			"-1" => "Unlimited"
		];

		$html = '<select name="script_timeout" class="input-select">';
		foreach ( $script_timeout_selection as $k=>$v ) {
			$html .= '<option value="'.$k.'" '.( $script_timeout == $k ? 'selected="selected"' : '' ).'>'.$v.'</option>';
		}
		$html .= '</select>';
		$html .= '<p class="description">Increase timeout limit if encounter memory exhaust issue.</p>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	public function date_format_field_callback($args) {
		$dateFormat = get_option('pf_exporter_date_format');
		$customDateFormat = get_option('pf_exporter_customize_date_format');

		$default = "";
		if (!$dateFormat) {
			$default = date('F j, Y');
		}

		if (!$customDateFormat) {
			$customDateFormat = 'F j, Y';
		}

		$html = '<label for="first-date" class="form-label">
				<input type="radio" name="date_format" id="first-date" '.($dateFormat != 'custom' && $default != '' ? 'checked="checked"' : '').' value="F j, Y" />
				<span class="date-time-text">'.date('F j, Y').'</span>
				<code>F j, Y</code>
			</label><br>
			<label for="second-date" class="form-label">
				<input type="radio" name="date_format" id="second-date" '.($dateFormat == 'Y-m-d' ? 'checked="checked"' : '').' value="Y-m-d" />
				<span class="date-time-text">'.date('Y-m-d').'</span>
				<code>Y-m-d</code>
			</label><br>
			<label for="third-date" class="form-label">
				<input type="radio" name="date_format" id="third-date" '.($dateFormat == 'm/d/Y' ? 'checked="checked"' : '').' value="m/d/Y" />
				<span class="date-time-text">'.date('m/d/Y').'</span>
				<code>m/d/Y</code>
			</label><br>
			<label for="fourth-date" class="form-label">
				<input type="radio" name="date_format" id="fourth-date" '.($dateFormat == 'd/m/Y' ? 'checked="checked"' : '').' value="d/m/Y" />
				<span class="date-time-text">'.date('d/m/Y').'</span>
				<code>d/m/Y</code>
			</label><br>
			<label for="fifth-date" class="form-label">
				<input type="radio" name="date_format" id="fifth-date" '.($dateFormat == 'custom' ? 'checked="checked"' : '').' value="custom" />
				<span class="date-time-text">Custom: </span>
				<input type="text" class="small-text" name="customize_date_format" id="pf-custom-date-format" value="'.esc_html($customDateFormat).'" />
			</label><br>
			<p>
				<strong>Preview: </strong>
				<span class="example">'.$default.'</span>
				<span class="spinner"></span>
			</p>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	/**
	 * PF_Order need to update order status after export
	 */
	public function update_order_status_exported_field_callback($args) {
		$updateStatusOnExport = get_option('pf_exporter_update_order_status_exported');

		$order_statuses = array(
			'wc-pending' => __('Pending payment', PRESSFERENCE_TEXT_DOMAIN),
			'wc-processing' => __('Processing', PRESSFERENCE_TEXT_DOMAIN),
			'wc-on-hold' => __('On hold', PRESSFERENCE_TEXT_DOMAIN),
			'wc-completed' => __('Completed', PRESSFERENCE_TEXT_DOMAIN),
			'wc-cancelled' => __('Cancelled', PRESSFERENCE_TEXT_DOMAIN),
			'wc-refunded' => __('Refunded', PRESSFERENCE_TEXT_DOMAIN),
			'wc-failed' => __('Failed', PRESSFERENCE_TEXT_DOMAIN),
		);

		// get status from woocommerce
		// if woocommerce plugin is not activate or installed then disable this settings
		if (!is_plugin_active('woocommerce/woocommerce.php')) {
			$html = '<p class="description">Woocommerce plugin have been disabled.</p>';
			echo wp_kses($html, $this->exporter_handling->get_allowed_html());
			return;
		}
		
		$html = '<select name="update_order_status_exported" class="input-select">
			<option value="">---</option>';
		foreach ($order_statuses as $k=>$v) {
			$html .= '<option value="'.$k.'" '.($updateStatusOnExport == $k ? 'selected="selected"' : '').'>'.$v.'</option>';
		}
		$html .= '</select>';
		$html .= '<p class="description">Order status will be update after export. Normal filter export with order type will not update the status. (Work for profile with sales orders type export only.) </p>';
		$html .= '<p class="description"><i class="dangerous-note">***Notes: </i> Sales order without filter from profile, when export will update ALL order status to the set status.</p>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	public function delimiter_field_callback($args) {
		$fieldDelimiter = get_option('pf_exporter_field_delimiter');

		if (!strlen($fieldDelimiter)) {
			$fieldDelimiter = ',';
		}

		$html = '<input type="text" name="field_delimiter" class="input-field '.$args['class'].'" id="'.$args['label_for'].'" autocomplete="off" value="'.esc_html($fieldDelimiter).'" />
			<p class="description">Field delimiter are character that use to separate each column in the comma separated value (CSV) file. Default value are comma (,).</p>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	/**
	 * Hide for security reason
	 */
	public function enclosure_field_callback($args) {
		$enclosure = get_option('pf_exporter_enclosure');

		if (!strlen($enclosure)) {
			$enclosure = '"';
		}

		$html = '<input type="text" name="enclosure" class="input-field '.$args['class'].'" id="'.$args['label_for'].'" autocomplete="off" value="'.addSlashes($enclosure).'" />
			<p class="description">Enclosure are character that use on each word at begining and ending. Default value are double quote (").</p>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	public function line_separator_field_callback($args) {
		$lineSeparator = get_option('pf_exporter_line_separator');

		if (!strlen($lineSeparator)) {
			$lineSeparator = '\n';
		}

		$html = '<!--input type="text" name="line_separator" class="input-field '.$args['class'].'" id="'.$args['label_for'].'" autocomplete="off" value="'.$lineSeparator.'" /-->
			<p calss="description">Default line separator for csv is new line (\n).</p>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	public function header_formatting_field_callback($args) {
		$headerFormatting = get_option('pf_exporter_header_formatting');

		if (!strlen($headerFormatting)) {
			$headerFormatting = '1';
		}

		$html = '<label><input type="radio" name="header_formatting" value="1" '.($headerFormatting == 1 ? 'checked' : '').' />Include export field column header</label><br/>
			<label><input type="radio" name="header_formatting" value="0" '.($headerFormatting == 0 ? 'checked' : '').' />Exclude export field column header</label>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	public function export_per_sheet_field_callback($args) {
		$exportSeparately = get_option('pf_exporter_export_per_sheet');

		$selection = [
			'No',
			'Yes' 
		];

		$html = '<select name="export_per_sheet" class="input-select">';
		foreach ($selection as $k=>$v) {
			$html .= '<option value="'.$k.'" '.($exportSeparately == $k ? 'selected="selected"' : '').'>'.$v.'</option>';
		}
		$html .= '</select>';
		$html .= '<p class="description">If set to yes, each export data would be saved in a multiple file. If set to no, one file will be created with all the export data in there.</p>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	/**
	 * Should be move to profile and allow mutiple select
	 * Not using conflict with filtering on order type
	 */
	public function export_by_status_field_callback($args) {
		$exportByStatus = get_option('pf_exporter_export_by_status');

		$order_statuses = array(
			'wc-pending' => __('Pending payment', PRESSFERENCE_TEXT_DOMAIN),
			'wc-processing' => __('Processing', PRESSFERENCE_TEXT_DOMAIN),
			'wc-on-hold' => __('On hold', PRESSFERENCE_TEXT_DOMAIN),
			'wc-completed' => __('Completed', PRESSFERENCE_TEXT_DOMAIN),
			'wc-cancelled' => __('Cancelled', PRESSFERENCE_TEXT_DOMAIN),
			'wc-refunded' => __('Refunded', PRESSFERENCE_TEXT_DOMAIN),
			'wc-failed' => __('Failed', PRESSFERENCE_TEXT_DOMAIN),
		);

		$html = '<select name="export_by_status" class="input-select" multiple>';
		foreach ($order_statuses as $k=>$v) {
			$html .= '<option value="'.$k.'" '.($exportByStatus == $k ? 'selected="selected"' : '').'>'.$v.'</option>';
		}
		$html .= '</select>';
		$html .= '<p class="description">Export only selected order status. (Work for order status only)</p>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	/**
	 * Currently not in use
	 */
	public function event_based_export_field_callback($args) {
		$eventBasedExport = get_option('pf_exporter_event_based_export');

		$unserialize = [];
		if ($eventBasedExport) {
			$unserialize = maybe_unserialize($eventBasedExport);
		}

		// sales order event based on magento 2
		// sales_order_save_after
		// sales_order_place_after
		// sales_order_invoice_register
		// sales_order_invoice_pay
		// sales_order_shipment_save_after
		// sales_order_creditmemo_save_after
		if ($unserialize) {

		}
	}

	public function email_recipient_field_callback($args) {
		$recipient = get_option('pf_exporter_email_recipient');

		$html = '<input type="text" name="email_recipient" class="input-field '.$args['class'].'" id="'.$args['label_for'].'" value="'.esc_html($recipient).'" />';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	public function email_subject_field_callback($args) {
		$subject = get_option('pf_exporter_email_subject');

		$html = '<input type="text" name="email_subject" class="input-field '.$args['class'].'" id="'.$args['label_for'].'" value="'.esc_html($subject).'" />';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	public function email_content_field_callback($args) {
		$content = get_option('pf_exporter_email_content');

		$html = '<textarea name="email_content" rows="5" cols="60" class="input-field '.$args['class'].'" id="'.$args['label_for'].'">'.esc_html($content).'</textarea>';

		echo wp_kses($html, $this->exporter_handling->get_allowed_html());
	}

	public function footer_scripts() {
		?>
		<script>
			jQuery(document).ready(function($) {
				/**
				 * Document name structure
				 */
				var $documentNameFocused = false,
					$documentNameTags = $('.form-table .available-structure-tags button'),
					$documentName = $('#document_name');

				function changeStructureTagButtonState(button) {
					if (-1 !== $documentName.val().indexOf(button.text().trim())) {
						button.attr('data-label', button.attr('aria-label'));
						button.attr('aria-label', button.attr('data-used'));
						button.attr('aria-pressed', true);
						button.addClass('active');
					} else if (button.attr( 'data-label')) {
						button.attr('aria-label', button.attr('data-label'));
						button.attr('aria-pressed', false);
						button.removeClass('active');
					}
				}

				$documentName.on('focus', function(event) {
					$documentNameFocused = true;
					$(this).off(event);
				});

				$documentNameTags.each(function() {
					changeStructureTagButtonState( $(this));
				});

				// Observe document name field and disable buttons of tags that are already present.
				$documentName.on('change', function() {
					$documentNameTags.each(function() {
						changeStructureTagButtonState($(this));
					});
				});

				$documentNameTags.on('click', function() {
					var documentNameValue = $documentName.val(),
						selectionStart = $documentName[0].selectionStart,
				    	selectionEnd = $documentName[0].selectionEnd,
						textToAppend = $(this).text().trim(),
						newSelectionStart;

					// Remove tag if already part of the input.
					if (-1 !== documentNameValue.indexOf(textToAppend)) {
						documentNameValue = documentNameValue.replace('-' + textToAppend, '');
						
						$documentName.val('-' === documentNameValue ? '' : documentNameValue);

						// Disable button.
						changeStructureTagButtonState($(this));

						return;
					}

					// Input field never had focus, move selection to end of input.
					if (!$documentNameFocused && 0 === selectionStart && 0 === selectionEnd) {
						selectionStart = selectionEnd = documentNameValue.length;
					}

					// Prepend and append slashes if necessary.
					if ('-' !== documentNameValue.substr(0, selectionStart).substr(-1)) {
						textToAppend = '-' + textToAppend;
					}

					// Insert structure tag at the specified position.
					$documentName.val(documentNameValue.substr(0, selectionStart) + textToAppend + documentNameValue.substr(selectionEnd));

					changeStructureTagButtonState($(this));

					// If input had focus give it back with cursor right after appended text.
					if ($documentNameFocused && $documentName[0].setSelectionRange) {
						newSelectionStart = (documentNameValue.substr(0, selectionStart) + textToAppend).length;
						$documentName[0].setSelectionRange(newSelectionStart, newSelectionStart);
						$documentName.focus();
					}
				});
			});
		</script>
		<?php
	}
}