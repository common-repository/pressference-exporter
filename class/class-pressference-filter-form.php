<?php

if (!defined('ABSPATH')) {
	exit;
}

class Pressference_Filter_Form {
	/**
	 * @var object
	 */
	private $wpdb;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var object
	 */
	private $profile;

	/**
	 * Use for request params
	 *
	 * @var array
	 */
	private $_request = [];

	/**
	 * Hold all column for export type
	 *
	 * @var array
	 */
	private $allColumn;

	/**
	 * Hold all profile
	 *
	 * @var array
	 */
	private $allProfile;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->exporter_handling = new Pressference_Exporter_Handling();
		$this->profile = new Pressference_Profile();
		$this->file_extension = new Pressference_File_Extension();
		$this->exporter_type = new Pressference_Exporter_Type();

		if (class_exists('Pressference_Order')) {
			$this->order = new Pressference_Order();
		}
		if (class_exists('Pressference_Product')) {
			$this->product = new Pressference_Product();
		}
		if (class_exists('Pressference_Customer')) {
			$this->customer = new Pressference_Customer();
		}

		$this->set_disabled();
		$this->set_frequency();
		
		add_action('filter_meta_boxes', array($this, 'pf_add_meta_box'));
		add_action('query_filter_form', array($this, 'query_filter_result'));
		add_action('load_all_profile', array($this, 'get_profile'));
		add_action('admin_footer-pf-exporter_page_pfe-filter', array($this, 'footer_scripts'));

		add_action('wp_ajax_create_profile', array($this, 'save_profile'));
		add_action('wp_ajax_update_profile_name', array($this, 'update_profile'));
		add_action('wp_ajax_load_profile', array($this, 'load_profile'));
	}
	
	/**
	 * Add metabox to page filter
	 */
	public function pf_add_meta_box() {
		add_meta_box(
			'pf-filter-metabox',
			esc_html('Filters', PRESSFERENCE_TEXT_DOMAIN),
			array($this, 'filter_meta_box'),
			'pfe-filter',
			'normal',
			'high'
		);
		
		if ($this->exporter_handling->postCheck($_GET) && $pid = $this->exporter_handling->indexCheck('pid', $_GET)) {
			if ($this->profile->is_profile_exist_by_id($pid)) {
				add_meta_box(
					'pf-schedule-export-metabox',
					esc_html('Schedule & Export', PRESSFERENCE_TEXT_DOMAIN),
					array($this, 'export_to_cloud_meta_box'),
					'pfe-filter',
					'normal',
					'high'
				);
				add_meta_box(
					'pf-load-profile-metabox',
					esc_html('Profile', PRESSFERENCE_TEXT_DOMAIN),
					array($this, 'load_profile_meta_box'),
					'pfe-filter',
					'side',
					'high'
				);
			}
		}else{
			add_meta_box(
				'pf-create-profile-metabox',
				esc_html('Profile', PRESSFERENCE_TEXT_DOMAIN),
				array($this, 'create_profile_meta_box'),
				'pfe-filter',
				'side',
				'high'
			);
		}

		add_meta_box(
			'pf-filter-result-metabox',
			esc_html('Results', PRESSFERENCE_TEXT_DOMAIN),
			array($this, 'filter_result_meta_box'),
			'pfe-filter',
			'normal',
			'high'
		);
	}

	/**
	 * Footer script
	 */
	public function footer_scripts() {
		?>
		<script>
			postboxes.add_postbox_toggles(pagenow);
		</script>
		<?php
	}

	/**
	 * template file
	 */
	public function filter_meta_box() {
		include_once(dirname(PRESSFERENCE_FILE).'/class/views/parts/parts-filter-metabox.php');
	}

	/**
	 * template file
	 */
	public function load_profile_meta_box() {
		include_once(dirname(PRESSFERENCE_FILE).'/class/views/parts/parts-profile-metabox.php');
	}

	/**
	 * template file
	 */
	public function create_profile_meta_box() {
		include_once(dirname(PRESSFERENCE_FILE).'/class/views/parts/parts-create-profile-metabox.php');
	}

	/**
	 * template file
	 */
	public function filter_result_meta_box() {
		include_once(dirname(PRESSFERENCE_FILE)."/class/views/parts/parts-filter-result-metabox.php");
	}

	/**
	 * template file
	 */
	public function export_to_cloud_meta_box() {
		include_once(dirname(PRESSFERENCE_FILE)."/class/views/parts/parts-cloud-export-metabox.php");
	}

	/**
	 * Disable export type setter
	 */
	public function set_disabled() {
		$this->disabled = [
			'product_review',
			'product_category',
			'product_attribute',
			'product_tag',
			'coupon'
		];
	}

	/**
	 * Disable export type getter
	 *
	 * @return array
	 */
	public function get_disabled() {
		return $this->disabled;
	}

	/**
	 * Frequency setter
	 */
	public function set_frequency() {
		$this->frequency = [
			'daily' => 'Daily',
			'weekly' => 'Weekly',
			'monthly' => 'Monthly'
		];
	}

	/**
	 * Frequency getter
	 *
	 * @return array
	 */
	public function get_frequency() {
		return $this->frequency;
	}

	/**
	 * Query filter result
	 * Load on form submit or page reload
	 */
	public function query_filter_result() {
		// verify nonce field
		if (isset($_POST['meta-box-filter-nonce'])) {
			if (!wp_verify_nonce($_POST['meta-box-filter-nonce'], 'meta-box-filter')) {
				die(-1);
			}

			$condition = "";

			if ($this->exporter_handling->postCheck($_POST['pf_export_type'])) {
				if ($_POST['pf_export_type'] == 'order') {
					$this->order->setFilter($_POST);
					$this->data = $this->order->getPostOrder();
					$this->allColumn = $this->order->getAllColumn();
				}else if ($_POST['pf_export_type'] == 'product') {
					$this->product->setFilter($_POST);
					$this->data = $this->product->getPostProduct();
					$this->allColumn = $this->product->getAllColumn([
						'product_meta._weight_unit',
						'product_meta._height_unit',
						'product_meta._width_unit',
						'product_meta._length_unit'
					]);
				}else if ($_POST['pf_export_type'] == 'customer') {
					$this->customer->setFilter($_POST);
					$this->data = $this->customer->getPostCustomer();
					$this->allColumn = $this->customer->getAllColumn();
				}

				$this->_request = array_merge($this->_request, $_POST);
			}
		}else{
			if ($pid = $this->exporter_handling->indexCheck('pid', $_GET)) {
				$data = $this->profile->load_profile_by_id($pid, $_GET);

				$this->data = $data['data'];
				$this->allColumn = $data['all_column'];
				$this->_request = $data['request'];
			}
		}
	}

	/**
	 * Save profile
	 *
	 * @return json
	 */
	public function save_profile() {
		$result = $this->profile->save($_POST);
		
		if ($result) {
			if (is_int($result)) {
				wp_send_json_success(array('msg' => 'Profile create successful.'), 200);
			}else if (is_string($result)) {
				wp_send_json_error(array('msg' => $result), 400);
			}
		}else{
			wp_send_json_error(array(
				'msg' => 'Failed to create profile.',
				'error' => $this->wpdb->show_errors()
			), 400);
		}
	}

	/**
	 * Update profile
	 *
	 * @return json
	 */
	public function update_profile() {
		$result = $this->profile->update($_POST);
		
		if ($result) {
			if (is_int($result)) {
				wp_send_json_success(array('msg' => 'Profile update successful.'), 200);
			}else if (is_string($result)) {
				wp_send_json_error(array('msg' => $result), 400);
			}
		}else{
			wp_send_json_error(array(
				'msg' => 'Failed to update profile.',
				'error' => $this->wpdb->last_error
			), 400);
		}
	}

	/**
	 * Load profile
	 *
	 * @return json
	 */
	public function load_profile() {
		if ($this->exporter_handling->postCheck($_POST['profile_name'])) {
			$editUrl = $this->profile->load_profile_by_name(sanitize_text_field($_POST['profile_name']));
			
			$return = [
				'url' => $editUrl
			];
			wp_send_json_success($return, 200);
		}

		wp_send_json_error([
			'msg' => 'Profile not found.'
		], 400);
	}

	/**
	 * Get all profile list
	 */
	public function get_profile() {
		$this->allProfile = $this->profile->get_all_profile();
	}
}

return new Pressference_Filter_Form();