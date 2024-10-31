<div class="metabox-content">
	<div class="row">
		<div class="col-lg-6">
			<div class="row mb-2 align-items-center">
				<div class="col-lg-5 col-md-5 col-sm-12">
					<label>Export to Cloud</label>
				</div>
				<div class="col-lg-7 col-md-7 col-sm-12">
					<select name="cloud_export" id="cloud_export" disabled>
						<option value="">---</option>
						<option value="1" <?php echo $this->_request['cloud_export'] ? 'selected="selected"' : ''; ?>>Yes</option>
						<option value="0" <?php echo $this->_request['cloud_export'] ? '' : 'selected="selected"'; ?>>No</option>
					</select>
				</div>
			</div>
			<div class="row mb-2 align-items-center" id="destination">
				<div class="col-lg-5 col-md-5 col-sm-12">
					<label>Destination</label>
				</div>
				<div class="col-lg-7 col-md-7 col-sm-12">
					<select name="export_destination" id="export_to" disabled>
						<option value="">---</option>
						<option value="google" <?php echo $this->_request['export_destination'] == 'google' ? 'selected="selected"' : ''; ?>>Google</option>
						<option value="dropbox" <?php echo $this->_request['export_destination'] == 'dropbox' ? 'selected="selected"' : ''; ?>>Dropbox</option>
					</select>
				</div>
			</div>
			<div class="row mb-2 align-items-center">
				<div class="col-lg-5 col-md-5 col-sm-12">
					<label>Frequency</label>
				</div>
				<div class="col-lg-7 col-md-7 col-sm-12">
					<select name="frequency" id="frequency" disabled>
						<option value="">---</option>
					<?php
						$html = '';
						foreach ($this->get_frequency() as $k=>$v) {
							$html .= '<option value="'.$k.'" '.(isset($this->_request['frequency']) && $this->_request['frequency'] == $k ? 'selected="selected"' : '').'>'.$v.'</option>';
						}

						echo wp_kses($html, $this->exporter_handling->get_allowed_html());
					?>
					</select>
				</div>
			</div>
			<div class="row mb-2 align-items-center">
				<div class="col-lg-5 col-md-5 col-sm-12">
					<label>Start time</label>
				</div>
				<div class="col-lg-7 col-md-7 col-sm-12">
					<div class="input-group date date_time_picker-t">
		                <input type='text' name="start_time" id="start_time" value="<?php echo ($this->exporter_handling->postCheck($this->_request['export_time']) ? $this->_request['export_time'] : ""); ?>" autocomplete="off" />
		                <div class="input-group-append input-group-addon">
		                    <span class="input-group-text">
		                    	<i class="dashicons dashicons-clock"></i>
		                    </span>
		                </div>
		            </div>
				</div>
			</div>
			<div class="row mb-2 align-items-center">
				<div class="col-lg-5 col-md-5 col-sm-12">
					<label>File format</label>
				</div>
				<div class="col-lg-7 col-md-7 col-sm-12">
					<select name="export_format" id="export_format">
						<option value="">---</option>
					<?php
						$html = '';
						foreach ($this->file_extension->get_file_extension() as $k=>$v) {
							$html .= '<option value="'.$k.'" '.(isset($this->_request['export_format']) && $this->_request['export_format'] == $k ? 'selected="selected"' : '').(in_array($k, $this->file_extension->get_disabled()) ? ' disabled' : '').'/> '.$v.
								'</option>';
						}

						echo wp_kses($html, $this->exporter_handling->get_allowed_html());
					?>
					</select>
				</div>
			</div>
			<div class="row mb-2 align-items-center">
				<div class="col-lg-5 col-md-5 col-sm-12">
					<label>Send Email</label>
				</div>
				<div class="col-lg-7 col-md-7 col-sm-12">
					<select name="send_email" id="send_email" disabled>
						<option value="">---</option>
						<option value="0" <?php echo $this->_request['send_email'] ? '' : 'selected="selected"'; ?>>No</option>
						<option value="1" <?php echo $this->_request['send_email'] ? 'selected="selected"' : ''; ?>>Yes</option>
					</select>
				</div>
			</div>
		</div>
		<div class="col-lg-6"></div>
	</div>
</div>