<?php if(isset($productInstance) && is_object($productInstance)) : ?>
	<div class = "row zero_padding zero_margin" style = "min-width:900px; min-height:600px;">
		<form name = "pi_edit_form" id = "pi_edit_form" method = "post" action = "">
			<input type = "hidden" name = "pi_id" id = "pi_id" value = "<?php echo $productInstance->id; ?>" />
			<div class = "row zero_padding zero_margin">
				<div class = "col-xs-12 col-md-2">Barcode</div>
				<div class = "col-xs-12 col-md-10">
					<input type = "text" name = "barcode" id = "barcode" readonly = "readonly" value = "<?php echo $productInstance->barcode; ?>" />
				</div>
			</div>
			<div class = "row zero_padding zero_margin mg_top_xs">
				<div class = "col-xs-12 col-md-2">Product</div>
				<div class = "col-xs-12 col-md-10">
					<select name = "product_id" id = "product_id" style = "width:100%;">
						<option value = "">Please Select A product</option>
						<?php foreach($productArray as $product) : ?>
							<?php 
								$extra = ((trim($product->id) === trim($productInstance->product_id)) ? 'selected = "selected"' : ''); 
							?>
							<option value = "<?php echo $product->id; ?>" <?php echo $extra; ?>><?php echo $product->name; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class = "row zero_padding zero_margin mg_top_xs">
				<div class = "col-xs-12 col-md-2">Availability</div>
				<div class = "col-xs-12 col-md-10">
					<select name = "available" id = "available">
						<?php foreach($availableArray as $available) : ?>
							<?php 
								$extra = ((trim($available->id) === trim($productInstance->available)) ? 'selected = "selected"' : '');
							?>
							<option value = "<?php echo $available->id; ?>" <?php echo $extra; ?>><?php echo $available->name; ?></option>	
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class = "row zero_padding zero_margin">
				<div class = "col-xs-12">
					<input type = "submit" name = "edit_pi_submit" id = "edit_pi_submit" value = "Edit" class = "btn btn-xs btn-success" />
				</div>
			</div>
		</form>
		<div class = "row zero_padding zero_margin">
			<div class = "col-xs-12 center" style = "background-color:#2093D1; color:white;">Add Extra Information</div>
		</div>
		
		<div class = "row zero_padding zero_margin pia_row mg_top_xs" pia_id = 'new' pi_id = "<?php echo $productInstance->id; ?>">
			<div class = "col-xs-12 zero_padding zero_margin">
				<select name = "product_instance_alias_type_id" class = "piat_id_select" style = "width:100%;">
					<option value = "">Select a Alias Type</option>
					<?php foreach($piatArray as $piat) : ?>
						<option value = "<?php echo $piat->id; ?>"><?php echo $piat->name.' - ['.(($piat->allow_multiple == 1) ? 'Multiple Allowed' : 'Multiple Not Allowed').']'; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class = "col-xs-12 col-md-8 zero_padding zero_margin mg_top_xs">
				<input type = "text" name = "alias" value = "" placeholder = "Alias" class = "alias_value_txt" />
			</div>
			<div class = "col-xs-12 col-md-3 zero_padding zero_margin mg_top_xs mg_left_xs">
				<input type = "button" name = "add_pia_btn" id = "add_pia_btn" value = "Add Alias" class = "btn btn-xs btn-info" onclick = "return addUpdateProductInstanceAlias(this);" />
			</div>
		</div>
		
		<?php if(isset($piaArray) && is_array($piaArray) && count($piaArray) > 0) : ?>
			<div class = "row zero_padding zero_margin mg_top_xs">
				<div class = "col-xs-12 center" style = "background-color:#2093D1; color:white;">Edit Existing Extra Information</div>
			</div>
			
			<?php 
				foreach($piaArray as $key => $value)
				{	
					foreach($value['alias_list'] as $k => $v)
					{
						echo '
						<div class = "row zero_padding zero_margin pia_row mg_top_xs" pia_id = "'.$v['id'].'" pi_id = "'.$v['product_instance_id'].'">
							<div class = "col-xs-12 col-md-4 zero_padding zero_margin">
								<select name = "product_instance_alias_type_id" class = "piat_id_select" style = "width:100%;">
						';
						
						foreach($piatArray as $piat)
						{	
							$extra = ((trim($value['id']) === trim($piat->id)) ? 'selected = "selected"' : '');
							echo '<option '.$extra.' value = "'.$piat->id.'">'.$piat->name.'</option>';
						}
						
						echo '
								</select>
							</div>
						';
						
						echo '
							<div class = "col-xs-12 col-md-6 zero_padding zero_margin mg_left_xs">
								<input type = "text" class = "alias_value_txt" name = "alias" value = "'.$v['alias'].'" placeholder = "Alias Value...">
							</div>';
						
						echo '
							<div class = "col-xs-12 col-md-1 zero_padding zero_margin right">
								<span title = "Update Alias" class = "glyphicon glyphicon-pencil btn btn-xs" style = "color:orange;" action = "update" onclick = "addUpdateProductInstanceAlias(this);"></span>
								<span title = "Delete Alias" class = "glyphicon glyphicon-trash btn btn-xs" style = "color:red;" action = "delete" onclick = "deleteProductInstanceAlias(this);"></span>
							</div>';
						
						echo '</div>';
					}
					
					echo '<hr/>';											
				}
			?>
		<?php endif; ?>
		
	</div>
<?php else : ?>
	No Product Instance to Show....
<?php endif; ?>
