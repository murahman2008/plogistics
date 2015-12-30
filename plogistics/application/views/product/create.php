<div class = "page-header mg_top_xs">
	<h3>Create Product</h3>
</div>

<form name = "product_creation_form" id = "product_creation_form" method = "post" onsubmit = "return onProductCreateSubmit(this);" action = "<?php echo site_url('product/create'); ?>">
	<div class = "row">
		<div class = "col-xs-12 col-md-1 bold">Name</div>
		<div class = "col-xs-12 col-md-10">
			<input type = "text" class = "basic_check" basic_check_criteria = "required" name = "name" id = "name" value = "<?php echo set_value('name'); ?>" placeholder = "Product Name..." />
		</div>
	</div>		
	<div class = "row mg_top_xs">
		<div class = "col-xs-12 col-md-1 bold">Description</div>
		<div class = "col-xs-12 col-md-10">
			<textarea class = "basic_check" basic_check_criteria = "required" name = "description" id = "description" style = "width:100%;" rows = "3"><?php echo set_value('description'); ?></textarea>
		</div>
	</div>		
	<div class = "row mg_top_xs">		
		<div class = "col-xs-12 col-md-1 bold">Code</div>
		<div class = "col-xs-12 col-md-10">
			<input type = "text" class = "basic_check" basic_check_criteria = "required" name = "code" id = "code" value = "<?php echo set_value('code'); ?>" placeholder = "Product Code..." />
		</div>		
	</div>
	<div class = "row mg_top_xs">		
		<div class = "col-xs-12 col-md-1 bold">Price</div>
		<div class = "col-xs-12 col-md-1" style = "color:red;">GST Ex.</div>
		<div class = "col-xs-12 col-md-4">
			<input type = "text" class = "basic_check" basic_check_criteria = "required|numeric" onchange = "return calculatePrice(this, 'inc', 'price_inc');" name = "price_ex" id = "price_ex" value = "<?php echo set_value('price_ex'); ?>" placeholder = "GST Exclusive Price..." />
		</div>		
		<div class = "col-xs-12 col-md-1" style = "color:red;">GST Inc.</div>
		<div class = "col-xs-12 col-md-4">
			<input type = "text" class = "basic_check" basic_check_criteria = "required|numeric" onchange = "return calculatePrice(this, 'ex', 'price_ex');" name = "price_inc" id = "price_inc" value = "<?php echo set_value('price_inc'); ?>" placeholder = "GST Inclusive Price..." />
		</div>		
	</div>
	<div class = "row mg_top_xs">
		<input class = "btn btn-success" type = "submit" name = "product_create_submit" id = "product_create_submit" value = "Create" />
	</div>
</form>	