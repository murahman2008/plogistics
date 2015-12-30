<?php

class Home extends CI_Controller
{
	public function index()
	{
		die('fsfsdfsd');
		
		$this->load->library('basic_barcode');
		$imageName = Basic_Barcode::generateBarcode('OL00000000000000000027');
		
		inspect($imageName); die();
		
		$this->load->model('product');
		
		
		
		try 
		{
			$this->product->addInstanceForProduct(array('qty' => 20, 'product_id' => 1));
		}
		catch(Exception $ex)
		{
			var_dump($ex->getMessage());
		}	
		
		//var_dump($this->product->bookBarcode(2));
		 die();
		 
		$this->load->library('basicauth');
		
// 		BasicAuth::test();
// 		$user = BasicAuth::authenticate(array('username' => 'mrahman', 'password' => 'mrahman'));
// 		inspect($user);
// 		die();
		
// 		$this->db->query("insert into user (`username`, `password`, `role_id`, `first_name`, `last_name`, `email`, `active`, `created`, `created_by_id`, `updated`, `updated_by_id`)
// 						  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array('mrahman', sha1('mrahman'), 1, 'Mushfique', 'Rahman', 'a@b.com', 1, date('Y-m-d H:i:s'), 1, date('Y-m-d H:i:s'), 1));
		die();
		
		
		$valueArray = $param = array();
		for($i = 2; $i <= 50; $i++)
		{
			$valueArray[] = "(".implode(", ", array('?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?')).")";
			$param[] = 'Bay '.$i;
			$param[] = 'Footscray Warehouse Bay '.$i;
			$param[] = 11;
			$param[] = 2;
			$param[] = '100010001'.str_pad($i,4, '0', STR_PAD_LEFT);
			$param[] = 0;
			$param[] = '';
			$param[] = 0;
			$param[] = 1;
			$param[] = date('Y-m-d H:i:s');
			$param[] = 1; 
			$param[] = date('Y-m-d H:i:s');
			$param[] = 1;
		}
		
		$query = 
			"insert into warehouse (`name`, `description`, `parent_id`, `root_id`, `position`, `address_id`, `code`, `warehouse_type_id`, `active`, `created`, `created_by_id`, `updated`, `updated_by_id`)
			 VALUES ".implode(", ", $valueArray);
		
		$result = $this->db->query($query, $param);
		die();
		
		$this->load->model('warehouse');
		
		$output = $output2 = array();
		//$this->warehouse->getAllWarehouseHierachy(0, 'id', $output);
		
		$output = $this->warehouse->getAllChildWarehouseHierachyForWarehouse(0, false, '*');
		$this->warehouse->converWarehouseArrayForFancyTree($output, $output2);
		
		inspect($output); 
		inspect($output2); 
		
		die();
		
		$output = $this->warehouse->displayAllChildWarehouseHierachyForWarehouse(3, false);
		
		
		echo $output;
		die();
		inspect($output); die();
	}
}