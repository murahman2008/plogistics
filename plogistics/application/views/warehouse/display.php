<div class = "row zero_padding zero_maring">
	<div class = "col-xs-12">
		<button name = "add_root_wh_btn" class = "btn btn-success btn-xs" id = "add_root_wh_btn" onclick = "return onAddRootWarehouseClick(this);">
			<span class = "glyphicon glyphicon-plus"></span>Add Root Warehouse
		</button>
	</div>	
</div>
<div class = "row zero_padding mg_top_xs">
	<div id = "wh_tree" class = "col-xs-5 tree" style = "width:300px; height:600px; overflow:auto;"></div>
	<div class = "col-xs-7 border pd_xs" id = "wh_option_holder" style = "border-color:grey; border-radius:3px;"></div>
</div>

<script type = "text/javascript">
	var whTree = {};

	function onAddRootWarehouseClick(btn) {
		$.ajax({
			dataType: 'json',
			method: 'post',
			url: '<?php echo base_url("ajax/add_child_warehouse"); ?>',
			data: {'parent_id': 0},
			success: function(data) {
				if(!data.status)
					alert(data.message);
				else 
				{
					$.fancybox({
						content: data.html,
						title: 'Add Root Warehouse'
					});
				}
				return false;
			}
		});

		return false;
	}

	function onChangeParentClick(btn) {
		var tmp = {};
		tmp.whId = $.trim($(btn).attr('wh_id'));

		$.ajax({
			url: WEB_ROOT + 'ajax/update_parent_warehouse',
			dataType: 'json',
			method: 'post',
			data: {'wh_id': tmp.whId},
			success: function(data) {
				if(!data.status)
					alert(data.message);
				else
				{
					$.fancybox({
						'content': data.html,
						'title': 'Change Parent Warehouse'
					});
				}
				return false;
			}
		});	
			
		return false;
	}

	function onChangeParentSubmit(form) {
		var tmp = {};
		tmp.newParentId = $.trim($(form).find('#new_parent_id').val());

		if(tmp.newParentId === '')
			alert("A Warehouse must be selected as the new parent warehouse");
		else
		{
			tmp.formData = $(form).serializeArray();
			tmp.formData.push( {'name': 'update_parent_wh_submit', 'value': 'update_parent_wh_submit'} );

			$.ajax({
				url: WEB_ROOT + 'ajax/update_parent_warehouse',
				dataType: 'json',
				method: 'post',
				data: $.param(tmp.formData),
				success: function(data) {
					if(!data.status)
						alert(data.message);
					else
					{
						alert(data.message);
						$.fancybox.close();
						whTree.reload();
					}

					return false;
				}
			});					
		}	
			
		return false;
	}	
	
	function onAddChildWarehouseClick(btn) {
		var tmp = {};
		tmp.whId = $.trim($(btn).attr('wh_id'));

		$.ajax({
			url: WEB_ROOT + 'ajax/add_child_warehouse',
			dataType: 'json',
			method: 'post',
			data: {'parent_id': tmp.whId},
			success: function(data) {

				if(!data.status)
					alert(data.message);
				else
				{
					$.fancybox({
						'content': data.html,
						'showCloseBtn': true,
						'title': 'Add Warehouse'
					});
				}	

				return false;
			}
		});		

		return false;
	}

	function onAddChildWarehouseSubmit(form) {
		var tmp = {};
		tmp.validForm = basic_check.validateForm(form);
		
		if(tmp.validForm)
		{	
			tmp.formData = $(form).serializeArray();
			tmp.formData.push({'name': 'add_child_warehouse_submit', 'value': 'add_child_warehouse_submit'})

			$.ajax({
				url: WEB_ROOT + 'ajax/add_child_warehouse',
				dataType: 'json',
				method: 'post',
				data: $.param(tmp.formData),
				success: function(data) {
					if(!data.status)
						alert(data.message);
					else
					{
						alert(data.message);
						$.fancybox.close();

						if(!data.root)
						{	
							var node = whTree.getActiveNode();
							node.load(true).done(function() {
								node.setExpanded();
							});
						}
						else
							whTree.reload();	
					}
					return false;					
				}
			});			
		}
		return false;
	}
	
	function onSortChildWarehouseClick(link) {
		var tmp = {};
		tmp.whId = $.trim($(link).attr('wh_id'));

		$.ajax({
			url: WEB_ROOT + 'ajax/fetch_child_warehouses',
			dataType: 'json',
			method: 'post',
			data: {'parent_id': tmp.whId, 'level': 0},
			success: function(data) {
				if(!data.status)
					alert(data.message);
				else
				{
					$.fancybox({
						content: data.html,
						'showCloseButton': true
					});
					triggerSortableList();					
				}
			}
		});		
		
		return false;
	}

	function onSortChildWarehouseSubmit(button) {
		var tmp = {};	
		tmp.whIdArray = $("#sortable_wh").sortable("toArray");

		$.ajax({
			url: WEB_ROOT + 'ajax/update_warehouse_sequence',
			dataType: 'json',
			method: 'post',
			data: {'wh_list': tmp.whIdArray, 'parent_id': $('#hdn_parent_wh_id').val()},
			success: function(data) {
				if(!data.status)
					alert(data.message);
				else
				{	
					$.fancybox.close();
					var tree = $('#wh_tree').fancytree("getTree");
					var node = tree.getActiveNode();
					console.debug(node);
					node.load(true).done(function() {
						node.setExpanded();
					});
					//node.collapseSiblings();
				}	
			}
		});		
		
		return false;
	}

	function onWarehouseDeleteClick(btn) {
		var tmp = {};
		tmp.whId = $.trim($(btn).attr('wh_id'));
		
		$.ajax({
			url: WEB_ROOT + 'ajax/delete_warehouse',
			dataType: 'json',
			method: 'post',
			data: {'wh_id': tmp.whId, 'pre_check': true},
			success: function(data) {
				if(!data.status)
					alert(data.message);
				else
				{
					$.fancybox({
						content: data.html,
						'showCloseButton': true
					});
				}	
				return false;
			}
		});		
		
		return false;
	}

	function onWarehouseDeleteSubmit(whId) {
		$.ajax({
			method: 'post',
			url: '<?php echo base_url("ajax/delete_warehouse"); ?>',
			dataType: 'json',
			data: {'wh_id': whId},
			success: function(data) {
				if(!data.status)
					alert(data.message);
				else
				{
					$.fancybox.close();
					alert(data.message);
					$('#wh_option_holder').html('');
					var node = whTree.getActiveNode().getParent();

					console.debug(node); 

					if(!isNaN(node.key))
					{	
						node.load(true).done(function() {
							node.setExpanded();						
						});
					}
					else
						whTree.reload();
				}	
				return false;	
			}
		});
		return false;
	}

	$(function() {
		$('#wh_tree').fancytree({
			'checkbox': false,
			'source': {
				'url': "<?php echo base_url('ajax/fetch_child_warehouses_tree'); ?>",
				'data': {parent_id: 0, level: 1},
				'cache': false	
			},
			
			lazyLoad: function(event, data) {
				data.result = {
					cache: false, 
					url: "<?php echo base_url('ajax/fetch_child_warehouses_tree'); ?>", 
					data: {parent_id: data.node.key, level: 1} 
				};		
			},
			
			expand: function(event, data) {
				//data.node.children = null;
				//data.node.children
				//data.node.resetLazy();
			},

			collapsed: function(event, data) {
				//data.node.resetLazy();
			},

			focus: function(event, data) {
				console.debug(data);
				var whId = data.node.key;

				$.ajax({
					url: WEB_ROOT + 'ajax/fetch_warehouse',
					dataType: 'json',
					method: 'post',
					data: {'wh_id': whId},
					success: function(ajaxData) {
						//console.debug(ajaxData);

						if(ajaxData.status == false)
							alert(ajaxData.message);
						else
						{
							$('#wh_option_holder').html(ajaxData.wh_html);
						}	
					}
				});			
			}
		});

		whTree = $('#wh_tree').fancytree("getTree");		
	});
</script>