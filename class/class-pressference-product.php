<?php

if (!defined('ABSPATH')) {
	exit;
}

class Pressference_Product {
	/**
	 * @var object
	 */
	private $wpdb;

	/**
	 * @var string
	 */
	private $condition;

	/**
	 * @var string
	 */
	private $metaCondition;

	/**
	 * @var string
	 */
	private $post_type_product = 'product';

	/**
	 * @var string
	 */
	private $post_type_product_variation = 'product_variation';

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->exporter_handling = new Pressference_Exporter_Handling();

		$this->set_product_statuses();
	}

	// public Getter and Setter
	public function set_product_statuses() {
		$this->product_statuses = array(
			'publish' => __('Publish', PRESSFERENCE_TEXT_DOMAIN),
			'draft' => __('Draft', PRESSFERENCE_TEXT_DOMAIN)
		);
	}

	public function get_product_statuses() {
		return $this->product_statuses;
	}

	/**
	 * Get product
	 *
	 * @return array
	 */
	public function getPostProduct() {
		$metaKey = $this->getDefaultColumn();

		$data = $this->wpdb->get_results($this->wpdb->prepare(
			'select ID, post_date, post_content, post_title, post_status, comment_status, post_modified, post_parent '.
			'from '.$this->wpdb->prefix.'posts '.
			'where (post_type=%s or post_type=%s)'.($this->condition ? $this->condition : ''),
			[
				$this->post_type_product,
				$this->post_type_product_variation
			]
		));
		
		$this->measureUnit = $this->wpdb->get_results(
			'select (select option_value from '.$this->wpdb->prefix.'options where option_name="woocommerce_weight_unit") as _weight_unit, '.
			'(select option_value from '.$this->wpdb->prefix.'options where option_name="woocommerce_dimension_unit") as _height_unit, '.
			'(select option_value from '.$this->wpdb->prefix.'options where option_name="woocommerce_dimension_unit") as _width_unit, '.
			'(select option_value from '.$this->wpdb->prefix.'options where option_name="woocommerce_dimension_unit") as _length_unit;'
		);
		
		foreach ($data as $v) {
			if ($v->post_parent != 0) {
				$variationProduct = new WC_Product_Variation($v->ID);
				$v->product_type = $variationProduct->get_type();
				$metaKey = array_merge($metaKey, [
					'price'
				]);
			}else{
				$product = wc_get_product($v->ID);
				$v->product_type = $product->get_type();
			}

			$postMeta = $this->wpdb->get_results($this->wpdb->prepare(
				'select meta_key, meta_value '.
				'from '.$this->wpdb->prefix.'postmeta '.
				'where post_id=%d and meta_key in ("'.implode('","', $metaKey).'") '.
				($this->metaCondition ? $this->metaCondition : ''),
				$v->ID
			));

			foreach ($v as $x=>$y) {
				$v->$x = [
					'name' => $this->exporter_handling->headerTitle($x),
					$y
				];
			}

			foreach ($this->measureUnit[0] as $i=>$j) {
				if ($i == "_weight_unit") {
					$objValue = (object) array("meta_key"=>$i, "meta_value"=>$j);
					array_splice($postMeta, 12, 0, array($objValue));
				}else if ($i == "_length_unit") {
					$objValue = (object) array("meta_key"=>$i, "meta_value"=>$j);
					array_splice($postMeta, 14, 0, array($objValue));
				}else if ($i == "_width_unit") {
					$objValue = (object) array("meta_key"=>$i, "meta_value"=>$j);
					array_splice($postMeta, 16, 0, array($objValue));
				}else if ($i == "_height_unit") {
					$objValue = (object) array("meta_key"=>$i, "meta_value"=>$j);
					array_splice($postMeta, 18, 0, array($objValue));
				}
			}

			foreach ($postMeta as $y) {
				$text = trim($this->exporter_handling->headerTitle($y->meta_key));

				$v->{"product_meta.$y->meta_key"} = [
					'name' => $text,
					$y->meta_value
				];
			}
		}

		foreach ($data as $v) {
			foreach ($v as $x=>$y) {
				if (strpos($x, '.') === false) {
					$v->{"product.$x"} = $v->$x;
					unset($v->$x);
				}
			}
		}

		return $data;
	}

	/**
	 * Get product from profile
	 *
	 * @param array $fieldName
	 * @return array
	 */
	public function getPostProductLoad($fieldName = array()) {
		$productMeta = $product = [];

		$fieldName = $this->getAllColumn();
		
		foreach ($fieldName as $k=>$v) {
			if (strpos($v, 'product_meta.') !== false) {
				$productMeta[] = explode('.', $v)[1];
			}else if (strpos($v, 'product.') !== false) {
				$product[] = explode('.', $v)[1];
			}
		}
		
		$index = array_search('product_type', $product);
		if ($index !== false) {
			unset($product[$index]);
		}
		
		$data = $this->wpdb->get_results($this->wpdb->prepare(
			'select '.implode(', ', $product).' '.
			'from '.$this->wpdb->prefix.'posts '.
			'where (post_type=%s or post_type=%s)'.($this->condition ? $this->condition : ''),
			[
				$this->post_type_product,
				$this->post_type_product_variation
			]
		));

		$this->measureUnit = $this->wpdb->get_results(
			'select (select option_value from '.$this->wpdb->prefix.'options where option_name="woocommerce_weight_unit") as _weight_unit, '.
			'(select option_value from '.$this->wpdb->prefix.'options where option_name="woocommerce_dimension_unit") as _height_unit, '.
			'(select option_value from '.$this->wpdb->prefix.'options where option_name="woocommerce_dimension_unit") as _width_unit, '.
			'(select option_value from '.$this->wpdb->prefix.'options where option_name="woocommerce_dimension_unit") as _length_unit;'
		);

		foreach ($data as $v) {
			if ($v->post_parent != 0) {
				$variationProduct = new WC_Product_Variation($v->ID);
				$v->product_type = $variationProduct->get_type();
				$productMeta = array_merge($productMeta, [
					'_price'
				]);
			}else{
				$product = wc_get_product($v->ID);
				$v->product_type = $product->get_type();
			}

			$postMeta = $this->wpdb->get_results($this->wpdb->prepare(
				'select meta_key, meta_value '.
				'from '.$this->wpdb->prefix.'postmeta '.
				'where post_id=%d and meta_key in ("'.implode('","', $productMeta).'") '.
				($this->metaCondition ? $this->metaCondition : ''),
				$v->ID
			));

			foreach ($v as $x=>$y) {
				$v->$x = [
					'name' => $this->exporter_handling->headerTitle($x),
					$y
				];
			}
			
			foreach ($this->measureUnit[0] as $i=>$j) {
				if ($i == "_weight_unit") {
					$objValue = (object) array("meta_key"=>$i, "meta_value"=>$j);
					array_splice($postMeta, 12, 0, array($objValue));
				}else if ($i == "_length_unit") {
					$objValue = (object) array("meta_key"=>$i, "meta_value"=>$j);
					array_splice($postMeta, 14, 0, array($objValue));
				}else if ($i == "_width_unit") {
					$objValue = (object) array("meta_key"=>$i, "meta_value"=>$j);
					array_splice($postMeta, 16, 0, array($objValue));
				}else if ($i == "_height_unit") {
					$objValue = (object) array("meta_key"=>$i, "meta_value"=>$j);
					array_splice($postMeta, 18, 0, array($objValue));
				}
			}
			
			foreach ($postMeta as $y) {
				$text = trim($this->exporter_handling->headerTitle($y->meta_key));

				$v->{"product_meta.$y->meta_key"} = [
					'name' => $text,
					$y->meta_value
				];
			}
		}

		foreach ($data as $v) {
			foreach ($v as $x=>$y) {
				if (strpos($x, '.') === false) {
					$v->{"product.$x"} = $v->$x;
					unset($v->$x);
				}
			}
		}

		return $data;
	}

	/**
	 * Get product meta key value pair
	 *
	 * @return array
	 */
	private function getDefaultColumn() {
		return [
			'_sku',
			'_regular_price',
			'_sale_price',
			'_sale_price_dates_from',
			'_sale_price_dates_to',
			'_tax_status',
			'_tax_class',
			'_manage_stock',
			'_backorders',
			'_sold_individually',
			'_weight',
			'_length',
			'_width',
			'_height',
			'_purchase_note',
			'_virtual',
			'_downloadable',
			'_stock_status',
			'_wc_average_rating',
			'_wc_rating_count',
			'_wc_review_count',
			'_variation_description'
		];
	}

	/**
	 * Get product all column
	 *
	 * @return array
	 */
	public function getAllColumn($additionalField = array()) {
		$columnArr = [];

		$post = $this->wpdb->get_results(
			'select column_name as fields
			from information_schema.columns
			where table_schema="'.$this->wpdb->dbname.'" and table_name="'.$this->wpdb->prefix.'posts"'
		);
		foreach ($post as $k=>$v) {
			$columnArr = array_merge($columnArr, ["product.".$v->fields]);
		}

		$sampleProduct = $this->wpdb->get_row($this->wpdb->prepare(
			'select ID from '.$this->wpdb->prefix.'posts '.
			'where post_type=%s '.
			'order by ID '.
			'limit 1',
			$this->post_type_product
		));
		if ($sampleProduct) {
			$postmeta = get_post_meta($sampleProduct->ID);
			if ($postmeta) {
				$postmetaArr = array_keys($postmeta);
				foreach ($postmetaArr as $k=>$v) {
					$postmetaArr[$k] = "product_meta.".$v;
				}
				$columnArr = array_merge($columnArr, $postmetaArr, ['product.product_type']);
			}
		}

		return array_merge($columnArr, $additionalField);
	}

	/**
	 * Product filter
	 *
	 * @param array $arr
	 */
	public function setFilter($arr = array()) {
		$this->condition = '';
		$this->metaCondition = '';

		if ($this->exporter_handling->indexCheck('pf_product_name', $arr)) {
			$this->condition .= " and post_title like '%".sanitize_text_field($arr['pf_product_name'])."%'";
		}
		if ($this->exporter_handling->indexCheck('pf_product_sku', $arr)) {
			$this->metaCondition .= " and meta_key='_sku' and meta_value like '%".sanitize_text_field($arr['pf_product_sku'])."%'";
		}
		if ($this->exporter_handling->indexCheck('pf_product_status', $arr)) {
			$this->condition .= " and post_status = '".sanitize_text_field($arr['pf_product_status'])."'";
		}
	}
}