<div class = "page-header mg_top_xs">
	<h3>File Uploader</h3>
</div>
<form name = "sell_order_upload_form" id = "sell_order_upload_form" enctype="multipart/form-data" method = "post" action = "<?php echo base_url('admin/upload_sell_order');?>">
	<div class = "row">
		<div class = "col-xs-12 col-md-2">Sell Order File Type</div>
		<div class = "col-xs-12 col-md-10">
			<select name = "upload_type" id = "upload_type" onchange = "return onFileUplaodTypeSelect(this);">
				<option value = "">Please Select A Type...</option>
				<?php foreach($fileUploadTypeArray as $key => $value) : ?>
					<option value = "<?php echo $key; ?>"><?php echo $value; ?></option>
				<?php endforeach; ?>
			</select>
		</div>	
	</div>
	<div class = "row mg_top_xs">
		<input type = "file" class = "upload_type_dependent" name = "sell_order_file" id = "sell_order_file" style = "display:none;" />
	</div>
	<div class = "row mg_top_xs">
		<input type = "submit" class = "upload_type_dependent btn btn-danger" name = "sell_order_file_submit" id = "sell_order_file_submit" value = "Upload" style = "display:none;" />
	</div>
</form>

<script type = "text/javascript">
	$(function() {
		onFileUplaodTypeSelect($('#uploader_type'));
	});
</script>
