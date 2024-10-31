<div class="wrap">
	<div>
		<h1 class="wp-heading-inline">Filters</h1>
	</div>
	<div class="pressference-wrapper">
		<form method="post" name="filters-form" action="<?php echo esc_url(admin_url('admin.php?page=pfe-filter')); ?>">
			<?php 
				wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
		        wp_nonce_field('meta-box-filter', 'meta-box-filter-nonce', false);
		        do_action('query_filter_form');
		        do_action('filter_meta_boxes', 'pfe-filter');
		    ?>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-1" class="postbox-container">
						<?php
							do_meta_boxes('pfe-filter', 'side', null);
						?>
					</div>
					<div id="postbox-container-2" class="postbox-container">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<?php
								do_meta_boxes('pfe-filter', 'normal', null); ?>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>