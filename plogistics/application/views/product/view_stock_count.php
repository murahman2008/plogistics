<form name = "view_stock_form" id = "view_stock_form" method = "post" action = "<?php echo base_url('product/view_stock_count'); ?>">
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
			<div class = "col-xs-12">
				<input type = "submit" name = "view_stock_count_submit" id = "view_stock_count_submit" value = "View Stock" class = "btn btn-xs btn-success "/>
			</div>
		</div>	
	</div>
</form>

<?php if(isset($result)) : ?>
	<?php if(is_array($result)) : ?>
		<?php if(count($result) > 0) : ?>
			<?php 
				echo '<table width = "100%" border = "1" style = "font-size:14px;">
						<tr class = "bold">
							<td width = "40%" class = "pd_left_xs">Warehouse</td>
							<td class = "pd_left_xs">Product Count</td>
						</tr>
				';
				
				foreach($result as $key => $value)
				{	
					echo '<tr>';
					echo '<td class = "pd_left_xs">'.implode(">", array_map(create_function('$a', 'return $a->name;'), $value['wh_data'])).'</td>';
					echo '<td class = "pd_left_xs">';
					if(count($value['stock_count']) <= 0)
						echo '<span style = "color:red;">No Stock Found!</span>';
					else
					{	
						foreach($value['stock_count'] as $k => $v)
						{
							$availableText = 'Unknown Status';
							
							if($k == PRODUCT_AVAILABLE)
								$availableText = "Available";
							else if($k == PRODUCT_UNAVAILABLE)
								$availableText = "Un-Available";
							else if($k == PRODUCT_BOOKED)
								$availableText = "Booked";
							
							$urlParam = array();
							$urlParam[] = "wh_id=".$key;
							$urlParam[] = "available=".$k;
							if(isset($searchProduct) && is_object($searchProduct))
								$urlParam[] = "product_id=".$searchProduct->id;
							
							echo '<div class = "row zero_padding mg_top_xs">
									<div class = "col-xs-6 italic" style = "color:#2093D1; font-size:12px;">'.$availableText.'</div>	
									<div class = "col-xs-6" style = "font-size:12px;"><a href = "'.base_url("product/view_stock?".implode("&", $urlParam)).'">'.$v.'</a></div>	
								  </div>
							';
						}	
					}
					echo '</td>';
					echo '</tr>';
				}
				
				echo '</table>';
			?>			
					
		<?php else : ?>
			<h3>No Stock Record Found...</h3>
		<?php endif;?>
	<?php else : ?>
		<h3>Invalid Result Found. Cannot Display. Please refresh the page and try again...</h3>
	<?php endif; ?>
<?php endif; ?>

<script type = "text/javascript">
	var whTree = {};
	var idArray = [];
	var idCounter = 0;
	var currentLevel = '';

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

		whTree = $('#wh_tree').fancytree("getTree");	/// initialize the fancytree object so that it can be used globally ///

		loadProductAutoComplete();
		loadWarehouseAutoComplete();

	});
</script>

