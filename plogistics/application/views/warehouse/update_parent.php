<?php if(isset($warehouse) && is_object($warehouse)) : ?>
	<div class = "row zero_margin" style = "width:600px; height:500px;">
		<div class = "col-xs-12 bold">Please Select the new Parent Warehouse</div>
		
		<form name = "update_parent_wh_form" id = "update_parent_wh_form" method  = "post" onsubmit = "return onChangeParentSubmit(this); ">
			<div class = "col-xs-12">
				<input type = "submit" class = "btn btn-success btn-xs" name = "update_parent_wh_submit" id = "update_parent_wh_submit" value = "Update Parent" />
			</div>	 
			
			<input type = "hidden" name = "wh_id" id = "wh_id" value = "<?php echo $warehouse->id; ?>" />
			
			<div id = "parent_wh_tree" class = "col-xs-6 mg_top_xs" style = "width:300px; height: 400px; overflow:auto;"></div>
			<div id = "parent_wh_tree" class = "col-xs-6 mg_top_xs">
				OR &nbsp;&nbsp;&nbsp;&nbsp; 
				<button name = "no_parent_btn" id = "no_parent_btn" class = "btn btn-xs btn-danger" onclick = "return onNoParentSelect(this);">
					<span class = "glyphicon glyphicon-remove"></span>No Parent
				</button>
			</div>
			
			<input type = "hidden" name = "new_parent_id" id = "new_parent_id" value = "" />
		</form>
	</div>
	
	<script type = "text/javascript">
		function onNoParentSelect(link) {
			$('#new_parent_id').val('0');
			return false;
		}

		$(function() {
			/// show the warehouse tree hierachy for choosing new parent warehouse ///
			$('#parent_wh_tree').fancytree({
				checkbox: false,
				source: {
					cache: false,
					data: {parent_id: 0, level: 1},
					url: "<?php echo base_url('ajax/fetch_child_warehouses_tree'); ?>"	
				},

				lazyLoad: function(event, data) {
					data.result = {
							cache: false, 
							url: "<?php echo base_url('ajax/fetch_child_warehouses_tree'); ?>", 
							data: {parent_id: data.node.key, level: 1} 
					};	
				},

				expand: function(event, data) {
					/// so far do nothing
				},

				collapsed: function(event, data) {
					/// do nothing
				},

				focus: function (event, data) {
					var whId = data.node.key;
					$('#new_parent_id').val(whId);
				}
			});

		});
	</script>
		
<?php else : ?>
	<h3>No Warehouse specified</h3>
<?php endif; ?>	
		

