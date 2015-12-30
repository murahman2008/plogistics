<?php if(isset($warehouseArray) && is_array($warehouseArray) && count($warehouseArray) > 0) : ?>
	<input type = "hidden" name = "hdn_parent_wh_id" id = "hdn_parent_wh_id" value = "<?php echo $parentId; ?>" />
	
	<ul id = "sortable_wh">
		<?php foreach($warehouseArray as $key => $value) : ?>
			<li class = "ui-state-default" id = "<?php echo $value['id'];?>" ><?php echo $value['name']; ?></li>
		<?php endforeach; ?>
	</ul>
	
	<input type = "button" name = "submit_wh_sort" id = "submit_wh_sort" value = "Update Order" onclick = "return onSortChildWarehouseSubmit(this);" />
	
	<script type = "text/javascript">
		$(function() {
			$('#sortable_wh').sortable({
				update: function(event, ui) {
					var order = $(this).sortable('serialize');
				}	
			}).disableSelection();
		});
	</script>
<?php else : ?>
	No Child Warehouse Found...
<?php endif; ?>
