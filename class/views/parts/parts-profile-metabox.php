<div class="submitbox">
	<div id="load_profile_actions">
		<input type="hidden" name="pid" id="pid" value="<?php echo $this->exporter_handling->indexCheck('id', $this->_request) ? esc_attr($this->_request['id']) : ''; ?>">
		<?php if ($this->exporter_handling->indexCheck('id', $this->_request)) { ?>
		<div class="row mb-3">
    		<div class="col-lg-12">
    			<label class="form-label">
		        	<span>Profile Name</span>
		    	</label>
    		</div>
    		<div class="col-lg-10">
    			<input type="text" class="widefat" name="profile_name" id="profile_name" value="<?php echo ($this->exporter_handling->postCheck($this->_request['profile_name']) ? $this->_request['profile_name'] : ""); ?>">
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
    		<div class="col-lg-10">
    			<select name="profile_status" id="profile_status">
    				<?php
    					$html = '';
    					foreach ($this->profile->get_status() as $k=>$v) {
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
    		<div class="col-lg-10">
    			<select name="autorun" id="profile_autorun">
    				<option value="0" <?php echo $this->_request['autorun'] ? '' : 'selected="selected"'; ?>>No</option>
    				<option value="1" <?php echo $this->_request['autorun'] ? 'selected="selected"' : ''; ?>>Yes</option>
    			</select>
    		</div>
    	</div>
    	<?php } ?>
	</div>
	<div id="save_profile_actions">
		<div class="float-end">
			<span class="spinner float-left"></span>
			<?php if ($this->exporter_handling->indexCheck('id', $this->_request)) { ?>
				<button type="button" class="button button-primary button-large" name="cancel-profile" id="cancel_profile">Cancel</button>
				<button type="button" class="button button-primary button-large" name="update-profile" id="update_btn">Update</button>
			<?php } ?>
			<!--button type="button" class="button button-primary button-large hide-default" name="save-profile" id="save_profile" data-toggle="modal" data-target="#save_profile_dialog">Save</button-->
		</div>
		<div class="clear"></div>
	</div>
</div>
<script>
	jQuery(function($){
		// window.onload = function() {
		// 	if ($('#pid').val() && $('#profile_name').val()) {
		// 		$('#cancel_profile').show();
		// 		$('#update_btn').show();
		// 	}
		// }

		$('#cancel_profile').on('click', function(){
			window.location.replace(pressference.adminUrl+'?page=pfe-filter');
		});

		$('#profile_name').on('keyup', function(e, v){
			if ($('#profile_name').val().trim()) {
				$('#profile_name').removeClass('is-invalid');
			}else{
				$('#profile_name').addClass('is-invalid');
			}
		});

		$('#update_btn').on('click', function(e){
			if ($('#profile_name').val()) {
				var fieldObj = {};
				var fieldSelection = $('select[name="field_selection[]"]').serializeArray();
				var naming = $('input[name="column_naming[]"]').serializeArray();

				fieldSelection.forEach(function(v, k){
					if (k in naming) {
						fieldObj[v.value] = {
							'name': naming[k].value
						}
					}
				})

				// field position arrangement
				var fieldPosition = grid.getColumns();
				fieldPosition.forEach(function(v, k) {
					if (v.field != 'sel') {
						if (fieldObj.hasOwnProperty(v.field)) {
							fieldObj[v.field]['order'] = k
						}
					}
				});

				$.ajax({
					url: pressference.ajaxUrl,
					type: 'POST',
					data: {
						action: 'update_profile_name',
						profile_id: $('#pid').val(),
						profile_name: $('#profile_name').val(),
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
						cloud_export: $('#cloud_export').val(),
						export_destination: $('#export_to').val(),
						frequency: $('#frequency').val(),
						export_time: $('#start_time').val(),
						export_format: $('#export_format').val(),
						status: $('#profile_status').val(),
						autorun: $('#profile_autorun').val(),
						send_email: $('#send_email').val(),
						fields: fieldObj
					},
					success: function (d, t, jqxhr) {
						alert(d.data.msg);
						location.reload();
					},
					error: function (d, t, jqxhr) {
						alert(d.responseJSON.data.msg);
					}
				});
			}else{
				$('#profile_name').addClass('is-invalid');
			}
		})
	});
</script>