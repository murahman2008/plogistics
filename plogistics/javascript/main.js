var WEB_ROOT = 'http://localhost/plogistics/';
var GST = 15;

function onProductSelectionLoad() {
	$('#select_product').on('change', function(e) {
		e.preventDefault();
		onProductSelectionChange(this);
	});
	return false;
}

function onProductSelectionChange(select) {
	var tmp = {};
	$('.product_dependent').hide();
	
	tmp.productId = $.trim($(select).val());
	
	if(tmp.productId !== '')
	{
		tmp.pIdArray = tmp.productId.split('~');
		if(tmp.pIdArray[1] > 0)
			$('.product_dependent').show();
	}	
	
	return false;
}

function loadProductSelection() {
	var tmp = {};
	
	$.ajax({
		url: WEB_ROOT + 'ajax/get_products_for_sell_order',
		dataType: 'json',
		method: 'post',
		data: {},
		success: function(data) {
			if(!data.status)
				alert(data.message);
			else
			{
				tmp.html = '<select name = "select_product" id = "select_product" style = "width:100%;">';
				tmp.html += '<option value = "">Please Select A Product...</option>';
				
				$.each(data.data, function(index, item) {
					tmp.soh = item['instances']['1'];
					tmp.soh = (tmp.soh - 1);
					if(tmp.soh <= 0)
					{
						tmp.soh = 0;
						tmp.extra = ' disabled = "disabled" ';	
					}	
					tmp.html += '<option ' + tmp.extra + ' value = "' + item.id + '~' + tmp.soh + '">' + item.name + ' - [' + tmp.soh + ']</option>';
				});
				tmp.html += '</select>';
				
				$('#product_holder').html(tmp.html);
				onProductSelectionChange($('#select_product'));
				onProductSelectionLoad();
			}	
		}
		
	});
	
	return false;
}

function bookProduct(btn) {
	var tmp = {};
	
	tmp.productData = $.trim($('#select_product').val());
	tmp.qty = $.trim($('#txt_prod_qty').val());
	
	if(tmp.qty === '' || tmp.qty <= 0 || isNaN(tmp.qty) || tmp.productData === '')
		alert("A product must be selected And the Qunatity must be numeric and greater than zero!");
	else
	{
		tmp.productDataArray = tmp.productData.split('~');
		tmp.productId = tmp.productDataArray[0];
		tmp.productQty = tmp.productDataArray[1];
		
		if(tmp.productQty <= 0)
			alert("The product selected is out of stock!");
		else if(tmp.productQty < tmp.qty)
			alert("Insufficient stock for product!");
		else
		{
			$.ajax({
				url: WEB_ROOT + 'ajax/book_product_for_sell_order',
				dataType: 'json',
				method: 'post',
				data: {
					'product_id': tmp.productId,
					'qty': tmp.qty
				},
				success: function(data) {
					if(!data.status)
						alert(data.message);
					else
					{
						//data.data
						tmp.piData = JSON.parse($.trim($('#hdn_pi_ids').val()));
						tmp.piData = tmp.piData.concat(data.data.product_instances);
						$('#hdn_pi_ids').val(JSON.stringify(tmp.piData));
						
						tmp.html = '<span style = "display:block;" class = "booked_product_row">Product: ' + data.data.product.name + ' - Qty [' + tmp.qty + '] Booked';
						tmp.html += '<a pi_ids = \'' + JSON.stringify(data.data.product_instances) + '\' onclick = "return removeBooking(this);">X</a></span>';
						$('#booked_product_holder').html($('#booked_product_holder').html() + tmp.html);
					}
					
					loadProductSelection();
					return false;
				}
			});	
		}	
	}
	
	return false;
}

function removeBooking(btn) {
	var tmp = {};
	tmp.piIdArray = JSON.parse($.trim($(btn).attr('pi_ids')));
	tmp.allInstanceArray = JSON.parse($.trim($('#hdn_pi_ids').val()));
	
	$.ajax({
		url: WEB_ROOT + 'ajax/release_product_booking',
		dataType: 'json',
		method: 'post',
		data: { 'pi_ids': tmp.piIdArray },
		success: function(data) {
			if(!data.status)
				alert(data.message);
			else
			{
				for(var i = 0; i < tmp.piIdArray.length; i++)
				{
					tmp.index = tmp.allInstanceArray.indexOf(tmp.piIdArray[i]);
					if(tmp.index > -1)
						tmp.allInstanceArray.splice(tmp.index, 1);
				}
				
				$('#hdn_pi_ids').val(JSON.stringify(tmp.allInstanceArray));
				
				$(btn).closest('.booked_product_row').remove();
			}
			
			loadProductSelection();
			return false;
		}
	});	
	
	return false;
}	

function _isValidEmailAddress(emailAddress) {
    var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
    return pattern.test(emailAddress);
};

function onProductCreateSubmit(form) {
	return basic_check.validateForm(form);
}

function calculatePrice(input, type, targetId) {
	var tmp = {};
	tmp.inputValue = $.trim($(input).val());
	
	if(tmp.inputValue === '' || isNaN(tmp.inputValue) || tmp.inputValue <= 0)
	{
		$('#' + targetId).val(0);
		$(input).val(0);
	}	
	else
	{
		tmp.outputValue = '';
		
		if(type == 'ex') 
			tmp.outputValue = tmp.inputValue * (100 /(100 + GST));
		else
			tmp.outputValue = (tmp.inputValue * 1) + ((GST / 100) * tmp.inputValue);
		
		$('#' + targetId).val(tmp.outputValue);
	}	
		
	return false;
}

function onFileUplaodTypeSelect(select) {
	var tmp = {};
	$('.upload_type_dependent').hide();
	tmp.uploadType = $.trim($(select).val());
	
	if(tmp.uploadType !== '')
		$('.upload_type_dependent').show();
	
	return false;
}

function triggerSortableList() {
	if($('.sortable').length)
	{
		$('.sortable').sortable();
		$('.sortable').disableSelection();
	}	
	return false;
}

function onProductAddStockSubmit(form) {
	var tmp = {};
	return basic_check.validateForm(form);
}

function onProductViewStockFormSubmit(form) {
	var tmp = {};
	
	$.ajax({
		url: WEB_ROOT + 'ajax/view_product_stock',
		dataType: 'json',
		method: 'get',
		data: $(form).serialize(),
		success: function(data) {
			if(!data.status)
				alert(data.message);
			else
			{
				if($.trim(data.reset) == '1')
					$('#product_view_stock_result_holder').html(data.html);
				else
				{
					$('#product_view_stock_result_holder').find('#load_more_holder').remove();	
					$('#product_view_stock_result_holder').append(data.html);
				}	
			}	
			return false;	
		}
	});	

	return false;
}

function viewEditProductInstance(btn) {
	var tmp = {};
	tmp.piId = $.trim($(btn).closest('.pi_row').attr('pi_id'));
	
	$.ajax({
		url: WEB_ROOT + 'ajax/view_edit_product_instance',
		dataType: 'json',
		method: 'post',
		data: {'pi_id': tmp.piId},
		success: function(data) {
			if(!data.status)
				alert(data.message);
			else
			{
				$.fancybox({
					content: data.html,
					title: 'View Product Instance'
//					helpers: {
//						overlay: {
//							locked: false
//						}
//					}
				});
			}	
			return false;
		}
	});
	
	return false;
}

function addUpdateProductInstanceAlias(btn) {
	var tmp = {};
	
	tmp.parent = $(btn).closest('.pia_row');
	tmp.piaId = $.trim(tmp.parent.attr('pia_id'));
	tmp.piId = $.trim(tmp.parent.attr('pi_id'));
	tmp.piatId = $.trim(tmp.parent.find('.piat_id_select').val());
	tmp.alias = $.trim(tmp.parent.find('.alias_value_txt').val());
	
	$.ajax({
		url: WEB_ROOT + 'ajax/add_update_product_instance_alias',
		dataType: 'json',
		method: 'post',
		data: {'pi_id': tmp.piId, 'pia_id': tmp.piaId, 'alias': tmp.alias, 'piat_id': tmp.piatId},
		success: function(data) {
			if(!data.status)
				alert(data.message);
			else 
			{
				alert(data.message);
				$.fancybox.close();
				$('#'+tmp.piId).find('.edit_pi_btn').click();
			}
			
			return false;
		}
	});	
	
	return false;
}

function deleteProductInstanceAlias(btn) {
	var tmp = {};
	tmp.parent = $(btn).closest('.pia_row');
	tmp.piaId = $.trim(tmp.parent.attr('pia_id'));
	tmp.piId = $.trim(tmp.parent.attr('pi_id'));
	
	if(confirm("Are you sure you want to delete this alias?"))
	{
		$.ajax({
			url: WEB_ROOT + 'ajax/delete_product_instance_alias',
			method: 'post',
			dataType: 'json',
			data: {'pia_id': tmp.piaId, 'pi_id': tmp.piId},
			success: function(data) {
				if(!data.status)
					alert(data.message);
				else
				{
					alert(data.message);
					$.fancybox.close();
					$('#' + tmp.piId).find('.edit_pi_btn').click();
				}	
				
				return false;	
			}
		});
	}	
	
	return false;
}

function changeProductInstanceWarehouse(btn) {
	var tmp = {};
	
	
	
	return false;
}



$(function(){
	onProductSelectionLoad();
	
	/// trigger the sortable list on a ul if present in the page ///
	triggerSortableList();
});