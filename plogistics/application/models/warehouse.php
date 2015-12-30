<?php

class Warehouse extends MY_Model
{
	private $_tableName = 'warehouse';
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('customer');
	}
	
	public function searchWarehouse($term)
	{
		if(($term = $this->_validateInput($term, 'string')) === false)
			throw new Exception('A Search term must be specified to search warehouse');
		
		$output = [];
		
		$this->db->from('warehouse')
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
	
	public function getFullParentHierachy($warehouseId, Array &$output)
	{
		$warehouse = $this->getWarehouse($warehouseId);
		if($warehouse !== false)
		{	
			$output[] = $warehouse;
			$this->getFullParentHierachy($warehouse->parent_id, $output);
		}		
	}
	
	public function getAllParentsInHierachy($warehouseId, $includeSelf = true)
	{
		$whArray = array();
		$this->getFullParentHierachy($warehouseId, $whArray);
		$whArray = array_reverse($whArray);
		
		if(!$includeSelf)
			array_pop($whArray);
		
		return $whArray;
	}
	
	public function searchWarehouseForAutocomplete($term)
	{
		$output = [];
		
		$whArray = $this->searchWarehouse($term);
		if(count($whArray) <= 0)
			return array(array('id' => '', 'value' => 'No Warehouse Found', 'label' => 'No Warehouse Found'));
		else
		{
			foreach($whArray as $wh)
			{
				$whHierachyArray = array();
				$this->getFullParentHierachy($wh->id, $whHierachyArray);	
				$whHierachyArray = array_reverse($whHierachyArray);
				
				$idArray = $nameArray = array();
				foreach($whHierachyArray as $w)
				{	
					$idArray[] = $w->id;
					$nameArray[] = $w->name;
				}
				
				$id = implode(">", $idArray);
				$name = implode(">", $nameArray);
				
				$output[] = array('id' => $id, 'value' => $name, 'label' => $name);
			}
		}

		return $output;
	}
	
	/**
	 * 
	 * @param unknown $id
	 */
	public function getWarehouse($id, $details = false)
	{
		if(($id = $this->_validateInput($id)) === false)
			throw new Exception('A warehouse id must be provided');
		
		if($details === false)
			return $this->_get($this->_tableName, $id);
		else
		{
			$this->db->select('wh.id as wh_id, wh.name as wh_name, wh.description as wh_description, wh.warehouse_type_id as wh_warehouse_type_id, wh.code as wh_code, wh.address_id as wh_address_id,
							  wht.id as wht_id, wht.name as wht_name, wh.parent_id as wh_parent_id, wh.position as wh_position, wh.root_id as wh_root_id, 
							  wh2.id as parent_id, wh2.name parent_name, wh2.description as parent_description, wh2.code as parent_code, 
							  wh3.id as root_id, wh3.name as root_name, wh3.code as root_code, wh3.description as root_description,
							  ad.id as address_id, ad.line_1 as address_line_1, ad.line_2 as address_line_2, ad.state_id as address_state_id, 
								s.id as state_id, s.name as state_name, s.abbreviation as state_abbreviation,
							  ad.postcode as address_postcode, ad.suburb as address_suburb')
					 ->from('warehouse as wh')
					 ->join('warehouse_type as wht', 'wht.id = wh.warehouse_type_id and wht.active = 1', 'left')
					 ->join('address as ad', 'ad.id = wh.address_id and ad.active = 1', 'left')
					 ->join('state as s', 's.id = ad.state_id', 'left')
					 ->join('warehouse as wh2', 'wh2.id = wh.parent_id and wh2.active = 1', 'left')
					 ->join('warehouse as wh3', 'wh3.id = wh.root_id', 'left')
					 ->where(array('wh.id' => $id));
			$query = $this->db->get();
			if($query->num_rows() > 0)
				return $query->row();
			else
				return false;
		}
	}
	
	/**
	 * 
	 * @param unknown $option
	 * @param unknown $orderBy
	 * @param unknown $limit
	 */
	public function getWarehouses(Array $option = array(), Array $orderBy = array(), Array $limit = array())
	{
		return $this->_getByCriteria($this->_tableName, $option, $orderBy, $limit);
	}
	
	public function getWarehouseType($whTypeId)
	{
		if(($whTypeId = $this->_validateInput($whTypeId)) === false)
			throw new Exception('An id must be provided to find the Warehouse Type');
		
		return $this->_get('warehouse_type', $whTypeId);
	}
	
	public function getWarehouseTypes(Array $option = array(), Array $orderBy = array(), Array $limit = array())
	{
		return $this->_getByCriteria('warehouse_type', $option, $orderBy, $limit);
	}
	
	public function extractIdsFromWarehouseHierachyArray($whArray, &$output)
	{
		foreach($whArray as $key => $value)
		{
			$output[] = $value['id'];
			$this->extractIdsFromWarehouseHierachyArray($value['children'], $output);
		}
	}
	
	/**
	 * This function finds all the warehouse in the system and organizes them in an array with the right hierachy and position order
	 * @param String $parentId
	 * @param string $rootId
	 * @param Array $output
	 */
	public function getAllWarehouseHierachy($parentId = 0, $rootId = 'id', Array &$output, $levelDepth = '*')
	{
		$queryPart = " root_id = ? ";
		$param = array($rootId);
		
		if(trim($rootId) === 'id')
		{	
			$queryPart = " root_id = ".$rootId." ";
			$param = array();
		}	
		
		$query = "select * from warehouse where parent_id = ? and ".$queryPart." order by parent_id ASC, position ASC";
		$queryParam = array($parentId);
		$queryParam = array_merge($queryParam, $param);
		
		$query = $this->db->query($query, $queryParam);
		if($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
			{
				$output[$row->id] = array();
				$output[$row->id]['id'] = trim($row->id);
				$output[$row->id]['name'] = trim($row->name);
				$output[$row->id]['code'] = trim($row->code);
				$output[$row->id]['warehouse_type_id'] = trim($row->warehouse_type_id);
				$output[$row->id]['parent_id'] = trim($row->parent_id);
				$output[$row->id]['position'] = trim($row->position);
				$output[$row->id]['root_id'] = trim($row->root_id);
				$output[$row->id]['address_id'] = trim($row->address_id);
				$output[$row->id]['children'] = array();
				
				if(!is_numeric($levelDepth) || $levelDepth > 0)
					$this->getAllWarehouseHierachy($row->id, $row->root_id, $output[$row->id]['children'], (is_numeric($levelDepth) ? $levelDepth - 1 : $levelDepth));
			}
		}
	}
	
	/**
	 * This function gets all the Child Warehouse(s) for a specific warehouse and outputs 
	 * them as an array maintaining the hierachy and order
	 * It can include itself it param is given
	 * This function uses the getAllWarehouseHierachy() function to find the results 
	 *  
	 * @param int $warehouseId
	 * @param string $includeSelf
	 * @throws Exception
	 * 
	 * @return Array
	 */
	public function getAllChildWarehouseHierachyForWarehouse($warehouseId, $includeSelf = false, $levelDepth = '*')
	{
		$warehouseId = $this->_validateInput($warehouseId);
		if($warehouseId === false)
			throw new Exception('A Warehouse must be specified to find all child warehouse underneath it');
		
		$output = $finalOutput = array();
		
		if($warehouseId == 0)
		{	
			$this->getAllWarehouseHierachy($warehouseId, 'id', $output, $levelDepth);
			$finalOutput = $output;
		}	
		else
		{
			$warehouse = $this->getWarehouse($warehouseId);
			if($warehouse === false)
				throw new Exception('Invalid Warehouse Id ['.$warehouseId.'] provided. No Warehouse Found');
			
			$this->getAllWarehouseHierachy($warehouse->id, $warehouse->root_id, $output, $levelDepth);
			
			if($includeSelf)
			{
				$finalOutput[$warehouse->id] = array();
				$finalOutput[$warehouse->id]['id'] = trim($warehouse->id);
				$finalOutput[$warehouse->id]['name'] = trim($warehouse->name);
				$finalOutput[$warehouse->id]['code'] = trim($warehouse->code);
				$finalOutput[$warehouse->id]['warehouse_type_id'] = trim($warehouse->warehouse_type_id);
				$finalOutput[$warehouse->id]['parent_id'] = trim($warehouse->parent_id);
				$finalOutput[$warehouse->id]['position'] = trim($warehouse->position);
				$finalOutput[$warehouse->id]['root_id'] = trim($warehouse->root_id);
				$finalOutput[$warehouse->id]['address_id'] = trim($warehouse->address_id);
				$finalOutput[$warehouse->id]['children'] = $output;
			}
			else
				$finalOutput = $output;	
		}

		return $finalOutput;
	}
	
	public function displayWarehouseHierachy(Array $warehouseArray, &$html)
	{
		if(count($warehouseArray) > 0)
		{	
			$html .= '<ul>';
			foreach($warehouseArray as $key => $value)
			{
				$html .= '<li wh_id = "'.$value['id'].'">'.$value['name'];
				$this->displayWarehouseHierachy($value['children'], $html);
				$html .= '</li>';
			}
			$html .= '</ul>';
		}	
	}
	
	public function displayAllWarehouseHierachy()
	{
		$warehouseArray = array();
		$this->getAllWarehouseHierachy(0, 'id', $warehouseArray, '*');
		
		$output = '';
		$this->displayWarehouseHierachy($warehouseArray, $output);
		
		return $output;
	}
	
	public function displayAllChildWarehouseHierachyForWarehouse($warehouseId, $includeSelf = false, $levelDepth = '*')
	{
		$warehouseArray = $this->getAllChildWarehouseHierachyForWarehouse($warehouseId, $includeSelf, $levelDepth);
		
		$output = '';
		$this->displayWarehouseHierachy($warehouseArray, $output);
		
		return $output;
	}
	
	public function converWarehouseArrayForFancyTree(Array $warehouseArray, Array &$output)
	{
		if(count($warehouseArray) > 0)
		{
			foreach($warehouseArray as $key => $value)
			{
				$tmp = array();
				$tmp['title'] = $value['name'];
				$tmp['name'] = $value['name'];
				$tmp['code'] = $value['code'];
				$tmp['warehouse_type_id'] = $value['warehouse_type_id'];
				$tmp['parent_id'] = $value['parent_id'];
				$tmp['root_id'] = $value['root_id'];
				$tmp['position'] = $value['position'];
				$tmp['address_id'] = $value['address_id'];
				$tmp['key'] = $value['id'];
				$tmp['children'] = array();
				$tmp['folder'] = false;
				$tmp['lazy'] = true;
				
				$this->converWarehouseArrayForFancyTree($value['children'], $tmp['children']);
				if(count($tmp['children']) > 0)
				{	
					$tmp['folder'] = true;
					unset($tmp['children']);
				}
				$output[] = $tmp;
			}				
		}
	}

	/**
	 * 
	 * @param unknown $parent
	 * @throws Exception
	 * @return string
	 */
	public function findNextAvailablePositionUnderParentWarehouse($parent)
	{
		if(!is_object($parent))
			throw new Exception('A Parent must be specified to find the an empty child position');
		
		$start = 1;
		$newPosition = false;
		
		if($parent->id == 0)
		{
			$childWarehouseArray = $this->getWarehouses(array('parent_id' => $parent->id), array('position' => 'ASC'), array());
			if(count($childWarehouseArray) <= 0)
				$newPosition = 1;
			else
			{
				$existingPositionArray = array();
				
				foreach($childWarehouseArray as $childWarehouse)
					$existingPositionArray[] = $childWarehouse->position;
				
				$positionFound = false;
				while($positionFound !== true)
				{
					$position = $start;
					if(!in_array($position, $existingPositionArray))
					{
						$positionFound = true;
						$newPosition = $position;
					}
					else
					{
						$start++;
						if($start > 9999)
							break;
					}
				}
			}
		}
		else
		{	
			$childWarehouseArray = $this->getWarehouses(array('parent_id' => $parent->id), array('position' => 'ASC'), array());
			if(count($childWarehouseArray) <= 0)
				$newPosition = $parent->position.str_pad($start, 4, '0', STR_PAD_LEFT);
			else
			{	
				$existingPositionArray = array();
				
				foreach($childWarehouseArray as $childWarehouse)
					$existingPositionArray[] = $childWarehouse->position;
				
				$positionFound = false;
				while($positionFound !== true)
				{
					$position = $parent->position.str_pad($start, 4, '0', STR_PAD_LEFT);
					if(!in_array($position, $existingPositionArray))
					{
						$positionFound = true;
						$newPosition = $position;
					}
					else
					{	
						$start++;
						if($start > 9999)
							break;
					}			
				}
			}
		}	

		if($newPosition === false)
			throw new Exception('Cannot find any empty position below the Parent Warehouse ['.$parent->name.']');
		
		return $newPosition;
	}
	
	/**
	 * This function does the basic validation check on the user input for warehouse insert/update
	 * It is used before W/H inser/update
	 * 
	 * @param Array $option
	 * @throws Exception
	 * @return Array
	 */
	private function _validateWarehouseInput(Array $option)
	{
		$errorArray = array();
		$parentWarehouse = false;
		
		if(!isset($option['parent_id']) || trim($option['parent_id']) === '')
			throw new Exception("A Parent must be specified to create/update warehouse");
		else
		{
			$currentWarehouse = false;
			if(isset($option['id']) && trim($option['id']) !== '' && $option['id'] != 0)
				$currentWarehouse = $this->getWarehouse($option['id']);
			
			if($option['parent_id'] == 0)
			{
				$option['parent_id'] = 0;	
				
				if($currentWarehouse === false || trim($currentWarehouse->parent_id) !== trim($option['parent_id']))
					$option['position'] = $this->findNextAvailablePositionUnderParentWarehouse((object)array('id' => 0));
				else
					$option['position'] = $currentWarehouse->position;						
			}
			else 
			{		
				if(($parentWarehouse = $this->getWarehouse($option['parent_id'])) === false)
					throw new Exception("Invalid Parent Warehouse provided");
				else
				{	
					if($currentWarehouse === false) 
					{		
						$option['parent_id'] = $parentWarehouse->id;
						$option['root_id'] = $parentWarehouse->root_id;
						$option['position'] = $this->findNextAvailablePositionUnderParentWarehouse($parentWarehouse);
					}
					else
					{
						if(trim($currentWarehouse->id) === trim($parentWarehouse->id))
							throw new Exception('You are trying to assign a parent warehouse which is itself!!!!');
						
						if(trim($option['parent_id']) !== trim($currentWarehouse->parent_id))
						{
							$childs = $childIdArray = array();
							$this->getAllWarehouseHierachy($currentWarehouse->id, $currentWarehouse->root_id, $childs, '*');
							$this->extractIdsFromWarehouseHierachyArray($childs, $childIdArray);
							
							if(in_array($parentWarehouse->id, $childIdArray))
								throw new Exception('You are trying to assign a Parent Warehouse that is one of the childs of the edit warehouse');
							
							$option['parent_id'] = $parentWarehouse->id;	
							$option['root_id'] = $parentWarehouse->root_id;	
							$option['position'] = $this->findNextAvailablePositionUnderParentWarehouse($parentWarehouse);
						}
						else
						{
							$option['parent_id'] = $parentWarehouse->id;								
							$option['root_id'] = $parentWarehouse->root_id;								
							$option['position'] = $currentWarehouse->position;								
						}	
					}		
				}
			}		
		}

		if(!isset($option['name']) || trim($option['name']) === '')
			throw new Exception("A Name must be specified to create/update warehouse");
		else
			$option['name'] = trim($option['name']);

		if(!isset($option['description']) || trim($option['description']) === '')
			throw new Exception("A Description must be specified to create/update warehouse");
		else
			$option['description'] = trim($option['description']);
		
		$option['code'] = trim($option['code']);
		
		if(!isset($option['warehouse_type_id']) || trim($option['warehouse_type_id']) === '')
			throw new Exception("A Warehouse type must be specified to create/update warehouse");
		else
			$option['warehouse_type_id'] = trim($option['warehouse_type_id']);
		
		$addressOption = array();
		if(isset($option['address_line_1']) && trim($option['address_line_1']) !== '')
			$addressOption['line_1'] = trim($option['address_line_1']);
		
		if(isset($option['address_line_2']) && trim($option['address_line_2']) !== '')
			$addressOption['line_2'] = trim($option['address_line_2']);
		
		if(isset($option['address_suburb']) && trim($option['address_suburb']) !== '')
			$addressOption['suburb'] = trim($option['address_suburb']);
		
		if(isset($option['address_state_id']) && trim($option['address_state_id']) !== '')
			$addressOption['state_id'] = trim($option['address_state_id']);
		
		if(isset($option['address_postcode']) && trim($option['address_postcode']) !== '')
			$addressOption['postcode'] = trim($option['address_postcode']);
		
		try 
		{
			$address = $this->customer->findAddress($addressOption);
			if($address === false)
				$option['address_id'] = $this->customer->createAddress($addressOption);
			else
				$option['address_id'] = $address->id;
		}
		catch(Exception $ex)
		{
			$option['address_id'] = 0;
		}
		
		unset($option['address_line_1']);
		unset($option['address_line_2']);
		unset($option['address_suburb']);
		unset($option['address_postcode']);
		unset($option['address_state_id']);

		$option['active'] = (!isset($option['active']) || trim($option['active']) === '') ? 1 : $option['active'];
		$option['created'] = date('Y-m-d H:i:s');
		$option['created_by_id'] = 1;
		$option['updated'] = date('Y-m-d H:i:s');
		$option['updated_by_id'] = 1;
		
		return $option;
	}
	
	/**
	 * 
	 * @param array $option
	 * @throws Exception
	 */
	public function addWarehouse(Array $option = array())
	{
		if(($option = $this->_validateInput($option, 'array')) === false)
			throw new Exception('No information provided for warehouse creation');
		
		$option = $this->_validateWarehouseInput($option);
		
		if(($duplicateWarehouse = $this->checkIfWarehouseExistsUnderParentWarehouse($option['parent_id'], $option['name'], $option['code'])) !== false)
			throw new Exception('A Warehouse ['.$duplicateWarehouse->name.'] already exists under the parent warehouse');
		
		$result = $this->db->insert('warehouse', $option);
		if(!$result)
			throw new Exception('Warehouse Add Failed. Query Failed');
		
		$newWarehouseId = $this->db->insert_id();
		
		if($option['parent_id'] == 0)
		{	
			$this->db->where(array('id' => $newWarehouseId));
			$this->db->update('warehouse', array('root_id' => $newWarehouseId));
		}

		return $newWarehouseId;
	}
	
	/**
	 * 
	 * @param unknown $warehouseId
	 * @param array $option
	 * @throws Exception
	 * @return Ambigous
	 */
	public function updateWarehouse($warehouseId, Array $option = array())
	{
		if(($warehouseId = $this->_validateInput($warehouseId)) === false)
			throw new Exception('A Warehouse id must be provided for warehouse update');
		
		if(($option = $this->_validateInput($option, 'array')) === false)
			throw new Exception('No information provided for warehouse update');
		
		$option['id '] = $warehouseId;
		
		$option = $this->_validateWarehouseInput($option);
		
		if(($duplicateWarehouse = $this->checkIfWarehouseExistsUnderParentWarehouse($option['parent_id'], $option['name'], $option['code'], $warehouseId)) !== false)
			throw new Exception('A Warehouse ['.$duplicateWarehouse->name.'] already exists under the parent warehouse');
		
		if($option['parent_id'] == 0)
			$option['root_id'] = $warehouseId;
		
		try
		{
			$this->db->trans_begin();
			
			$oldWarehouse = $this->getWarehouse($warehouseId);
			
			$this->db->where(array('id' => $warehouseId));
			$result = $this->db->update('warehouse', $option);
			if(!$result)
				throw new Exception('Failed to update warehouse. query failed');
			
			$newWarehouse = $this->getWarehouse($warehouseId);
			
			$childPositionUpdate = $this->updateAllChildWarehousePositionsForParentWarehouse($newWarehouse, $oldWarehouse);
			if($childPositionUpdate === false)
				throw new Exception('Failed to update child warehouse position for warehouse ['.$warehouseId.']');
			
			if($this->db->trans_status() === false)
				throw new Exception("Failed to update warehouse!!!!");
			else
				$this->db->trans_commit();
		}
		catch(Exception $ex)
		{
			$this->db->trans_rollback();
			throw $ex;
		}	
		
		return $warehouseId;
	}
	
	public function updateChildWarehousePositions($childArray, $newParentPosition, $newRootId)
	{
		foreach($childArray as $key => $value)
		{
			//$value['id']
			$actualPosition = substr($value['position'], -4);
			$newPosition = $newParentPosition.$actualPosition;
			
			$result = $this->db->query("update warehouse set position = ?, root_id = ? where id = ?", array($newPosition, $newRootId, $value['id']));
			if(!$result)
				throw new Exception('Unable to update warehouse position for warehouse ['.$value['id'].']');
			
			$this->updateChildWarehousePositions($value['children'], $newPosition, $newRootId);
		}
	}
	
	public function updateAllChildWarehousePositionsForParentWarehouse($newWarehouseData, $oldWarehouseData)
	{
		if(!is_object($newWarehouseData) || !is_object($oldWarehouseData))	
			return false;
		else
		{
			if(trim($newWarehouseData->position) !== trim($oldWarehouseData->position))
			{
				$childArray = array();
				$this->getAllWarehouseHierachy($oldWarehouseData->id, $oldWarehouseData->root_id, $childArray, '*');
				$this->updateChildWarehousePositions($childArray, $newWarehouseData->position, $newWarehouseData->root_id);
			}	
		}
		return true;	
	}
	
	public function reorderChildWarehouseSequence($parentId, Array $sortedChildWarehouseArray)
	{
		$parent = $this->warehouse->getWarehouse($parentId);
			
		$childWarehouseArray = $this->getAllChildWarehouseHierachyForWarehouse($parentId, false, 0);
		if(count(array_diff($sortedChildWarehouseArray, array_keys($childWarehouseArray))) > 0)
			throw new Exception('There are some child warehouse in the sorted list that does not belong the parent warehouse provided. Please Reload the page and try again');
			
		if(count(array_diff(array_keys($childWarehouseArray), $sortedChildWarehouseArray)) > 0)
			throw new Exception('The Sorted warehouse list is not complete. Please reload the page and try again');
			
		try
		{
			$this->db->trans_begin();
		
			for($i = 0; $i < count($sortedChildWarehouseArray); $i++)
			{
				if($parent !== false)
					$newPosition = $parent->position.str_pad(($i+1), 4, '0', STR_PAD_LEFT);
				else
					$newPosition = ($i+1);
								
				$oldWarehouseData = (object)$childWarehouseArray[$sortedChildWarehouseArray[$i]];
					
				$this->db->where(array('id' => $sortedChildWarehouseArray[$i]));
				$result = $this->db->update('warehouse', array('position' => $newPosition));
					
				if(!$result)
					throw new Exception('Unable to update Warehouse position for wh ['.$sortedChildWarehouseArray[$i].']');
						
				$newWarehouseData = $this->getWarehouse($sortedChildWarehouseArray[$i]);
						
				$this->updateAllChildWarehousePositionsForParentWarehouse($newWarehouseData, $oldWarehouseData);
			}
		
			if($this->db->trans_status() === false)
				throw new Exception("Failed to update warehouse!!!!");
			else
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
	 * @param unknown $parentId
	 * @param unknown $name
	 * @param unknown $code
	 * @param string $excludedId
	 * @throws Exception
	 * @return Ambigous <unknown>|boolean
	 */
	public function checkIfWarehouseExistsUnderParentWarehouse($parentId, $name, $code, $excludedId = false)
	{
		if(($name = trim($name)) === '' && ($code = trim($code)) === '')
			throw new Exception("A Name And/Or Code is required for duplicate warehouse check");
		
		if(($parentId = trim($parentId)) === '')
			throw new Exception('A parent must be specified for duplicate warehouse check');
		
		$option = array();
		
		if($code !== '')
			$option['code like'] = $code;
		else if($name !== '')
			$option['name like'] = $name;
		
		if($excludedId !== false || ($excludedId = trim($excludedId)) !== '')
			$option['id !='] = $excludedId;
		
		$option['parent_id'] = $parentId;
		
		$warehouseArray = $this->getWarehouses($option, array(), array('limit' => 1));
		if(count($warehouseArray) > 0)
			return $warehouseArray[0];
		
		return false;
	}

	public function getAllProductsForWarehouse($warehouseId, $onlySelf = false, $level = '*')
	{
		if($warehouseId === false || ($warehouseId = trim($warehouseId)) === '')
			throw new Exception('A warehouse must be specified to get a list of all the products list');
		
		$output = $whIdArray = [];
		
		if($onlySelf === false)
		{	
			$whArray = $this->getAllChildWarehouseHierachyForWarehouse($warehouseId, true, $level);
			$this->extractIdsFromWarehouseHierachyArray($whArray, $whIdArray);
			//$whIdArray[] = $warehouseId;
			$whIdArray = array_unique($whIdArray);
		}
		else
			$whIdArray[] = $warehouseId;	
		
		$this->db->from('product_instance')
				 ->where_in('warehouse_id', $whIdArray);
		$query = $this->db->get();
		if($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
				$output[] = $row;
		}

		return $output;
	}
	
	public function moveProductsFromWarehouseToWarehouse($sourceWarehouseId, $sourceOnlySelf = false, $sourceLevel = '*', $destinationWarehouseId)
	{
		$piArray = $this->getAllProductsForWarehouse($sourceWarehouseId, $sourceOnlySelf, $sourceLevel);
		if(count($piArray) > 0)
		{
			if($destinationWarehouseId === false || ($destinationWarehouseId = trim($destinationWarehouseId)) === '')
				$destinationWarehouseId = 0;
			
			$piArray = array_map(create_function('$a', 'return $a->id;'), $piArray);
			
			$this->db->where_in('id', $piArray);
			$result = $this->db->update('product_instance', array('warehouse_id' => $destinationWarehouseId, 'updated' => date('Y-m-d H:i:s'), 'updated_by_id' => 1));
			
			return $result;
		}
		
		return true;
	}
	
	public function preCheckDeleteWarehouse($whId)
	{
		if($whId === false || ($whId = trim($whId)) === '')
			throw new Exception('A warehouse must be specified for delete');
		
		if($whId === '0')
			throw new Exception("!!! Security Exception !!! You are trying to delete all the warehouse(s) in the system. PERMISSION DENIED");
		
		$warningArray = [];
		
		$childWarehouseArray = $this->getAllChildWarehouseHierachyForWarehouse($whId, false, '*');
		if(count($childWarehouseArray) > 0)
		{	
			$warningArray[] = "The warehouse you want to delete has child warehouse(s) underneath it. 
							   Deleting this warehouse will also delete all the child warehouse(s) beneath it.  
							   You can either proceed with the delete OR move all the child warehouse(s) into anothoer warehouse before delete";
		}

		$whIdArray = [];
		$this->extractIdsFromWarehouseHierachyArray($childWarehouseArray, $whIdArray);
		$whIdArray[] = $whId;
		$whIdArray = array_unique($whIdArray);

		$this->db->from('product_instance')
				  ->where_in('warehouse_id', $whIdArray);
		$query = $this->db->get();
		$productCount = $query->num_rows();
		
		if($productCount > 0)
		{
			$warningArray[] = "There are a total of [".$productCount."] products underneath this warehouse.
						  	   If you proceed on deleting this warehouse, All these products will be moved to the VIRTUAL FLOATING WAREHOUSE (if exists) / ORPHANED
						  	   Alternatively you can move all the stock to another location before delete";
		}
		
		$warningArray[] = "ARE YOU SURE YOU WANT TO DELETE THIS WAREHOUSE ?";
		
		return $warningArray;
	}
	
	public function deleteWarehouse($whId, $confirm = false)
	{
		if($whId === false || ($whId = trim($whId)) === '')
			throw new Exception('A warehouse must be specified for delete');
		
		if($whId === '0')
			throw new Exception("!!! Security Exception !!! You are trying to delete all the warehouse(s) in the system. PERMISSION DENIED");
		
		$destinationWhId = 0;
		$whArray = $this->getWarehouses(array('code' => WAREHOUSE_CODE_TEMP_HOLDER), array(), array('limit' => 1));
		if(count($whArray) > 0)
		{	
			$tempWarehouse = $whArray[0];
			$destinationWhId=  $tempWarehouse->id;
		}
		
		try
		{
			$this->db->trans_begin();
			
			$productMovementSuccess = $this->moveProductsFromWarehouseToWarehouse($whId, false, '*', $destinationWhId);
			if($productMovementSuccess)
			{
				$childWarehouseIdArray = [];
				$childWarehouseArray = $this->getAllChildWarehouseHierachyForWarehouse($whId, true, '*');
				$this->extractIdsFromWarehouseHierachyArray($childWarehouseArray, $childWarehouseIdArray);
				
				$this->db->where_in('id', $childWarehouseIdArray);
				$success = $this->db->delete('warehouse');
				if(!$success)
					throw new Exception('Warehouse delete failed. Query Failed');
			}
			else
				throw new Exception('Failed to move products from the warehouse and its child warehouse(s)');

			if($this->db->trans_status() === false)
				throw new Exception("QUER FAILED");
			
			$this->db->trans_commit();
		}
		catch(Exception $ex)
		{
			$this->db->trans_rollback();
			throw $ex;
		}	
		
		return true;
	}
}