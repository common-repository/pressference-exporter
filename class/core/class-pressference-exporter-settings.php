<?php

if (!defined('ABSPATH')) {
	exit;
}

if (class_exists('Pressference_Exporter_Settings', false)) {
	return new Pressference_Exporter_Settings();
}

class Pressference_Exporter_Settings {
	private $wpdb;

	public function __construct() {
		global $wpdb;
		$this->excludeKey = [
			'action',
			'general-setting-form-nonce',
			'csv-setting-form-nonce',
			'email-setting-form-nonce',
			'submit'
		];

		$this->wpdb = $wpdb;
		$this->exporter_handling = new Pressference_Exporter_Handling();

		add_action('admin_post_general_settings', array($this, 'general_settings'));
		add_action('admin_post_csv_settings', array($this, 'csv_settings'));
		add_action('admin_post_email_settings', array($this, 'email_settings'));
		add_action('wp_ajax_custom_date_format', array($this, 'custom_date_format'));
	}

	/**
	 * Store general settings
	 *
	 * @return redirect
	 */
	public function general_settings() {
		if ($this->exporter_handling->postCheck($_POST['general-setting-form-nonce'])) {
			if (!wp_verify_nonce($_POST['general-setting-form-nonce'], 'general-setting-form')) {
				wp_redirect(admin_url('admin.php?page=pf-settings'));
				wp_die();
			}
		}
		
		foreach ($this->excludeKey as $v) {
			if (array_key_exists($v, $_POST)) {
				unset($_POST[$v]);
			}
		}
		
		foreach ($_POST as $k=>$v) {
			update_option('pf_exporter_'.$k, $v);
		}
		
		$goBack = add_query_arg([
			'settings-updated' => 'success'
		], admin_url('admin.php?page=pf-settings'));
		wp_redirect($goBack);
		// wp_die();
	}

	/**
	 * Store csv settings
	 *
	 * @return redirect
	 */
	public function csv_settings() {
		if ($this->exporter_handling->postCheck($_POST['csv-setting-form-nonce'])) {
			if (!wp_verify_nonce($_POST['csv-setting-form-nonce'], 'csv-setting-form')) {
				wp_redirect(admin_url('admin.php?page=pf-csv-settings'));
				wp_die();
			}
		}

		foreach ($this->excludeKey as $v) {
			if (array_key_exists($v, $_POST)) {
				unset($_POST[$v]);
			}
		}

		foreach ($_POST as $k=>$v) {
			if (get_option('pf_exporter_'.$k) != false) {
				update_option('pf_exporter_'.$k, $v);
			}else{
				add_option('pf_exporter_'.$k, $v);
			}
		}

		$goBack = add_query_arg([
			'settings-updated' => 'success'
		], admin_url('admin.php?page=pf-csv-settings'));
		wp_redirect($goBack);
	}

	/**
	 * Store email settings
	 * 
	 * @return 
	 */
	public function email_settings() {
		if ($this->exporter_handling->postCheck($_POST['email-setting-form-nonce'])) {
			if (!wp_verify_nonce($_POST['email-setting-form-nonce'], 'email-setting-form')) {
				wp_redirect(admin_url('admin.php?page=pf-email-settings'));
				wp_die();
			}
		}

		foreach ($this->excludeKey as $v) {
			if (array_key_exists($v, $_POST)) {
				unset($_POST[$v]);
			}
		}

		foreach ($_POST as $k=>$v) {
			if (get_option('pf_exporter_'.$k) != false || get_option('pf_exporter_'.$k) == "") {
				update_option('pf_exporter_'.$k, $v);
			}else{
				add_option('pf_exporter_'.$k, $v);
			}
		}

		$goBack = add_query_arg([
			'settings-updated' => 'success'
		], admin_url('admin.php?page=pf-email-settings'));
		wp_redirect($goBack);
	}

	/**
	 * Date format settings
	 *
	 * @return json
	 */
	public function custom_date_format() {
		if (isset($_POST['date']) && $_POST['date']) {
			wp_send_json_success(date_i18n(wp_unslash($_POST['date'])), 200);
			wp_die();
		}
	}
}