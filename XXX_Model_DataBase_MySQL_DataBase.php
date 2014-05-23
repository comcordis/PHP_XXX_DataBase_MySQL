<?php

abstract class XXX_Model_DataBase_MySQL_DataBase
{
	public static $project = false;
	public static $abstractionLayer = false;
	public static $dataBase = false;
	
	public static function initialize ($project = '', $abstractionLayer = '', $dataBase = '')
	{
		if ($project != '')
		{
			self::$project = $project;
		}
		
		if (self::$project == '')
		{
			if (XXX::$deploymentInformation['project'] != '')
			{
				self::$project = XXX::$deploymentInformation['project'];
			}
		}
		
		if ($abstractionLayer != '')
		{
			self::$abstractionLayer = $abstractionLayer;
		}
		
		if ($dataBase != '')
		{
			self::$dataBase = $dataBase;
		}
		
		XXX_DataBase_MySQL_Connections::add(false, self::$abstractionLayer, array(), false, 'local');
	}
	
	public static function createDataBase ()
	{
		$result = XXX_DataBase_MySQL_Connections::$abstractionLayers[self::$abstractionLayer]->createDataBase(XXX_DataBase_MySQL_Connections::$dataBases[self::$dataBase]);
		
		return $result;
	}
		
	public static function deleteDataBase ()
	{
		$result = XXX_DataBase_MySQL_Connections::$abstractionLayers[self::$abstractionLayer]->deleteDataBase(XXX_DataBase_MySQL_Connections::$dataBases[self::$dataBase]);
		
		return $result;
	}
}

?>