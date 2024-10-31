<?php
	if (!current_user_can('manage_options')) {
		return;
	}

	flush_rewrite_rules();
	if (isset($_GET['settings-updated']) && isset($_GET['page']) && $_GET['settings-updated'] == 'success') {
		add_settings_error('pf_csv_setting', 'settings_updated', __('Csv settings saved.'), 'updated');
	}
?>
<div class="wrap pressference-wrapper">
	<div>
		<h1 class="nav-tab-wrapper">
			<a href="<?php echo admin_url('admin.php?page=pf-settings'); ?>" class="nav-tab">General</a>
			<a href="<?php echo admin_url('admin.php?page=pf-csv-settings'); ?>" class="nav-tab nav-tab-active">CSV</a>
			<a href="<?php echo admin_url('admin.php?page=pf-email-settings'); ?>" class="nav-tab">Email</a>
		</h1>
	</div>
	<?php settings_errors('pf_csv_setting'); ?>
	<div class="wrap">
		<form name="csv-setting-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
			<input type="hidden" name="action" value="csv_settings">
			<?php
				wp_nonce_field('csv-setting-form', 'csv-setting-form-nonce', false);
			
				do_settings_sections('pf-csv-settings');
				
				submit_button('Save Settings');
			?>
		</form>
	</div>
</div>