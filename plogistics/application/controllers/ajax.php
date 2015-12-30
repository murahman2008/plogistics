<?php

class Ajax extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		
		if(!$this->input->is_ajax_request())
			die("No direct script access allowed");
		
		$this->load->model('product');
		$this->load->model('warehouse');
		$this->load->model('customer');
	}
	
	/**
	 * This function will return a view content if it can find one
	 * 
	 * @throws Exception
	 */
	public function get_view_content()
	{
		$output = [];
		
		try
		{
			if(isset($_REQUEST['view']) && ($view = trim($_REQUEST['view'])) !== '')
			{
				$view = rtimr(ltrim($view, "/"), "/");
				if(!file_exists(APPPATH.'views/'.$view.'.php'))
					throw new Exception('Invalid view provided. View does not exist');
				
				$viewParam = ((isset($_REQUEST['view_param']) && is_array($_REQUEST['view_param'])) ?  $_REQUEST['view_param'] : array());
				
				$output['html'] = $this->load->view($view, $viewParam, true);
				$output['status'] = true;
				$output['data'] = array();
			}
			else
				throw new Exception('A View must be provided for loading');	
		}
		catch(Exception $ex)
		{
			$output['status'] = false;
			$output['message'] = $ex->getMessage();
		}	
		
		ajaxResponse($output);
	}
	
	public function book_product_for_sell_order()
	{
		$output = array();
		
		try 
		{
			if(isset($_POST['product_id']) && ($productId = trim($_POST['product_id'])) !== '' && isset($_POST['qty']) && ($qty = trim($_POST['qty'])) !== '')
			{
				$piArray = $this->product->bookProductWithQty($productId, $qty);
				
				$output['data']['product'] = $this->product->getProduct($productId);

				$output['status'] = true;
				$output['message'] = 'Product booking success';
				$output['data']['product_instances'] = $piArray;
			}
			else
				throw new Exception("Insufficinet data provided. cannot book product");	
		}
		catch(Exception $ex)
		{
			$output['message'] = $ex->getMessage();
			$output['status'] = false;
		}

		echo json_encode($output);
	}
	
	public function get_products_for_sell_order()
	{
		$output = array();
		
		try 
		{
			$productArray = $this->product->getProductsWithSOHCount(false, array(PRODUCT_AVAILABLE));
			
			$output['status'] = true;
			$output['message'] = "Product with SOH fetched";
			$output['data'] = $productArray;
		}
		catch(Exception $ex)
		{
			$output['message'] = $ex->getMessage();
			$output['status'] = false;
		}	
		
		echo json_encode($output);
	}
	
	public function release_product_booking()
	{
		$output = array();
		
		try
		{
			if(isset($_POST['pi_ids']) && is_array($_POST['pi_ids']))
			{
				$output['status'] = $this->product->releaseProductBooking($_POST['pi_ids']);
				$output['message'] = 'cleared';
			}
			else
				throw new Exception('No Product iinstances provided');	 
		}
		catch(Exception $ex)
		{
			$output['status'] = false;
			$output['message'] = $ex->getMessage();
		}
		
		echo json_encode($output);
	}

	public function fetch_warehouse()
	{
		$output = array();
		
		try 
		{
			if(isset($_POST['wh_id']) && ($warehouseId = trim($_POST['wh_id'])) !== '')
			{
				if(($warehouse = $this->warehouse->getWarehouse($warehouseId, true)) === false)
					throw new Exception('Invalid Warehouse id provided');
				else
				{
					$output['wh_data'] = $warehouse;
					
					$whTypeArray = $this->warehouse->getWarehouseTypes();
					$stateArray = $this->customer->getStates();
					
					$output['wh_html'] = $this->load->view('warehouse/edit', array('warehouse' => $warehouse, 
																				   'whTypeArray' => $whTypeArray,
																				   'stateArray' => $stateArray										
																			), 
															true);
				}	
				
				$output['status'] = true;
			}
			else
				throw new Exception('No Warehouse id specified');	
		}
		catch(Exception $ex)
		{
			$output['status'] = false;
			$output['message'] = $ex->getMessage();
		}

		echo json_encode($output);
	}
	
	public function fetch_child_warehouses_tree()
	{
		$output = array();
		
		try
		{
			if(isset($_GET['parent_id']) && trim($_GET['parent_id']) != '')
				$parentId = trim($_GET['parent_id']);	
			else
				$parentId = 0;	
			
			$level = 1;
			if(isset($_GET['level']) && trim($_GET['level']) !== '')
				$level = trim($_GET['level']);
			
			$level = (is_numeric($level) ? $level : 1);		
			
			$warehouseArray = $this->warehouse->getAllChildWarehouseHierachyForWarehouse($parentId, false, $level);
			//inspect($warehouseArray); die();
			//$finalWarehouseArray = array();
			
			$this->warehouse->converWarehouseArrayForFancyTree($warehouseArray, $output);
			//var_dump($output); die();
			//echo json_encode()
		}
		catch(Exception $ex)
		{
			$output = array();
		}	
		
		ajaxResponse($output);
	}
	
	public function fetch_child_warehouses()
	{
		$output = array();
		
		try 
		{
			if(isset($_POST['parent_id']) && ($parentId = trim($_POST['parent_id'])) !== '')
			{	
				$level = false;
				if(isset($_POST['level']) && trim($_POST['level']) !== '')
					$level = trim($_POST['level']);
			
				if(!is_numeric($level) || $level === false)
					throw new Exception('You have to specify the level of the depth of the warehouse tree hierachy');
				
				$warehouseArray = $this->warehouse->getAllChildWarehouseHierachyForWarehouse($parentId, false, $level);
				
				$output['html'] = $this->load->view('warehouse/sort_warehouse', array('parentId' => $parentId, 'warehouseArray' => $warehouseArray), true);
				$output['status'] = true;
				$output['data'] = $warehouseArray;
			}
			else
				throw new Exception('A Parent id must be provided to fetch the children');	
		}
		catch(Exception $ex)
		{
			$output['status'] = false;
			$output['message'] = $ex->getMessage();
		}
		
		echo json_encode($output);
	}
	
	public function update_warehouse_sequence()
	{
		$output = array();
		
		try
		{
			if(isset($_POST['wh_list']) && is_array($_POST['wh_list']) && count($_POST['wh_list']) > 0)
			{
				if(isset($_POST['parent_id']) && ($parentId = trim($_POST['parent_id'])) !== '')
				{
					$success = $this->warehouse->reorderChildWarehouseSequence($parentId, $_POST['wh_list']);	
					if(!$success)
						throw new Exception('Failed to reorder warehosue sequence');
					
					$output['status'] = true;
					$output['data'] = array();
				}
				else
					throw new Exception('No Parent info provided. Failed to sort list');	
			}
			else
				throw new Exception('No Warehouse List provided. Failed to sort');	
		}
		catch(Exception $ex)
		{
			$output['status'] = false;
			$output['message'] = $ex->getMessage();
		}

		echo json_encode($output);
	}
	
	public function add_child_warehouse()
	{
		$output = [];
		 
		try 
		{
			if(isset($_POST['add_child_warehouse_submit']) && trim($_POST['add_child_warehouse_submit']) !== '')
			{
				unset($_POST['add_child_warehouse_submit']);
				
				$this->warehouse->addWarehouse($_POST);
				
				$output['status'] = true;
				$output['message'] = "Child Warehouse Added Successfully";
				$output['root'] = false;
				
				if($_POST['parent_id'] == 0)
					$output['root'] = true;
			}
			else
			{ 	 
				if(isset($_POST['parent_id']) && ($parentId = trim($_POST['parent_id'])) !== '')
				{
					if($parentId == 0)
						$parent = (object)array('id' => $parentId);
					else
						$parent = $this->warehouse->getWarehouse($parentId);
					
					$warehouseTypeArray = $this->warehouse->getWarehouseTypes();
					$stateArray = $this->customer->getStates();
					
					$output['html'] = $this->load->view('warehouse/add', 
														array('parent' => $parent, 
															  'warehouseTypeArray' => $warehouseTypeArray, 
															  'stateArray' => $stateArray), 
														true);
					$output['status'] = true;
					$output['data'] = [];
				}
				else
					throw new Exception('A Parent must be provided to add child');	
			}
		}
		catch(Exception $ex)
		{
			$output['status'] = false;
			$output['message'] = $ex->getMessage();
		}

		ajaxResponse($output);
	}
	
	public function update_parent_warehouse()
	{
		$output = [];
		
		try
		{
			if(isset($_POST['update_parent_wh_submit']) && trim($_POST['update_parent_wh_submit']) !== '')
			{
				if(isset($_POST['wh_id']) && ($whId = trim($_POST['wh_id'])) !== '' && isset($_POST['new_parent_id']) && ($newParentId = trim($_POST['new_parent_id'])) !== '')
				{
					if(($warehouse = $this->warehouse->getWarehouse($whId)) === false)
						throw new Exception('Invalid warehouse provided!!!');
					
					$option = array('id' => $warehouse->id,
									'name' => $warehouse->name,	
									'description' => $warehouse->description,
									'warehouse_type_id' => $warehouse->warehouse_type_id,
									'code' => $warehouse->code,
									'parent_id' => $newParentId	
					);
					
					$whAddress = $this->customer->getAddress($warehouse->address_id);
					if($whAddress !== false)
					{	
						$option['address_line_1'] = $whAddress->line_1;
						$option['address_line_2'] = $whAddress->line_2;
						$option['address_suburb'] = $whAddress->suburb;
						$option['address_state_id'] = $whAddress->state_id;
						$option['address_postcode'] = $whAddress->postcode;
					}
						
					$warehouseId = $this->warehouse->updateWarehouse($whId, $option);
					
					if($warehouseId == $whId)
					{
						$output['status'] = true;
						$output['message'] = "Warehouse parent update successful";
						$output['data'] = [];
					}
				}
				else
					throw new Exception('Incomplete data provided. Please reload the page and try again');
			}
			else
			{
				if(isset($_POST['wh_id']) && ($whId = trim($_POST['wh_id'])) !== '')
				{
					if(($warehouse = $this->warehouse->getWarehouse($whId)) === false)
						throw new Exception('Invalid warehouse provided');
					
					$output['html'] = $this->load->view('warehouse/update_parent', array('warehouse' => $warehouse), true);	
					$output['status'] = true;
					$output['data'] = [];
				}
				else
					throw new Exception('No Warehouse specified. Unable to change parent');	
			} 	
		}
		catch(Exception $ex)
		{
			$output['status'] = false;
			$output['message'] = $ex->getMessage();
		}

		ajaxResponse($output);
	}

	public function delete_warehouse()
	{
		$output = [];
		
		try
		{
			if(isset($_POST['wh_id']) && ($whId = trim($_POST['wh_id'])) !== '')
			{	
				if(isset($_POST['pre_check']) && $_POST['pre_check'] == true)
				{
					$warningArray = $this->warehouse->preCheckDeleteWarehouse($whId);
					
					$output['html'] = $this->load->view('ajax_warning', array('warningArray' => $warningArray, 'onConfirmFunction' => 'onWarehouseDeleteSubmit('.$whId.');'), true);
					$output['status'] = true;
					$output['data'] = $warningArray;
				}
				else
				{
					$success = $this->warehouse->deleteWarehouse($whId);
					if(!$success)
						throw new Exception('warehouse delete failed');
					
					$output['status'] = true;
					$output['message'] = "Warehouse delete successful";	
				}		
			}
			else
				throw new Exception('No warehouse id specified');	
		}
		catch(Exception $ex)
		{
			$output['status'] = false;
			$output['message'] = $ex->getMessage();
		}	
		
		ajaxResponse($output);
	}
	
	public function get_parent_warehouses()
	{
		$output = array();
		
		try
		{
			if(isset($_POST['wh_id']) && ($whId = trim($_POST['wh_id'])) !== '')
			{
				$parentWarehouseArray = [];
				$this->warehouse->getFullParentHierachy($whId, $parentWarehouseArray);
				$parentWarehouseArray = array_reverse($parentWarehouseArray);
				
				$output['status'] = true;
				$output['data'] = $parentWarehouseArray;
				$output['message'] = '';
			}
			else
				throw new Exception('A Warehouse must be specified');	
		}
		catch(Exception $ex)
		{	
			$output['status'] = false;
			$output['message'] = $ex->getMessage(); 
		}
		
		ajaxResponse($output);
	}

	public function search_product()
	{
		$output = [];
		
		try
		{
			if(isset($_GET['term']) && ($term = trim($_GET['term'])) !== '')
			{
				$productArray = $this->product->searchProduct($term);
				if(count($productArray) <= 0)
					throw new Exception("No Product Found");
				
				$productArray = $this->product->convertProductArrayToAutocompleteOutput($productArray);
				$output = $productArray;
			}
			else
				throw new Exception('A serarch term must be specified');
		}
		catch(Exception $ex)
		{
			$output['id'] = '0';
			$output['label'] = $ex->getMessage();
			$output['value'] = $ex->getMessage();
			$output = array($output);
		}			
		
		ajaxResponse($output);
		
	}
	
	public function search_warehouse()
	{
		$output = [];
		
		try
		{
			if(isset($_GET['term']) && ($term = trim($_GET['term'])) !== '')
				$output = $this->warehouse->searchWarehouseForAutocomplete($term);
			else
				throw new Exception('A serarch term must be specified');
		}
		catch(Exception $ex)
		{
			$output['id'] = '0';
			$output['label'] = $ex->getMessage();
			$output['value'] = $ex->getMessage();
			$output = array($output);
		}
		
		ajaxResponse($output);
	}
	
	public function view_product_stock()
	{
		$output = array();
		
		try 
		{
			$product = $warehouse = false;
			
			if(isset($_GET['product_id']) && trim($_GET['product_id']) !== '')
			{	
				if(($product = $this->product->getProduct($_GET['product_id'])) === false)
					throw new Exception('Invalid Product info provided');
			}
			
			if(isset($_GET['warehouse_id']) && trim($_GET['warehouse_id']) !== '')
			{	
				if(($warehouse = $this->warehouse->getWarehouse($_GET['warehouse_id'])) === false)
					throw new Exception('Invalid warehouse info provided');
			}
			
			if($product === false && $warehouse === false)
				throw new Exception('Atleast one of the following information is required [Warehouse, Product]');
			
			if(!isset($_GET['page_no']) || !is_numeric($_GET['page_no']) || $_GET['page_no'] < 1)
				$_GET['page_no'] = 1;
			
			if(!isset($_GET['reset']))
				$_GET['reset'] = 1;
			
			$html = '';
			$output['data'] = $this->product->getStockReportForProduct($_GET);
			$availableArray = $this->product->getProductAvailableStatuses();
			$output['status'] = true;
			$output['message'] = "Report Generation successful";
			$output['reset'] = $_GET['reset'];
			$output['html'] = $this->load->view('product/view_stock_result', array('result' => $output['data'], 'availableArray' => $availableArray, 'criteria' => $_GET), true);
				
		}
		catch(Exception $ex)
		{
			$output['status'] = false;
			$output['message'] = $ex->getMessage();
		}
		
		ajaxResponse($output);
	}
	
	public function view_edit_product_instance()
	{
		$outpu = array();
		
		try 
		{
			if(isset($_POST['pi_id']) && ($piId = trim($_POST['pi_id'])) !== '')
			{
				if(($productInstance = $this->product->getProductInstance($piId)) === false)
					throw new Exception('Invalid Product Instance Info provided');
				
				$productArray = $this->product->getProducts(array(), array('name' => 'ASC'), array());
				$availableArray = $this->product->getProductAvailableStatuses();
				$piaArray = $this->product->getProductInstanceAliases(array('pia.product_instance_id' => $productInstance->id, 'pia.active' => 1), array('pia.product_instance_alias_type_id' => 'asc'), array(), true);
				$piatArray = $this->product->getProductInstanceAliasTypes();
				
				$output['html'] = $this->load->view('product/view_product_instance', array('productInstance' => $productInstance, 
																						   'productArray' => $productArray,
																						   'availableArray' => $availableArray,			
																						   'piaArray' => $piaArray,
																						   'piatArray' => $piatArray							
																					), true);
				$output['status'] = true;
				$output['message'] = 'PI Fetch Successful';
				$output['data'] = $productInstance;
			}
			else
				throw new Exception('A Product Instance id must be provided');	
		}
		catch(Exception $ex)
		{
			$output['status'] = false;
			$output['message'] = $ex->getMessage();
		}
		
		ajaxResponse($output);
	}
	
	public function add_update_product_instance_alias()
	{
		$output = [];
		
		try
		{
			if(isset($_POST['pi_id']) && ($piId = trim($_POST['pi_id'])) !== '' && is_numeric($piId) && 
			   isset($_POST['piat_id']) && ($piatId = trim($_POST['piat_id'])) !== '' && is_numeric($piatId) && 
			   isset($_POST['alias']) && ($alias = trim($_POST['alias'])) !== '')
			{
				$success = false;
				
				$piaId = trim($_POST['pia_id']);
				if(is_numeric($piaId))
				{
					$success = 
						$this->product->updateProductInstanceAliasById(
							$piaId,
							array('product_instance_id' => $piId,
								  'product_instance_alias_type_id' => $piatId,
								  'alias' => $alias
							)
						);					
				}
				else
				{
					$success = 
						$this->product->addProductInstanceAlias(
							array('product_instance_id' => $piId,
								  'alias' => $alias,
								  'product_instance_alias_type_id' => $piatId
						));	
				}
				
				if($success)
				{	
					$output['status'] = $success;
					//$output['data'] = array();
					$output['message'] = "Product Instance Alias Add/Update Successful";
				}
				else
					throw new Exception('Query Failed');	
			}
			else
				throw new Exception('Incomplete data provided. Unable to update/add product instance alias');	 
		}
		catch(Exception $ex)
		{
			$output['status'] = false;
			$output['message'] = $ex->getMessage();
		}

		ajaxResponse($output);
	}
	
	public function delete_product_instance_alias()
	{
		$output = [];
		
		try 
		{
			if(isset($_POST['pia_id']) && ($piaId = trim($_POST['pia_id'])) !== '')
			{
				$success = $this->product->deleteProductInstanceAlias($piaId);
				if(!$success)
					throw new Exception('Delete Query Failed');
				
				$output['status'] = $success;
				$output['message'] = 'Alias Deleted';
				$output['data'] = [];
			}
			else
				throw new Exception('No Product Instance Alias Info provided. Failed to Delete');	
		}
		catch(Exception $ex)
		{
			$output['message'] = $ex->getMessage();
			$output['status'] = false;
		}
		
		ajaxResponse($output);
	}

}