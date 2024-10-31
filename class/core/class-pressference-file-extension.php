<?php

if (!defined('ABSPATH')) {
	exit;
}

class Pressference_File_Extension {
	/*
	 * Supported file extension
	 */
	private $file;

	private $disabled;

	public function __construct() {
		$this->set_file_extension();

		$this->disabled = [
			'excel',
			'json',
			'xml'
		];
	}

	// getter & setter
	public function set_file_extension($file = []) {
		$allows = [
			"csv" => "CSV",
			"tsv" => "TSV",
			"excel" => "Excel",
			"json" => "JSON",
			"xml" => "XML"
		];

		$this->file = array_merge($allows, $file);
	}

	public function get_file_extension() {
		return $this->file;
	}

	public function get_disabled() {
		return $this->disabled;
	}
}