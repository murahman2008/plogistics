<?php

class Warehouses extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('warehouse');
	}
	
	public function display()
	{
		//$warehouseTreeHTML = $this->warehouse->displayAllWarehouseHierachy();
		$viewParam = array('header' => true, 'footer' => true, 'source' => 'warehouse/display');
		$viewParam['data'] = array();
		
		$this->load->view('smart_view', $viewParam);
	}
	
}