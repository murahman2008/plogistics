<?php if(isset($warehouse) && is_object($warehouse)) : ?>
	<div class = "row zero_padding zero_margin mg_bottom_xs">
		<div class = "col-xs-12 btn-group-xs" role = "toolbar" style = "text-align:right;">
			<button type = "button" title = "Add Child Warehouse" class = "btn btn-warning" wh_id = "<?php echo $warehouse->wh_id; ?>" onclick = "return onAddChildWarehouseClick(this);">
				<span class = "glyphicon glyphicon-plus-sign"></span>Add Child
			</button>
			<button type = "button" class = "btn btn-info" wh_id = "<?php echo $warehouse->wh_id; ?>" onclick = "return onSortChildWarehouseClick(this);">
				<span class = "glyphicon glyphicon-sort"></span>Sort Child
			</button>
			<button type = "button" class = "btn btn-danger" wh_id = "<?php echo $warehouse->wh_id; ?>" onclick = "return onWarehouseDeleteClick(this);">
				<span class = "glyphicon glyphicon-trash"></span>Delete
			</button>
		</div>
	</div>
	<div class = "row zero_padding zero_margin" style = "background-color: green; color:white;">
		<!-- 	<div class = "col-xs-12 center"><h4>Edit Warehouse</h4></div> -->
	</div>	
	<form name = "warehouse_edit_form" id = "warehouse_edit_form" method = "post">
		<input type = "hidden" name = "id" id = "id" value = "<?php echo $warehouse->wh_id; ?>" />
		<div class = "row mg_top_xs">
			<div class = "col-xs-12 col-md-2 bold">Name</div>
			<div class = "col-xs-12 col-md-10">
				<input type = "text" name = "name" id = "name" value = "<?php echo $warehouse->wh_name; ?>" />
			</div>
		</div>
		<div class = "row mg_top_xs">
			<div class = "col-xs-12 col-md-2 bold">Description</div>
			<div class = "col-xs-12 col-md-10">
				<input type = "text" name = "description" id = "description" value = "<?php echo $warehouse->wh_description; ?>" />
			</div>
		</div>
		<div class = "row mg_top_xs">
			<div class = "col-xs-12 col-md-2 bold">Code</div>
			<div class = "col-xs-12 col-md-10">
				<input type = "text" name = "description" id = "description" value = "<?php echo $warehouse->wh_code; ?>" />
			</div>
		</div>
		<div class = "row mg_top_xs">
			<div class = "col-xs-12 col-md-2 bold">Warehouse Type</div>
			<div class = "col-xs-12 col-md-10">
				<select name = "" id = "">
					<option value = "">Please Select A Type.</option>
					<?php foreach($whTypeArray as $whType) : ?>
						<?php 
							$selected = '';
							if(trim($warehouse->wh_warehouse_type_id) === trim($whType->id))
								$selected = ' selected = "selected" ';
						?>
						
						<option value = "<?php echo $whType->id; ?>" <?php echo $selected; ?>><?php echo $whType->name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class = "row mg_top_xs">
			<div class = "col-xs-12 col-md-2 bold">Parent W/H</div>
			<div class = "col-xs-12 col-md-5">
				<?php 
					$warehouseParentId = 0;
					
					if(trim($warehouse->wh_parent_id) !== '0' && trim($warehouse->parent_id) !== '')
					{	
						echo $warehouse->parent_name;
						$warehouseParentId = $warehouse->parent_id;
					}
					else
						echo "No Parent";
				?>
				<input type = "hidden" name = "parent_id" id = "parent_id" value = "<?php echo $warehouseParentId; ?>" />
			</div>
			<div class = "col-xs-12 col-md-5">
				<button name = "change_wh_parent_btn" id = "change_wh_parent_btn" wh_id = "<?php echo $warehouse->wh_id; ?>" class = "btn btn-danger btn-xs" onclick = "return onChangeParentClick(this);">
					<span class = "glyphicon glyphicon-collapse-up"></span>Change Parent
				</button>
			</div>
		</div>
		<div class = "row mg_top_xs">
			<div class = "col-xs-12 col-md-2 bold">Root W/H</div>
			<div class = "col-xs-12 col-md-10">
				<?php echo $warehouse->root_name; ?>
				<input type = "hidden" name = "root_id" id = "root_id" value = "<?php echo $warehouse->root_id; ?>" />
			</div>
		</div>
		<div class = "row mg_top_xs">
			<?php if(trim($warehouse->address_id) !== '') : ?>
				<input type = "hidden" name = "address_id" id = "address_id" value = "<?php echo $warehouse->address_id; ?>" />
			<?php endif; ?>
			<div class = "col-xs-12 bold">Address</div>
			<div class = "col-xs-12 mg_top_xs">
				<input type = "text" name = "address_line_1" id = "address_line_1" value = "<?php echo $warehouse->address_line_1; ?>" placeholder = "Address Line 1..."/>
			</div>
			<div class = "col-xs-12 mg_top_xs">
				<input type = "text" name = "address_line_2" id = "address_line_2" value = "<?php echo $warehouse->address_line_2; ?>" placeholder = "Address Line 2..."/>
			</div>
			<div class = "col-xs-12 mg_top_xs">
				<input type = "text" name = "address_suburb" id = "address_suburb" value = "<?php echo $warehouse->address_suburb; ?>" placeholder = "Address Suburb..."/>
			</div>
			<div class = "col-xs-6 mg_top_xs">
				<input type = "text" name = "address_postcode" id = "address_postcode" value = "<?php echo $warehouse->address_postcode; ?>" placeholder = "Address Postcode..."/>
			</div>
			<div class = "col-xs-6 mg_top_xs">
				<select name = "address_state_id" id = "address_state_id">
					<option value = "">Select A State</option>
					<?php foreach($stateArray as $state) : ?>
						<?php 
							$selected = '';
							if(trim($warehouse->address_state_id) === trim($state->id))
								$selected = ' selected = "selected" ';
						?>
						<option value = "<?php echo $state->id; ?>" <?php echo $selected; ?> ><?php echo $state->abbreviation; ?></option>
					<?php endforeach;?>
				</select>
			</div>
		</div>
		<div class = "row mg_top_xs">
			<div class = "col-xs-12" style = "text-align:right;">
				<input type = "submit" class = "btn btn-success btn-sm" name = "edit_warehouse_submit" id = "edit_warehouse_submit" value = "Edit Warehouse" />
			</div>	
		</div>
	</form>
		
<?php else : ?>
	<h3>No Warehouse Info to show</h3>
<?php endif; ?>
