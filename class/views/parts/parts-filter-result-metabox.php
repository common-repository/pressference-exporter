<div id="pf_filter_result" class="metabox-content" style="width:100%; height:500px;"></div>
<div class="actions mb-footer-ctrl">
	<div class="float-end">
		<div style="display: inline-block; margin: 0 10px 0 0">
			<?php
				$html = '';
				if (!$this->exporter_handling->indexCheck('id', $this->_request)) {
					$html .= '<select name="export_file_type">';
					foreach ($this->file_extension->get_file_extension() as $k=>$v) {
						$html .= '<option value="'.$k.'" '.(in_array($k, $this->file_extension->get_disabled()) ? 'disabled' : '').' /> '.$v.'</option>';
					}
					$html .= '</select>';

					echo wp_kses($html, $this->exporter_handling->get_allowed_html());
				}
			?>
		</div>
		<button type="button" class="button button-primary button-large" id="export_btn"><i class="fa fa-spinner fa-spin"></i> Export</button>
	</div>
	<div class="clear"></div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="message_dialog">
  	<div class="modal-dialog" role="document">
	    <div class="modal-content">
		    <div class="modal-header">
		        <h5 class="modal-title">Alert!!!</h5>
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		        	<span aria-hidden="true">&times;</span>
		        </button>
		    </div>
		    <div class="modal-body">
		        <p id="message_notice"></p>
		    </div>
		    <div class="modal-footer">
		        <button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
		    </div>
	    </div>
  	</div>
</div>

<script>
	var grid,
		dataView = new Slick.Data.DataView(),
		checkboxSelector1,
		sData = []

	jQuery(function($){
		var columnpicker,
			loadingIndicator = null,
			data1 = [],
			loader = new Slick.Data.RemoteModel(),
			columns = [],
			options = {
				editable: false,
				autoEdit: false,
				enableCellNavigation: true,
		    	asyncEditorLoading: false
			},
			filename = <?php echo wp_json_encode($this->exporter_handling->get_filename()); ?>;
		sData = <?php echo $this->data ? wp_json_encode($this->data) : wp_json_encode([]); ?>;

	    checkboxSelector1 = new Slick.CheckboxSelectColumn({
			cssClass: "slick-cell-checkboxsel"
	    });
	    columns.push(checkboxSelector1.getColumnDefinition());
	    
	    var sorted = sData.sort(function(a, b) {
	    	return Object.keys(b).length - Object.keys(a).length;
	    });

	    if (0 in sorted) {
	    	var header = Object.keys(sorted[0]);
	    	
	    	header.forEach(function(v, k) {
    			var item = {
		    		id: k,
		    		name: sorted[0][v].name,
		    		field: v,
		    		editor: Slick.Editors.Text
		    	}
				if (sorted[0][v].order) {
					columns[sorted[0][v].order] = item;
				}else{
					columns.push(item);
				}
	    	});

	    	columns = columns.filter(e => e != null);
	    }

	    sorted.forEach(function(v, k) {
	    	var temp = []
	    	var header = Object.keys(sorted[k]);
	    	header.forEach(function(y) {
	    		temp[y] = v[y][0]
	    	})
	    	data1[k] = temp;
	    })
	    
		grid = new Slick.Grid('#pf_filter_result', data1, columns, options);
		grid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: false}));
    	grid.registerPlugin(checkboxSelector1);

		columnpicker = new Slick.Controls.ColumnPicker(columns, grid, options);
		
		$('#export_btn').on('click', function(e){
			var selectedFileType = '';
			$(this).addClass('loading');
			$(this).prop('disabled', true);

			if ($('#pid').val()) {
				selectedFileType = $('#export_format').val()
			}else{
				selectedFileType = $('select[name="export_file_type"]').val();
			}

			window.pfExportFile(selectedFileType, filename, grid.getColumns(), grid.getSelectedRows(), true);
		})

		exportCsv = function(data) {
			var dataString = "";
			var csvContent = "data:text/csv;charset=utf-8,";
			data.forEach(function(v){
				if (typeof v == "object") {
					v = Object.values(v);
				}
				dataString = v.join(",");
				csvContent += dataString+"\n";
			});

			var encodedUri = encodeURI(csvContent);
			var downloadLink = document.createElement("a");
			downloadLink.href = encodedUri;

			if (filename.indexOf('%type%') != -1) {
				filename = filename.replace('%type%', $('#ic_export_type').val())
			}
			downloadLink.download = filename+".csv";
			
			document.body.appendChild(downloadLink);
			downloadLink.click();
			document.body.removeChild(downloadLink);
		}

		escapeHtml = function(text) {
			return text.replace('&#038;', '&');
		}
	});
</script>

<style>
	.modal-backdrop {
		position: fixed;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		z-index: 1040;
		background-color: #000;
	}
	.fade {
		transition: opacity .15s linear;
	}
	.modal-backdrop.show{
		opacity: .5;
	}
	.modal-open > #wpwrap > #adminmenumain > #adminmenuwrap {
		z-index: 1030;
	}
	.modal-open > #wpwrap > #wpcontent > #wpadminbar {
		z-index: 1035;
	}
</style>