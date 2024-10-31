<div class="metabox-content">
	<div class="row">
		<div class="col-lg-6">
			<?php
				if ($this->exporter_handling->indexCheck('id', $this->_request)) {
					$exportType = $this->exporter_handling->indexCheck('pf_export_type', $this->_request['_filter_value']);
					$productName = $this->exporter_handling->indexCheck('pf_product_name', $this->_request['_filter_value']);
					$productSku = $this->exporter_handling->indexCheck('pf_product_sku', $this->_request['_filter_value']);
					$dateFrom = $this->exporter_handling->indexCheck('pf_date_from', $this->_request['_filter_value']);
					$dateTo = $this->exporter_handling->indexCheck('pf_date_to', $this->_request['_filter_value']);
					$orderStatus = $this->exporter_handling->indexCheck('pf_order_status', $this->_request['_filter_value']);
					$productStatus = $this->exporter_handling->indexCheck('pf_product_status', $this->_request['_filter_value']);
					$email = $this->exporter_handling->indexCheck('pf_email', $this->_request['_filter_value']);
				}else{
					$exportType = $this->exporter_handling->indexCheck('pf_export_type', $this->_request);
					$productName = $this->exporter_handling->indexCheck('pf_product_name', $this->_request);
					$productSku = $this->exporter_handling->indexCheck('pf_product_sku', $this->_request);
					$dateFrom = $this->exporter_handling->indexCheck('pf_date_from', $this->_request);
					$dateTo = $this->exporter_handling->indexCheck('pf_date_to', $this->_request);
					$orderStatus = $this->exporter_handling->indexCheck('pf_order_status', $this->_request);
					$productStatus = $this->exporter_handling->indexCheck('pf_product_status', $this->_request);
					$email = $this->exporter_handling->indexCheck('pf_email', $this->_request);
				}
			?>
			<div class="row mb-2 align-items-center">
				<label class="col-lg-5">Export type: </label>
				<div class="col-lg-7">
					<select name="pf_export_type" id="pf_export_type" class="pf_onchange" <?php echo $this->exporter_handling->indexCheck('profile_name', $this->_request) ? 'disabled="disabled"' : ''; ?>>
						<?php
							foreach ($this->exporter_type->get_type() as $k=>$v) {
								echo "<option value='$k' ".(in_array($k, $this->get_disabled()) ? "disabled" : "")." ".(isset($exportType) && $exportType == $k ? "selected='selected'" : "").">$v</option>";
							}
						?>
					</select>
				</div>
			</div>
			<div class="row mb-2 align-items-center hide-default" id="pf_show_product_name">
				<label class="col-lg-5">Product Name: </label>
				<div class="col-lg-7">
					<input type="text" name="pf_product_name" id="pf_product_name" value="<?php echo ($this->exporter_handling->postCheck($productName)) ? esc_attr($productName) : ''; ?>" />
				</div>
			</div>
			<div class="row mb-2 align-items-center hide-default" id="pf_show_product_sku">
				<label class="col-lg-5">Product SKU:</label>
				<div class="col-lg-7">
					<input type="text" name="pf_product_sku" id="pf_product_sku" value="<?php echo ($this->exporter_handling->postCheck($productSku)) ? esc_attr($productSku) : ''; ?>" />
				</div>
			</div>
			<div class="row mb-2 align-items-center hide-default" id="pf_show_purchase_date_range">
				<label class="col-lg-5">Purchase Date: </label>
				<div class="col-lg-3">
					<input type="text" name="pf_date_from" class="pf-datepicker" id="date_from" value="<?php echo ($this->exporter_handling->postCheck($dateFrom)) ? esc_attr($dateFrom) : ''; ?>" />
				</div>
				<label class="col-lg-1">To </label>
				<div class="col-lg-3">
					<input type="text" name="pf_date_to" class="pf-datepicker" id="date_to" value="<?php echo ($this->exporter_handling->postCheck($dateTo)) ? esc_attr($dateTo) : ''; ?>" />
				</div>
			</div>
			<div class="row mb-2 align-items-center hide-default" id="pf_show_order_status">
				<label class="col-lg-5">Status:</label>
				<div class="col-lg-7">
					<select name="pf_order_status" id="pf_order_status">
						<option value="">-- All --</option>
						<?php
							$html = '';
							foreach ($this->order->get_order_statuses() as $k=>$v) {
								$html .= "<option value='$k' ".(isset($orderStatus) && $orderStatus == $k ? "selected='selected'" : "").">$v</option>";
							}

							echo wp_kses($html, $this->exporter_handling->get_allowed_html());
						?>
					</select>
				</div>
			</div>
			<div class="row mb-2 align-items-center hide-default" id="pf_show_product_status">
				<label class="col-lg-5">Status:</label>
				<div class="col-lg-7">
					<select name="pf_product_status" id="pf_product_status">
						<option value="">-- All --</option>
						<?php
							$html = '';
							foreach ($this->product->get_product_statuses() as $k=>$v) {
								$html .= "<option value='$k' ".(isset($productStatus) && $productStatus == $k ? "selected='selected'" : "").">$v</option>";
							}

							echo wp_kses($html, $this->exporter_handling->get_allowed_html());
						?>
					</select>
				</div>
			</div>
			<div class="row mb-2 align-items-center hide-default" id="pf_show_email">
				<label class="col-lg-5">Email: </label>
				<div class="col-lg-7">
					<input type="text" name="pf_email" id="pf_email" value="<?php echo (isset($email)) ? $email : ''; ?>" />
				</div>
			</div>
		</div>
	</div>
</div>
<?php
	if (!$this->exporter_handling->indexCheck('id', $this->_request)) {
?>
<div class="actions mb-footer-ctrl">
	<div class="float-end">
		<button type="submit" class="button button-primary button-large" id="filter-btn">Apply Filters</button>
	</div>
	<div class="clear"></div>
</div>
<?php } ?>
