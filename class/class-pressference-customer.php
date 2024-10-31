<?php

if (!defined('ABSPATH')) {
	exit;
}

class Pressference_Customer {
	/**
	 * @var object
	 */
	private $wpdb;

	/**
	 * @var string
	 */
	private $condition;

	/**
	 * Field list in here, will not be display on the frontend
	 */
	private $excludeField;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->exporter_handling = new Pressference_Exporter_Handling();

		$this->excludeField = [
			'user_pass',
			'user_activation_key',
			'user_url',
			'user_login',
			'user_status'
		];
	}

	/**
	 * Get customer
	 *
	 * @return array
	 */
	public function getPostCustomer() {
		$metaKey = $this->getDefaultColumn();

		$data = $this->wpdb->get_results($this->wpdb->prepare(
			'select ID, user_nicename, user_email, user_registered, display_name '
			.'from '.$this->wpdb->prefix.'users '
			.'left join '.$this->wpdb->prefix.'usermeta on ID=user_id '
			.'where meta_value like %s'
			.($this->condition ? $this->condition : ''),
			'%customer%'
		));

		foreach ($data as $v) {
			$userMeta = $this->wpdb->get_results($this->wpdb->prepare(
				'select meta_key, meta_value '.
				'from '.$this->wpdb->prefix.'usermeta '.
				'where user_id=%d and meta_key in ("'.implode('","', $metaKey).'")',
				$v->ID
			));

			foreach ($v as $x=>$y) {
				$v->$x = [
					'name' => $this->exporter_handling->headerTitle($x),
					$y
				];
			}

			foreach ($userMeta as $y) {
				$text = trim($this->exporter_handling->headerTitle($y->meta_key));
				
				$v->{"customer_meta.$y->meta_key"} = [
					'name' => $text,
					$y->meta_value
				];
			}
		}

		foreach ($data as $v) {
			foreach ($v as $x=>$y) {
				if (strpos($x, '.') === false) {
					$v->{"customer.$x"} = $v->$x;
					unset($v->$x);
				}
			}
		}

		return $data;
	}

	/**
	 * Get customer from profile
	 *
	 * @param array $fieldName
	 * @return array
	 */
	public function getPostCustomerLoad($fieldName = array()) {
		$customerMeta = $customer = [];

		$fieldName = $this->getAllColumn();

		foreach ($fieldName as $k=>$v) {
			if (strpos($v, 'customer_meta.') !== false) {
				$customerMeta[] = explode('.', $v)[1];
			}else if (strpos($v, 'customer.') !== false) {
				if (!in_array(explode('.' , $v)[1], $this->excludeField)) {
					$customer[] = explode('.', $v)[1];
				}
			}
		}

		$data = $this->wpdb->get_results($this->wpdb->prepare(
			'select '.implode(',', $customer).' '
			.'from '.$this->wpdb->prefix.'users '
			.'left join '.$this->wpdb->prefix.'usermeta on ID=user_id '
			.'where meta_value like %s'
			.($this->condition ? $this->condition : ''),
			"%customer%"
		));

		foreach ($data as $v) {
			$userMeta = $this->wpdb->get_results($this->wpdb->prepare(
				'select meta_key, meta_value '
				.'from '.$this->wpdb->prefix.'usermeta '
				.'where user_id=%d and meta_key in ("'.implode('","', $customerMeta).'")',
				$v->ID
			));

			foreach ($v as $x=>$y) {
				$v->$x = [
					'name' => $this->exporter_handling->headerTitle($x),
					$y
				];
			}

			foreach ($userMeta as $y) {
				$text = trim($this->exporter_handling->headerTitle($y->meta_key));
				
				$v->{"customer_meta.$y->meta_key"} = [
					'name' => $text,
					$y->meta_value
				];
			}
		}

		foreach ($data as $v) {
			foreach ($v as $x=>$y) {
				if (strpos($x, '.') === false) {
					$v->{"customer.$x"} = $v->$x;
					unset($v->$x);
				}
			}
		}

		return $data;
	}

	/**
	 * Get customer meta key value pair
	 *
	 * @return array
	 */
	private function getDefaultColumn() {
		return [
			'nickname',
			'first_name',
			'last_name',
			'description',
			'last_update',
			'billing_first_name',
			'billing_last_name',
			'billing_company',
			'billing_address_1',
			'billing_city',
			'billing_state',
			'billing_postcode',
			'billing_country',
			'billing_email',
			'billing_phone',
			'shipping_method',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_company',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_city',
			'shipping_state',
			'shipping_postcode',
			'shipping_country',
			'wc_last_active'
		];
	}

	/**
	 * Get customer all column
	 *
	 * @return array
	 */
	public function getAllColumn() {
		$columnArr = [];

		$post = $this->wpdb->get_results(
			'select column_name as fields '
			.'from information_schema.columns '
			.'where table_schema="'.$this->wpdb->dbname.'" and table_name="'.$this->wpdb->prefix.'users"'
		);
		foreach ($post as $k=>$v) {
			if (!in_array($v->fields, $this->excludeField)) {
				$columnArr = array_merge($columnArr, ["customer.".$v->fields]);
			}
		}

		$subquery = 'select group_concat(distinct user_id separator ",") from '.$this->wpdb->prefix.'usermeta where meta_value like %s group by user_id';
		$sampleCustomer = $this->wpdb->get_row($this->wpdb->prepare(
			'select ID, count(*) as t_fields from '.$this->wpdb->prefix.'users '
			.'left join '.$this->wpdb->prefix.'usermeta on ID=user_id '
			.'where user_id in ('.$subquery.') '
			.'group by user_id '
			.'order by t_fields desc '
			.'limit 1',
			'%customer%'
		));

		if ($sampleCustomer) {
			$userMeta = get_user_meta($sampleCustomer->ID);
			if ($userMeta) {
				$userMetaArr = array_keys($userMeta);
				foreach ($userMetaArr as $k=>$v) {
					if (!in_array($v, $this->excludeField)) {
						$userMetaArr[$k] = "customer_meta.".$v;
					}
				}
				$columnArr = array_merge($columnArr, $userMetaArr);
			}
		}

		return $columnArr;
	}

	/**
	 * Customer filter
	 *
	 * @param array $arr
	 */
	public function setFilter($arr = array()) {
		$this->condition = '';

		if ($this->exporter_handling->indexCheck('pf_email', $arr)) {
			$this->condition .= " and user_email like '%".sanitize_email($arr['pf_email'])."%'";
		}
	}
}