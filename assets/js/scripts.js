jQuery(document).ready(function($) {
	$('.pf-tip').tipsy({
		gravity: $.fn.tipsy.autoNS,
		fade: true,
		html: true
	});
	
	$('#date_from').datepicker({
		dateFormat: 'yy-mm-dd'
	}).bind('change', function(){
		var minValue = $(this).val();
		minValue = $.datepicker.parseDate("yy-mm-dd", minValue);
		minValue.setDate(minValue.getDate()+0);
		$('#date_to').datepicker('option', 'minDate', minValue);
	});
	$('#date_to').datepicker({
		dateFormat: 'yy-mm-dd'
	}).bind('change', function(){
		var maxValue = $(this).val();
		maxValue = $.datepicker.parseDate("yy-mm-dd", maxValue);
		maxValue.setDate(maxValue.getDate()+0);
		$('#date_from').datepicker('option', 'maxDate', maxValue);
	});

	$('.date_time_picker-t').datetimepicker({
		format: 'YYYY-MM-DD HH:mm',
		icons: {
			time: "dashicons dashicons-clock",
			date: "dashicons dashicons-calendar-alt",
			up: "dashicons dashicons-arrow-up-alt2",
			down: "dashicons dashicons-arrow-down-alt2",
			previous: "dashicons dashicons-arrow-left-alt2",
			next: "dashicons dashicons-arrow-right-alt2"
		}
	});
	
	$(window).load(function() {
		$('#cloud_export').trigger('change');

		// if ($('#pf_onchange').val() != "") {
		// 	$('#pf_onchange').trigger('change');
		// }

		if ($('input[name="date_format"]:checked').val() != 'custom') {
			ajaxDateUpdate($('input[name="date_format"]:checked').val());
		}else{
			ajaxDateUpdate($('#pf-custom-date-format').val());
		}
	});

	$('.pf_onchange').change(function(){
		if ($(this).val() == 'order') {
			$('#pf_show_email').css({ 'display': 'flex' });
			$('#pf_show_order_status').css({ 'display': 'flex' });
			$('#pf_show_purchase_date_range').css({ 'display': 'flex' });

			$('#pf_show_product_sku').hide();
			$('#pf_show_product_name').hide();
			$('#pf_show_product_status').hide();
		}else if ($(this).val() == 'customer') {
			$('#pf_show_email').css({ 'display': 'flex' });

			$('#pf_show_product_status').hide();
			$('#pf_show_order_status').hide();
			$('#pf_show_product_sku').hide();
			$('#pf_show_product_name').hide();
			$('#pf_show_purchase_date_range').hide();
		}else if ($(this).val() == 'product') {
			$('#pf_show_product_sku').css({ 'display': 'flex' });
			$('#pf_show_product_name').css({ 'display': 'flex' });
			$('#pf_show_product_status').css({ 'display': 'flex' });

			$('#pf_show_order_status').hide();
			$('#pf_show_email').hide();
			$('#pf_show_purchase_date_range').hide();
		}else{
			$('#pf_show_email').hide();
			$('#pf_show_product_status').hide();
			$('#pf_show_order_status').hide();
			$('#pf_show_product_sku').hide();
			$('#pf_show_product_name').hide();
			$('#pf_show_purchase_date_range').hide();
		}
	});

	$('#cloud_export').on('change', function(){
		if ($(this).val() == '1') {
			$('#destination').show();
		}else{
			$('#destination').hide();
		}
	});

	if ($('#pf_export_type').val() != ''){
		$('.pf_onchange').trigger('change')
	}

	$('input[name="date_format"]').click(function(){
		if ("fifth-date" != $(this).attr("id")) {
			$('#pf-custom-date-format').val($(this).val()).closest('.date-format').find('.example').text($(this).parent('label').children('.date-time-text').text());
		}
	});
	$('#pf-custom-date-format').on('click input', function() {
		$('#fifth-date').prop('checked', true);
	});
	$('#pf-custom-date-format').on('input', function() {
		var format = $(this),
			dateFormat = format.closest('.date-format'),
			example = dateFormat.find('.example'),
			spinner = dateFormat.find('.spinner');
			
		// Debounce the event callback while users are typing.
		clearTimeout($.data(this, 'timer'));
		$(this).data('timer', setTimeout(function() {
			if (format.val()) {
				spinner.addClass('is-active');
				
				ajaxDateUpdate(format.val());
			}
		}, 500));
	});

	function ajaxDateUpdate(format) {
		example = $('.date-format').find('.example'),
		spinner = $('.date-format').find('.spinner');

		$.ajax({
			url: pressference.ajaxUrl,
			type: 'POST',
			data: {
				action: 'custom_date_format',
				date: format
			}, 
			success: function(d, t, jqxhr) {
				if (t == 'success') {
					spinner.removeClass('is-active');
					example.text(d.data);
				}
			}
		});
	}

	function colArrangement(data, col) {
		var temp = [], header = [], count = 1, key = 0;
		col.forEach(function(v, k){
			if (k > 0) {
				header.push(v.name);
			}
		});

		temp[0] = header;
		data.forEach(function(y) {
			col.forEach(function(v, k) {
				if (k > 0) {
					key = v['field'];
					var value = "";

					if (y[key]) {
						if (0 in y[key]) {
							value = escapeHtml(y[key][0]);
						}else{
							value = escapeHtml(y[key]);
						}
					}

					if (count in temp) {
						temp[count][k-1] = value;
					}else{
						temp[count] = { [k-1]: value };
					}
				}
			});
			count++;
		});
		return temp
	}

	window.pfExportFile = function (exportType, filename, data, selectedRow = [], showNotify = false) {
		if (exportType) {
			var selectedData = [],
				selectedRows = selectedRow ? selectedRow : data,
				columnData = data;
			
			if (selectedRows.length) {
				var action = '',
					content = [];
				selectedRows.forEach(function(v){
					selectedData.push(sData[v]);
				});
				selectedData = colArrangement(selectedData, columnData);

				if (filename.indexOf('%type%') != -1) {
					filename = filename.replace('%type%', $('#pf_export_type').val())
				}
				
				if (exportType == "json") {
					selectedRows.forEach(function(v){
						content.push(sData[v]);
					});
				}else {
					content = selectedData;
				}

				$.ajax({
					url: pressference.ajaxUrl,
					type: 'POST',
					data: {
						action: 'export_'+exportType,
						data: content,
						filename: filename
					},
					success: function(d, t, jqxhr) {
						$('#export_btn')[0].classList.remove('loading');
						$('#export_btn').attr('disabled', false);
						if (d.success) {
							alert(d.data.msg)
						}
					}
				})
			}else{
				$('#export_btn')[0].classList.remove('loading');
				$('#export_btn').attr('disabled', false);
				if (showNotify) {
					$('#message_notice').text("No data to export. Please select at least one row.");
					$('#message_dialog').modal('show');
				}
			}
		}else{
			$('#export_btn')[0].classList.remove('loading');
			$('#export_btn').attr('disabled', false);
			if (showNotfy) {
				$('message_notice').text("Please select file type to export.");
				$('message_dialog').modal('show');
			}
		}
	};
});

/**
 * Reload SlickGrid data
 */
window.pfReloadGrid = function (data) {
	var columns = [],
		count = 0,
		rows = [];
		
	if (data && data.length) {
		columns.push(checkboxSelector1.getColumnDefinition());
		
	    if (0 in data) {
	    	var header = Object.keys(data[0]);
	    	header.sort();

	    	header.forEach(function(v, k){
				var item = {
		    		id: k,
		    		name: data[0][v].name,
		    		field: v,
		    		editor: Slick.Editors.Text
		    	};
		    	columns.push(item);
	    	})
	    }

	    data.forEach(function(v, k){
	    	var temp = [];
	    	var header = Object.keys(data[k]);
	    	header.forEach(function(y) {
	    		temp[y] = v[y][0]
	    	});
	    	rows[k] = temp;
	    })

	    grid.invalidateAllRows();
	    grid.setColumns(columns);
	    grid.setData(rows, true);
	    grid.render();
	}
};
