<form name = "product_add_stock_form" id = "product_add_stock_form" onsubmit = "return onProductAddStockSubmit(this);" 
	method = "post" action = "<?php echo base_url("product/add_stock"); ?>">
	<div class = "row">
		<input type = "hidden" name = "product_id" id = "product_id" value = "<?php echo set_value('product_id'); ?>" />
		<div class = "col-xs-12 col-md-1">Product:</div>
		<div class = "col-xs-12 col-md-5">
			<input type = "text" name = "product_box" id = "product_box" value = "<?php echo set_value('product_box'); ?>" class = "auto_complete" />
		</div>
		<div class = "col-xs-12 col-md-1">Qty:</div>
		<div class = "col-xs-12 col-md-1">
			<input type = "text" name = "qty" id = "qty" value = "<?php echo set_value('qty', ''); ?>" />
		</div>
		<div class = "col-xs-12 col-md-1">Available:</div>
		<div class = "col-xs-12 col-md-1">
			<select name = "available" id = "available">
				<option value = "1" <?php echo set_select('available', "1", true); ?>>Available</option>
				<option value = "0" <?php echo set_select('available', "0"); ?>>Not Available</option>
				<option value = "2" <?php echo set_select('available', "2"); ?>>Booked</option>
			</select>
		</div>
	</div>
	
	<div class = "row mg_top_xs">
		<div class = "col-xs-1">
			Warehouse
		</div>
		<div class = "col-xs-11">
			<input type = "hidden" name = "warehouse_id" id = "warehouse_id" value = "<?php echo set_value('warehouse_id'); ?>" />
			<input type = "text" name = "warehouse_box" id = "warehouse_box" value = "" class = "auto_complete" />
		</div>
	</div>
	
	<div class = "row mg_top_xs">
		<div class = "col-xs-6" id = "wh_tree" style = "width:300px; height:600px; overflow:auto;"></div>
		<div class = "col-xs-6">
			<input type = "submit" class = "btn btn-success btn-sm" name = "product_add_stock_submit" id = "product_add_stock_submit" value = "Add Stock" />
		</div>
	</div>
</form>

<script type = "text/javascript">

	var idCounter = 0;
	var currentLevel = '';
	var idArray = [];
	var whTree = {};

	function checkAndLoadWarehouseTreeWithTarget() {
		if($.trim($('#warehouse_id').val()) !== '')
		{
			$.ajax({
				url: WEB_ROOT + 'ajax/get_parent_warehouses',
				dataType: 'json',
				method: 'post',
				data: {'wh_id': $('#warehouse_id').val()},
				success: function(data) {
					if(data.status)
					{
						$.each(data.data, function(index, item){
							idArray.push(item.id);
						});

						idCounter = idArray.length;
						whTree.reload();
					}	
					return false;
				}	
			});
		}
		return false;	
	}
	
	$(function() {
		$('#product_box').keyup(function() {
			$('#product_id').val('');
			return false;
		});

		$('#product_box').autocomplete({
			source: '<?php echo base_url("ajax/search_product"); ?>',
			minLength: 3,
			open: function(event, ui) {
				
			},
			select: function(event, ui) {
				$('#product_id').val($.trim(ui.item.id));
			}
		});

		$('#wh_tree').fancytree({
			'checkbox': false,
			'source': {
				'url': '<?php echo base_url("ajax/fetch_child_warehouses_tree"); ?>',
				'cache': false,
				'data': {"parent_id": 0, "level": 1}
			},

			lazyLoad: function(event, data) {
				data.result = {
					cache: false, 
					url: "<?php echo base_url('ajax/fetch_child_warehouses_tree'); ?>", 
					data: {parent_id: data.node.key, level: 1} 
				};	
			},

			loadChildren: function(event, data) {
				if(idArray.length) 
				{
					if(isNaN(data.node.key))
						currentLevel = 0;
					else
						currentLevel = (((currentLevel*1) + (1*1))*1);
					
					if(currentLevel < idCounter)
					{	
						//console.debug(currentLevel);
						//console.debug(data);
						
						$.each(data.node.children, function(index, item) {
							if(item.key == idArray[currentLevel])
							{
								currentLevel = (((currentLevel*1) + 1)*1);
								if(currentLevel >= idCounter)
								{	
									//console.debug(item);
									item.setFocus();
									//.done(function() {
									//currentLevel = currentLevel - 1;
									//});
								}	
								else
								{	
									//console.debug(currentLevel);
									item.load().done(function() {
										//console.debug('fasfsdff');
										//currentLevel = currentLevel - 1;	
									});
								}	
							}
						});
						currentLevel = currentLevel - 1;
					}	
				}
			},

			expand: function(event, data) {

			},

			collapsed: function(event, data) {
			},

			focus: function(event, data) {
				//console.debug(data);
				$('#warehouse_box').val('');
				$('#warehouse_id').val(data.node.key);
			}
		});

		whTree = $('#wh_tree').fancytree("getTree");

		$('#warehouse_box').keyup(function() {
			$('#warehouse_id').val('');
			idArray = [];
			idCounter = 0;
			currentLevel = '';
			whTree.reload();
			
			return false;	
		});

		$('#warehouse_box').autocomplete({
			source: '<?php echo base_url("ajax/search_warehouse"); ?>',
			minLength: 3,
			open: function(event, ui) {
				
			},
			select: function(event, ui) {
				var whIdArray = ui.item.id.split(">");

				idArray = whIdArray;
				idCounter = whIdArray.length;
				currentLevel = '';
				whTree.reload();

				$('#warehouse_id').val($.trim(whIdArray[whIdArray.length - 1]));
			}	
		});

		checkAndLoadWarehouseTreeWithTarget();
		
	});
	
</script>

