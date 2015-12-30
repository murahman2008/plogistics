<?php

class Product extends MY_Model
{
	/**
	 * 
	 * @param unknown $productInstanceId
	 * @throws Exception
	 * @return boolean
	 */
	public function getProductInstance($productInstanceId)
	{
		if($productInstanceId === false || ($productInstanceId = trim($productInstanceId)) === '')
			throw new Exception('An Id must be specified to get product instance');
		
		return $this->_get('product_instance', $productInstanceId);
	}
	
	public function getProductInstances(Array $option = array(), Array $orderBy = array(), Array $limit = array())
	{
		return $this->_getByCriteria('product_instance', $option, $orderBy, $limit);
	}
	
	public function searchProduct($term)
	{
		if(($term = $this->_validateInput($term, 'string')) === false)
			throw new Exception('No search term provided');
		
		$output = [];
		
		$this->db->from('product')
				 ->where(array('name like' => "%".$term."%"))
				 ->or_where(array('code like' => "%".$term."%"));
		$query = $this->db->get();
		if($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
				$output[] = $row;
		}
		
		return $output;
	}
	
	public function convertProductArrayToAutocompleteOutput(Array $productArray)
	{
		$output = [];
		foreach($productArray as $product)
		{	
			if(is_object($product))
			{	
				$tmp = [];
				$tmp['id'] = $product->id;
				$tmp['value'] = trim($product->name.' - '.$product->code);
				$tmp['label'] = $tmp['value'];
				$output[] = $tmp;
			}	
		}

		return $output;
	}
	
	public function getProduct($productId)
	{
		if($productId === false || ($productId = trim($productId)) === '')
			throw new Exception('An Id must be specified to get product');
		
		return $this->_get('product', $productId);
	}
	
	public function getProducts(Array $option = array(), Array $orderBy = array(), Array $limit = array())
	{
		return $this->_getByCriteria('product', $option, $orderBy, $limit);
	}
	
	/**
	 * This function deletes a single product based on the product id provided
	 * @param int $piId
	 * @throws Exception
	 * @return boolean
	 */
	public function deleteProductById($productId)
	{
		if(($productId = $this->_validateInput($productId)) === false)
			throw new Exception('A product id must be specified for delete');
		
		$piArray = $this->getProductInstances(array('product_id' => $productId, 'active' => 1), array(), array('limit' => 1));
		if(count($piArray) > 0 && is_object($piArray[0]))
			throw new Exception('There are instances for this product in the system. Unable to delete');
		
		$dependencyArray = array();
		$dependencyArray[] = array('table_name' => 'product_alias', 'foreign_key' => 'product_id');
		
		try
		{
			$success = $this->_deleteById('product', $id, $dependencyArray);
		}
		catch(Exception $ex)
		{
			throw new Exception('Unable to delete product. Reason ['.$ex->getMessage().']');
		}	
		
		return $success;
	}
	
	/**
	 * This function deletes a single product instance based on the product instance id provided
	 * @param int $piId
	 * @throws Exception
	 * @return boolean
	 */
	public function deleteProductInstanceById($piId)
	{
		if(($piId = $this->_validateInput($piId)) === false)
			throw new Exception('A product id must be specified for delete');
	
		$dependencyArray = array();
		$dependencyArray[] = array('table_name' => 'product_instance_alias', 'foreign_key' => 'product_instance_id');
	
		try
		{
			$success = $this->_deleteById('product_instance', $id, $dependencyArray);
		}
		catch(Exception $ex)
		{
			throw new Exception('Unable to delete product instance. Reason ['.$ex->getMessage().']');
		}
	
		return $success;
	}
	
	public function getProductAlias($id)
	{
		if(($id = $this->_validateInput($id)) === false)
			throw new Exception('An id must be specified');
		
		return $this->_get('product_alias', $id);
	}
	
	public function getProductAliases(Array $option = array(), Array $orderBy = array(), Array $limit = array())
	{
		return $this->_getByCriteria('product_alias', $option, $orderBy, $limit);
	}
	
	public function getAliasForProduct($productId, $productAliasTypeId)
	{
		return $this->getProductAliases(array('product_id' => $productId, 'product_alias_type_id' => $productAliasTypeId), array('updated' => 'asc'));		
	}
	
	public function getProductAliasType($id)
	{
		if(($id = $this->_validateInput($id)) === false)
			throw new Exception('An id must be specified');
		
		return $this->_get('product_alias_type', $id);
	}
	
	public function getProductAliasTypes(Array $option = array(), Array $orderBy = array(), Array $limit = array())
	{
		return $this->_getByCriteria('product_alias_type', $option, $orderBy, $limit);
	}
	
	public function getProductInstanceAliasType($id)
	{
		if(($id = $this->_validateInput($id)) === false)
			throw new Exception('An id must be specified');
	
		return $this->_get('product_instance_alias_type', $id);
	}
	
	public function getProductInstanceAliasTypes(Array $option = array(), Array $orderBy = array(), Array $limit = array())
	{
		return $this->_getByCriteria('product_instance_alias_type', $option, $orderBy, $limit);
	}
	
	public function getProductInstanceAlias($id)
	{
		if(($id = $this->_validateInput($id)) === false)
			throw new Exception('An id is required');
		
		return $this->_get('product_instance_alias', $id);
	}
	
	public function getProductInstanceAliases(Array $option = array(), Array $orderBy = array(), Array $limit = array(), $details = false)
	{
		if(!$details)
			return $this->_getByCriteria('product_instance_alias', $option, $orderBy, $limit);
		else
		{
			$output = [];
			
			$this->db->select('pia.id as pia_id, pia.product_instance_id as pia_product_instance_id, pia.alias as pia_alias, 
							   pia.product_instance_alias_type_id as pia_product_instance_alias_type_id,
							   pi.id as pi_id, pi.barcode as pi_barcode, pi.product_id as pi_product_id, 
							   piat.id as piat_id, piat.name piat_name, piat.allow_multiple as piat_allow_multiple, piat.mandatory as piat_mandatory	
							')
					 ->from('product_instance_alias as pia')
					 ->join('product_instance_alias_type as piat', 'piat.id = pia.product_instance_alias_type_id and piat.active = 1', 'inner')
					 ->join('product_instance as pi', 'pi.id = pia.product_instance_id and pi.active = 1', 'inner');
			
			if(count($option) > 0)
			{
				foreach($option as $key => $value)
				{
					if(is_array($value))
					{
						if(count($value) > 0)
							$this->db->where_in($key, $value);
					}
					else
						$this->db->where(array($key => $value));
				}
			}

			if(count($orderBy) > 0)
			{
				foreach($orderBy as $key => $value)
					$this->db->order_by($key, (strtolower(trim($value)) === 'asc' ? 'ASC' : 'DESC'));
			}

			if(count($limit) > 0)
			{
				if(isset($limit['limit']) && is_numeric($limit['limit']))
				{
					if(isset($limit['offset']) && is_numeric($limit['offset']))
						$this->db->limit($limit['limit'], $limit['offset']);
					else
						$this->db->limit($limit['limit']);
				}	
			}
			
			$query = $this->db->get();
			if($query->num_rows() > 0)
			{
				foreach($query->result() as $row)
				{
					if(!isset($output[$row->piat_id]))
					{	
						$output[$row->piat_id] = array();
						$output[$row->piat_id]['id'] = $row->piat_id;
						$output[$row->piat_id]['name'] = trim($row->piat_name);
						$output[$row->piat_id]['allow_multiple'] = trim($row->piat_allow_multiple);
						$output[$row->piat_id]['alias_list'] = array();
					}

					if(!isset($output[$row->piat_id]['alias_list'][$row->pia_id]))
					{
						$output[$row->piat_id]['alias_list'][$row->pia_id] = array();
						$output[$row->piat_id]['alias_list'][$row->pia_id]['id'] = $row->pia_id;
						$output[$row->piat_id]['alias_list'][$row->pia_id]['alias'] = trim($row->pia_alias);
						$output[$row->piat_id]['alias_list'][$row->pia_id]['product_instance_id'] = trim($row->pia_product_instance_id);
					}	
				}
			}
			
			return $output;
		}	
	}
	
	public function getAliasForProductInstance($piId, $piAliasTypeId)
	{
		return $this->getProductInstanceAliases(array('product_instance_id' => $piId, 'product_instance_alias_type_id' => $piAliasTypeId), array('updated' => 'asc'));
	}
	
	public function updateProductInstanceAlias(Array $updateCriteria, Array $updateCondition)
	{
		if(count($updateCriteria) <= 0 || count($updateCondition) <= 0)
			throw new Exception('Update Criteria and Condition are mandatory for updating product instance alias');
		
		return $this->_updateByCondition('product_instance_alias', $updateCriteria, $updateCondition);
	}
	
	/**
	 * This function updates a product instance alias based on id
	 * @param String/Int $piaId
	 * @param Array $option
	 * @throws Exception
	 * 
	 * @return Boolean
	 */
	public function updateProductInstanceAliasById($piaId, Array $option)
	{
		if(($piaId = trim($piaId)) === '' || !is_numeric($piaId) || $piaId <= 0)
			throw new Exception('Invalid product instance alias id provided');
	
		if(count($option) <= 0)
			throw new Exception('No data provided. Failed to update product instance alias');
		
		if(!isset($option['product_instance_alias_type_id']))
			throw new Exception('Product Instance Alias Type Must be provided');
		
		if(($piat = $this->getProductInstanceAliasType($option['product_instance_alias_type_id'])) === false)
			throw new Exception('Invalid Product Instance Alias Type provided');
	
		if(trim($piat->allow_multiple) == '1')
		{
			$piaArray =
			$this->getProductInstanceAliases(
					array('product_instance_id' => $option['product_instance_id'], 'alias' => trim($option['alias']),
							'product_instance_alias_type_id' => $piat->id, 'id !=' => $piaId
					)
			);
			
			if(count($piaArray) > 0)
			{
				$pia = $piaArray[0];
				throw new Exception('An alias ['.$pia->alias.'] with id ['.$pia->id.'] and alias type ['.$piat->name.'] already exists for this product instance.');
			}
		}
		else
		{
			$piaArray =
			$this->getProductInstanceAliases(
					array('product_instance_id' => $option['product_instance_id'],
							'product_instance_alias_type_id' => $piat->id, 'id !=' => $piaId
					)
			);
			
			if(count($piaArray) > 0)
			{
				$pia = $piaArray[0];
				throw new Exception('You are not allowed to have multiple alias for alias type ['.$piat->name.']. An alias ['.$pia->alias.'] for alias type ['.$piat->name.'] with id ['.$pia->id.'] is already found in the system for this product');
			}
		}
	
		return $this->_updateById('product_instance_alias', $piaId, $option);
	}
	
	public function deleteProductInstanceAlias($piaId)
	{
		if($piaId === false || ($piaId = trim($piaId)) === '')
			throw new Exception('No Alias id provided for delete');
		
		return $this->db->delete('product_instance_alias', array('id' => $piaId));
	}
	
	private function _precheckProductInstanceAliasAdd(Array $option)
	{
		if(count($option) <= 0)
			throw new Exception('No Data provided for adding Product Instance Alias');
		
		if(!isset($option['product_instance_id']) || !is_numeric($option['product_instance_id']))
			throw new Exception('No valid Product instance id provided');
		
		if(!isset($option['alias']) || trim($option['alias']) === '')
			throw new Exception('Empty/No Alias info provided');
		
		if(!isset($option['product_instance_alias_type_id']) || !is_numeric($option['product_instance_alias_type_id']))
			throw new Exception('No valid Product instance Alias Type id provided');
		
		if(($productInstance = $this->getProductInstance($option['product_instance_id'])) === false)
			throw new Exception('Invalid Product Instance Information provided');
		
		if(($piat = $this->getProductInstanceAliasType($option['product_instance_alias_type_id'])) === false)
			throw new Exception('Invalid Product Instance Alias Type information provided');
		
		$option['product_instance_id'] = $productInstance->id;
		$option['alias'] = trim($option['alias']);
		$option['product_instance_alias_type_id'] = $piat->id;
		
		$extras = array_diff(array_keys($option), array('product_instance_id', 'alias', 'product_instance_alias_type_id', 'active'));
		foreach($extras as $extra)
			unset($option[$extra]);
		
		$piaArray = [];
		if(trim($piat->allow_multiple) === '1')
		{
			$piaArray = 
				$this->getProductInstanceAliases(
					array('product_instance_id' => $productInstance->id, 
						  'alias' => $option['alias'], 
						  'product_instance_alias_type_id' => $piat->id
					)  
				);
			if(count($piaArray) > 0)
			{
				$pia = $piaArray[0];
				throw new Exception('An Alias with id ['.$pia->id.'] and the same value ['.$pia->alias.'] is already found in the system');
			}	
		}
		else
		{
			$piaArray = 
				$this->getProductInstanceAliases(
						array('product_instance_id' => $productInstance->id,
							  'product_instance_alias_type_id' => $piat->id
						)
				);
			if(count($piaArray) > 0)
			{
				$pia = $piaArray[0];
				throw new Exception('An Alias with id ['.$pia->id.'] and value ['.$pia->alias.'] is already found in the system. Mutiple alias for this alias type is not allowed');
			}
		}
		
		$option['created'] = date('Y-m-d H:i:s');
		$option['updated'] = date('Y-m-d H:i:s');
		$option['created_by_id'] = 1;
		$option['updated_by_id'] = 1;

		return $option;
	}
	
	public function addProductInstanceAlias(Array $option)
	{
		$option = $this->_precheckProductInstanceAliasAdd($option);
		return $this->db->insert('product_instance_alias', $option);
	}
	
	public function bookProductWithQty($productId, $qty)
	{
		if($productId === false || ($productId = trim($productId)) === '')
			throw new Exception("A product must be specified for booking");
		
		if($qty === false || $qty === '' || !is_numeric($qty) || $qty <= 0)
			throw new Exception("Valid Quantity must be specified for booking");
		$qty += 1;
		
		$this->db->select('*')
				 ->from('product_instance')
				 ->where(array('active' => 1, 'available' => PRODUCT_AVAILABLE, 'product_id' => $productId))
				 ->order_by('created', 'ASC')
				 ->limit($qty);
		$query = $this->db->get();
		if($query->num_rows() != $qty)
			throw new Exception('Insufficient stock for product available. SOH ['.($query->num_rows() - 1).']');
		else
		{
			$qty = $qty - 1;
			$piArray = array();
			
			foreach($query->result() as $row)
				$piArray[] = $row->id;
			
			array_pop($piArray);
			
			$this->db->where_in('id', $piArray)
					 ->where(array('available' => PRODUCT_AVAILABLE));
			
			$result = $this->db->update("product_instance", array("available" => PRODUCT_BOOKED, "updated_by_id" => 1, "updated" => date('Y-m-d H:i:s')));
			if(!$result)
				throw new Exception("Unable to Book product [".$productId."] with qty [".$qty."]");
			
			return $piArray;
		}			
	}
	
	public function changeProductAvailability(Array $productInstanceArray, $newStatus)
	{
		$now = date('Y-m-d H:i:s');
		
		$this->db->where_in('id', $productInstanceArray);
		$this->db->update('product_instance', array('available' => $newStatus, 'updated' => $now, 'updated_by_id' => 1));
		
		return true;
	}
	
	public function releaseProductBooking($piIdArray)
	{
		if(count($piIdArray) > 0)
		{	
			$this->db->where_in('id', $piIdArray);
			$this->db->update('product_instance', array('available' => 1, 'updated' => 'NOW()', 'updated_by_id' => 1));
		}
		return true;	
	}
	
	/**
	 * This function tries to clear any product instance that has been BOOKED for >= 30 minutes (1800 sec) 
	 * @return boolean
	 */
	public function releaseUnusedBookedProducts()
	{
		$piIdArray = array();
		$now = date('Y-m-d H:i:s');
		
		$query = "select * from product_instance where available = ? and updated < ? and TIMESTAMPDIFF(SECOND, updated, ?) >= ?";
		$query = $this->db->query($query, array(PRODUCT_BOOKED, $now, $now, BOOKING_EXPIRY_DURATION_SEC));
		
		if($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
				$piIdArray[] = $row->id;
		}

		if(count($piIdArray) > 0)
		{
			$this->db->where_in('id', $piIdArray);
			$this->db->update('product_instance', array('available' => PRODUCT_AVAILABLE, 'updated' => $now, 'updated_by_id' => 1));
			
		}
		
		echo "No of product instances released [".count($piIdArray)."]".PHP_EOL;
		return true;		
	}
	
	public function getProductsWithSOHCount($productId = false, Array $available = array())
	{
		$output = array();
		
		if(count($available) <= 0)
			$available = array(PRODUCT_AVAILABLE, PRODUCT_UNAVAILABLE, PRODUCT_BOOKED);
		
		$this->db->select('p.id as product_id, p.name as product_name, p.description as product_description, p.product_type_id as product_product_type_id, p.code as product_code,
						   p.created as product_created, p.updated as product_updated, pi.available as pi_available, count(pi.id) as pi_count')
				 ->from('product as p')
				 ->join('product_instance as pi', 'pi.product_id = p.id and pi.active = 1 '.(count($available) > 0 ? ' and pi.available IN ('.implode(", ", $available).')' : ''), 'left')
				 ->where(array('p.active' => 1))
				 ->group_by(array('p.id', 'pi.available'));
		
		if($productId !== false && ($productId = trim($productId)) !== '')
			$this->db->where(array('p.id' => $productId));
		
		//$this->db->order_by('pi.created', 'ASC');
		$query = $this->db->get();
		
		if($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
			{
				if(!isset($output[$row->product_id]))
				{
					$output[$row->product_id] = array();
					$output[$row->product_id]['id'] = trim($row->product_id);
					$output[$row->product_id]['name'] = trim($row->product_name);
					$output[$row->product_id]['description'] = trim($row->product_description);
					$output[$row->product_id]['product_type_id'] = trim($row->product_product_type_id);
					$output[$row->product_id]['code'] = trim($row->product_code);
					$output[$row->product_id]['created'] = trim($row->product_created);
					$output[$row->product_id]['updated'] = trim($row->product_updated);
					$output[$row->product_id]['instances'] = array();
				}

				if(trim($row->pi_available) !== '')
				{
					if(!isset($output[$row->product_id]['instances'][$row->pi_available]))
						$output[$row->product_id]['instances'][$row->pi_available] = trim($row->pi_count);
				}	
			}
			
			foreach($output as $key => $value)
			{
				$included = array_keys($value['instances']);
				$notIncluded = array_diff($available, $included);
				
				foreach($notIncluded as $ni)
					$output[$key]['instances'][$ni] = 0;						
			}
		}

		return $output;
	}
	
	public function createProduct(Array $option)
	{
		if(count($option) <= 0)
			throw new Exception('No data provided to create new product');
		
		if(!isset($option['name']) || trim($option['name']) === '')
			throw new Exception('A name must be provided to create/update product');
		else
			$option['name'] = trim($option['name']);
		
		if(!isset($option['description']) || trim($option['description']) === '')
			throw new Exception('A description must be provided to create/update product');
		else
			$option['description'] = trim($option['description']);
		
		if(!isset($option['code']) || trim($option['code']) === '')
			throw new Exception('A Product code must be provided to create/update product');
		else
			$option['code'] = trim($option['code']);
		
		if(isset($option['price_ex']))
			$option['price_ex'] = trim($option['price_ex']);
		else
			$option['price_ex'] = 0;
		
		if(isset($option['price_inc']))
			$option['price_inc'] = trim($option['price_inc']);
		else
			$option['price_inc'] = 0;
		
		$invalidPriceEx = false;
		if($option['price_ex'] <= 0 || $option['price_ex'] == '' || !is_numeric($option['price_ex']))
			$invalidPriceEx = true;
		
		$invalidPriceInc = false;
		if($option['price_inc'] <= 0 || $option['price_inc'] == '' || !is_numeric($option['price_inc']))
			$invalidPriceInc = true;
		
		if($invalidPriceEx && $invalidPriceInc)
			throw new Exception("Price (GST Ex AND/OR GST Inc.) must be specified to create/update product");
		
		if($invalidPriceEx)
			$option['price_ex'] = getGSTExclusivePrice($option['price_inc']);

		if($invalidPriceInc)
			$option['price_inc'] = getGSTInclusivePrice($option['price_ex']);

		$option['active'] = 1;
		$option['created'] = $option['updated'] = date('Y-m-d H:i:s');
		$option['created_by_id'] = $option['updated_by_id'] = 1;
		
		$result = $this->db->insert('product', $option);
		if(!$result)
			throw new Exception('Product Create Failed. Query Failed!!!!');
		
		return $this->db->insert_id();
	}
	
	public function getProductAvailableStatus($id)
	{
		if(($id = $this->_validateInput($id)) === false)
			throw new Exception('An id must be specified');
		
		return $this->_get('product_availability', $id);
	}
	
	public function getProductAvailableStatuses(Array $option = array(), Array $orderBy = array(), Array $limit = array())
	{
		return $this->_getByCriteria('product_availability', $option, $orderBy, $limit);
	}
	
	/**
	 * This function pre-books the counter for barcode
	 * 
	 * @param int $count
	 * @throws Exception
	 * @return int
	 */
	public function bookBarcodeCounter($count)
	{
		if($count === false || ($count = trim($count)) === '' || !is_numeric($count) || $count <= 0)
			throw new Exception('Count must be a non zero numeric value');
		
		$success = false;
		while($success !== true)
		{	
			$this->db->from('barcode_counter')
					 ->limit(1);
			$query = $this->db->get();
	
			$barcodeCounter = $query->row();
			$lastCounter = $barcodeCounter->last_counter;
			$this->db->where(array('last_counter' => $lastCounter));
			$this->db->update('barcode_counter', array('last_counter' => ($lastCounter + $count)));
			
			if($this->db->affected_rows() === 0)
				$success = false;
			else
				$success = true;
		}	

		return $lastCounter;
	}
	
	/**
	 * This function generates a number of barcode(s) based on the last barcode count used from the barcode_counter table
	 * The number of barcodes that will be geneated are based on the param provided
	 * 
	 * @param int $count
	 * @throws Exception
	 * 
	 * @return Array of barcode(s)
	 */
	public function generateBarcode($count)
	{
		if(!is_numeric($count) || $count <= 0)
			throw new Exception('Count must be a non zero numeric value');
		
		$barcodeArray = [];
		$lastCounter = $this->bookBarcodeCounter($count);
		
		for($i = ($lastCounter + 1); $i <= ($lastCounter + $count); $i++)
			$barcodeArray[] = BARCODE_PREFIX.str_pad($i, BARCODE_NUMBER_LENGTH, '0', STR_PAD_LEFT);

		return $barcodeArray;
	}
	
	private function _precheckProductInstanceAdd(Array $option)
	{
		$this->load->model('warehouse');
		
		if(count($option) <= 0)
			throw new Exception('No data provided for adding stock for a product');

		if(!isset($option['product_id']) || trim($option['product_id']) === '' || $this->getProduct($option['product_id']) === false)
			throw new Exception('A Valid Product must be provided for adding stock');

		if(!isset($option['warehouse_id']) || trim($option['warehouse_id']) === '' || $this->warehouse->getWarehouse($option['warehouse_id']) === false)
			$option['warehouse_id'] = 0;
		
		if(!isset($option['qty']) || !is_numeric($option['qty']) || $option['qty'] <= 0)
			throw new Exception('Valid Non-zero Quantity must be provided to add stock');
		
		if(isset($option['available']) && trim($option['available']) !== '')
		{
			if(!in_array(trim($option['available']), $this->getProductInstanceAvailableStatuses()))
				throw new Exception('Invalid Product Available statue provided');
		}
		else 
			$option['available'] = PRODUCT_AVAILABLE;

		$option['active'] = 1;
		$option['created'] = date('Y-m-d H:i:s');
		$option['created_by_id'] = 1;
		$option['updated'] = date('Y-m-d H:i:s');
		$option['updated_by_id'] = 1;

		return $option;
	}

	public function addInstanceForProduct(Array $option)
	{
		$option = $this->_precheckProductInstanceAdd($option);
		
		try
		{
			$this->db->trans_begin();
			
			$barcodeArray = $this->generateBarcode($option['qty']);
			if(count($barcodeArray) != $option['qty'])
				throw new Exception('The number of barcodes booked and generated ['.count($barcodeArray).'] DOES NOT match with the number ['.$option['qty'].'] required');
			
			$criteria = [];
			for($i = 0; $i < $option['qty']; $i++)
			{
				$tmp = array('product_id' => $option['product_id'], 
							 'warehouse_id' => $option['warehouse_id'], 
							 'available' =>	$option['available'], 
							 'active' => $option['active'],
					   		 'barcode' => $barcodeArray[$i], 
							 'created' => $option['created'], 
							 'created_by_id' => $option['created_by_id'], 
							 'updated' => $option['updated'], 
							 'updated_by_id' => $option['updated_by_id']);
				$criteria[] = $tmp;
			}	
			
			$result = $this->db->insert_batch('product_instance', $criteria);
			
			if($this->db->trans_status() === false)
				throw new Exception('Query Failed');
			
			$this->db->trans_commit();
		}
		catch(Exception $ex)
		{
			$this->db->trans_rollback();
			throw $ex;
		}	
		
		return true;
	}

	/**
	 * 
	 * @param array $option
	 * 		The options are 'product_id', 'warehouse_id', 'available', 'page_no', 'page_size'			
	 * @throws Exception
	 * @return multitype:
	 */
	public function getStockCountReportForProduct(Array $option)
	{
		$output = [];
		
		$this->load->model('warehouse');
		
		if(count($option) <= 0)
			throw new Exception('No info provided');
		
		$product = $productId = false;
		if(isset($option['product_id']) && trim($option['product_id']) !== '')
		{	
			$productId = trim($option['product_id']);
			if(($product = $this->getProduct($productId)) === false)
				throw new Exception('Invalid product id ['.$productId.'] provided');
		}	

		$warehouse = $warehouseId = false;
		if(isset($option['warehouse_id']) && trim($option['warehouse_id']) !== '')
		{
			$warehouseId = trim($option['warehouse_id']);
			if(($warehouse = $this->warehouse->getWarehouse($warehouseId)) === false)
				throw new Exception('Invalid warehouse id ['.$warehouseId.'] provided');
		}

		if($warehouse === false && $product === false)
			throw new Exception('Valid information is required for atleast one of the following [Warehouse, Product]');
		
		$available = false;
		if(isset($option['available']) && trim($option['available']) !== '' && in_array($option['available'], $this->getProductInstanceAvailableStatuses()))
			$available = $option['available'];
		
		
		
		$whArray = array();
		if($warehouse !== false)
		{
			$warehouseArray = $this->warehouse->getAllChildWarehouseHierachyForWarehouse($warehouseId, true, '*');
			$whIdArray = [];
			$this->warehouse->extractIdsFromWarehouseHierachyArray($warehouseArray, $whIdArray);
			
			$whArray[$warehouse->id] = $whIdArray;
		}
		else
		{
			$rootWarehouseArray = $this->warehouse->getAllChildWarehouseHierachyForWarehouse(0, false, 0);
			foreach($rootWarehouseArray as $key => $value)
			{
				$childWarehouseArray = $this->warehouse->getAllChildWarehouseHierachyForWarehouse($value['id'], true, '*');
				$childWarehouseIdArray = array();
				$this->warehouse->extractIdsFromWarehouseHierachyArray($childWarehouseArray, $childWarehouseIdArray);
			
				$whArray[$value['id']] = $childWarehouseIdArray;
			}

			if(count($whArray) <= 0)
				throw new Exception('No Warehouse Found in the system');
		}		
		
		if($available !== false)
			$this->db->where(array('pi.available' => $available));
		
		foreach($whArray as $key => $value)
		{
			$parentHierachyArray = array();
			$this->warehouse->getFullParentHierachy($key, $parentHierachyArray);
			$parentHierachyArray = array_reverse($parentHierachyArray);
			
			$this->db->select('pi.available as pi_available, count(pi.id) as pi_count')
					 ->from('product_instance as pi');
			
			$this->db->where_in('pi.warehouse_id', $value);
			
			if($product !== false)
				$this->db->where(array('pi.product_id' => $product->id));
			
			if($available !== false)
				$this->db->where(array('pi.available' => $available));
			
			$this->db->group_by('pi.available');
			
			$output[$key] = array();
			$output[$key]['stock_count'] = array();
			$output[$key]['wh_data'] = $parentHierachyArray;
			
			$query = $this->db->get();
			if($query->num_rows() > 0)
			{
				foreach($query->result() as $row)
					$output[$key]['stock_count'][$row->pi_available] = $row->pi_count;
			}
		}
		
		return $output;
		
// 		$query = 
// 				"select 
// 					pi.id as pi_id, pi.barcode as pi_barcode, pi.available as pi_available, pi.warehouse_id as pi_warehouse_id, pi.product_id as pi_product_id,
// 					pi.created as pi_created, pi.created_by_id as pi_created_by_id, pi.updated as pi_updated, pi.updated_by_id as pi_updated_by_id,
// 					p.id as product_id, p.name as product_name, 
// 					wh.id as wh_id, wh.name as wh_name, wh.parent_id as wh_parent_id, wh.position as wh_position, wh.root_id as wh_root_id
// 				 from product_instance pi
// 				 INNER JOIN product p ON (p.id = pi.product_id)
// 				 LEFT JOIN warehouse wh ON (wh.id = pi.warehouse_id)
// 				 where ".implode(" AND ", $criteria);
// 		$query .= " order by pi.warehouse_id ASC, pi.available ASC";
		
// 		$query = $this->db->query($query);
// 		if($query->num_rows() > 0)
// 		{
// 			foreach($query->result() as $row)
// 			{
// 				$output[$warehouseId][]
// 			}
// 		}	
		
// 		if(!isset($option['page_no']) || !is_numeric($option['page_no']))
// 			$option['page_no'] = 1;
		
// 		if(($option['page_no'] -1) < 0)
// 			$option['page_no'] = 1;
		
// 		if(!isset($option['page_size']) || !is_numeric($option['page_size']))
// 			$option['page_size'] = DEFAULT_PAGE_SIZE;
		
// 		$query .= " LIMIT ".(($option['page_no'] - 1) * $option['page_size']).", ".$option['page_size'];
		
// 		$query = $this->db->query($query, $param);
// 		if($query->num_rows() > 0)
// 		{
// 			foreach($query->result() as $row)
// 			{		
// 				if(trim($row->wh_id) === '')
// 					$whId = 0;
// 				else 
// 					$whId = trim($row->wh_id);
				
// 				if(!isset($output[$whId]))
// 				{	
// 					$output[$whId] = array();
// 					$output[$whId]['details'] = array();
// 					$output[$whId]['products'] = array();
// 					$this->warehouse->getFullParentHierachy($whId, $output[$whId]['details']);
// 					$output[$whId]['details'] = array_reverse($output[$whId]['details']);
// 				}
				
// 				if(!isset($output[$whId]['products'][$row->pi_available]))
// 					$output[$whId]['products'][$row->pi_available] = array();
				
// 				if(!isset($output[$whId][$row->pi_available][$row->pi_id]))
// 				{	
// 					$output[$whId]['products'][$row->pi_available][$row->pi_id] = array();
// 					$output[$whId]['products'][$row->pi_available][$row->pi_id]['id'] = $row->pi_id;
// 					$output[$whId]['products'][$row->pi_available][$row->pi_id]['barcode'] = $row->pi_barcode;
// 					$output[$whId]['products'][$row->pi_available][$row->pi_id]['available'] = $row->pi_available;
// 					$output[$whId]['products'][$row->pi_available][$row->pi_id]['product'] = array();
// 					$output[$whId]['products'][$row->pi_available][$row->pi_id]['product']['id'] = $row->product_id;
// 					$output[$whId]['products'][$row->pi_available][$row->pi_id]['product']['name'] = $row->product_name;
// 					$output[$whId]['products'][$row->pi_available][$row->pi_id]['created'] = $row->pi_created;
// 					$output[$whId]['products'][$row->pi_available][$row->pi_id]['updated'] = $row->pi_updated;
// 				}
// 			}
// 		}
		
// 		return $output;
				
	}
	
	public function getStockReportForProduct(Array $option)
	{
		$output = [];
		
		$this->load->model('warehouse');
		
		$whCacheArray = array();
		
		if(count($option) <= 0)
			throw new Exception('Search options must be provided for SOH report');
		
		$warehouseId = $warehouse = false;
		if(!isset($option['warehouse_id']) || ($warehouseId = trim($option['warehouse_id'])) === '' || !is_numeric($warehouseId) || $warehouseId <= 0)
			$warehouse = false;
		else
		{
			if(($warehouse = $this->warehouse->getWarehouse($warehouseId)) === false)
				throw new Exception('Invalid warehouse id ['.$warehouseId.'] provided');
		}	
		
		$productId = $product = false;
		if(!isset($option['product_id']) || ($productId = trim($option['product_id'])) === '' || !is_numeric($productId) || $productId <= 0)
			$product = false;
		else
		{
			if(($product = $this->getProduct($productId)) === false)
				throw new Exception('Invalid Product id ['.$productId.'] provided');
		}
		
		if($warehouse === false && $product === false)
			throw new Exception('Valid information is required for atleast one of the following [Warehouse, Product]');
		
		if(isset($option['available']) && trim($option['available']) !== '' && in_array($option['available'], $this->getProductInstanceAvailableStatuses()))
		{
			//
		}
		else
			unset($option['available']);	
		
		if(!isset($option['sort_by']) || !is_array($option['sort_by']) || count($option['sort_by']) <= 0)
			$option['sort_by'] = array('p.name' => 'ASC', 'pi.barcode' => 'ASC');
		
		if(!isset($option['page_no']) || trim($option['page_no']) === '' || !is_numeric($option['page_no']) || $option['page_no'] < 1)
			$option['page_no'] = 1;
		
		if(!isset($option['page_size']) || trim($option['page_size']) === '')
			$option['page_size'] = DEFAULT_PAGE_SIZE;
		
		$whArray = array();
		
		if($warehouse !== false)
		{
			$warehouseArray = $this->warehouse->getAllChildWarehouseHierachyForWarehouse($warehouse->id, true, '*');
			$this->warehouse->extractIdsFromWarehouseHierachyArray($warehouseArray, $whArray);
		}
		else
		{	
			$warehouseArray = $this->warehouse->getWarehouses();
			if(count($warehouseArray) <= 0)
				throw new Exception('No Warehouse found in the system');
				
			$whArray = array_map(create_function('$a', 'return $a->id;'), $warehouseArray);
		}
		
		if($product !== false)
			$this->db->where(array("pi.product_id" => $product->id));
		
		$this->db->where_in("pi.warehouse_id", $whArray);
		
		if(isset($option['available']))
			$this->db->where(array('pi.available' => $option['available']));
		
		$this->db->select("pi.id as pi_id, pi.barcode as pi_barcode, pi.available as pi_available, pi.product_id as pi_product_id, pi.warehouse_id as pi_warehouse_id,
						   p.id as product_id, p.name as product_name, 
						   wh.id as wh_id
						  ")
				 ->from("product_instance as pi")
				 ->join("product as p", "p.id = pi.product_id", "inner")
				 ->join("warehouse as wh", "wh.id = pi.warehouse_id", "inner");
		
		foreach($option['sort_by'] as $key => $value)
			$this->db->order_by($key, $value);
		
		$this->db->limit($option['page_size'], (($option['page_no'] - 1) * $option['page_size']));
		$query = $this->db->get();
		
		if($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
			{
				if(!isset($whCacheArray[$row->wh_id]))
				{	
					$whArray = [];
					$this->warehouse->getFullParentHierachy($row->wh_id, $whArray);
					$whArray = array_reverse($whArray);
					$whCacheArray[$row->wh_id] = $whArray;
				}
									
				$output[$row->pi_id] = array();	
				$output[$row->pi_id]['id'] = trim($row->pi_id);	
				$output[$row->pi_id]['barcode'] = trim($row->pi_barcode);	
				$output[$row->pi_id]['product'] = array();	
				$output[$row->pi_id]['product']['id'] = trim($row->product_id);	
				$output[$row->pi_id]['product']['name'] = trim($row->product_name);	
				$output[$row->pi_id]['available'] = $row->pi_available;	
				$output[$row->pi_id]['warehouse'] = $whCacheArray[$row->wh_id];	
			}	
		}
		
		return $output;
	}
	
}
