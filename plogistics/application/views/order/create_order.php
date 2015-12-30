<form name = "create_sell_order_form" id = "create_sell_order_form" method = "post" action = "<?php echo base_url('sell/create'); ?>">
	<div class = "row mg_top_xs">
		<input type = "hidden" name = "hdn_pi_ids" id = "hdn_pi_ids" value = <?php echo "'".set_value('hdn_pi_ids', json_encode(array()))."'";?> />
		<div class = "col-xs-12 col-md-12 bold">Product:</div>
		<div class = "col-xs-12 col-md-4" id = "product_holder">
			<select name = "select_product" id = "select_product" style = "width:100%;">
				<option value = "" <?php echo set_select('select_product', ""); ?> >Please Select A Product</option>
				<?php 
					foreach($productArray as $key => $value)
					{
						$soh = $value['instances'][PRODUCT_AVAILABLE];
						$soh = ($soh - 1);
						$extra = '';
						if($soh <= 0)
						{	
							$soh = 0;
							$extra = ' disabled = "disabled" ';
						}	

						echo '<option '.$extra.' value = "'.$value['id'].'~'.$soh.'" '.set_select('select_product', $value['id'].'~'.$soh).'>'.$value['name'].' - ['.$soh.']</option>';
					} 
				?>
			</select>
		</div>
		<div class = "col-xs-12 col-md-4 product_dependent" id = "container_prod_qty">
			<input type = "text" name = "txt_prod_qty" id = "txt_prod_qty" value = "1" />
		</div>
		<div class = "col-xs-12 col-md-4 product_dependent" id = "container_prod_book">
			<input type = "button" name = "btn_prod_qty_book" id = "btn_prod_qty_book" value = "Book" onclick = "return bookProduct(this);" />
		</div>
	</div>
	
	<div class = "row mg_top_xs">
		<div class = "col-xs-12" id = "booked_product_holder"></div>
	</div>
	
	<div class = "row mg_top_xs">
		<div class = "col-xs-12 col-md-2 txt_postcode bold left">Sell Source:</div>
		<div class = "col-xs-12 col-md-4">
			<select name = "select_sell_source" id = "select_sell_source" class = "full_width">
				<option value = "" <?php echo set_select('select_sell_source', ''); ?> >Please Select A Source</option>
				<?php foreach($sourceArray as $source) : ?>
					<option value = "<?php echo $source->id;?>" <?php echo set_select('select_sell_source', $source->id); ?> ><?php echo $source->name; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class = "col-xs-12 col-md-2 txt_postcode bold center">Status:</div>
		<div class = "col-xs-12 col-md-4">
			<select name = "select_sell_order_status" id = "select_sell_order_status" class = "full_width">
				<option value = "" <?php echo set_select('select_sell_order_status', ''); ?> >Please Select A Status</option>
				<?php foreach($statusArray as $status) : ?>
					<option value = "<?php echo $status->id;?>" <?php echo set_select('select_sell_order_status', $status->id); ?> ><?php echo $status->name; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	
	<div class = "row mg_top_xs">
		<div class = "col-xs-12 col-md-2 txt_postcode bold right">Sell Price(inc. GST):</div>
		<div class = "col-xs-12 col-md-4">
			<input type = "text" name = "txt_price_inc" id = "txt_price_inc" value = "<?php echo set_value('txt_price_inc', ''); ?>" />
		</div>
		<div class = "col-xs-12 col-md-2 bold center">Payment Method</div>
		<div class = "col-xs-12 col-md-4">
			<select name = "select_payment_method" id = "select_payment_method">
				<option value = "" <?php echo set_select('select_payment_method', ""); ?> >Please Select a Payment Method</option>
				<?php foreach($paymentMethodArray as $paymentMethod) : ?>
					<option value = "<?php echo $paymentMethod->id; ?>" <?php echo set_select('select_payment_method', $paymentMethod->id); ?> ><?php echo $paymentMethod->name; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<div class = "row mg_top_xs">
		<div class = "col-xs-12 col-md-2 txt_postcode bold left">First Name:</div>
		<div class = "col-xs-12 col-md-4"><input type = "text" name = "txt_first_name" id = "txt_first_name" value = "<?php echo set_value('txt_first_name', ''); ?>" /></div>
		<div class = "col-xs-12 col-md-2 bold right">Last Name:</div>
		<div class = "col-xs-12 col-md-4"><input type = "text" name = "txt_last_name" id = "txt_last_name" value = "<?php echo set_value('txt_last_name', ''); ?>" /></div>
	</div>
	
	<div class = "row mg_top_xs">
		<div class = "col-xs-12 col-md-2 bold center">Address Line 1:</div>
		<div class = "col-xs-12 col-md-4"><input type = "text" name = "txt_address_line_1" id = "txt_address_line_1" value = "<?php echo set_value('txt_address_line_1', ''); ?>" /></div>
		<div class = "col-xs-12 col-md-2 bold center">Address Line 2:</div>
		<div class = "col-xs-12 col-md-4"><input type = "text" name = "txt_address_line_2" id = "txt_address_line_2" value = "<?php echo set_value('txt_address_line_2', ''); ?>" /></div>
	</div>
	<div class = "row mg_top_xs">
		<div class = "col-xs-12 col-md-2 center bold">State:</div>
		<div class = "col-xs-12 col-md-4">
			<select name = "select_state" id = "select_state" class = "full_width">
				<option value = "" <?php echo set_select('select_state', ''); ?> >Select a State</option>
				<?php foreach($stateArray as $state) : ?>
					<option value = "<?php echo $state->id;?>" <?php echo set_select('select_state', $state->id); ?> ><?php echo $state->abbreviation;?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class = "col-xs-12 col-md-2 center bold">Postcode:</div>
		<div class = "col-xs-12 col-md-4">
			<input type = "text" name = "txt_postcode" id = "txt_postcode" value = "<?php echo set_value('txt_postcode', ''); ?>" />
		</div>
	</div>
	<div class = "row mg_top_xs">
		<div class = "col-xs-12 col-md-2 bold left">Suburb</div>
		<div class = "col-xs-12 col-md-4"><input type = "text" name = "txt_suburb" id = "txt_suburb" value = "<?php echo set_value('txt_suburb', ''); ?>" /></div>
		<div class = "col-xs-12 col-md-2 txt_postcode bold center">Ebay Id:</div>
		<div class = "col-xs-12 col-md-4"><input type = "text" name = "txt_ebay_id" id = "txt_ebay_id" value = "<?php echo set_value('txt_ebay_id', ''); ?>" /></div>
	</div>
	
	<div class = "row mg_top_xs">
		<div class = "col-xs-12 col-md-2 bold center">Contact No:</div>
		<div class = "col-xs-12 col-md-4"><input type = "text" name = "txt_contact_no" id = "txt_contact_no" value = "<?php echo set_value('txt_contact_no', ''); ?>" /></div>
		<div class = "col-xs-12 col-md-2 bold center">Email:</div>
		<div class = "col-xs-12 col-md-4"><input type = "text" name = "txt_email" id = "txt_email" value = "<?php echo set_value('txt_email', ''); ?>" /></div>
	</div>
	<div class = "row mg_top_xs">
		<div class = "col-xs-12 col-md-2 bold center">Consignment No:</div>
		<div class = "col-xs-12 col-md-4"><input type = "text" name = "txt_consignment_no" id = "txt_consignment_no" value = "<?php echo set_value('txt_consignment_no', ''); ?>" /></div>
		<div class = "col-xs-12 col-md-2 bold center">Delivery:</div>
		<div class = "col-xs-12 col-md-4">
			<select name = "select_delivery_method" id = "select_delivery_method" class = "full_width">
				<option value = "" <?php echo set_select('select_delivery_method', ''); ?> >Select A Delivery Method</option>
				<?php foreach($deliveryArray as $delivery) : ?>
					<option value = "<?php echo $delivery->id; ?>" <?php echo set_select('select_delivery_method', $delivery->id); ?> ><?php echo $delivery->name; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<div class = "row mg_top_xs">
		
	</div>
	<div class = "row mg_top_xs">
		<div class = "col-xs-12 col-md-2 bold">Additional Comments:</div>
		<div class = "col-xs-12 col-md-4">
			<textarea name = "txt_additional_comments" id = "txt_additional_comments" rows = "2" style = "width:100%;"><?php echo set_value('txt_additional_comments', ''); ?></textarea>
		</div>
		<div class = "col-xs-12 col-md-2 bold">Payment Refrence:</div>
		<div class = "col-xs-12 col-md-4">
			<textarea name = "txt_payment_reference" id = "txt_payment_reference" rows = "2" style = "width:100%;"><?php echo set_value('txt_payment_reference', ''); ?></textarea>
		</div>
	</div>
	<div class = "row mg_top_xs">
		<div class = "col-xs-12 txt_postcode bold">
			<input type = "submit" class = "btn btn-success" name = "sell_order_create_submit" id = "sell_order_create_submit" value = "Submit" />
		</div>
	</div>
</form>

<script type = "text/javascript">
	$(function(){
		onProductSelectionChange($('#select_product'));
	});

	/*
	window.onbeforeunload = function (evt) {
	  var message = 'Are you sure you want to leave?';
	  if (typeof evt == 'undefined') {
	    evt = window.event;
	  }

	  if(evt) 
	  {
		  $.ajax({
				url: WEB_ROOT + 'ajax/release_product_booking',
				dataType: 'json',
				method: 'post',
				data: {'pi_ids' : JSON.parse($.trim($('#hdn_pi_ids').val())) },
				success: function(data) {
					//alert(data.message);
				}
		  });			

		console.debug('fasfsdfd12');
		//evt.returnValue = message;
	  }
	  else
		  console.debug('222');
	  
	  //return message;
	}
	*/
	
</script>