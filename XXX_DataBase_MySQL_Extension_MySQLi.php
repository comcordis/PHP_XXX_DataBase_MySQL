<?php

class XXX_DataBase_MySQL_Extension_MySQLi
{
	const CLASS_NAME = 'XXX_DataBase_MySQL_Extension_MySQLi';
	
	protected $connection = false;
	
	protected $hasTriedToConnect = false;	
	protected $establishedConnection = false;
	
	protected $settings = array();
	
	protected $selectedDataBase = false;
	
	protected $lastError = false;
	
	public function __construct (array $settings)
	{
		$settings['inTransaction'] = false;
		
		$this->settings = $settings;
	}
		
	public function __destruct ()
	{
		return $this->disconnect();
	}
	
	public function getSettings ()
	{
		return $this->settings;
	}
	
	public function getSettingsDataBase ()
	{
		return $this->settings['defaultDataBase'];
	}
	
	public function getSettingsServer ()
	{
		return $this->settings['server_ID'];
	}
	
	public function hasEstablishedConnection ()
	{
		return $this->establishedConnection;
	}
	
	public function establishConnection ()
	{
		$result = false;
		
		if (!$this->hasTriedToConnect)
		{
			$this->hasTriedToConnect = true;
			
			$connected = false;
			
			if (XXX_Type::isValue($this->settings['address']) && XXX_Type::isValue($this->settings['user']))
			{
				$connected = $this->connect($this->settings['address'], $this->settings['user'], $this->settings['pass']);
			}
			
			if ($connected)
			{
				$this->establishedConnection = true;
						
				$setCharacterSetAndCollation = true;
				
				if (XXX_Type::isValue($this->settings['characterSet']) && XXX_Type::isValue($this->settings['collation']))
				{			
					$setCharacterSetAndCollation = $this->setCharacterSetAndCollation($this->settings['characterSet'], $this->settings['collation']);
				}
				
				if ($setCharacterSetAndCollation)
				{
					$selectedDataBase = true;
					
					if (XXX_Type::isValue($this->settings['defaultDataBase']) && $this->settings['connectionType'] != 'administration')
					{
						$selectedDataBase = $this->selectDataBase($this->settings['defaultDataBase']);
					}
					
					if ($selectedDataBase)
					{
						$result = true;
					}
					else
					{
						$this->disconnect();
					}
				}
				else
				{
					$this->disconnect();
				}
			}
			else
			{
				$this->disconnect();
			}
		}
		
		return $result;
	}
	
	public function connect ($address = '127.0.0.1', $user = 'root', $pass = '')
	{
		$result = true;
		
		$this->disconnect();
		
		if ($this->settings['persistent'])
		{
			$this->connection = new mysqli('p:' . $address, $user, $pass);
		}
		else
		{
			$this->connection = new mysqli($address, $user, $pass);
		}
		
		if ($this->connection->connect_errno)
		{
			$result = false;
			trigger_error('Unable to connect to address: "' . $address . '"<br>MySQL error: "' . $this->getLastMySQLError() . '"');
		}
				
		return $result;
	}
	
	public function disconnect ()
	{
		$result = true;
		
		if ($this->settings['inTransaction'])
		{
			$this->rollBackTransaction();	
		}
		
		if (!$this->settings['doNotDisconnect'])
		{
			if ($this->connection)
			{
				$result = $this->connection->close();
				$this->connection = false;
			}
		}
		
		$this->establishedConnection = false;
		
		return $result;
	}
	
	public function setCharacterSetAndCollation ($characterSet = 'utf8', $collation = 'utf8_unicode_ci')
	{
		$result = false;
		
		if (!$this->establishedConnection)
		{
			$this->establishConnection();
		}
		
		if ($this->establishedConnection)
		{		
			$setCharacterSet = $this->connection->set_charset($characterSet) && $this->query('SET CHARACTER SET "' . $characterSet . '"', false, 'all');
			$setNamesAndCollation = $this->query('SET NAMES "' . $characterSet . '" COLLATE "' . $collation . '"', false, 'all');
				
			if ($setCharacterSet && $setNamesAndCollation)
			{
				$result = true;
			}
			else
			{
				trigger_error('Unable to set character set to: "' . $characterSet . '" and collation to "' . $collation . '"<br>MySQL error: "' . $this->getLastMySQLError() . '"');
			}
		}
		
		return $result;
	}
	
	
	public function selectDataBase ($dataBase, $force = false)
	{
		$result = false;
		
		if (!$this->establishedConnection)
		{
			$this->establishConnection();
		}
		
		if ($this->establishedConnection)
		{
			if ($dataBase == $this->selectedDataBase && !$force)
			{
				$result = true;
			}
			else
			{
				$result = $this->query('USE ' . $dataBase . ';', false, 'all');
				
				$this->selectedDataBase = $dataBase;
			}
		}
		
		return $result;
	}
	
	public function getSelectedDataBase ()
	{
		return $this->selectedDataBase;
	}
			
	public function query ($query, $responseType = false, $requiredConnectionType = 'administration', $moveResultFromMySQLMemoryToPHPMemory = true)
	{
		$result = false;
		
		if (!$this->establishedConnection)
		{
			$this->establishConnection();
		}
		
		if ($this->establishedConnection)
		{
			if (XXX_DataBase_MySQL_Factory::validateConnectionTypeForQuery($this->settings['connectionType'], $requiredConnectionType))
			{			
				$queryStart = XXX_TimestampHelpers::getCurrentMillisecondTimestamp();
				
				if ($moveResultFromMySQLMemoryToPHPMemory)
				{
					$queryResult = $this->connection->query($query);
				}
				else
				{
					$queryResult = $this->connection->real_query($query);
				}
				
				$queryEnd = XXX_TimestampHelpers::getCurrentMillisecondTimestamp();
				
				if ($queryResult)
				{
					if (!$moveResultFromMySQLMemoryToPHPMemory)
					{
						$queryResult = $this->connection->use_result();
					}
					
					$result = array();
					
					switch ($responseType)
					{
						case 'ID':
							$result['ID'] = $this->connection->insert_id;
							break;
						case 'record':
							$result['record'] = array();
							$result['total'] = 0;
							
							$row = $queryResult->fetch_assoc();
							
							if ($row)
							{
								$result['record'] = $row;
								$result['total'] = 1;
							}
							
							$queryResult->free();
							break;
						case 'records':
							$result['records'] = array();
							while ($row = $queryResult->fetch_assoc())
							{
								$result['records'][] = $row;
							}
							$result['total'] = XXX_Array::getFirstLevelItemTotal($result['records']);
							
							$queryResult->free();
							break;
						case 'affected':
							$result['affected'] = $this->connection->affected_rows;
							break;
					}
					
					$result['success'] = true;
					$result['queryMillisecondTime'] = $queryEnd - $queryStart;
					
					/*XXX_Profiler::addTimeToGroupTiming('mySQL', $result['queryMillisecondTime']);
					XXX_Profiler::incrementGroupCounter('mySQL', 1);*/
				}
				else
				{
					$result = false;
					trigger_error('Unable to execute query:<br>' . $query . '<br>MySQL error: "' . $this->getLastMySQLError() . '"');
				}
			}
			else
			{
				$result = false;
				trigger_error('Required connectionType "' . $requiredConnectionType . '" doesn\'t work with the current connectionType "' . $this->settings['connectionType'] . '"<br>For query:<br>' . $query . '');
			}
		}
		
		return $result;
	}
		
	public function beginTransaction ()
	{
		$result = false;
		
		if (!$this->establishedConnection)
		{
			$this->establishConnection();
		}
		
		if ($this->establishedConnection)
		{
			if ($this->connection->autocommit(false))
			{
				$this->settings['inTransaction'] = true;
				
				$result = true;
			}
		}
		
		return $result;
	}
	
	public function isInTransaction ()
	{
		$result = false;
		
		if (!$this->establishedConnection)
		{
			$this->establishConnection();
		}
		
		if ($this->establishedConnection)
		{	
			$result = $this->settings['inTransaction'];
		}
		
		return $result;
	}
	
	public function commitTransaction ()
	{
		$result = false;
		
		if (!$this->establishedConnection)
		{
			$this->establishConnection();
		}
		
		if ($this->establishedConnection)
		{
			if ($this->settings['inTransaction'])
			{
				if ($this->connection->commit() && $this->connection->autocommit(true))
				{
					$this->settings['inTransaction'] = false;
					
					$result = true;
				}
			}
		}
		
		return $result;
	}
	
	public function rollBackTransaction ()
	{
		$result = false;
		
		if (!$this->establishedConnection)
		{
			$this->establishConnection();
		}
		
		if ($this->establishedConnection)
		{
			if ($this->settings['inTransaction'])
			{							
				if ($this->connection->rollBack() && $this->connection->autocommit(true))
				{
					$this->settings['inTransaction'] = false;
					
					$result = true;
				}
			}
		}
		
		return $result;
	}
		
	public function getLastMySQLError ()
	{
		$result = $this->lastError;
		
		if ($this->connection)
		{
			$result = $this->connection->error;
		}
		else
		{
			$result = mysqli_connect_error();
		}
		
		if ($result !== false)
		{
			$this->lastError = $result;
		}
		
		return $result;
	}
	
}

?>