<?php

if (!defined('ABSPATH')) {
	exit;// die('No script kiddies please!');
}

register_activation_hook(PRESSFERENCE_FILE, array('Pressference_Exporter_Activator', 'activate'));
register_deactivation_hook(PRESSFERENCE_FILE, array('Pressference_Exporter_Deactivator', 'deactivate'));

final class Pressference_Exporter {
	public static function init() {
		$class = __CLASS__;
		new $class;
	}

	public function __construct() {
		global $pf_version;
		
		$pf_version = "1.0.0";
		$this->updateDB($pf_version);
		$this->require_file();
		$this->init_hook();
	}

	public function init_hook() {
		$this->pf_exporter_text_domain();
	}

	public function pf_exporter_text_domain() {
		load_plugin_textdomain('pf-exporter', false, basename(dirname(PRESSFERENCE_FILE)).'/languages');
	}

	public function require_file() {
		// include_once ABSPATH."/wp-includes/class-wp-error.php";
		include_once dirname(PRESSFERENCE_FILE)."/class/core/class-pressference-exporter-install.php";
		include_once dirname(PRESSFERENCE_FILE)."/class/class-pressference-exporter-activator.php";
		include_once dirname(PRESSFERENCE_FILE)."/class/class-pressference-exporter-deactivator.php";
		include_once dirname(PRESSFERENCE_FILE)."/class/class-pressference-capability.php";

		if (is_admin()) {
			include_once dirname(PRESSFERENCE_FILE).'/class/class-pressference-admin-menu.php';
		}
	}

	private function updateDB($version) {
		if (add_option('pf_db_version')) {
			update_option('pf_db_version', $version);
		}
	}
}