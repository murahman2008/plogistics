<div >
<?php if(isset($parent) && is_object($parent)) : ?>
	<form name = "add_warehouse_form" id = "add_warehouse_form" method = "post" onsubmit = "return onAddChildWarehouseSubmit(this);">
		<div class = "row zero_padding mg_top_xs">
			<div class = "col-xs-12 col-md-2">Name:</div>
			<div class = "col-xs-12 col-md-10">
				<input type = "text" class = "basic_check" basic_check_criteria = "required" name = "name" id = "name" value = "" placeholder = "Warehouse Name..." />
			</div>
		</div>
		<div class = "row zero_padding mg_top_xs">
			<div class = "col-xs-12 col-md-2">Code:</div>
			<div class = "col-xs-12 col-md-10">
				<input type = "text" class = "basic_check" basic_check_criteria = "required" name = "code" id = "code" value = "" placeholder = "Warehouse Code..." />
			</div>
		</div>
		<div class = "row zero_padding mg_top_xs">
			<div class = "col-xs-12">Description:</div>
			<div class = "col-xs-12">
				<textarea name = "description" id = "description" class = "basic_check" style = "width:100%;" basic_check_criteria = "required"></textarea>
			</div>
		</div>
		<div class = "row zero_padding mg_top_xs">
			<div class = "col-xs-12 col-md-2">Parent:</div>
			<div class = "col-xs-12 col-md-10">
				<?php 
					if($parent->id == 0)
						echo "No Parent --- ROOT WAREHOUSE";
					else
						echo $parent->name;
				?>
				<input type = "hidden" name = "parent_id" id = "parent_id" value = "<?php echo $parent->id; ?>" />
			</div>
		</div>
		
		<?php if(isset($warehouseTypeArray) && count($warehouseTypeArray) > 0) : ?>
			<div class = "row zero_padding mg_top_xs">
				<div class = "col-xs-12 col-md-2">Type:</div>
				<div class = "col-xs-12 col-md-10">
					<select name = "warehouse_type_id" id = "warehouse_type_id" class = "basic_check" basic_check_criteria = "required|numeric">
						<option value = "">Please Select a Type</option>
						<?php foreach($warehouseTypeArray as $whType) : ?>
							<option value = "<?php echo $whType->id; ?>"><?php echo $whType->name; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		<?php else : ?>
			<input type = "hidden" name = "warehouse_type_id" id = "warehouse_type_id" value = "0" />	
		<?php endif; ?>
		
		<div class = "row zero_padding mg_top_xs">
			<div class = "col-xs-12">Address:</div>
			<div class = "col-xs-1">Line 1:</div>
			<div class = "col-xs-11">
				<input type = "text" name = "address_line_1" id = "address_line_1" value = "" placeholder = "Address Line 1" />
			</div>
			<div class = "col-xs-1">Line 2:</div>
			<div class = "col-xs-11">
				<input type = "text" name = "address_line_2" id = "address_line_2" value = "" placeholder = "Address Line 2" />
			</div>
			<div class = "col-xs-1">Suburb</div>
			<div class = "col-xs-5">
				<input type = "text" name = "address_suburb" id = ""address_suburb"" value = "" placeholder = "Address Suburb" />
			</div>
			<div class = "col-xs-1">Postcode</div>
			<div class = "col-xs-5">
				<input type = "text" name = "address_postcode" id = "address_postcode" value = "" placeholder = "Address Postcode" />
			</div>
			<div class = "col-xs-1">State</div>
			<div class = "col-xs-11">
				<select name = "address_state_id" id = "address_state_id">
					<option value = "">Please Select a State</option>
					<?php foreach($stateArray as $state) : ?>
						<option value = "<?php echo $state->id; ?>"><?php echo $state->abbreviation; ?></option>
					<?php endforeach;?>
				</select>
			</div>
		</div>
		<div class = "row zero_padding mg_top_xs">
			<div class = "col-xs-12">
				<input type = "submit" class = "btn btn-success btn-xs" name = "add_child_warehouse_submit" id = "add_child_warehouse_submit" value = "Add Warehouse" />
			</div>	
		</div>	
	</form>
<?php else : ?>
	<h3>A Parent Warehouse must be specified for adding a child warehouse</h3>
<?php endif; ?>
</div>

