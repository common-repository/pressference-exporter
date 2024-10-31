<?php

if (!defined('ABSPATH')) {
	exit;
}

class Pressference_Exporter_Handling {
	public function headerTitle($title) {
		$text = str_replace("_", " ", $title);
		return ucwords($text);
	}

	public function wcOrderStatus($status) {
		$wcStatus = 'wc' === substr($status, 0, 2) ? substr($status, 2) : $status;
		return ucfirst(trim(str_replace("-", " ", $wcStatus)));
	}

	/**
	 * Validate for post data
	 *
	 * @return boolean
	 */
	public function postCheck($var) {
		if (isset($var) && $var) {
			return true;
		}
		return false;
	}

	/**
	 * Get filename settings
	 *
	 * @return string
	 */
	public function get_filename($type = '') {
		$filename = get_option('pf_exporter_filename');
		$dateFormat = get_option('pf_exporter_customize_date_format');

		$strReplace = [
			"%date%" => date($dateFormat),
			"%time%" => date('h-m'),
			"%store_name%" => get_option('blogname')
		];

		if ($type) {
			$strReplace = array_merge($strReplace, ["%type%" => $type]);
		}

		foreach ($strReplace as $k=>$v) {
			if (strpos($filename, $k) !== false) {
				$filename = str_replace($k, $v, $filename);
			}
		}

		return $filename;
	}

	/**
	 * Return index value if exist
	 *
	 * @return mixed
	 */
	public function indexCheck($index, $array) {
		return $array && array_key_exists($index, $array) ? $array[$index] : false;
	}

	/**
	 * Convert system text string to human readable text
	 *
	 * @return string
	 */
	public function human_readable($string) {
		if ($string) {
			$text = str_replace(" ", "_", $string);
			return ucfirst($text);
		}

		return "";
	}

	/**
	 * Convert human text string to system readable
	 *
	 * @return string
	 */
	public function system_readable($string) {
		if ($string) {
			$text = str_replace("_", " ", $string);
			return strtolower($text);
		}

		return "";
	}

	/**
	 * Check directory existence
	 *
	 * @param $path string
	 */
	public function directory_checking($path) {
		if ($path) {
			if (file_exists($path)) {
				if (!is_dir($path)) {
					mkdir($path);
				}
			}else{
				mkdir($path);
			}
		}
	}

	/**
	 * Check file existence and append date time
	 *
	 * @param $filename string full path file name
	 * @param $originalFilename default file name
	 */
	public function file_checking($filename, $originalFilename = '') {
		if (file_exists($filename)) {
			if ($originalFilename) {
				$oFilename = $originalFilename;
			}else{
				$oFilename = $filename;
			}

			$detail = pathinfo($oFilename);
			$newName = $detail['dirname'].'/'.$detail['filename'].'-'.date('H-i-s').'.'.$detail['extension'];
			return $this->file_checking($newName, $oFilename);
		}

		return $filename;
	}

	/**
	 * Convert original data to output file data
	 *
	 * @param array data
	 * @return array
	 */
	public function convertData($data) {
		$arrangeData = [];
		$arr = [];

		foreach ($data as $v) {
			$arr[] = array_map(function($a) {
				return $a;
			}, get_object_vars($v));
		}

		$extractValue = [];
		foreach ($arr as $v) {
			$extractValue[] = array_values($v);
		}
		$arr = $extractValue;

		$sortData = [];
		foreach ($arr as $k=>$v) {
			$sortData[$k] = $v;
			usort($sortData[$k], function($a, $b) {
				return $a['order'] > $b['order'];
			});
		}
		$arr = $sortData;
		
		$sampleData = [];
		if (count($arr)) {
			foreach ($arr[0] as $v) {
				$sampleData[] = $v['name'];
			}
		}
		$arrangeData[] = $sampleData;
		
		$content = [];
		if (count($sortData)) {
			foreach($sortData as $v) {
				$content = array_map(function($a) {
					return $a[0];
				}, $v);
				$arrangeData[] = $content;
			}
		}
		
		return $arrangeData;
	}

	/**
	 * Set option
	 */
	public function setOption($key, $value) {
		if (get_option($key) !== false || get_option($key) == '') {
			update_option($key, $value);
		}else {
			add_option($key, $value);
		}
	}

	public static function get_allowed_html() {
		return [
			'input' => [
				'type' => [],
				'name' => [],
				'class' => [],
				'id' => [],
				'autocomplete' => [],
				'value' => [],
				'checked' => []
			],
			'p' => [
				'class' => []
			],
			'ul' => [
				'class' => []
			],
			'li' => [],
			'button' => [
				'type' => [],
				'class' => [],
				'aria-pressed' => [],
				'data-label' => []
			],
			'select' => [
				'name' => [],
				'class' => []
			],
			'option' => [
				'value' => [],
				'selected' => [],
				'disabled' => []
			],
			'label' => [
				'for' => [],
				'class' => []
			],
			'span' => [
				'class' => []
			],
			'code' => [],
			'strong' => [],
			'br' => [],
			'i' => [
				'class' => []
			],
			'div' => [
				'class' => []
			],
			'textarea' => [
				'name' => [],
				'rows' => [],
				'cols' => [],
				'class' => [],
				'id' => []
			],
			'a' => [
				'class' => [],
				'href' => [],
				'aria-label' => []
			],
			'time' => [
				'datetime' => []
			]
		];
	}
}