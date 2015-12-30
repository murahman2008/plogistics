<?php

class Customer extends MY_Model
{
	/**
	 * 
	 * @param unknown $stateId
	 * @throws Exception
	 * @return boolean
	 */
	public function getState($stateId)
	{
		if($stateId === false || ($stateId = trim($stateId)) == '')
			throw new Exception("An Id must be specified to find state");
		
		return $this->_get('state', $stateId);
	}
	
	/**
	 * This function get a list of State based on the option/criteria provided
	 * It can also order the list based on the orderBy (if provided)
	 *
	 * @param Array $option
	 * @param Array $orderBy
	 * @return Array of State ActiveRecord(s)
	 */
	public function getStates(Array $option = array(), Array $orderBy = array())
	{
		return $this->_getByCriteria('state', $option, $orderBy);
	}
	
	/**
	 * 
	 * @param unknown $customerId
	 * @throws Exception
	 * @return boolean
	 */
	public function getCustomer($customerId)
	{
		if($customerId === false || ($customerId = trim($customerId)) == '')
			throw new Exception("An Id must be specified to find Customer");
		
		return $this->_get('customer', $customerId);
	}
	
	/**
	 * This function get a list of customers based on the option/criteria provided 
	 * It can also order the list based on the orderBy (if provided)
	 * 
	 * @param Array $option
	 * @param Array $orderBy
	 * @return Array of Customer ActiveRecord(s)
	 */
	public function getCustomers(Array $option = array(), Array $orderBy = array())
	{
		return $this->_getByCriteria('customer', $option, $orderBy);
	}

	/**
	 * This function tries to fetch a list of customers based on Email / Ebay Id / Phone
	 * 
	 * @param String $email
	 * @param String $ebayId
	 * @param String $phone
	 * 
	 * @throws Exception
	 * @return Array of Customer ActiveRecord(s)
	 */
	public function getCustomersByEmailOrEbay($email, $ebayId)
	{
		$email = trim($email);
		$ebayId = trim($ebayId);
		
		if($email === '' && $ebayId === '')
			throw new Exception("Atleaset one information of the following [Email, EbayId] must be provided");
		
		$option = $output = $param = array();
		
		if($ebayId !== '')
		{	
			$option[] = 'ebay_id like ?';
			$param[] = $ebayId;
		}	
		else if($email !== '')
		{	
			$option[] = 'email like ?';
			$param[] = $email;
		}	

		$query = $this->db->query("select * from customer where active = 1 AND (".implode(" OR ", $option).")", $param);
		
		if($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
				$output[] = $row;
		}
	
		return $output;				 
	}
	
	/**
	 * This function pre-checks all the inputs and requirements before creating a customer 
	 * @param unknown $option
	 * @throws Exception
	 * @return multitype:string NULL Ambigous <boolean, number>
	 */
	private function _checkInputForCustomerCreation($option)
	{
		$output = array();
		
		$output['first_name'] = (isset($option['first_name']) ? trim($option['first_name']) : '');
		$output['last_name'] = (isset($option['last_name']) ? trim($option['last_name']) : '');
		
		if($output['first_name'] === '' && $output['last_name'] === '')
			throw new Exception('First And/Or Last Name is Required to create/update customer');
		
		if(isset($option['email']) && trim($option['email']) !== '')
			$output['email'] = trim($option['email']);
		//else
		//	$output['email'] = '';
		
		if(isset($option['phone']) && trim($option['phone']) !== '')
			$output['mobile'] = $output['phone'] = trim($option['phone']);
		//else
		//	$output['mobile'] = $output['phone'] = '';
		
		if(isset($option['ebay_id']) && trim($option['ebay_id']) !== '')
			$output['ebay_id'] = trim($option['ebay_id']);
		//else
		//	$output['ebay_id'] = '';
		
		$addressOption = array();
		$addressOption['line_1'] = ((isset($option['address_line_1']) && trim($option['address_line_1']) !== '') ? trim($option['address_line_1']) : '');
		$addressOption['line_2'] = ((isset($option['address_line_2']) && trim($option['address_line_2']) !== '') ? trim($option['address_line_2']) : '');
		$addressOption['suburb'] = ((isset($option['address_suburb']) && trim($option['address_suburb']) !== '') ? trim($option['address_suburb']) : '');
		$addressOption['state_id'] = ((isset($option['address_state_id']) && trim($option['address_state_id']) !== '') ? trim($option['address_state_id']) : '');
		$addressOption['postcode'] = ((isset($option['address_postcode']) && trim($option['address_postcode']) !== '') ? trim($option['address_postcode']) : '');
		
		$addressId = false;
		
		$address = $this->findAddress($addressOption);
		if($address === false)
		{
			try 
			{
				$addressId = $this->createAddress($addressOption);
			}
			catch(Exception $ex)
			{
				$address = false;
				$addressId = 0;
			}	
		}
		else
			$addressId = $address->id;
		
		if($addressId === false)
			$addressId = 0;
		
		if($addressId != 0)
			$output['address_id'] = $addressId;
		
		return $output;
	}
	
	/**
	 * This function creates a new customer 
	 * If the customer is created successfully, it returns the newly created customer id
	 * 
	 * @param Array $option
	 * 
	 * @throws Exception
	 *		returns Id
	 */
	public function createCustomer(Array $option)
	{
		$param = $this->_checkInputForCustomerCreation($option);
		$param['created'] = date('Y-m-d H:i:s');
		$param['updated'] = date('Y-m-d H:i:s');
		
		$success = $this->db->insert('customer', $param);
		if(!$success)
			throw new Exception("New Customer creation failed");
		
		return $this->db->insert_id();
	}
	
	public function updateCustomer($customerId, Array $option)
	{
		if($customerId === false || ($customerId = trim($customerId)) === '')
			throw new Exception('a customer id is required to update a customer');
		
		$param = $this->_checkInputForCustomerCreation($option);
		$param['updated'] = date('Y-m-d H:i:s');
		
		$this->db->where(array('id' => $customerId));
		$success = $this->db->update('customer', $param);
		
		if(!$success)
			throw new Exception("Customer Update failed");
		
		return $customerId;
	}
	
	/**
	 * 
	 * @param array $option
	 * @return boolean|Ambigous <unknown>
	 */
	public function findAddress(Array $option)
	{
		$line1 = (isset($option['line_1']) && trim($option['line_1']) !== '' ? trim($option['line_1']) : '');
		$line2 = (isset($option['line_2']) && trim($option['line_2']) !== '' ? trim($option['line_2']) : '');
		$suburb = (isset($option['suburb']) && trim($option['suburb']) !== '' ? trim($option['suburb']) : '');
		$stateId = (isset($option['state_id']) && trim($option['state_id']) !== '' ? trim($option['state_id']) : '');
		$postcode = (isset($option['postcode']) && trim($option['postcode']) !== '' ? trim($option['postcode']) : '');
		
		if(($line1 == '' && $line2 == '') || $suburb == '' || ($stateId == '' || $stateId == 0) || $postcode == '')
			return false;
		
		$param = array();
		
		if($line1 != '')
			$param['line_1'] = $line1;		
		if($line2 != '')
			$param['line_2'] = $line2;		
		
		$param['suburb'] = $suburb;		
		$param['state_id'] = $stateId;		
		$param['postcode'] = $postcode;		
		
		$addressArray = $this->getAddresses($param);
		if(count($addressArray) > 0)
			return $addressArray[0];
		
		return false;
	}
	
	public function createAddress(Array $option)
	{
		$line1 = (isset($option['line_1']) && trim($option['line_1']) !== '' ? trim($option['line_1']) : '');
		$line2 = (isset($option['line_2']) && trim($option['line_2']) !== '' ? trim($option['line_2']) : '');
		$suburb = (isset($option['suburb']) && trim($option['suburb']) !== '' ? trim($option['suburb']) : '');
		$stateId = (isset($option['state_id']) && trim($option['state_id']) !== '' ? trim($option['state_id']) : '');
		$postcode = (isset($option['postcode']) && trim($option['postcode']) !== '' ? trim($option['postcode']) : '');
		
		if(($line1 == '' && $line2 == '') || $suburb == '' || ($stateId == '' || $stateId == 0) || $postcode == '')
			throw new Exception("The following information are required to create an address. [Line1/Line2, Suburb, Postcode, State]");
		
		$success = $this->db->insert('address', array('line_1' => $line1, 'line_2' => $line2, 'suburb' => $suburb, 'postcode' => $postcode, 'state_id' => $stateId, 
						                   			  'created' => date('Y-m-d H:i:s'), 'updated' => date('Y-m-d H:i:s')));
		if(!$success)
			throw new Exception("Address creation failed");
		
		return $this->db->insert_id();
	}
	
	public function getAddress($addressId)
	{
		if($addressId === false || ($addressId = trim($addressId)) === '')
			throw new Exception('An id must be provided to get address');
		
		return $this->_get('address', $addressId);
	}
	
	public function getAddresses(Array $option = array(), Array $orderBy = array())
	{
		return $this->_getByCriteria('address', $option, $orderBy);
	}

}