<?php

if (!defined('ABSPATH')) {
	exit;// die('No script kiddies please!');
}

if (class_exists('Pressference_Admin_Menu', false)) {
	return new Pressference_Admin_Menu();
}

class Pressference_Admin_Menu {
	public function __construct() {
		// $maxTime = ini_get('max_execution_time');
		// ini_set('max_execution_time', 300);

		add_action('init', array($this, 'includes'));

		add_action('admin_menu', array($this, 'main_menu'));
		add_action('admin_menu', array($this, 'profile_menu'));
		add_action('admin_menu', array($this, 'upgrade_menu'));
		add_action('admin_menu', array($this, 'settings'));
		add_action('admin_menu', array($this, 'remove_duplicate_menu'), 999);
		add_filter('submenu_file', array($this, 'remove_submenu'));

		add_action('current_screen', array($this, 'active_screen'));
		add_action('check_ajax_referer', array($this, 'active_screen'));
	}

	public function main_menu() {
		add_menu_page(__("", PRESSFERENCE_TEXT_DOMAIN), __("PF Exporter", PRESSFERENCE_TEXT_DOMAIN), "manage_pfe", "pfe-main-menu", null, 'dashicons-feedback', '58');
		
		add_submenu_page("pfe-main-menu", __("Filters", PRESSFERENCE_TEXT_DOMAIN), __("Filters", PRESSFERENCE_TEXT_DOMAIN), "manage_pfe", 'pfe-filter', array($this, "get_filters"));
	}

	public function profile_menu() {
		add_submenu_page("pfe-main-menu", __("Profile", PRESSFERENCE_TEXT_DOMAIN), __("Profile", PRESSFERENCE_TEXT_DOMAIN), "manage_pfe", 'pfe-profile', array($this, 'profile_table_loader'));
	}

	public function upgrade_menu() {
		add_submenu_page("pfe-main-menu", __("Upgrade", PRESSFERENCE_TEXT_DOMAIN), __("<span style='color: red;'>Upgrade</span>", PRESSFERENCE_TEXT_DOMAIN), "manage_pfe", 'pf-upgrade', array($this, 'get_upgrade'));
	}

	public function settings() {
		add_submenu_page("pfe-main-menu", __("Settings", PRESSFERENCE_TEXT_DOMAIN), __("Settings", PRESSFERENCE_TEXT_DOMAIN), "manage_pfe", 'pf-settings', array($this, 'get_settings'));

		add_submenu_page("pfe-main-menu", __("Csv", PRESSFERENCE_TEXT_DOMAIN), __("", PRESSFERENCE_TEXT_DOMAIN), "manage_pfe", "pf-csv-settings", array($this, 'get_csv_setting'));

		add_submenu_page("pfe-main-menu", __("Email", PRESSFERENCE_TEXT_DOMAIN), __("", PRESSFERENCE_TEXT_DOMAIN), "manage_pfe", "pf-email-settings", array($this, 'get_email_setting'));
	}

	public function remove_duplicate_menu() {
		global $submenu;

		if (array_key_exists('pfe-main-menu', $submenu)) {
			foreach ($submenu['pfe-main-menu'] as $k=>$v) {
				if (in_array('pfe-main-menu', $v)) {
					unset($submenu['pfe-main-menu'][$k]);
				}
			}
		}
	}

	public function remove_submenu() {
		global $plugin_page, $submenu;
		
		$hiddenSubmenus = array(
			'pf-csv-settings' => true,
			'pf-email-settings' => true
	    );

	    // Select another submenu item to highlight (optional).
	    if ($plugin_page && isset($hiddenSubmenus[$plugin_page])) {
	        $submenuFile = 'pf-settings';
	    }else{
	    	$submenuFile = $plugin_page;
	    }

	    // Hide the submenu.
	    foreach ($hiddenSubmenus as $k=>$v) {
	        remove_submenu_page('pfe-main-menu', $k);
	    }

	    return $submenuFile;
	}

	public function active_screen() {
		$screen_id = "";

		if (function_exists('get_current_screen')) {
			$screen = get_current_screen();
			$screen_id = isset($screen, $screen->id) ? $screen->id : '';
		}
		
		if (!empty($_REQUEST['screen'])) {
			$screen_id = wc_clean(wp_unslash($_REQUEST['screen']));
		}
		
		switch ($screen_id) {
			case 'pf-exporter_page_pfe-filter':
				break;
			case 'edit-exporter_profile' :
				break;
			default:
				break;
		}

		// Ensure the table handler is only loaded once. Prevents multiple loads if a plugin calls check_ajax_referer many times.
		remove_action('current_screen', array($this, 'active_screen'));
		remove_action('check_ajax_referer', array($this, 'active_screen'));
	}

	public function get_filters() {
		include_once(dirname(PRESSFERENCE_FILE)."/class/views/page-filters.php");
	}

	public function get_upgrade() {
		include_once(dirname(PRESSFERENCE_FILE)."/class/views/page-upgrade.php");
	}

	public function get_settings() {
		include_once(dirname(PRESSFERENCE_FILE)."/class/views/page-settings.php");
	}

	public function get_csv_setting() {
		include_once(dirname(PRESSFERENCE_FILE)."/class/views/page-csv-settings.php");
	}

	public function get_email_setting() {
		include_once(dirname(PRESSFERENCE_FILE)."/class/views/page-email-settings.php");
	}

	public function includes() {
		include_once(dirname(PRESSFERENCE_FILE)."/class/class-pressference-admin-assets.php");
		include_once(dirname(PRESSFERENCE_FILE)."/class/core/class-pressference-exporter-handling.php");
		include_once(dirname(PRESSFERENCE_FILE)."/class/core/class-pressference-exporter-type.php");
		include_once(dirname(PRESSFERENCE_FILE)."/class/core/class-pressference-file-extension.php");
		include_once(dirname(PRESSFERENCE_FILE)."/class/class-pressference-post-types.php");
		include_once(dirname(PRESSFERENCE_FILE)."/class/class-pressference-admin-settings.php");

		include_once(dirname(PRESSFERENCE_FILE)."/class/class-pressference-order.php");
		include_once(dirname(PRESSFERENCE_FILE)."/class/class-pressference-product.php");
		include_once(dirname(PRESSFERENCE_FILE)."/class/class-pressference-customer.php");
		include_once(dirname(PRESSFERENCE_FILE)."/class/class-pressference-profile.php");
		include_once(dirname(PRESSFERENCE_FILE)."/class/class-pressference-filter-form.php");

		include_once(dirname(PRESSFERENCE_FILE)."/class/core/class-pressference-exporter-settings.php");
	}

	public function profile_table_loader() {
		include_once(dirname(PRESSFERENCE_FILE).'/class/tables/class-pressference-admin-table-profile.php');

		$profileTable = new Pressference_Admin_Table_Profile();

		$postTypeObject = get_post_type_object($profileTable->get_post_type());
		$postNewFile = 'admin.php?page=pfe-filter';
		
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo esc_html($postTypeObject->labels->name); ?></h1>
			<?php
				if (current_user_can($postTypeObject->cap->create_posts)) {
					$html = ' <a href="'.esc_url(admin_url($postNewFile)).'" class="page-title-action">'.esc_html($postTypeObject->labels->add_new).'</a>';

					echo wp_kses($html, Pressference_Exporter_Handling::get_allowed_html());
				}
			?>
			<hr class="wp-header-end">
			<?php 
				if (isset($_GET['action']) && isset($_GET['total'])) {
					echo apply_filters('bulk_updated_messages', $_GET['action'], $_GET['total']); 
				}
			?>
			<?php $profileTable->prepare_items(); ?>
			<input type="hidden" name="page" value="" />
			<input type="hidden" name="section" value="issues" />
			<?php $profileTable->views(); ?>
			<form method="post" class="pressference-wrapper">
				<?php
					$doAction = $profileTable->current_action();
					
					if ($doAction) {
						$sendBack = admin_url('admin.php?page=pfe-profile');

						if (!empty($_POST['post'])) {
							$ids = array_map('intval', $_POST['post']);
						}

						apply_filters('handle_bulk_actions-edit-exporter_profile', $sendBack, $doAction, $ids);
						unset($_POST['action']);
					}

					$profileTable->search_box($postTypeObject->labels->search_items, 'search_id');
					$profileTable->display();
				?>
			</form>
		</div>
		<?php
	}
}
