<?php

class MY_Model extends CI_Model
{
	/**
	 * 
	 * @param unknown $input
	 * @param string $type
	 * @return Ambigous <boolean, unknown, string>
	 */
	protected function _validateInput($input, $type = 'string')
	{
		$type = strtolower(trim($type));
		if($type === 'string')
			$input = (trim($input) !== '' ? trim($input) : false);
		else if($type === 'array')
			$input = ((is_array($input) && count($input) > 0) ? $input : false);
		else if($type === 'object')
			$input = (is_object($input) ? $input : false); 
		
		return $input;
	}
	
	/**
	 * 
	 * @param unknown $tableName
	 * @param unknown $id
	 * @throws Exception
	 * @return boolean
	 */
	protected function _get($tableName, $id)
	{
		if($tableName === false || ($tableName = trim($tableName)) === '')
			throw new Exception(__FUNCTION__."A Table name must be specified");
		
		if($id === false || ($id = trim($id)) === '')
			throw new Exception(__FUNCTION__."An ID name must be specified");
		
		$this->db->from($tableName)
				 ->where(array('id' => $id))
				 ->limit(1);
		$query = $this->db->get();
		
		return (($query->num_rows() > 0) ? $query->row() : false); 
	}
	
	/**
	 * This function is the base function for get on any MODEL
	 * 
	 * @param String $tableName
	 * @param Array $option
	 * @param Array $orderBy
	 * 
	 * @throws Exception
	 * @return Array of ActiveRecord(s)
	 */
	protected function _getByCriteria($tableName, Array $option = array(), Array $orderBy = array(), Array $limit = array())
	{
		if($tableName === false || ($tableName = trim($tableName)) === '')
			throw new Exception(__FUNCTION__."A Table name must be specified");
		
		$output = array();
		
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
		
		$this->db->from($tableName);
		$query = $this->db->get();

		if($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
				$output[] = $row;
		}
		
		return $output;
	}
	
	/**
	 * This function is the BASE function for any entry delete based on id
	 * It will also try to delete any dependencies related to the entity by foreign key
	 * 
	 * @param String $tableName
	 * @param String $id
	 * @param Array $dependents
	 * 
	 * @throws Exception	-- if any exception occurs
	 * @return boolean
	 * 			- true if success
	 */
	protected function _deleteById($tableName, $id, Array $dependents = array())
	{
		if($tableName === false || ($tableName = trim($tableName)) === '')
			throw new Exception('A Table name must be specified for function '.__FUNCTION__);
		
		if($id === false || ($id = trim($id)) === '')
			throw new Exception('An id must be specified for function '.__FUNCTION__);
		
		try
		{
			$this->db->trans_begin();
			
			if(count($dependents) > 0)
			{
				 foreach($dependents as $dd)
				 {
				 	$ddTable = (isset($dd['table_name']) ? trim($dd['table_name']) : '');
				 	$ddForeignKey = (isset($dd['foreign_key']) ? trim($dd['foreign_key']) : '');
				 	
				 	if($ddTable !== '' && $ddForeignKey !== '')
				 		$this->db->delete($ddTable, array($ddForeignKey => $id));
				 }	
			}
			
			$this->db->delete($tableName, array('id' => $id));

			if($this->db->trans_status() === false)
				throw new Exception('Query Failed for function '.__FUNCTION__);
			
			$this->db->trans_commit();
			
		}
		catch(Exception $ex)
		{
			$this->db->trans_rollback();
			throw $ex;
		}

		return true;
	}
	
	protected function _updateById($tableName, $id, Array $updateCriteria)
	{
		if($tableName === false || ($tableName = trim($tableName)) === '')
			throw new Exception('A Table name must be specified for function '.__FUNCTION__);
		
		if($id === false || ($id = trim($id)) === '')
			throw new Exception('An id must be specified for function '.__FUNCTION__);
		
		if(count($updateCriteria) <= 0)
			throw new Exception('Update criteria must be provided for function '.__FUNCTION__);
		
		$this->db->where(array('id' => $id));
		return $this->db->update($tableName, $updateCriteria);
	} 
	
	protected function _updateByCondition($tableName, Array $updateCriteria, Array $updateCondition)
	{
		if($tableName === false || ($tableName = trim($tableName)) === '')
			throw new Exception('A Table name must be specified for function '.__FUNCTION__);
		
		if(count($updateCriteria) <= 0)
			throw new Exception('Update Criteria must be provided for function'.__FUNCTION__);
		
		if(count($updateCondition) <= 0)
			throw new Exception('Update Condition must be provided for function'.__FUNCTION__);
		
		$this->db->where($updateCondition);
		return $this->db->update($tableName, $updateCriteria);
	}
	
}