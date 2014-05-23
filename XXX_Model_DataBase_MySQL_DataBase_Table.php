<?php

abstract class XXX_Model_DataBase_MySQL_DataBase_Table
{
	public static $project = false;
	public static $abstractionLayer = false;
	public static $dataBase = false;
	public static $table = false;
	
	public static function initialize ($project = '', $abstractionLayer = '', $dataBase = '', $table = '')
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
		
		if ($table != '')
		{
			self::$table = $table;
		}
	}
	
	public static function getQueryTemplatePrefix ()
	{
		return self::$project . '>' . self::$dataBase . '>' . self::$table . '>';
	}
	
	public static function createQueryTemplateByArray ($name, $array = array())
	{
		return XXX_DataBase_MySQL_QueryTemplate::createByArray(self::getQueryTemplatePrefix() . $name, $array);
	}
	
	public static function executeQueryTemplate ($name, $values = array(), $simplifyResult = false, $moveResultFromMySQLMemoryToPHPMemory = true)	
	{
		return XXX_DataBase_MySQL_Connections::$abstractionLayers[self::$abstractionLayer]->executeQueryTemplate(self::getQueryTemplatePrefix() . $name, $values, $simplifyResult, $moveResultFromMySQLMemoryToPHPMemory);
	}
	
	public static function resetTable ()
	{
		$result = XXX_DataBase_MySQL_Connections::$abstractionLayers[self::$abstractionLayer]->resetTable(XXX_DataBase_MySQL_Connections::$dataBases[self::$dataBase], self::$table);
		
		return $result;
	}
	
	public static function backUpTableStructure ()
	{
		$result = XXX_DataBase_MySQL_Connections::$abstractionLayers[self::$abstractionLayer]->backUpTableStructure(XXX_DataBase_MySQL_Connections::$dataBases[self::$dataBase], self::$table);
	}
	
	public static function backUpTableData ()
	{
		$result = XXX_DataBase_MySQL_Connections::$abstractionLayers[self::$abstractionLayer]->backUpTableData(XXX_DataBase_MySQL_Connections::$dataBases[self::$dataBase], self::$table);
		
		return $result;
	}
}

?>