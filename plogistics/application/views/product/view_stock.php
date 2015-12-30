<form name = "product_view_stock_form" id = "product_view_stock_form" method = "get" 
	onsubmit = "return onProductViewStockFormSubmit(this);" action = "<?php echo base_url('product/view_stock'); ?>">
	<div class = "row zero_padding zero_margin">
		<div class = "col-xs-6 mg_top_xs" id = "wh_tree" style = "width:300px; overflow:auto;"><b>Warehouse Tree</b></div>
		<div class = "col-xs-6 mg_top_xs">
			<div class = "col-xs-2 mg_top_xs">Product</div>
			<div class = "col-xs-10 mg_top_xs">
				<input type = "hidden" name = "product_id" id = "product_id" value = "<?php echo set_value('product_id'); ?>" />
				<input type = "text" name = "product_box" id = "product_box" value = "<?php echo set_value('product_box'); ?>" class = "auto_complete" />
			</div>
			
			<div class = "col-xs-2 mg_top_xs">Warehouse</div>
			<div class = "col-xs-10 mg_top_xs">
				<input type = "hidden" name = "warehouse_id" id = "warehouse_id" value = "<?php echo set_value('warehouse_id'); ?>" />
				<input type = "text" name = "warehouse_box" id = "warehouse_box" value = "<?php echo set_value('warehouse_box'); ?>" class = "auto_complete" />
			</div>
			<div class = "col-xs-2 mg_top_xs">Status</div>
			<div class = "col-xs-10 mg_top_xs">
				<select name = "available" id = "available">
					<option value = "">Please Select...</option>
					<?php foreach($availableArray as $available) : ?>
						<option value = "<?php echo $available->id; ?>"><?php echo $available->name; ?></option>
					<?php endforeach;?>
				</select>
			</div>
			<div class = "col-xs-12">
				<input type = "submit" name = "view_stock_submit" id = "view_stock_submit" value = "View Stock" class = "btn btn-xs btn-success "/>
			</div>
		</div>	
	</div>
</form>
<div id = "product_view_stock_result_holder"></div>

<script type = "text/javascript">
	var whTree = {};
	var idArray = [];
	var currentLevel = '';
	var idCounter = 0;

	function loadProductAutoComplete() {

		$('#product_box').autocomplete({
			source: '<?php echo base_url("ajax/search_product"); ?>',
			minLength: 3,
			open: function(event, ui) {
			},
			select: function(event, ui) {
				$('#product_id').val(ui.item.id);
			}
		});

		$('#product_box').keyup(function() {
			$('#product_id').val('');
			return false;
		});
		
		return false;		
	}

	function loadWarehouseAutoComplete() {
		$('#warehouse_box').autocomplete({
			source: '<?php echo base_url("ajax/search_warehouse"); ?>',
			minLength: 3,
			open: function(event, ui) {
			},
			select: function(event, ui) {
				idArray = ui.item.id.split(">");
				idCounter = idArray.length;
				whTree.reload();
				$('#warehouse_id').val(idArray[idArray.length - 1]);
			}
		});

		$('#warehouse_box').keyup(function() {
			$('#warehouse_id').val('');
			return false;
		});
		
		return false;
	}
	
	$(function() {
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
						//currentLevel = (((currentLevel*1) + (1*1))*1);
					
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

		whTree = $('#wh_tree').fancytree("getTree");	/// initialize the fancytree object so that it can be used globally ///

		loadProductAutoComplete();
		loadWarehouseAutoComplete();

		<?php if(isset($submitForm) && $submitForm == true) : ?>
			
			<?php if($product !== false) : ?>
				$('#product_id').val(<?php echo "'".$product->id."'"; ?>);
				$('#product_box').val(<?php echo "'".$product->name."'"; ?>);
			<?php endif; ?>

			<?php if($warehouse !== false && is_array($warehouse) && count($warehouse) > 0) : ?>
				<?php 
					$wh = $warehouse[count($warehouse) - 1];
				?>
				$('#warehouse_id').val(<?php echo $wh->id; ?>);
				$('#warehouse_box').val(<?php echo implode(">", array_map(create_function('$a', 'return $a->name;'), $warehouse)); ?>);
			<?php endif; ?>

			$('#view_stock_submit').click();
		
		<?php endif; ?>
		
	});			
</script>