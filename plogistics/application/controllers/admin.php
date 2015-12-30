<?php

class Admin extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('uploader');
	}
	
	private function _validateSellOrderFile()
	{
		$acceptableExtensions = array('csv');
		
		if(isset($_FILES['sell_order_file']) && isset($_FILES['sell_order_file']['name']) && trim($_FILES['sell_order_file']['name']) !== '' && 
		   isset($_FILES['sell_order_file']['tmp_name']) && trim($_FILES['sell_order_file']['tmp_name']) !== ''
		)
		{
			return checkFileExtension($_FILES['sell_order_file']['name'], $acceptableExtensions);
		}
		else
			throw new Exception('No Sell Order File Uploaded. A sell order file must be uploaded');
	}
	
	public function upload_sell_order()
	{
		$display = true;
		if(isset($_POST['sell_order_file_submit']) && trim($_POST['sell_order_file_submit']) !== '')
		{
			try
			{
			
				$this->form_validation->set_rules('upload_type', 'Sell Order File Type', 'trim|required|numeric');
			
				if($this->form_validation->run() === false)
					throw new Exception(validation_errors());
				
				$validFile = $this->_validateSellOrderFile();
				
				if($_POST['upload_type'] == FILE_UPLOAD_TYPE_EBAY)
				{
					$output = $this->uploader->uploadEbaySellOrderFile($_FILES['sell_order_file']);
					
					$this->load->view('smart_view', array('header' => true, 'footer' => true, 'source' => 'admin/upload_file_result', 'data' => array('output' => $output)));
					$display = false;
				}
			}
			catch(Exception $ex)
			{
				setSessionData('view_error_message', $ex->getMessage());	
			}	
		}
		
		if($display)
		{	
			$param = array('source' => 'admin/upload_file',
						   'header' => true,
					 	   'footer' => true		
			);
			
			$fileUploadTypeArray = array(FILE_UPLOAD_TYPE_EBAY => 'eBay File Upload',
										 FILE_UPLOAD_TYPE_WEBSITE => 'Website File Upload',	
										 FILE_UPLOAD_TYPE_OTHER => 'Other File Upload'	
			);
			
			$param['data'] = array();
			$param['data']['fileUploadTypeArray'] = $fileUploadTypeArray;
			
			$this->load->view('smart_view', $param);
		}		
	}
}