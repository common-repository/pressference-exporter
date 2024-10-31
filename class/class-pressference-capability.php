<?php

if (!defined('ABSPATH')) {
	exit;
}

class Pressference_Capability {
	public function __construct() {
		add_action('admin_init', array($this, 'add_menu_cap'));
	}

	public function add_menu_cap() {
		$role = get_role('administrator');

		$role->add_cap('manage_pfe');
	}
}

return new Pressference_Capability();