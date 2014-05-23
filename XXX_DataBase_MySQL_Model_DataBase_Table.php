<?php

class XXX_DataBase_MySQL_Model_DataBase_Table
{
	public $project = false;
	public $abstractionLayer = false;
	public $dataBase = false;
	public $table = false;
	
	public function __construct ($project = '', $abstractionLayer = '', $dataBase = '', $table = '')
	{
		if ($project != '')
		{
			$this->project = $project;
		}
		
		if ($this->project == '')
		{
			if (XXX::$deploymentInformation['project'] != '')
			{
				$this->project = XXX::$deploymentInformation['project'];
			}
		}
				
		if ($abstractionLayer != '')
		{
			$this->abstractionLayer = $abstractionLayer;
		}
		
		if ($dataBase != '')
		{
			$this->dataBase = $dataBase;
		}
		
		if ($table != '')
		{
			$this->table = $table;
		}
	}
	
	public function getDataBaseAndTableForQuery ()
	{
		return XXX_DataBase_MySQL_Connections::$dataBases[$this->dataBase] . '.' . $this->table;
	}
	
	public function getQueryTemplatePrefix ()
	{
		return $this->project . '>' . $this->dataBase . '>' . $this->table . '>';
	}
	
	public function createQueryTemplateByArray ($name, $array = array())
	{
		return XXX_DataBase_MySQL_QueryTemplate::createByArray(self::getQueryTemplatePrefix() . $name, $array);
	}
	
	public function executeQueryTemplate ($name, $values = array(), $simplifyResult = false, $moveResultFromMySQLMemoryToPHPMemory = true)	
	{
		return XXX_DataBase_MySQL_Connections::$abstractionLayers[$this->abstractionLayer]->executeQueryTemplate(self::getQueryTemplatePrefix() . $name, $values, $simplifyResult, $moveResultFromMySQLMemoryToPHPMemory);
	}
	
	public function resetTable ()
	{
		return XXX_DataBase_MySQL_Connections::$abstractionLayers[$this->abstractionLayer]->resetTable(XXX_DataBase_MySQL_Connections::$dataBases[$this->dataBase], $this->table);
	}
	
	public function backUpTableStructure ()
	{
		return XXX_DataBase_MySQL_Connections::$abstractionLayers[$this->abstractionLayer]->backUpTableStructure(XXX_DataBase_MySQL_Connections::$dataBases[$this->dataBase], $this->table);
	}
	
	public function backUpTableData ()
	{
		return XXX_DataBase_MySQL_Connections::$abstractionLayers[$this->abstractionLayer]->backUpTableData(XXX_DataBase_MySQL_Connections::$dataBases[$this->dataBase], $this->table);
	}
}

?>