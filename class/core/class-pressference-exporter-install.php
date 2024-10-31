<?php

if (!defined('ABSPATH')) {
	exit;
}

class Pressference_Exporter_Install {
	private $wpdb;
	private $tableName;

	public function __construct() {
		global $wpdb, $pf_version;

		$this->wpdb = $wpdb;
		$installedVersion = get_option('pf_db_version');
		
		if (!$this->wpdb->get_var("SHOW TABLES LIKE '".$this->wpdb->prefix."pf_exporter_profile'")) {
			$this->install($pf_version);
		}else if ($installedVersion != $pf_version) {
			$this->upgrade();
		}
	}

	private function install($version) {
		$charsetCollate = $this->wpdb->get_charset_collate();
		$sql = "CREATE TABLE ".$this->wpdb->prefix."pf_exporter_profile ("
			."id integer NOT NULL AUTO_INCREMENT,"
			."create_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,"
			."update_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,"
			."profile_name varchar(255) NOT NULL,"
			."filter_value text,"
			."field_name text,"
			."status boolean default 1,"
			."autorun boolean default 0,"
			."cloud_export boolean default 0,"
			."export_destination varchar(50),"
			."frequency varchar(100),"
			."export_time datetime,"
			."export_format varchar(50),"
			."send_email boolean default 0,"
			."PRIMARY KEY (id),"
			."UNIQUE (profile_name)"
			.") $charsetCollate;";
		
		require_once(ABSPATH."wp-admin/includes/upgrade.php");
		dbDelta($sql);

		add_option('pf_db_version', $version);
	}

	private function upgrade() {
		// upgrade can backup the data first before proceed
	}
}