<?php

class BasicAuth 
{
	/**
	 * The table name. MUST BE SET BEFORE CALLING THE CLASS
	 * @var String
	 */
	private static $_authTable = 'user';
	
	/**
	 * The identifer column name(s). MUST BE SET BEFORE CALLING THE CLASS
	 * @var Array
	 */
	private static $_identifierColumns = ['username'];
	
	/**
	 * The password column name(s) MUST BE SET BEFORE CALLING THE CLASS
	 * @var Array
	 */
	private static $_passwordColumns = ['password'];
	
	/**
	 * The encryption to use on the password column value
	 * by default it is sha1()
	 * 
	 * @param String $input
	 * @throws Exception
	 * @return string
	 */
	public static function encrypt($input)
	{
		if($input === false || ($input = trim($input)) === '')
			throw new Exception('Nothing to encrypt');
		
		/// do your own encryption mechanism here ///
		return sha1($input);
		////////////////////////////////////////////
	}
	
	/**
	 * Getter for table name
	 * @return string
	 */
	public static function getAuthTable()
	{
		return self::$_authTable;
	}
	
	/**
	 * SEtter for table name
	 * @param String $tableName
	 * @throws Exception
	 * @return boolean
	 */
	public static function setAuthTable($tableName)
	{
		if($tableName === false || ($tableName = trim($tableName)) === '')
			throw new Exception("A Table name must be specified");
		
		self::$_authTable = $tableName;
		return true;
	}
	
	/**
	 * Getter for identifier columns
	 * @return Array
	 */
	public static function getIdentifierColumns()
	{
		return self::$_identifierColumns;
	}

	/**
	 * Setter for identifier columns
	 * @param Array $columnArray
	 * @throws Exception
	 * @return boolean
	 */
	public static function setIdentiferColumns(Array $columnArray)
	{
		if(count($columnArray) <= 0)
			throw new Exception('A list of columns must be specified as identifier columns');
		
		self::$_identifierColumns = $columnArray;
		return true;
	}
	
	/**
	 * Getter for password column(s)
	 * @return Array
	 */
	public static function getPasswordColumns()
	{
		return self::$_passwordColumns;
	}
	
	/**
	 * Setter for password column(s)
	 * 
	 * @param array $columnArray
	 * @throws Exception
	 * @return boolean
	 */
	public static function setPasswordColumns(Array $columnArray)
	{
		if(count($columnArray) <= 0)
			throw new Exception('A list of columns must be specified as identifier columns');
		
		self::$_passwordColumns = $columnArray;
		return true;
	}

	/**
	 * Custom Pre authentication function 
	 * @param Any Data Type $data
	 * @return boolean
	 */
	private static function _preAuthenticate($data = false)
	{
		return true;
	}

	/**
	 * Custom post authentication function
	 * @param Any Data Type $data
	 * @return boolean
	 */
	private static function _postAuthenticate($data = false)
	{
		return true;
	}
	
	/**
	 * The main authentication function that authenticates the user log in
	 * @param Array $option
	 * @throws Exception
	 * @return Array
	 */
	public static function authenticate(Array $option = array())
	{
		$identifierColumnArray = self::getIdentifierColumns();
		$passwordColumnArray = self::getPasswordColumns();
		$tableName = trim(self::getAuthTable());
		
		if($tableName == '')
			throw new Exception('A Table name must be specified first');
		
		if(!is_array($identifierColumnArray) || count($identifierColumnArray) <= 0)
			throw new Exception('No Identifier columns specified. Please set the identifer columns first');
		
		if(!is_array($passwordColumnArray) || count($passwordColumnArray) <= 0)
			throw new Exception('No Password columns specified. Please set the password columns first');
		
		$missingIdentifiers = array_diff($identifierColumnArray, array_keys($option));
		if(count($missingIdentifiers) > 0)
			throw new Exception('The following identifier columns are missing ['.implode(", ", $missingIdentifiers).']');
		
		$missingPasswordColumns = array_diff($passwordColumnArray, array_keys($option));
		if(count($missingPasswordColumns) > 0)
			throw new Exception('The following password columns are missing ['.implode(", ", $missingPasswordColumns).']');
		
		/// do the custom pre auth check ///
		try
		{
			self::_preAuthenticate();
		}
		catch(Exception $ex)
		{	
			throw new Exception("Pre Authentication failed. Reason - [".$ex->getMessage()."]");
		}
		/////////////////////////////////////
		
		$ci = &get_instance();
		
		$criteria = $param = array();
		foreach($identifierColumnArray as $iColumn)
		{
			$criteria[] = $iColumn." = ? ";
			$param[] = $option[$iColumn]; 
		}
		foreach($passwordColumnArray as $pColumn)
		{
			$criteria[] = $pColumn." = ? ";
			$param[] = self::encrypt($option[$pColumn]);
		}		
		
		$query = "select * from ".self::$_authTable." where ".implode(" AND ", $criteria)." limit 1";
		$query = $ci->db->query($query, $param);
		
		if($query->num_rows() <= 0)
			throw new Exception('Invalid ['.implode(" OR ", $identifierColumnArray).' OR '.implode(" OR ", $passwordColumnArray).'] provided');
		else
			$row = $query->result();
		
		/// do the custom post auth check ///
		try
		{
			self::_postAuthenticate();
		}
		catch(Exception $ex)
		{	
			throw new Exception("Post Authentication failed. Reason - [".$ex->getMessage()."]");
		}
		/////////////////////////////////////////
		
		return json_decode(json_encode($row), true);
	}
	
	public static function test()
	{
		echo "fasfsdfsdf";
	}
}