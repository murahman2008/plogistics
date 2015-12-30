<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sell_Orders extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('sell_order');
		$this->load->model('product');
		$this->load->model('customer');
	}
	
	private function _validateInputForCreate()
	{
		$this->form_validation->set_rules('hdn_pi_ids', 'Product', 'trim|required');
		$this->form_validation->set_rules('select_sell_source', 'Sell Source', 'trim|required|numeric');
		$this->form_validation->set_rules('txt_price_inc', 'GST Included Sell Price', 'trim|required|numeric');
		$this->form_validation->set_rules('txt_first_name', 'First Name', 'trim|required');
		$this->form_validation->set_rules('txt_last_name', 'Last Name', 'trim|required');
		$this->form_validation->set_rules('txt_ebay_id', 'Ebay Id', 'trim');
		$this->form_validation->set_rules('txt_address_line_1', 'Address Line 1', 'trim');
		$this->form_validation->set_rules('txt_address_line_2', 'Address Line 2', 'trim');
		$this->form_validation->set_rules('select_state', 'State', 'trim');
		$this->form_validation->set_rules('txt_postcode', 'Postcode', 'trim');
		$this->form_validation->set_rules('txt_suburb', 'Suburb', 'trim');
		$this->form_validation->set_rules('txt_contact_no', 'Contact no', 'trim');
		$this->form_validation->set_rules('txt_email', 'Email', 'trim');
		$this->form_validation->set_rules('txt_consignment_no', 'Consignment No', 'trim');
		$this->form_validation->set_rules('select_delivery_method', 'Delivery Method', 'trim|required|numeric');
		$this->form_validation->set_rules('select_sell_order_status', 'Order Status', 'trim|required|numeric');
		$this->form_validation->set_rules('txt_additional_comments', 'Additional Comments', 'trim');
		$this->form_validation->set_rules('select_payment_method', 'Payment Method', 'trim|required|numeric');
		$this->form_validation->set_rules('txt_payment_reference', 'Payment Refrence', 'trim');
		
		if($this->form_validation->run() === false)
			throw new Exception(validation_errors());
		
		$piIdArray = json_decode($_POST['hdn_pi_ids']);
		if(count($piIdArray) <= 0)
			throw new Exception("No Product Selected");
		
		return true;
	}
	
	public function create()
	{
		//$this->product->releaseUnusedBookedProducts();
		//die();
		
		if(isset($_POST['sell_order_create_submit']) && trim($_POST['sell_order_create_submit']) !== '')
		{
			try 
			{
				/// first check the form inputs ///
				$validData = $this->_validateInputForCreate();
				
				/// now gather the customer infos and try to find/create customer ///
				$customerOption['first_name'] = $_POST['txt_first_name'];
				$customerOption['last_name'] = $_POST['txt_last_name'];
				$customerOption['ebay_id'] = $_POST['txt_ebay_id'];
				$customerOption['email'] = $_POST['txt_email'];
				$customerOption['phone'] = $_POST['txt_contact_no'];
				$customerOption['address_line_1'] = $_POST['txt_address_line_1'];
				$customerOption['address_line_2'] = $_POST['txt_address_line_2'];
				$customerOption['address_suburb'] = $_POST['txt_suburb'];
				$customerOption['address_postcode'] = $_POST['txt_postcode'];
				$customerOption['address_state_id'] = $_POST['select_state'];
				/////////////////////////////////////////////////////////////
				
				/// now gather infos for sell order ///
				$sellOrderOption['consignment_no'] = $_POST['txt_consignment_no'];
				$sellOrderOption['delivery_method_id'] = $_POST['select_delivery_method'];
				$sellOrderOption['sell_order_status_id'] = $_POST['select_sell_order_status'];
				$sellOrderOption['additional_comments'] = $_POST['txt_additional_comments'];
				$sellOrderOption['order_total_inc'] = $_POST['txt_price_inc'];
				$sellOrderOption['sell_source_id'] = $_POST['select_sell_source'];
				$sellOrderOption['payment_method_id'] = $_POST['select_payment_method'];
				$sellOrderOption['payment_reference_no'] = $_POST['txt_payment_reference'];
				$sellOrderOption['product_instances'] = $piArray = json_decode(trim($_POST['hdn_pi_ids']));
				/////////////////////////////////////////////////////////////////////////
				
				$sellOrderId = $this->sell_order->createSellOrder($customerOption, $sellOrderOption);
				
				setSessionData('view_success_message', "A new Sell order created successfully");
				
				redirect('sell/create');
			}
			catch(Exception $ex)
			{
				setSessionData('view_error_message', $ex->getMessage());
			}	
		}
		
		$stateArray = $this->customer->getStates();
		$productArray = $this->product->getProductsWithSOHCount(false, array(PRODUCT_AVAILABLE));
		$statusArray = $this->sell_order->getSellOrderStatuses();
		$sourceArray = $this->sell_order->getSellSources();
		$deliveryArray = $this->sell_order->getDeliveryMethods();
		$paymentMethodArray = $this->sell_order->getPaymentMethods();
		
		$data = array('productArray' => $productArray,
				'stateArray' => $stateArray,
				'statusArray' => $statusArray,
				'sourceArray' => $sourceArray,
				'deliveryArray' => $deliveryArray,
				'paymentMethodArray' => $paymentMethodArray
		);
		
		$param = array('header' => true, 'footer' => true, 'source' => 'order/create_order', 'data' => $data);
		$this->load->view('smart_view', $param);
	}
}