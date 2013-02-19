<?php


class XXX_DataBase_MySQL_AbstractionLayer
{
	const CLASS_NAME = 'XXX_DataBase_MySQL_AbstractionLayer';
	
	protected $connection = false;
	
	protected $queryTemplates = array();
	
	public function open ($connection)
	{
		$this->connection = $connection;
		
		return ($this->connection === false ? false : true);
	}
	
	public function close ()
	{
		$result = false;
		
		if ($this->connection !== false)
		{
			$result = $this->connection->disconnect();
			
			$this->connection = false;
		}
		
		return $result;
	}
	
	public function selectDataBase ($dataBase, $force = false)
	{
		if (!XXX_Type::isValue($dataBase) && $this->connection !== false)
		{
			$tempSettings = $this->connection->getSettings();
			
			if (XXX_Type::isValue($tempSettings['dataBase']))
			{
				$dataBase = $tempSettings['dataBase'];
			}
		}	
	
		return ($this->connection !== false) ? $this->connection->selectDataBase($dataBase, $force) : false;
	}
		
	public function getSelectedDataBase ()
	{
		return ($this->connection !== false) ? $this->connection->getSelectedDataBase() : false;
	}
	
	public function getConnectionSettingsDataBase ()
	{
		return ($this->connection !== false) ? $this->connection->getSettingsDataBase() : false;
	}
	
	public function getConnectionSettingsServer ()
	{
		return ($this->connection !== false) ? $this->connection->getSettingsServer() : false;
	}
	
	public function testConnection ()
	{
		$result = false;
		
		$queryResult = $this->executeQueryTemplate('Administration>testConnection');
		
		if ($queryResult && $queryResult['total'] > 0)
		{
			$result = true;
		}
		
		return $result;
	}
	
	public function query ($query, $responseType = false, $requiredConnectionType = 'administration', $simplifyResult = false, $moveResultFromMySQLMemoryToPHPMemory = true)
	{
		$result = false;
		
		if ($this->connection !== false)
		{
			$result = $this->connection->query($query, $responseType, $requiredConnectionType, $moveResultFromMySQLMemoryToPHPMemory);
			
			if ($result)
			{
				if ($simplifyResult)
				{
					switch ($responseType)
					{
						case 'ID':
							$result = $result['ID'];
							break;
						case 'record':
							$result = $result['record'];
							break;
						case 'records':
							$result = $result['records'];
							break;
						case 'affected':
							$result = $result['affected'];
							break;
						case 'success':
							$result = $result['success'];
							break;
					}
				}
			}
		}
		
		return $result;
	}
	
	public function beginTransaction ()
	{
		return ($this->connection !== false) ? $this->connection->beginTransaction() : false;
	}
	
	public function isInTransaction ()
	{
		return ($this->connection !== false) ? $this->connection->isInTransaction() : false;
	}
	
	public function commitTransaction ()
	{
		return ($this->connection !== false) ? $this->connection->commitTransaction() : false;
	}
	
	public function rollbackTransaction ()
	{
		return ($this->connection !== false) ? $this->connection->rollbackTransaction() : false;
	}
	
	public function executeQueryTemplate ($name, $values = array(), $simplifyResult = false, $moveResultFromMySQLMemoryToPHPMemory = true)
	{
		$result = false;
		
		if ($values === false)
		{
			$values = array();
		}
		
		if ($this->connection !== false)
		{
			$queryTemplateID = XXX_DataBase_MySQL_QueryTemplate::getIDByName($name);
						
			if ($queryTemplateID !== false)
			{
				$processedQueryTemplateInput = XXX_DataBase_MySQL_QueryTemplate::processInput($queryTemplateID, $values);
				
				if ($processedQueryTemplateInput !== false)
				{
					if (XXX_Type::isEmpty($processedQueryTemplateInput['dataBase']) || $this->selectDataBase($processedQueryTemplateInput['dataBase']))
					{						
						$result = $this->query($processedQueryTemplateInput['queryString'], $processedQueryTemplateInput['responseType'], $processedQueryTemplateInput['requiredConnectionType'], false, $moveResultFromMySQLMemoryToPHPMemory);
						
						if ($result)
						{
							if (XXX_Type::isFilledArray($processedQueryTemplateInput['recordCasting']))
							{
								$result = XXX_DataBase_MySQL_QueryTemplate::processResult($result, $processedQueryTemplateInput['recordCasting']);
							}
							
							if (XXX_PHP::$debug)
							{
								$debugNotification = '';
								$debugNotification .= 'template: ' . $name . '<br>';
								if ($processedQueryTemplateInput['dataBase'])
								{
									$debugNotification .= 'dataBase: "' . $processedQueryTemplateInput['dataBase'] . '"<br>';
								}
								$debugNotification .= 'query:<br>' . $processedQueryTemplateInput['queryString'] . '<br>';
								
								switch ($processedQueryTemplateInput['responseType'])
								{
									case 'ID':
										$debugNotification .= 'Returned ID: <b>' . $result['ID'] . '</b><br>';
										break;
									case 'record':
										$debugNotification .= 'Returned <b>' . $result['total'] . '</b> record(s):<br>';
										
										$debugNotification .= '<pre>' . print_r($result['record'], true) . '</pre>';
										break;
									case 'records':
										$debugNotification .= 'Returned <b>' . $result['total'] . '</b> record(s):<br>';
										
										$debugNotification .= '<pre>' . print_r($result['records'], true) . '</pre>';
										break;
									case 'affected':
										$debugNotification .= 'Affected <b>' . $result['affected'] . '</b> record(s)<br>';
										break;
								}
								$debugNotification .= 'In <b>' . $result['queryMillisecondTime'] . 'ms</b>';
								
								trigger_error($debugNotification);
							}
							
							if ($simplifyResult)
							{
								switch ($processedQueryTemplateInput['responseType'])
								{
									case 'ID':
										$result = $result['ID'];
										break;
									case 'record':
										$result = $result['record'];
										break;
									case 'records':
										$result = $result['records'];
										break;
									case 'affected':
										$result = $result['affected'];
										break;
									case 'success':
										$result = $result['success'];
										break;
								}
							}
						}
						else
						{
							trigger_error($processedQueryTemplateInput['queryString'] . ' - ' . $this->connection->getLastMySQLError());
						}
					}
				}
			}			
		}		
		
		return $result;
	}
	
	
	
}

?>