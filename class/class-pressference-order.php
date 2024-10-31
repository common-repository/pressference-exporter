<?php

if (!defined('ABSPATH')) {
	exit;
}

class Pressference_Order {
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
	private $post_type = 'shop_order';

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;

		$this->exporter_handling = new Pressference_Exporter_Handling();
		$this->set_order_statuses();
	}

	// public Getter and Setter
	public function set_order_statuses() {
		$this->order_statuses = array(
			'wc-pending' => __('Pending Payment', PRESSFERENCE_TEXT_DOMAIN),
			'wc-processing' => __('Processing', PRESSFERENCE_TEXT_DOMAIN),
			'wc-on-hold' => __('On Hold', PRESSFERENCE_TEXT_DOMAIN),
			'wc-completed' => __('Completed', PRESSFERENCE_TEXT_DOMAIN),
			'wc-cancelled' => __('Cancelled', PRESSFERENCE_TEXT_DOMAIN),
			'wc-refunded' => __('Refunded', PRESSFERENCE_TEXT_DOMAIN),
			'wc-failed' => __('Failed', PRESSFERENCE_TEXT_DOMAIN),
		);
	}

	public function get_order_statuses() {
		return $this->order_statuses;
	}

	/**
	 * Get order
	 *
	 * @return array
	 */
	public function getPostOrder() {
		$metaKey = $this->getDefaultColumn();

		$data = $this->wpdb->get_results($this->wpdb->prepare(
			'select ID, post_date, post_status
			from '.$this->wpdb->prefix.'posts as a
			where post_type=%s'.($this->condition ? $this->condition : ''),
			$this->post_type
		));
		
		foreach ($data as $v) {
			$postMeta = $this->wpdb->get_results($this->wpdb->prepare(
				'select meta_key, meta_value '.
				'from '.$this->wpdb->prefix.'postmeta '.
				'where post_id=%d and meta_key in ("'.implode('","', $metaKey).'")',
				$v->ID
			));

			foreach ($v as $x=>$y) {
				if ($x == 'post_status') {
					$y = $this->exporter_handling->wcOrderStatus($y);
				}
				$v->$x = [
					'name' => $this->exporter_handling->headerTitle($x),
					$y
				];
			}

			foreach ($postMeta as $i=>$j) {
				if ($j->meta_key == '_customer_user') {
					if ($j->meta_value != 0) {
						$user = get_user_by('ID', $j->meta_value);

						if ($user) {
							$v->{"order_meta._customer_name"} = [
								'name' => 'Customer Name',
								$user->display_name
							];
						}
						continue;
					}else{
						$v->{"order_meta._customer_name"} = [
							'name' => 'Customer Name',
							""
						];
					}
				}
				$text = trim($this->exporter_handling->headerTitle($j->meta_key));
				
				$v->{"order_meta.$j->meta_key"} = [
					'name' => $text,
					$j->meta_value
				];
			}
		}

		foreach ($data as $v) {
			foreach ($v as $x=>$y) {
				if (strpos($x, '.') === false) {
					$v->{"order.$x"} = $v->$x;
					unset($v->$x);
				}
			}
		}

		return $data;
	}

	/**
	 * Get order from profile
	 *
	 * @param array $fieldName
	 * @return array
	 */
	public function getPostOrderLoad($fieldName = array()) {
		$orderMeta = $order = [];

		$fieldName = $this->getAllColumn();

		foreach ($fieldName as $k=>$v) {
			if (strpos($v, 'order_meta.') !== false) {
				$orderMeta[] = explode('.', $v)[1];
			}else if (strpos($v, 'order.') !== false) {
				$order[] = explode('.', $v)[1];
			}
		}

		$data = $this->wpdb->get_results($this->wpdb->prepare(
			'select '.implode(', ', $order).' '.
			'from '.$this->wpdb->prefix.'posts '.
			'where post_type=%s'.($this->condition ? $this->condition : ''),
			$this->post_type
		));

		foreach ($data as $v) {
			$postMeta = $this->wpdb->get_results($this->wpdb->prepare(
				'select meta_key, meta_value '.
				'from '.$this->wpdb->prefix.'postmeta '.
				'where post_id=%d and meta_key in ("'.implode('","', $orderMeta).'")',
				$v->ID
			));

			foreach ($v as $x=>$y) {
				if ($x == 'post_status') {
					$y = $this->exporter_handling->wcOrderStatus($y);
				}
				$v->$x = [
					'name' => $this->exporter_handling->headerTitle($x),
					$y
				];
			}

			foreach ($postMeta as $i=>$j) {
				if ($j->meta_key == '_customer_user') {
					if ($j->meta_value != 0) {
						$user = get_user_by('ID', $j->meta_value);

						if ($user) {
							$v->{"order_meta._customer_name"} = [
								'name' => 'Customer Name',
								$user->display_name
							];
						}
						continue;
					}else{
						$v->{"order_meta._customer_name"} = [
							'name' => 'Customer Name',
							""
						];
					}
				}

				$text = trim($this->exporter_handling->headerTitle($j->meta_key));
				$v->{"order_meta.$j->meta_key"} = [
					'name' => $text,
					$j->meta_value
				];
			}
		}
		
		foreach ($data as $v) {
			foreach ($v as $x=>$y) {
				if (strpos($x, '.') === false) {
					$v->{"order.$x"} = $v->$x;
					unset($v->$x);
				}
			}
		}
		
		return $data;
	}

	/**
	 * Get order meta key value pair
	 *
	 * @return array
	 */
	private function getDefaultColumn() {
		$postCol = [
			'ID', 'post_date', 'post_status'
		];

		$postmetaCol = [
			'_customer_user',
			'_payment_method',
			'_payment_method_title',
			'_customer_ip_address',
			'_billing_first_name',
			'_billing_last_name',
			'_billing_company',
			'_billing_address_1',
			'_billing_address_2',
			'_billing_city',
			'_billing_state',
			'_billing_postcode',
			'_billing_country',
			'_billing_email',
			'_billing_phone',
			'_shipping_first_name',
			'_shipping_last_name',
			'_shipping_company',
			'_shipping_address_1',
			'_shipping_address_2',
			'_shipping_city',
			'_shipping_state',
			'_shipping_postcode',
			'_shipping_country',
			'_order_currency',
			'_cart_discount',
			'_cart_discount_tax',
			'_order_shipping',
			'_order_shipping_tax',
			'_cart_discount',
			'_completed_date',
			'_paid_date'
		];

		return $postmetaCol;
	}

	/**
	 * Get order all column
	 *
	 * @return array
	 */
	public function getAllColumn() {
		$columnArr = [];

		$post = $this->wpdb->get_results(
			'select column_name as fields
			from information_schema.columns
			where table_schema="'.$this->wpdb->dbname.'" and table_name="'.$this->wpdb->prefix.'posts"'
		);
		foreach ($post as $k=>$v) {
			$columnArr = array_merge($columnArr, ["order.".$v->fields]);
		}

		$sampleOrder = $this->wpdb->get_row($this->wpdb->prepare(
			'select ID from '.$this->wpdb->prefix.'posts '.
			'where post_type=%s '.
			'limit 1',
			$this->post_type
		));
		
		if ($sampleOrder) {
			$postmeta = get_post_meta($sampleOrder->ID);
			if ($postmeta) {
				$postmetaArr = array_keys($postmeta);
				foreach ($postmetaArr as $k=>$v) {
					$postmetaArr[$k] = "order_meta.".$v;
				}
				$columnArr = array_merge($columnArr, $postmetaArr, ['order_meta._customer_name']);
			}
		}

		return $columnArr;
	}

	/**
	 * Order filter
	 *
	 * @param array $arr
	 */
	public function setFilter($arr = array()) {
		$this->condition = '';
		if ($this->exporter_handling->indexCheck('pf_order_status', $arr)) {
			$this->condition .= " and post_status='".sanitize_text_field($arr['pf_order_status'])."'";
		}
		if ($this->exporter_handling->indexCheck('pf_date_from', $arr)) {
			$this->condition .= " and post_date >= ".sanitize_text_field($arr['pf_date_from']);
		}
		if ($this->exporter_handling->indexCheck('pf_date_to', $arr)) {
			$this->condition .= " and post_date < date_add(".sanitize_text_field($arr['pf_date_to']).", interval 1 day)";
		}
		if ($this->exporter_handling->indexCheck('pf_email', $arr)) {
			$this->condition .= " and email like '%".sanitize_email($arr['pf_email'])."%'";
		}
	}

	/**
	 * Update order status
	 * 
	 * @param array $data
	 * @param string $type
	 */
	public function updateOrderStatus($data, $type) {
		$updateStatusOnExport = get_option('pf_exporter_update_order_status_exported');

		if ($type !== 'order' || !$updateStatusOnExport) {
			return;
		}

		foreach ($data as $v) {
			if ($v->{'order.post_status'}[0] != $this->exporter_handling->wcOrderStatus($updateStatusOnExport)) {
				$order = [
					'ID' => $v->{'order.ID'}[0],
					'o_post_status' => $v->{'order.post_status'}[0],
					'n_post_status' => $updateStatusOnExport
				];
				file_put_contents("PF_Exporter-log.txt", "\n".print_r($order, true), FILE_APPEND);

				$this->wpdb->update(
					$this->wpdb->prefix."posts",
					[
						'post_status' => $updateStatusOnExport
					],
					['ID' => $v->{'order.ID'}[0]]
				);
			}
		}
	}
}