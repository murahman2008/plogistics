<?php

class Sell_Order extends MY_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('customer');
	}
	
	/**
	 * This function get a single sale order based on Id
	 * 
	 * @param Int $orderId
	 * 
	 * @throws Exception
	 * @return SaleOrder / False
	 */
	public function getSellOrder($orderId)
	{
		if($orderId === false || ($orderId = trim($orderId)) === '')
			throw new Exception("An Id must be specified");
		
		return $this->_get('sell_order', $orderId);
	}
	
	public function getSellOrders(Array $option = array(), Array $orderBy = array(), $detailed = false)
	{
		$output = array();
		
		if($detailed)
		{
			
		}
		else
			$output = $this->_getByCriteria('sell_order', $option, $orderBy);		 

		return $output;
	}
	
	public function getSellOrderStatus($statusId)
	{
		if($statusId === false || ($statusId = trim($statusId)) === '')
			throw new Exception("An Id must be specified to fetch Sell Order Status");
		
		return $this->_get('sell_order_status', $statusId);
	}
	
	public function getSellOrderStatuses(Array $option = array(), Array $orderBy = array())
	{
		return $this->_getByCriteria('sell_order_status', $option, $orderBy);
	}
	
	public function getDeliveryMethod($deliveryMethodId)
	{
		if($deliveryMethodId === false || ($deliveryMethodId = trim($deliveryMethodId)) === '')
			throw new Exception("An Id must be specified to fetch Delivery Method");
		
		return $this->_get('delivery_method', $deliveryMethodId);
	}
	
	public function getDeliveryMethods(Array $option = array(), Array $orderBy = array())
	{
		return $this->_getByCriteria('delivery_method', $option, $orderBy);
	}
	
	public function getSellSource($sourceId)
	{
		if($sourceId === false || ($sourceId = trim($sourceId)) === '')
			throw new Exception("An Id must be specified to fetch Sell Source");
		
		return $this->_get('sell_source', $sourceId);
	}
	
	public function getSellSources(Array $option = array(), Array $orderBy = array())
	{
		return $this->_getByCriteria('sell_source', $option, $orderBy);
	}
	
	public function getPaymentMethod($paymentMethodId)
	{
		if($paymentMethodId === false || ($paymentMethodId = trim($paymentMethodId)) === '')
			throw new Exception("An id must be provided to fetch payment method");
		
		return $this->_get('payment_method', $paymentMethodId);
	}
	
	public function getPaymentMethods(Array $option = array(), Array $orderBy = array())
	{
		return $this->_getByCriteria('payment_method', $option, $orderBy);
	}
	
	private function _precheckSellOrderInput(Array $option)
	{
		if(!isset($option['customer_id']) || ($option['customer_id'] = trim($option['customer_id'])) === '')
			$errorArray[] = "A Customer is required to create/update sell order";  
		
		if(!isset($option['sell_source_id']) || ($option['sell_source_id'] = trim($option['sell_source_id'])) === '')
			$errorArray[] = "A Sell Source is required to create/update sell order";
		
		if(!isset($option['product_instances']) || !is_array($option['product_instances']) || count($option['product_instances']) <= 0)
			throw new Exception("Product(s) must be specified to create/update sell order");
		
		if(!isset($option['sell_order_status_id']) || trim($option['sell_order_status_id']) === '')
			throw new Exception('A status must be specified to create/update order');
		
		if(!isset($option['delivery_method_id']) || trim($option['delivery_method_id']) === '')
			throw new Exception('A Delivery Method must be specified to create/update order');
		
		if(!isset($option['order_total_ex']) || trim($option['order_total_ex']) === '' || !is_numeric($option['order_total_ex']))
			$option['order_total_ex'] = false;
			 
		if(!isset($option['order_total_inc']) || trim($option['order_total_inc']) === '' || !is_numeric($option['order_total_inc']))
			$option['order_total_inc'] = false;
			 
		if($option['order_total_ex'] === false && $option['order_total_inc'] === false)
			$errorArray[] = 'A sell price must be specified to create/update order';
		else
		{
			if($option['order_total_ex'] === false)
				getGSTExclusivePrice($option['order_total_inc']);
			else if($option['order_total_inc'] === false)
				getGSTInclusivePrice($option['order_total_ex']);
		}
		
		if(!isset($option['payment_method_id']) || ($option['payment_method_id'] = trim($option['payment_method_id'])) === '')
			$errorArray[] = 'A Payment method must be specified';
		
		if(isset($option['payment_received_date']) && ($prd = trim($option['payment_received_date'])) !== '' && validateDateTime($prd, 'Y-m-d H:i:s'))
			$option['payment_received_date'] = $prd;
		else
			$option['payment_received_date'] = date('Y-m-d H:i:s');
			
		$option['address_line_1'] = (isset($option['address_line_1']) ? trim($option['address_line_1']) : '');
		$option['address_line_2'] = (isset($option['address_line_2']) ? trim($option['address_line_2']) : '');
		$option['suburb'] = (isset($option['suburb']) ? trim($option['suburb']) : '');
		$option['postcode'] = (isset($option['postcode']) ? trim($option['postcode']) : '');
		$option['state_id'] = (isset($option['state_id']) ? trim($option['state_id']) : '');
		$option['email'] = (isset($option['email']) ? trim($option['email']) : '');
		$option['contact_no'] = (isset($option['contact_no']) ? trim($option['contact_no']) : '');
		$option['consignment_no'] = (isset($option['consignment_no']) ? trim($option['consignment_no']) : '');
		$option['payment_reference_no'] = (isset($option['payment_reference_no']) ? trim($option['payment_reference_no']) : '');
		$option['additional_comments'] = (isset($option['additional_comments']) ? trim($option['additional_comments']) : '');
		$option['external_identifier'] = (isset($option['external_identifier']) ? trim($option['external_identifier']) : '');
		$option['postage_cost_inc'] = (isset($option['postage_cost_inc']) ? trim($option['postage_cost_inc']) : '');
		
		return $option;		
	}
	
	public function addItemsToSellOrder($sellOrderId, Array $productInstanceArray)
	{
		if($sellOrderId === false || ($sellOrderId = trim($sellOrderId)) === '')
			throw new Exception('A sell order id is required to add item');
		
		if(count($productInstanceArray) <= 0)
			throw new Exception('An Item list is required to add item to a sell order');
		
		$this->db->from('product_instance')
				 ->where_in('id', $productInstanceArray)
				 ->where(array('available !=' => 0));
		$query = $this->db->get();
		
		if($query->num_rows() <= 0 || $query->num_rows() != count($productInstanceArray))
			throw new Exception("Products must be in Available/Booked statue to be added for a sell order");
		
		$this->db->from('sell_order_items')
				 ->where(array('sell_order_id' => $sellOrderId))
				 ->where_in('product_instance_id', $productInstanceArray);
		$query = $this->db->get();
		if($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
			{
				$this->db->where(array('id' => $row->id));
				$this->db->update('sell_order_items', array('updated' => date('Y-m-d H:i:s'), 'updated_by_id' => 1));
				$existingProductInstanceArray[] = $row->product_instance_id;
			}
			
			$productInstanceArray = array_diff($productInstanceArray, $existingProductInstanceArray);
		}
		
		if(count($productInstanceArray) > 0)
		{
			$inserCriteria = array();
			foreach($productInstanceArray as $piId)
			{	
				$inserCriteria[] = array('sell_order_id' => $sellOrderId, 'product_instance_id' => $piId, 
										 'created' => date('Y-m-d H:i:s'), 'updated' => date('Y-m-d H:i:s'),
										 'created_by_id' => 1, 'updated_by_id' => 1
				);
			}
			
			$this->db->insert_batch('sell_order_items', $inserCriteria);
		}

		$this->load->model('product');
		$this->product->changeProductAvailability($productInstanceArray, PRODUCT_UNAVAILABLE);
		
		return true;
	}
	
	public function createSellOrder(Array $customerOption, Array $sellOrderOption)
	{
		$sellOrderId = false;
		
		try 
		{
			$this->db->trans_begin();

			$customerId = false;
			if(!isset($customerOption['customer_id']) || $customerOption['customer_id'] == '')
			{	
				if((isset($customerOption['ebay_id']) && trim($customerOption['ebay_id']) !== '') || (isset($customerOption['email']) && trim($customerOption['email']) !== ''))
				{
					$customerArray = $this->customer->getCustomersByEmailOrEbay($customerOption['email'], $customerOption['ebay_id']);
					if(count($customerArray) > 0)
					{
						$customer = $customerArray[0];
						$customerId = $customer->id;
					}
				}
				
				if($customerId === false)
					$customerId = $this->customer->createCustomer($customerOption);
				
				if($customerId === false)
					throw new Exception("Unable to find/create customer for sell order");
				
				$sellOrderOption['customer_id'] = $customerId;
				$sellOrderOption['address_line_1'] = (isset($sellOrderOption['address_line_1']) ? trim($sellOrderOption['address_line_1']) : $customerOption['address_line_1']);
				$sellOrderOption['address_line_2'] = (isset($sellOrderOption['address_line_2']) ? trim($sellOrderOption['address_line_2']) : $customerOption['address_line_2']);
				$sellOrderOption['suburb'] = (isset($sellOrderOption['suburb']) ? trim($sellOrderOption['suburb']) : $customerOption['address_suburb']);
				$sellOrderOption['postcode'] = (isset($sellOrderOption['postcode']) ? trim($sellOrderOption['postcode']) : $customerOption['address_postcode']);
				$sellOrderOption['state_id'] = (isset($sellOrderOption['state_id']) ? trim($sellOrderOption['state_id']) : $customerOption['address_state_id']);
				$sellOrderOption['contact_no'] = (isset($sellOrderOption['contact_no']) ? trim($sellOrderOption['contact_no']) : $customerOption['phone']);
				$sellOrderOption['email'] = (isset($sellOrderOption['email']) ? trim($sellOrderOption['email']) : $customerOption['email']);
			}
			else
			{
				$sellOrderOption['customer_id'] = $customerOption['customer_id'];
			}		
				
			$sellOrderOption = $this->_precheckSellOrderInput($sellOrderOption);
			
			$piArray = $sellOrderOption['product_instances'];
			unset($sellOrderOption['product_instances']);
			
			$this->db->insert('sell_order', $sellOrderOption);
			$sellOrderId = $this->db->insert_id();
			
			$this->addItemsToSellOrder($sellOrderId, $piArray);
			
			if($this->db->trans_status() === false)
				throw new Exception("Query failed for Sell order creation");
			
			$this->db->trans_commit();
		}
		catch(Exception $ex)
		{
			$this->db->trans_rollback();
			throw $ex;
		}
		
		return $sellOrderId;
	}
	
	/**
	 * This function updates a sell order 
	 * @param unknown $sellOrderId
	 * @param array $option
	 * @throws Exception
	 * @return boolean
	 */
	public function updateSellOrder($sellOrderId, Array $customerOption, Array $sellOrderOption)
	{
		if($sellOrderId === false || ($sellOrderId = trim($sellOrderId)) === '')
			throw new Exception('A Sell order Id must be specified to update Sell Order');
		
		if(count($customerOption) <= 0 || count($sellOrderOption) <= 0)
			throw new Exception('Customer info and sell order update info must be provided to update Sell Order');
		
		try 
		{
			$this->db->trans_begin();
			
			$email = $ebayId = '';
			$customer = $customerId = false;
			
			if((isset($customerOption['email']) && ($email = trim($customerOption['email'])) !== '') || 
			   (isset($customerOption['ebay_id']) && ($ebayId = trim($customerOption['ebay_id'])) !== ''))
			{
				$customer = $this->customer->getCustomersByEmailOrEbay($email, $ebayId);
			}
			
			if($customer === false)
				$customerId = $this->customer->createCustomer($customerOption);
			else
				$customerId = $this->customer->updateCustomer($customer->id, $customerOption);
			
			if($customerId === false)
				throw new Exception('Cannot find/create customer. Unable to update Sell Order');
			
			$sellOrderOption['customer_id'] = $customerId;
			$sellOrderOption['address_line_1'] = (isset($sellOrderOption['address_line_1']) ? $sellOrderOption['address_line_1'] : $customerOption['address_line_1']);
			$sellOrderOption['address_line_2'] = (isset($sellOrderOption['address_line_2']) ? $sellOrderOption['address_line_2'] : $customerOption['address_line_2']);
			$sellOrderOption['suburb'] = (isset($sellOrderOption['suburb']) ? $sellOrderOption['suburb'] : $customerOption['address_suburb']);
			$sellOrderOption['postcode'] = (isset($sellOrderOption['postcode']) ? $sellOrderOption['postcode'] : $customerOption['address_postcode']);
			$sellOrderOption['state_id'] = (isset($sellOrderOption['state_id']) ? $sellOrderOption['state_id'] : $customerOption['address_state_id']);
			
			$sellOrderOption = $this->_precheckSellOrderInput($sellOrderOption);
			$sellOrderOption['updated'] = date('Y-m-d H:i:s');
			$sellOrderOption['updated_by_id'] = 1;
			
			$this->db->where(array('id' => $sellOrderId));
			if(($result = $this->db->update('sell_order', $sellOrderOption)) === false)
				throw new Exception('Sell Order Updae Failed. Query Failed');
				
			if($this->db->trans_status() === false)
				throw new Exception("Query failed for Sell order Update");
				
			$this->db->trans_commit();
		}
		catch(Exception $ex)
		{
			$this->db->trans_rollback();
			throw new Exception('Unable to update Sell order. Reason: ['.$ex->getMessage().']');
		}	

		return $sellOrderId;
	}

}