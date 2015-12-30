<?php

class Products extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('product');
	}
	
	public function create()
	{
		if(isset($_POST['product_create_submit']) && trim($_POST['product_create_submit']) !== '')
		{
			try 
			{
				$this->form_validation->set_rules('name', 'Product Name', 'trim|required');
				$this->form_validation->set_rules('code', 'Product Code', 'trim|required');
				$this->form_validation->set_rules('description', 'Product Description', 'trim|required');
				$this->form_validation->set_rules('price_ex', 'Product Ex', 'trim');
				$this->form_validation->set_rules('price_inc', 'Product Inc', 'trim');
				
				if($this->form_validation->run() === false)
					throw new Exception(validation_errors());
				
				if(($_POST['price_ex'] == '' && $_POST['price_inc'] == '') || 
				   ($_POST['price_ex'] <= 0 && $_POST['price_inc'] <= 0)   || 
				   (!is_numeric($_POST['price_ex']) && !is_numeric($_POST['price_inc']))
				)
				{
					throw new Exception('Price must be specified');
				}
				else
				{
					if($_POST['price_ex'] == '' || $_POST['price_ex'] <= 0 || is_numeric($_POST['price_ex']))
						$_POST['price_ex'] = getGSTExclusivePrice($_POST['price_inc']);
					if($_POST['price_inc'] == '' || $_POST['price_inc'] <= 0 || is_numeric($_POST['price_inc']))
						$_POST['price_inc'] = getGSTInclusivePrice($_POST['price_ex']);
				}
				
				$existingProductArray = $this->product->getProducts(array('code' => $_POST['code']), array(), array('limit' => 1));
				if(count($existingProductArray) > 0)
				{
					$ep = $existingProductArray[0];
					throw new Exception("A Product with name [".$ep->name."] already exists with the same code [".$_POST['code']."]");
				}	
				
				unset($_POST['product_create_submit']);
				$newProductId = $this->product->createProduct($_POST);
				setSessionData('view_success_message', "New Product Created Successfully");
				
				redirect('product/create');
					
			}
			catch(Exception $ex)
			{
				setSessionData('view_error_message', $ex->getMessage());
			}				
		}
		
		$param = array();
		$param['header'] = true;
		$param['footer'] = true;
		$param['source'] = 'product/create';
		$param['data'] = array();
 
		
		$this->load->view('smart_view', $param);
	}
	
	public function add_stock()
	{
		if(isset($_POST['product_add_stock_submit']) && trim($_POST['product_add_stock_submit']) !== '')
		{
			try
			{
				$this->form_validation->set_rules('product_id', 'Product', 'trim|required');
				$this->form_validation->set_rules('qty', 'Quantity', 'trim|required|integer');
				$this->form_validation->set_rules('available', 'Availability', 'trim|required|integer');
				$this->form_validation->set_rules('warehouse_id', 'Warehouse', 'trim|required|integer');
				
				if($this->form_validation->run() === false)
					throw new Exception(validation_errors());
				
				unset($_POST['product_add_stock_submit']);
				
				if(($success = $this->product->addInstanceForProduct($_POST)) === true)
				{	
					setSessionData('view_success_message', "Stock Added Successfully");
					redirect('product/add_stock');
				}	
				else
					throw new Exception('Unable to add stock. Please try again');
			}
			catch(Exception $ex)
			{
				setSessionData('view_error_message', $ex->getMessage());
			}	
		}	
		
		$param = [];
		$param['header'] = true;
		$param['footer'] = true;
		$param['source'] = 'product/add_stock';
		$param['data'] = array();
		
		$this->load->view('smart_view', $param);
	}

	private function _validateStockCountReportInput(Array $input)
	{
		$warehouseId = $productId = false;
		
		if(!isset($input['warehouse_id']) || ($warehouseId = trim($input['warehouse_id'])) === '' || !is_numeric($warehouseId) || $warehouseId <= 0)
			$warehouseId = false;
		
		if(!isset($input['product_id']) || ($productId = trim($input['product_id'])) === '' || !is_numeric($productId) || $productId <= 0)
			$productId = false;
		
		if($warehouseId === false && $productId === false)
			throw new Exception('Atleaset one of the following information is required [Warehouse, Product]');
		
		return true;
	}
	
	public function view_stock_count()
	{
		$param = $dataArray = array();
		
		if(isset($_POST['view_stock_count_submit']) && trim($_POST['view_stock_count_submit']) !== '')
		{
			try 
			{
				$this->form_validation->set_rules('product_id', 'Product', 'trim');
				$this->form_validation->set_rules('warehouse_id', 'Warehouse', 'trim');
				
				$this->_validateStockCountReportInput($_POST);
				
				//if($this->form_validation->run() === false)
				//	throw new Exception(validation_errors());			
				
				$whProductArray = $this->product->getStockCountReportForProduct($_POST);
				
				if(($productId = trim($_POST['product_id'])) !== '')
				{	
					if(($product = $this->product->getProduct($productId)) !== false)
						$dataArray['searchProduct'] = $product;
				}	
				
				$dataArray['result'] = $whProductArray;
			}
			catch(Exception $ex)
			{
				setSessionData('view_error_message', $ex->getMessage());
			}		
		}
		
		$param['header'] = true;
		$param['footer'] = true;
		$param['source'] = 'product/view_stock_count';
		$param['data'] = $dataArray;
		
		$this->load->view('smart_view', $param);
	}
	
	public function view_stock()
	{
		$viewParam = $dataArray = array();
		$warehouse = $product = false;
		
		if(count($_GET) > 0)
		{	
			$this->load->model('warehouse');
			
			if(isset($_GET['wh_id']) && is_numeric($_GET['wh_id']))
			{	
				$whArray = array();
				$this->warehouse->getFullParentHierachy($_GET['wh_id'], $whArray);
				if(count($whArray) > 0)
					$warehouse = array_reverse($whArray);
			}
			
			if(isset($_GET['product_id']) && is_numeric($_GET['product_id']))
				$product = $this->product->getProduct($_GET['product_id']);
		}
		
		$dataArray['product'] = $product;
		$dataArray['warehouse'] = $warehouse;
		$dataArray['submitForm'] = (($product !== false || $warehouse !== false) ? true : false);
		$dataArray['availableArray'] = $this->product->getProductAvailableStatuses();
		
		$viewParam['header'] = true;
		$viewParam['footer'] = true;
		$viewParam['source'] = 'product/view_stock';
		$viewParam['data'] = $dataArray;
		$this->load->view('smart_view', $viewParam);
	}
}