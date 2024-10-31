<?php

if (!defined('ABSPATH')) {
	exit;
}

class Pressference_Profile {
	/**
	 * @var object
	 */
	private $wpdb;

	/**
	 * @var array
	 */
	private $status;

	/**
	 * Store load profile data
	 *
	 * @var array
	 */
	private $profile;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;

		$this->exporter_handling = new Pressference_Exporter_Handling();

		if (class_exists('Pressference_Order')) {
			$this->order = new Pressference_Order();
		}
		if (class_exists('Pressference_Product')) {
			$this->product = new Pressference_Product();
		}
		if (class_exists('Pressference_Customer')) {
			$this->customer = new Pressference_Customer();
		}

		$this->profile = [
			'data' => [],
			'all_column' => [],
			'request' => []
		];

		$this->set_status();
	}

	/**
	 * Add status to profile
	 *
	 * @param array $status
	 */
	public function set_status($status = []) {
		$stat = [
			"Inactive",
			"Active"
		];
		$this->status = array_merge($stat, $status);
	}

	/**
	 * Get profile status
	 *
	 * @return array
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Check profile using id
	 *
	 * @param $id int
	 * @return boolean
	 */
	public function is_profile_exist_by_id($id) {
		$profile = $this->wpdb->get_row($this->wpdb->prepare(
			'select id from '.$this->wpdb->prefix.'pf_exporter_profile '.
			'where id=%d',
			$id
		));

		if ($profile) {
			return true;
		}

		return false;
	}

	/**
	 * Save profile
	 *
	 * @param array $_post
	 * @param object|boolean
	 */
	public function save($_post) {
		$fields = [
			'create_date' => "",
			'update_date' => "",
			'profile_name' => "",
			'filter_value' => "",
			'status' => 1,
			'autorun' => 1,
			'send_email' => 0
		];

		if ($this->exporter_handling->postCheck($_post['profile_name'])) {
			$fields['profile_name'] = sanitize_text_field($_post['profile_name']);
			$fields['create_date'] = current_time('Y-m-d H:i:s');
			$fields['update_date'] = current_time('Y-m-d H:i:s');
			$fields['status'] = sanitize_text_field($_post['status']);
			$fields['autorun'] = sanitize_text_field($_post['autorun']);
			$fields['send_email'] = sanitize_text_field($_post['send_email']);
			
			if ($this->exporter_handling->postCheck($_post['filter'])) {
				$fields['filter_value'] = maybe_serialize($_post['filter']);
			}

			$rows = $this->wpdb->get_row($this->wpdb->prepare(
				'select id from '.$this->wpdb->prefix.'pf_exporter_profile '.
				'where profile_name=%s',
				$fields['profile_name']
			));
			if ($rows && $rows->id) {
				return 'Duplicate entry of profile name.';
			}else{
				$result = $this->wpdb->insert($this->wpdb->prefix.'pf_exporter_profile', $fields);
				return $result;
			}
		}

		return false;
	}

	/**
	 * Update profile
	 *
	 * @param array $_post
	 * @return object|boolean
	 */
	public function update($_post) {
		$fields = [
			'update_date' => "",
			'profile_name' => "",
			'filter_value' => "",
			'status' => "",
			'cloud_export' => "",
			'export_destination' => "",
			'frequency' => "",
			'export_time' => "",
			'export_format' => "",
			'send_email' => ""
		];

		if ($this->exporter_handling->postCheck($_post['profile_id'])) {
			$fields['update_date'] = current_time('Y-m-d H:i:s');
			$fields['profile_name'] = sanitize_text_field($_post['profile_name']);
			$fields['status'] = sanitize_text_field($_post['status']);
			$fields['cloud_export'] = sanitize_text_field($_post['cloud_export']);
			$fields['export_destination'] = $_post['cloud_export'] ? sanitize_text_field($_post['export_destination']) : '';
			$fields['frequency'] = sanitize_text_field($_post['frequency']);
			$fields['export_time'] = sanitize_text_field($_post['export_time']);
			$fields['export_format'] = sanitize_text_field($_post['export_format']);
			$fields['autorun'] = sanitize_text_field($_post['autorun']);
			$fields['send_email'] = sanitize_text_field($_post['send_email']);

			if ($this->exporter_handling->postCheck($_post['filter'])) {
				$fields['filter_value'] = maybe_serialize($_post['filter']);
			}

			$rows = $this->wpdb->get_row($this->wpdb->prepare(
				'select id from '.$this->wpdb->prefix.'pf_exporter_profile '.
				'where profile_name=%s and id <> %d',
				[
					$fields['profile_name'],
					sanitize_text_field($_post['profile_id'])
				]
			));

			if ($rows && $rows->id) {
				return 'Duplicate entry of profile name.';
			}else{
				$result = $this->wpdb->update(
					$this->wpdb->prefix."pf_exporter_profile",
					$fields,
					array('id' => sanitize_text_field($_post['profile_id']))
				);

				return $result;
			}
		}

		return false;
	}

	/**
	 * Load profile by name
	 *
	 * @param string $name 	profile name
	 * @return string
	 */
	public function load_profile_by_name($name) {
		$condition = "";
		$profile = $this->wpdb->get_row($this->wpdb->prepare(
			'select id, profile_name, filter_value, field_name from '.$this->wpdb->prefix.'pf_exporter_profile '.
			'where profile_name=%s',
			$name
		));
		
		return admin_url('admin.php?page=pfe-filter&pid='.$profile->id.'&action=edit');
	}

	/**
	 * Get all profile
	 *
	 * @return object
	 */
	public function get_all_profile() {
		$profile = $this->wpdb->get_results(
			'select id, profile_name from '.$this->wpdb->prefix.
			'pf_exporter_profile'
		);

		return $profile;
	}

	/**
	 * Load profile by id
	 *
	 * @param integer $id
	 * @param array $_get
	 * @return array
	 */
	public function load_profile_by_id($id, $_get = []) {
		$profile = $this->wpdb->get_row($this->wpdb->prepare(
			'select * from '.$this->wpdb->prefix.'pf_exporter_profile '.
			'where id=%d',
			$id
		));

		if (isset($_get['action'])) {
			switch ($_get['action']) {
				case 'edit':
					$this->getProfile($profile, $_get);
					break;
				case 'delete':
					// handle delete action
					$this->deleteProfile($id);
					break;
			}
		}

		return $this->profile;
	}

	/**
	 * Get profile detail
	 *
	 * @param object $profileData
	 * @param array $_data
	 */
	private function getProfile($profileData, $_data) {
		if ($profileData->filter_value) {
			$profileData->_filter_value = maybe_unserialize($profileData->filter_value);

			if ($this->exporter_handling->postCheck($profileData->_filter_value['pf_export_type'])) {
				$fieldName = $profileData->field_name ? maybe_unserialize($profileData->field_name) : [];

				unset($_data['field_selection'], $_data['column_naming']);
				$latestFilter = array_merge($profileData->_filter_value, $_data);
				
				if ($latestFilter['pf_export_type'] == 'order') {
					$this->order->setFilter($latestFilter);
					$this->profile['data'] = $this->order->getPostOrderLoad($fieldName);
					$this->profile['all_column'] = $this->order->getAllColumn();
				}else if ($latestFilter['pf_export_type'] == 'product') {
					$this->product->setFilter($latestFilter);
					$this->profile['data'] = $this->product->getPostProductLoad($fieldName);
					$this->profile['all_column'] = $this->product->getAllColumn([
						'product_meta._weight_unit',
						'product_meta._height_unit',
						'product_meta._width_unit',
						'product_meta._length_unit'
					]);
				}else if ($latestFilter['pf_export_type'] == 'customer') {
					$this->customer->setFilter($latestFilter);
					$this->profile['data'] = $this->customer->getPostCustomerLoad($fieldName);
					$this->profile['all_column'] = $this->customer->getAllColumn();
				}
				
				$this->profile['request'] = array_merge($this->profile['request'], (array)$profileData);
			}
		}
	}

	/**
	 * Delete profile
	 *
	 * @param integer $id
	 */
	private function deleteProfile($id) {
		$result = $this->wpdb->delete($this->wpdb->prefix.'pf_exporter_profile', array('id' => $id));
		
		if ($result) {
			wp_safe_redirect(admin_url('admin.php?page=pfe-profile&pid='.$id));
		}
	}

	/**
	 * Get profile by custom data
	 *
	 * @param array $data
	 * @return object|boolean
	 */
	public function loadProfile($data) {
		if ($id = $this->exporter_handling->indexCheck('id', $data)) {
			return $this->wpdb->get_row($this->wpdb->prepare(
				'select * from '.$this->wpdb->prefix.'pf_exporter_profile '.
				'where id=%d',
				$id
			));
		}else if ($profileName = $this->exporter_handling->indexCheck('name', $data)) {
			return $this->wpdb->get_row($this->wpdb->prepare(
				'select * from '.$this->wpdb->prefix.'pf_exporter_profile '.
				'where profile_name=%s',
				$profileName
			));
		}

		return false;
	}

	/**
	 * Export profile by id
	 * 
	 * @param int $id
	 */
	public function export($id) {
		$exporter_type = new Pressference_Exporter_Type();
		$email = new PF_Exporter_Email();

		$value = [];
		$profile = $this->load_profile_by_id($id, ['action' => 'edit']);

		$data = [
			'filename' => $this->exporter_handling->get_filename($profile['request']['export_format']),
			'data' => $this->exporter_handling->convertData($profile['data']),
			'profile' => $profile['request']
		];

		switch ($profile['request']['export_format']) {
			case 'csv':
				$value = $exporter_type->to_csv($data);
				break;
			case 'tsv':
				$value = $exporter_type->to_tsv($data);
				break;
		}

		if ($value) {
			$this->order->updateOrderStatus(
				$profile['data'],
				$profile['request']['_filter_value']['pf_export_type']
			);
		}
	}
}