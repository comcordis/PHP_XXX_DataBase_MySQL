<?php

abstract class XXX_DataBase_MySQL_Model
{
	public static $connection = false;
	
	public static function processArgumentConnection  ($connection)
	{
		if ($connection)
		{
			self::$connection = $connection;
		}
		
		return self::$connection !== false;
	}
	
	public static function setConnection  ($connection)
	{
		self::$connection = $connection;
		
		return self::$connection !== false;
	}
	
	public static function resetConnection ()
	{
		self::$connection = false;
		
		return false;
	}	
}

?>