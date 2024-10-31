<?php

if (!defined('ABSPATH')) {
	exit;
}

class Pressference_Admin_Assets {
	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'include_scripts'));
		add_action('admin_enqueue_scripts', array($this, 'include_styles'));
	}

	public function include_scripts() {
		wp_enqueue_script("pressference-popper", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/popper.min.js");
		wp_enqueue_script("pressference-bootstrap", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/bootstrap.min.js");
		wp_enqueue_script("pressference-tipsy-js", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/jquery.tipsy.js");
		wp_enqueue_script("pressference-bs-datetimepicker", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/bootstrap-datetimepicker.min.js", ['jquery', 'moment']);

		// Slick Grid JS
		wp_enqueue_script("pressference-jqueryevt-drag", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/SlickGrid/lib/jquery.event.drag-2.3.0.js");
		wp_enqueue_script("pressference-slickgrid-core", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/SlickGrid/slick.core.js");
		wp_enqueue_script("pressference-slickgrid-checkbox-selectcolumn", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/SlickGrid/plugins/slick.checkboxselectcolumn.js");
		wp_enqueue_script("pressference-slickgrid-cellselectionmodel", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/SlickGrid/plugins/slick.cellselectionmodel.js");
		wp_enqueue_script("pressference-slickgrid-rowselectionmodel", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/SlickGrid/plugins/slick.rowselectionmodel.js");
		wp_enqueue_script("pressference-slickgrid-jsonp", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/SlickGrid/lib/jquery.jsonp-2.4.min.js");
		wp_enqueue_script("pressference-slickgrid-dataview", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/SlickGrid/slick.dataview.js");
		wp_enqueue_script("pressference-slickgrid-columnpicker", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/SlickGrid/controls/slick.columnpicker.js");
		wp_enqueue_script("pressference-slickgrid-editor", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/SlickGrid/slick.editors.js");
		wp_enqueue_script("pressference-slickgrid-formatter", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/SlickGrid/slick.formatters.js");
		wp_enqueue_script("pressference-slickgrid-remotemodel", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/SlickGrid/slick.remotemodel.js");
		wp_enqueue_script("pressference-slickgrid", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/SlickGrid/slick.grid.js");
		
		wp_register_script("pressference-scripts", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/scripts.js");

		$translation_array = [
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'adminUrl' => admin_url('admin.php')
		];
		wp_localize_script('pressference-scripts', 'pressference', $translation_array);

		wp_enqueue_script("postbox");
		wp_enqueue_script("jquery-ui-datepicker");
		wp_enqueue_script("pressference-scripts");
	}

	public function include_styles() {
		wp_enqueue_style("pressference-tipsy-css", plugin_dir_url(PRESSFERENCE_FILE)."assets/css/tipsy.css");
		wp_enqueue_style("pressference-jquery-ui-css", plugin_dir_url(PRESSFERENCE_FILE)."assets/css/jquery-ui.min.css");
		wp_enqueue_style("pressference-bootstrap-datetimepicker", plugin_dir_url(PRESSFERENCE_FILE)."assets/css/bootstrap-datetimepicker.min.css");
		wp_enqueue_style("pressference-slickgrid-columnpicker-css", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/SlickGrid/controls/slick.columnpicker.css");
		wp_enqueue_style("pressference-slickgrid-css", plugin_dir_url(PRESSFERENCE_FILE)."assets/js/SlickGrid/slick.grid.css");

		wp_enqueue_style("pressference-style", plugin_dir_url(PRESSFERENCE_FILE)."assets/css/styles.css");

		if (is_admin()) {
			wp_enqueue_style('pressference-sass-style', plugin_dir_url(PRESSFERENCE_FILE)."assets/css/style.css");
		}
	}
}

return new Pressference_Admin_Assets();