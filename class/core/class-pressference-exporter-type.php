<?php

if (!defined('ABSPATH')) {
	exit;
}

class Pressference_Exporter_Type {
	/**
	 * @var array
	 */
	private $export_type;

	public function __construct() {
		$this->set_type();

		$this->file_path = dirname(PRESSFERENCE_FILE).'/export-file/';
		$this->scriptTimeout = get_option('pf_exporter_script_timeout');
		$this->defaultExeTime = ini_get('max_execution_time');
		$this->includeHeader = get_option('pf_exporter_header_formatting');
		$this->exportSeparately = get_option('pf_exporter_export_per_sheet');

		if (class_exists('Pressference_Exporter_Handling')) {
			$this->exporter_handling = new Pressference_Exporter_Handling();
			$this->exporter_handling->directory_checking($this->file_path);
		}

		add_action('wp_ajax_export_csv', array($this, 'to_csv'));
		add_action('wp_ajax_export_tsv', array($this, 'to_tsv'));
	}

	/**
	 * Set allow export type
	 *
	 * @param array $type
	 */
	public function set_type($type = []) {
		$allows = [
			'order' => __('Orders', PRESSFERENCE_TEXT_DOMAIN),
			'product' => __('Products', PRESSFERENCE_TEXT_DOMAIN),
			'customer' => __('Customers', PRESSFERENCE_TEXT_DOMAIN),
			'product_review' => __('Product Reviews', PRESSFERENCE_TEXT_DOMAIN),
			'product_category' => __('Product Categories', PRESSFERENCE_TEXT_DOMAIN),
			'product_attribute' => __('Product Attributes', PRESSFERENCE_TEXT_DOMAIN),
			'product_tag' => __('Product Tags', PRESSFERENCE_TEXT_DOMAIN),
			'coupon' => __('Coupon', PRESSFERENCE_TEXT_DOMAIN)
		];

		$this->export_type = array_merge($allows, $type);
	}

	/**
	 * Get allow export type
	 *
	 * @return array
	 */
	public function get_type() {
		return $this->export_type;
	}

	/**
	 * Check if export type is valid
	 *
	 * @param String $type
	 *
	 * @return Boolean
	 */
	public function type_check($type) {
		if (array_key_exists($type, $this->export_type)) {
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Export to csv
	 *
	 * @param array $args
	 * @return ajax|array
	 */
	public function to_csv($args = []) {
		ini_set('max_execution_time', $this->scriptTimeout);
		
		if (wp_doing_ajax()) {
			$data = isset($_POST['data']) ? $_POST['data'] : [];
			$filename = sanitize_file_name($_POST['filename']);

			array_walk_recursive($data, 'sanitize_text_field');
		}

		$fieldDelimiter = ($fd = get_option('pf_exporter_field_delimiter')) ? $fd : ',';

		if (!$this->exportSeparately) {
			if (!$this->includeHeader) {
				array_shift($data);
			}

			$newFileName = $this->exporter_handling->file_checking($this->file_path.$filename.'.csv');
			$fHandler = fopen($newFileName, 'w');
			foreach ($data as $v) {
				fputcsv($fHandler, $v, $fieldDelimiter);
			}

			fclose($fHandler);
		}else{
			foreach ($data as $k=>$v) {
				if ($k >= 1) {
					$newRecord = [];
					if ($this->includeHeader) {
						$newRecord = [$data[0], $v];
					}else{
						$newRecord = [$v];
					}

					$newFileName = $this->exporter_handling->file_checking($this->file_path.$filename.'.csv');
					$fHandler = fopen($newFileName, 'w');
					foreach ($newRecord as $y) {
						fputcsv($fHandler, $y, $fieldDelimiter);
					}

					fclose($fHandler);
				}
			}
		}

		ini_set('max_execution_time', $this->defaultExeTime);
		wp_send_json_success(['msg' => 'export successful.'], 200);
	}

	/**
	 * Export to tsv
	 *
	 * @param array $args
	 * @return ajax|array
	 */
	public function to_tsv($args = []) {
		ini_set('max_execution_time', $this->scriptTimeout);

		if (wp_doing_ajax()) {
			$data = isset($_POST['data']) ? $_POST['data'] : [];
			$filename = sanitize_file_name($_POST['filename']);

			array_walk_recursive($data, 'sanitize_text_field');
		}

		$fieldDelimiter = "\t";

		if (!$this->exportSeparately) {
			$newFileName = $this->exporter_handling->file_checking($this->file_path.$filename.'.tsv');
			$fHandler = fopen($newFileName, 'w');
			foreach ($data as $v) {
				fputcsv($fHandler, $v, $fieldDelimiter);
			}

			fclose($fHandler);
		}else{
			foreach ($data as $k=>$v) {
				if ($k >= 1) {
					$newRecord = [];
					if ($this->includeHeader) {
						$newRecord = [$data[0], $v];
					}else{
						$newRecord = [$v];
					}

					$newFileName = $this->exporter_handling->file_checking($this->file_path.$filename.'.tsv');
					$fHandler = fopen($newFileName, 'w');
					foreach ($newRecord as $y) {
						fputcsv($fHandler, $y, $fieldDelimiter);
					}

					fclose($fHandler);
				}
			}
		}

		ini_set('max_execution_time', $this->defaultExeTime);
		wp_send_json_success(['msg' => 'export successful.'], 200);
	}
}