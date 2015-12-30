<?php if(isset($result) && is_array($result) && count($result) > 0) : ?>
	<table width = "100%" border = "1" style = "border:solid 1px; font-size:12px; border-color:grey;">
	
	<?php if($criteria['reset'] == 1) : ?>
		<tr class = "bold" style = "background-color:#2093D1; color:white;">
			<td class = "center">Product</td>
			<td class = "center">Barcode</td>
			<td class = "center">Availability</td>
			<td class = "center">Warehouse</td>
			<td class = "center">Action</td>
		</tr>
	<?php endif; ?>	
	
	<?php 
		foreach($result as $key => $value)
		{				
			echo '<tr id = "'.$value['id'].'" class = "pi_row" pi_id = "'.$value['id'].'">';
			echo '<td width = "20%" class = "pd_left_xs">'.$value['product']['name'].'</td>';					
			echo '<td width = "20%" class = "pd_left_xs">'.$value['barcode'].'</td>';					
			
			echo '<td width = "10%" class = "pd_left_xs bold">';
			foreach($availableArray as $available)
			{
				if(trim($available->id) === trim($value['available']))
				{	
					echo '<span>'.$available->name.'</span>';
					break;
				}	
			}	
			echo '</td>';
								
			echo '<td width = "40%" class = "italic pd_left_xs">';
			echo implode("<b style = 'color:red;'>&nbsp;>&nbsp;</b>", array_map(create_function('$a', 'return $a->name;'), $value['warehouse']));
			echo '</td>';
			
			echo '<td width = "10%" class = "pd_left_xs">
					<div class = "row zero_padding zero_margin">
						<div class = "col-xs-1">
							<button class = "btn btn-xs edit_pi_btn" title = "Edit" onclick = "return viewEditProductInstance(this);">
								<span style = "color:red;" title = "Edit" class = "glyphicon glyphicon-pencil"></span
							</button>						
						</div>		
						<div class = "col-xs-1">
							<button class = "btn btn-xs edit_pi_btn" title = "Change Warehouse" onclick = "return changeProductInstanceWarehouse(this);">
								<span style = "color:red;" title = "Change Warehouse" class = "glyphicon glyphicon-home"></span
							</button>						
						</div>		
					</div>			
			</td>';					
			echo '</tr>';
		}
	?>
	
	</table>
		
	<div class = "row" id = "load_more_holder" class = "mg_top_xs center">
		<form name = "product_view_stock_load_more_form" id = "product_view_stock_load_more_form" method = "get" onsubmit = "return onProductViewStockFormSubmit(this);">
		<input type = "hidden" name = "product_id" value = "<?php echo $criteria['product_id']; ?>" />
		<input type = "hidden" name = "warehouse_id" value = "<?php echo $criteria['warehouse_id']; ?>" />
		<input type = "hidden" name = "available" value = "<?php echo $criteria['available']; ?>" />
		<input type = "hidden" name = "page_no" value = "<?php echo ($criteria['page_no'] + 1); ?>" />
		<input type = "hidden" name = "reset" value = "0" />
		<input type = "submit" name = "product_view_stock_load_more_submit" value = "Load More..." class = "btn btn-warning btn-xs" />
	</div>
<?php else : ?>	
	<div class = "row" style = "font-weight:bold; font-size:16px; color:red; ">No Stock Data Found</div>
<?php endif; ?>
