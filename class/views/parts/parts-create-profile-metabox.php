<?php
	do_action('load_all_profile');
?>
<div class="metabox-content">
	<div class="row mb-3">
		<div class="col-lg-12">
			<div style="display: inline-flex; width: 100%">
				<select name="profile_name_load" id="profile_name_load" class="widefat" style="margin-right: 5px;">
					<option value="">Select a Profile</option>
					<?php
						if (count($this->allProfile)) {
							$html = '';
							foreach ($this->allProfile as $v) {
								$html .= '<option '.(($this->exporter_handling->indexCheck('profile_name', $this->_request) == $v->profile_name) ? 'selected' : '').'>'.$v->profile_name.'</option>';
							}

							echo wp_kses($html, $this->exporter_handling->get_allowed_html());
						}
					?>
				</select>
				<button type="button" class="load-btn pf-tip" name="load_profile_btn" id="load_profile_btn" title="load">
					<i class="far fa-arrow-alt-circle-right"></i>
				</button>
			</div>
		</div>
	</div>
	<div class="row mb-3 margin-top-20 margin-bottom-20">
		<div class="col-lg-12">
			<span class="line-through-center">OR</span>
		</div>
	</div>
	<div class="row mb-3">
		<div class="col-lg-12">
			<label class="form-label">
	        	<span>Profile Name</span>
	    	</label>
		</div>
		<div class="col-lg-12">
			<input type="text" class="widefat form-control" name="profile_name" id="p_name" value="">
			<div class="invalid-feedback">
	          	Please fill in profile name.
	        </div>
		</div>
	</div>
	<div class="row mb-3">
		<div class="col-lg-12">
			<label class="form-label">
	        	<span>Status</span>
	    	</label>
		</div>
		<div class="col-lg-12">
			<select class="widefat form-control" name="profile_status" id="p_status">
				<?php
					$html = '';
					$reverse = array_reverse($this->profile->get_status(), true);
					foreach ($reverse as $k=>$v) {
						$html .= '<option value="'.$k.'" '.(isset($this->_request['status']) && $this->_request['status'] == $k ? 'selected="selected"' : '').'>'.$v.'</option>';
					}

					echo wp_kses($html, $this->exporter_handling->get_allowed_html());
				?>
			</select>
			<div class="invalid-feedback">
	          	Please select a status.
	        </div>
		</div>
	</div>
	<div class="row mb-3">
		<div class="col-lg-12">
			<label class="form-label">
	        	<span>Auto Run</span>
	    	</label>
		</div>
		<div class="col-lg-12">
			<select class="widefat form-control" name="autorun" id="p_autorun">
				<option value="0">No</option>
				<option value="1">Yes</option>
			</select>
		</div>
	</div>
</div>
<div class="actions mb-footer-ctrl">
	<div class="float-end">
		<button type="button" class="button button-primary button-large" id="create_btn">Create</button>
	</div>
	<div class="clear"></div>
</div>
<script>
	jQuery(function($){
		$('#create_btn').on('click', function(e) {
			if ($('#p_name').val() || $('#p_name').val().trim()) {
				$('#p_name').removeClass('is-invalid');

				var naming = $('input[name="column_naming[]"]').serializeArray();
				
				$.ajax({
					url: pressference.ajaxUrl,
					type: 'POST',
					data: {
						action: 'create_profile',
						profile_name: $('#p_name').val(),
						filter: {
							pf_export_type: $('#pf_export_type').val(),
							pf_product_name: $('#pf_product_name').val(),
							pf_product_sku: $('#pf_product_sku').val(),
							pf_date_from: $('#date_from').val(),
							pf_date_to: $('#date_to').val(),
							pf_order_status: $('#pf_order_status').val(),
							pf_product_status: $('#pf_product_status').val(),
							pf_email: $('#pf_email').val()
						},
						status: $('#p_status').val(),
						autorun: $('#p_autorun').val()
					},
					success: function(d, t, jqxhr) {
						alert(d.data.msg);
						window.location.replace(pressference.adminUrl+'?page=pfe-profile');
					},
					error: function(d, t, jqxhr) {
						alert(d.responseJSON.data.msg);
					}
				});
			}else{
				$('#p_name').addClass('is-invalid');
			}
		});

		$('#load_profile_btn').on('click', function(e){
			if ($('#profile_name_load').val().trim()) {
				$.ajax({
					url: pressference.ajaxUrl,
					type: 'POST',
					data: {
						action: 'load_profile',
						profile_name: $('#profile_name_load').val()
						// 'meta-box-filter-nonce': $('#meta-box-filter-nonce').val()
					},
					success: function (d, t, jqxhr) {
						if (d.data.url) {
							window.location.replace(d.data.url);
						}
					}
				})
			}
		});
	});
</script>