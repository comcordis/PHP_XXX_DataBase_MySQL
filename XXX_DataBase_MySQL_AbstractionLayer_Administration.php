<?php

class XXX_DataBase_MySQL_AbstractionLayer_Administration extends XXX_DataBase_MySQL_AbstractionLayer
{
	const CLASS_NAME = 'XXX_DataBase_MySQL_AbstractionLayer_Administration';
	
	// Configuration
	
		/*
		
		http://dev.mysql.com/doc/refman/5.0/en/server-system-variables.html#sysvar_character_set_database
		
		auto_increment_offset = offset (Default offset to start when generating IDs)
		auto_increment_increment = incrementStep (Default step when incrementing IDs)
		
		character_set_client = character set of input statements from a client
		character_set_connection = character set of literals and number-to-string conversion
		
		character_set_database = character set used by the default database.
		character_set_filesystem = character set used by the filesystem, string literals that refer to file names. (The path names will be converted from character_set_client to character_set_filesystem before opening)
		
		character_set_results = character set of output results and errors
		character_set_server = character set of the server (default)
		
		character_set_system = character set internal for storing identifiers (always utf-8)
		
		collation_connection = collation of the connection character set
		
		collation_database = collation used by the default database.
		collation_server = collation of the server
				
		storage_engine = default storage engine/tableType
		
		system_time_zone = The server system time zone.
		time_zone = the time_zone of the client
		
		*/
	
		public function getConfiguration ()
		{
			$result = $this->executeQueryTemplate('Administration>getConfiguration');
			
			if ($result !== false && $result['total'] > 0)
			{
				$processedVariables = array();
				
				foreach ($result['records'] as $record)
				{
					$processedVariables[$record['Variable_name']] = $record['Value'];
				}
							
				$result = array
				(
					'connectionSettings' => $this->connection->getSettings(),
					
					'autoIncrement' => array
					(
						'offset' => $processedVariables['auto_increment_offset'],
						'incrementStep' => $processedVariables['auto_increment_increment']
					),
					'version' => $processedVariables['version'],
					'versionComment' => $processedVariables['version_comment'],
					'lowerCaseTableNames' => $processedVariables['lower_case_table_names'],
					'lowerCaseFileSystem' => $processedVariables['lower_case_file_system'],
					'defaultStorageEngine' => $processedVariables['storage_engine'],
					'dataDirectory' => $processedVariables['datadir'],
					'temporaryDirectory' => $processedVariables['tmpdir'],
					
					'innoDB' => array
					(
						'dataFilePath' => $processedVariables['innodb_data_file_path'],
						'dataHomeDirectory' => $processedVariables['innodb_data_home_dir'],
						'filePerTable' => $processedVariables['innodb_file_per_table']
					),
					
					'timeZone' => array
					(
						'server' => $processedVariables['system_time_zone'],
						'client' => $processedVariables['time_zone']
					),
					
					'characterSet' => array
					(
						'client' => $processedVariables['character_set_client'],
						'connection' => $processedVariables['character_set_connection'],
						'defaultDataBase' => $processedVariables['character_set_database'],
						'fileSystem' => $processedVariables['character_set_filesystem'],
						'results' => $processedVariables['character_set_results'],
						'server' => $processedVariables['character_set_server']
					),
					
					'collation' => array
					(
						'connection' => $processedVariables['collation_connection'],
						'defaultDataBase' => $processedVariables['collation_database'],
						'server' => $processedVariables['collation_server']
					),
					
					'replication' => array
					(
						'serverID' => $processedVariables['server_id'],
						'binaryLog' => $processedVariables['log_bin'],
						
						'master' => array
						(
							'acceptConnectionsFromAddress' => $processedVariables['master_host'],
							'user' => $processedVariables['master_user'],
							'pass' => $processedVariables['master_password'],
							'port' => $processedVariables['master_port']
						)
					)
				);
				
			}
			else
			{
				$result = false;
			}
			
			return $result;
		}
	
	// User
	
		public function createUser ($user, $pass, $acceptConnectionsFromAddress = 'localhost')
		{
			$result = false;
			
			$result = $this->executeQueryTemplate('Administration>createUser', array($user, 'localhost', $pass));
			$result = $this->executeQueryTemplate('Administration>createUser', array($user, 'localhost.localdomain', $pass));
			$result = $this->executeQueryTemplate('Administration>createUser', array($user, '127.0.0.1', $pass));
						
			if (!($acceptConnectionsFromAddress == 'localhost' || $acceptConnectionsFromAddress == 'localhost.localdomain' || $acceptConnectionsFromAddress == '127.0.0.1'))
			{
				$result = $this->executeQueryTemplate('Administration>createUser', array($user, $acceptConnectionsFromAddress, $pass));
			}
			
			return $result;
		}
		
		public function createRandomUser ($acceptConnectionsFromAddress = 'localhost')
		{
			$result = false;
			
			// Create user
			$user = 'x' . XXX_String::getPart(XXX_String::getRandomHash(), 0, 15);
			$pass = XXX_String::getRandomHash();
			
			$createdUser = $this->createUser($user, $pass, $acceptConnectionsFromAddress);
			
			if ($createdUser)
			{
				$result = array
				(
					'user' => $user,
					'pass' => $pass,
					'acceptConnectionsFromAddress' => $acceptConnectionsFromAddress
				);
			}
			
			return $result;
		}
		
		public function deleteUser ($user, $acceptConnectionsFromAddress = 'localhost')
		{
			$result = false;
			
			$result = $this->revokeAllRightsFromUser($user, $acceptConnectionsFromAddress);
			
			if ($result)
			{
				$result = $this->executeQueryTemplate('Administration>deleteUser', array($user, 'localhost'));
				$result = $this->executeQueryTemplate('Administration>deleteUser', array($user, 'localhost.localdomain'));
				$result = $this->executeQueryTemplate('Administration>deleteUser', array($user, '127.0.0.1'));
				
				if (!($acceptConnectionsFromAddress == 'localhost' || $acceptConnectionsFromAddress == 'localhost.localdomain' || $acceptConnectionsFromAddress == '127.0.0.1'))
				{
					$result = $this->executeQueryTemplate('Administration>deleteUser', array($user, $acceptConnectionsFromAddress));
				}
			}
			
			return $result;
		}
		
		public function getUsersForDataBase ($dataBase)
		{
			$result = $this->executeQueryTemplate('Administration>getUsersForDataBase', array($dataBase));
			
			if ($result !== false && $result['total'] > 0)
			{
				$tempResult = array();
				
				if ($result['total'] > 0)
				{
					foreach ($result['records'] as $record)
					{
						$tempResult[] = array
						(
							'user' => $record['User'],
							'acceptConnectionsFromAddress' => $record['Host']
						);
					}
				}
				
				$result = $tempResult;
			}
			else
			{
				$result = false;
			}
			
			return $result;
		}
		
		public function deleteUsersForDataBase ($dataBase)
		{
			$result = false;
			
			$users = $this->getUsersForDataBase($dataBase);
			
			if ($users !== false)
			{
				$result = true;
				
				if (XXX_Array::getFirstLevelItemTotal($users) > 0)
				{
					$tempResult = true;
					
					foreach ($users as $user)
					{
						if (!($user['acceptConnectionsFromAddress'] == 'localhost' || $user['acceptConnectionsFromAddress'] == 'localhost.localdomain' || $user['acceptConnectionsFromAddress'] == '127.0.0.1'))
						{
							if (!$this->deleteUser($user['user'], $user['acceptConnectionsFromAddress']))
							{
								$tempResult = false;
							}
						}
					}
					
					$result = $tempResult;
				}
			}
			else
			{
				trigger_error('No users to delete.');
				
				$result = true;
			}
			
			return $result;
		}
		
		public function doesUserExist ($user, $acceptConnectionsFromAddress = 'localhost')
		{
			$result = $this->executeQueryTemplate('Administration>doesUserExist', array($user, $acceptConnectionsFromAddress));
			
			$result = ($result !== false && $result['total'] > 0);
			
			return $result;
		}
		
		public function grantDataBaseReadContentRightsToUser ($dataBase, $user, $acceptConnectionsFromAddress = 'localhost')
		{
			$result = $this->executeQueryTemplate('Administration>grantDataBaseReadContentRightsToUser', array($dataBase, $user, 'localhost'));
			$result = $this->executeQueryTemplate('Administration>grantDataBaseReadContentRightsToUser', array($dataBase, $user, 'localhost.localdomain'));
			$result = $this->executeQueryTemplate('Administration>grantDataBaseReadContentRightsToUser', array($dataBase, $user, '127.0.0.1'));
			
			if (!($acceptConnectionsFromAddress == 'localhost' || $acceptConnectionsFromAddress == 'localhost.localdomain' || $acceptConnectionsFromAddress == '127.0.0.1'))
			{
				$result = $this->executeQueryTemplate('Administration>grantDataBaseReadContentRightsToUser', array($dataBase, $user, $acceptConnectionsFromAddress));
			}
		
			return $result;
		}
		
		public function grantDataBaseWriteContentRightsToUser ($dataBase, $user, $acceptConnectionsFromAddress = 'localhost')
		{
			$result = $this->executeQueryTemplate('Administration>grantDataBaseWriteContentRightsToUser', array($dataBase, $user, 'localhost'));
			$result = $this->executeQueryTemplate('Administration>grantDataBaseWriteContentRightsToUser', array($dataBase, $user, 'localhost.localdomain'));
			$result = $this->executeQueryTemplate('Administration>grantDataBaseWriteContentRightsToUser', array($dataBase, $user, '127.0.0.1'));
			
			if (!($acceptConnectionsFromAddress == 'localhost' || $acceptConnectionsFromAddress == 'localhost.localdomain' || $acceptConnectionsFromAddress == '127.0.0.1'))
			{
				$result = $this->executeQueryTemplate('Administration>grantDataBaseWriteContentRightsToUser', array($dataBase, $user, $acceptConnectionsFromAddress));
			}
		
			return $result;
		}
		
		public function grantDataBaseContentRightsToUser ($dataBase, $user, $acceptConnectionsFromAddress = 'localhost')
		{
			$result = $this->executeQueryTemplate('Administration>grantDataBaseContentRightsToUser', array($dataBase, $user, 'localhost'));
			$result = $this->executeQueryTemplate('Administration>grantDataBaseContentRightsToUser', array($dataBase, $user, 'localhost.localdomain'));
			$result = $this->executeQueryTemplate('Administration>grantDataBaseContentRightsToUser', array($dataBase, $user, '127.0.0.1'));
			
			if (!($acceptConnectionsFromAddress == 'localhost' || $acceptConnectionsFromAddress == 'localhost.localdomain' || $acceptConnectionsFromAddress == '127.0.0.1'))
			{
				$result = $this->executeQueryTemplate('Administration>grantDataBaseContentRightsToUser', array($dataBase, $user, $acceptConnectionsFromAddress));
			}
		
			return $result;
		}
		
		public function revokeAllRightsFromUser ($user, $acceptConnectionsFromAddress = 'localhost')
		{
			$result = $this->executeQueryTemplate('Administration>revokeAllRightsFromUser', array($user, 'localhost'));
			$result = $this->executeQueryTemplate('Administration>revokeAllRightsFromUser', array($user, 'localhost.localdomain'));
			$result = $this->executeQueryTemplate('Administration>revokeAllRightsFromUser', array($user, '127.0.0.1'));
			
			if (!($acceptConnectionsFromAddress == 'localhost' || $acceptConnectionsFromAddress == 'localhost.localdomain' || $acceptConnectionsFromAddress == '127.0.0.1'))
			{
				$result = $this->executeQueryTemplate('Administration>revokeAllRightsFromUser', array($user, $acceptConnectionsFromAddress));
			}
		
			return $result;
		}
		
		public function grantRemoteAccessToRootUser ()
		{
			$result = false;
			
			$connectionSettings = $this->connection->getSettings();
			
			$pass = $connectionSettings['pass'];
			
			if ($pass)
			{			
				$result = $this->executeQueryTemplate('Administration>grantRemoteAccessToRootUser', array($pass));
			}
			
			return $result;
		}
		
		public function revokeRemoteAccessToRootUser ()
		{
			$result = false;
				
			$result = $this->executeQueryTemplate('Administration>revokeRemoteAccessToRootUser');
			
			return $result;
		}
		
		public function flushPrivileges ()
		{
			$result = false;
		
			$result = $this->executeQueryTemplate('Administration>flushPrivileges');
			
			return $result;
		}
		
		public function createRandomUserWithDataBaseRights ($dataBase, $acceptConnectionsFromAddress = 'localhost', $rightsType = 'readContent')
		{
			$result = false;
			
			$createdRandomUser = $this->createRandomUser($acceptConnectionsFromAddress);
			
			if ($createdRandomUser)
			{
				$this->revokeAllRightsFromUser($createdRandomUser['user'], $createdRandomUser['acceptConnectionsFromAddress']);
				
				switch ($rightsType)
				{
					case 'readContent':
						$grantedRightsToUser = $this->grantDataBaseReadContentRightsToUser($dataBase, $createdRandomUser['user'], $createdRandomUser['acceptConnectionsFromAddress']);
						break;
					case 'writeContent':
						$grantedRightsToUser = $this->grantDataBaseWriteContentRightsToUser($dataBase, $createdRandomUser['user'], $createdRandomUser['acceptConnectionsFromAddress']);
						break;
					case 'content':
						$grantedRightsToUser = $this->grantDataBaseContentRightsToUser($dataBase, $createdRandomUser['user'], $createdRandomUser['acceptConnectionsFromAddress']);
						break;
				}
				
				if ($grantedRightsToUser)
				{
					$result = array
					(
						'dataBase' => $dataBase,
						'user' => $createdRandomUser['user'],
						'pass' => $createdRandomUser['pass'],
						'acceptConnectionsFromAddress' => $createdRandomUser['acceptConnectionsFromAddress']
					);
				}
			}
			
			return $result;
		}
		
		public function createRandomUserWithDataBaseReadContentRights ($dataBase, $acceptConnectionsFromAddress = 'localhost')
		{
			return $this->createRandomUserWithDataBaseRights($dataBase, $acceptConnectionsFromAddress, 'readContent');
		}
		
		public function createRandomUserWithDataBaseWriteContentRights ($dataBase, $acceptConnectionsFromAddress = 'localhost')
		{
			return $this->createRandomUserWithDataBaseRights($dataBase, $acceptConnectionsFromAddress, 'writeContent');
		}
		
		public function createRandomUserWithDataBaseContentRights ($dataBase, $acceptConnectionsFromAddress = 'localhost')
		{
			return $this->createRandomUserWithDataBaseRights($dataBase, $acceptConnectionsFromAddress, 'content');
		}
		
	// DataBase
	
		public function createDataBase ($dataBase, $defaultCharacterSet = 'utf8', $defaultCollation = 'utf8_unicode_ci')
		{
			return $this->executeQueryTemplate('Administration>createDataBase', array($dataBase, $defaultCharacterSet, $defaultCollation));
		}
		
		public function deleteDataBase ($dataBase)
		{
			return $this->executeQueryTemplate('Administration>deleteDataBase', array($dataBase));
		}
		
		public function doesDataBaseExist ($dataBase)
		{
			$result = $this->executeQueryTemplate('Administration>doesDataBaseExist', array($dataBase));
			
			$result = ($result !== false && $result['total'] > 0);
			
			return $result;
		}
		
		public function getDataBaseSize ($dataBase)
		{
			$result = $this->executeQueryTemplate('Administration>getDataBaseSize', array($dataBase));
			
			$result = ($result !== false && $result['total'] > 0);
			
			return $result;
		}
		
		public function getDataBases ()
		{
			$result = $this->executeQueryTemplate('Administration>getDataBases');
			
			if ($result !== false && $result['total'] > 0)
			{
				$filteredResult = array();
				
				$nativeDataBases = array('mysql', 'information_schema', 'test');
				
				foreach ($result['records'] as $record)
				{
					if (!XXX_Array::hasValue($nativeDataBases, $record['Database']))
					{
						$filteredResult[] = $record['Database'];
					}
				}
				
				$result = $filteredResult;
			}
			else
			{
				$result = false;
			}
			
			return $result;
		}
	
	// Table
		
		public function doesTableExist ($dataBase, $table)
		{
			$result = $this->executeQueryTemplate('Administration>doesTableExist', array($dataBase, $table));
			
			$result = ($result !== false && $result['total'] > 0);
			
			return $result;
		}
		
		public function deleteTable ($dataBase, $table)
		{
			return $this->executeQueryTemplate('Administration>deleteTable', array($dataBase, $table));
		}
		
		public function resetTable ($dataBase, $table)
		{
			return $this->executeQueryTemplate('Administration>resetTable', array($dataBase, $table));
		}
		
		public function getTableSize ($dataBase, $table)
		{
			$result = $this->executeQueryTemplate('Administration>getTableSize', array($dataBase, $table));
			
			if ($result !== false && $result['total'] > 0)
			{
				$result = $result['records'][0];
			}
			else
			{
				$result = false;
			}
			
			return $result;
		}
		
		public function getTables ($dataBase)
		{
			$result = $this->executeQueryTemplate('Administration>getTables', array($dataBase));
			
			if ($result !== false && $result['total'] > 0)
			{
				$filteredResult = array();
				
				foreach ($result['records'] as $record)
				{
					$filteredResult[] = $record['Tables_in_' . $dataBase];
				}
				
				$result = $filteredResult;
			}
			else
			{
				$result = false;
			}
			
			return $result;
		}
		
		public function renameTable ($dataBase, $oldTable, $newTable)
		{
			$result = $this->executeQueryTemplate('Administration>renameTable', array($dataBase, $oldTable, $dataBase, $newTable));
			
			$result = ($result !== false && $result['total'] > 0);
			
			return $result;
		}
		
		public function swapTablesWithIdenticalStructure ($dataBase, $tableA, $tableB)
		{
			$result = $this->executeQueryTemplate('Administration>swapTablesWithIdenticalStructure', array($dataBase, $tableA, $dataBase, $dataBase, $tableB, $dataBase, $tableA, $dataBase, $dataBase, $tableB));
			
			$result = ($result !== false && $result['total'] > 0);
			
			return $result;
		}
		
		public function getCreateTableQuery ($dataBase, $table)
		{
			$result = $this->executeQueryTemplate('Administration>getCreateTableQuery', array($dataBase, $table));
			
			if ($result !== false && $result['total'] > 0)
			{
				$result = $result['record']['Create Table'];
			}
			else
			{
				$result = false;
			}
			
			return $result;
		}
		
		// Export
		
			/*
			
			Please note that the owner of the mysql process needs write access to the location
			
			*/
			
			public function exportTableStructureToLocalFile ($dataBase, $table, $outputFile)
			{
				XXX_FileSystem_Local::ensurePathExistenceByDestination($outputFile);
				
				$result = $this->getCreateTableQuery($dataBase, $table);
				
				if ($result !== false)
				{
					$result = XXX_FileSystem_Local::writeFileContent($outputFile, $result);
				}
				
				return $result;
			}
			
			/*
			
			-d = no data
			
			*/
			
			public function dumpTableStructureToLocalFile ($dataBase, $table, $outputFile)
			{
				$result = false;
				
				XXX_FileSystem_Local::ensurePathExistenceByDestination($outputFile);
				
				$connectionSettings = $this->connection->getSettings();
				
				$user = $connectionSettings['user'];
				$pass = $connectionSettings['pass'];
				$address = $connectionSettings['address'];
				
				$mysqlDumpCommand = '';
				$mysqlDumpCommand .= 'mysqldump';
				if (XXX_OperatingSystem::$platformName == 'windows')
				{
					$mysqlDumpCommand .= '.exe';
				}
				$mysqlDumpCommand .= ' -d -h ' . $address . ' --user=' . $user . ' --password=' . $pass;
				$mysqlDumpCommand .= ' ' . $dataBase;
				$mysqlDumpCommand .= ' ' . $table;
				$mysqlDumpCommand .= ' > ' . $outputFile;
				
				$commandResponse = XXX_CommandLineHelpers::executeCommand($mysqlDumpCommand);
				
				XXX_CommandLineHelpers::clearHistory();
					
				if ($commandResponse)
				{
					if ($commandResponse['statusCode'] == 0)
					{
						$result = true;
					}
				}
				
				return $result;
			}
			
			public function backUpTableStructure ($dataBase, $table, $method = 'dump', $outputFile = '')
			{
				$method = XXX_Default::toOption($method, array('dump', 'export'), 'dump');
				
				$result = false;
				
				if ($outputFile == '')
				{
					$outputFile = 'mySQL.' . $method . '.structure.' . $dataBase . '.' . $table;			
					$outputFile .= '.' . XXX_TimestampHelpers::getTimestampPartForFile() . '.sql';
					
					$outputFile = XXX_Path_Local::extendPath(XXX_Path_Local::$deploymentDataPathPrefix, array('backUps', 'dataBase', 'mySQL', $outputFile));
				}
				
				switch ($method)
				{
					case 'dump':
						$result = $this->dumpTableStructureToLocalFile($dataBase, $table, $outputFile);
						break;
					case 'export':
						$result = $this->exportTableStructureToLocalFile($dataBase, $table, $outputFile);
						break;
				}
				
				return $result;
			}
		
	// Column
		
		public function getColumns ($dataBase, $table)
		{
			$result = $this->executeQueryTemplate('Administration>getColumns', array($table, $dataBase));
			
			if ($result !== false && $result['total'] > 0)
			{
				$filteredResult = array();
				
				foreach ($result['records'] as $record)
				{
					$filteredResult[] = $record['Field'];
				}
				
				$result = $filteredResult;
			}
			else
			{
				$result = false;
			}
			
			return $result;
		}
		
			
	// Data
		
		// Export
		
			public function exportTableDataToLocalFile ($dataBase, $table, $outputFile)
			{
				XXX_FileSystem_Local::ensurePathExistenceByDestination($outputFile);
				
				$result = $this->executeQueryTemplate('Administration>exportTableDataToLocalFile', array($outputFile, $dataBase, $table));
				
				$result = ($result !== false);
				
				return $result;
			}
			
			/*
			
			-t = no table create
			-n = no dataBase create
			
			*/
			
			public function dumpTableDataToLocalFile ($dataBase, $table, $outputFile)
			{
				$result = false;
				
				XXX_FileSystem_Local::ensurePathExistenceByDestination($outputFile);
				
				$connectionSettings = $this->connection->getSettings();
				
				$user = $connectionSettings['user'];
				$pass = $connectionSettings['pass'];
				$address = $connectionSettings['address'];
				
				$mysqlDumpCommand = '';
				$mysqlDumpCommand .= 'mysqldump';
				if (XXX_OperatingSystem::$platformName == 'windows')
				{
					$mysqlDumpCommand .= '.exe';
				}
				$mysqlDumpCommand .= ' -t -n -h ' . $address . ' --user=' . $user . ' --password=' . $pass;
				$mysqlDumpCommand .= ' ' . $dataBase;
				$mysqlDumpCommand .= ' ' . $table;
				$mysqlDumpCommand .= ' > ' . $outputFile;
				
				$commandResponse = XXX_CommandLineHelpers::executeCommand($mysqlDumpCommand);
				
				XXX_CommandLineHelpers::clearHistory();
					
				if ($commandResponse)
				{
					if ($commandResponse['statusCode'] == 0)
					{
						$result = true;
					}
				}
							
				return $result;
			}
			
			public function backUpTableData ($dataBase, $table, $method = 'dump', $outputFile = '')
			{
				$method = XXX_Default::toOption($method, array('dump', 'export'), 'dump');
				
				$result = false;
				
				if ($outputFile == '')
				{
					$outputFile = 'mySQL.' . $method . '.data.' . $dataBase . '.' . $table;			
					$outputFile .= '.' . XXX_TimestampHelpers::getTimestampPartForFile() . '.sql';
					
					$outputFile = XXX_Path_Local::extendPath(XXX_Path_Local::$deploymentDataPathPrefix, array('backUps', 'dataBase', 'mySQL', $outputFile));
				}
				
				switch ($method)
				{
					case 'dump':
						$result = $this->dumpTableDataToLocalFile($dataBase, $table, $outputFile);
						break;
					case'export':
						$result = $this->exportTableDataToLocalFile($dataBase, $table, $outputFile);
						break;
				}
				
				return $result;
			}
		
		// Import
		
			public function importTableDataFromLocalFile ($dataBase, $table, $inputFile)
			{
				$result = $this->executeQueryTemplate('Administration>importTableDataFromLocalFile', array($inputFile, $dataBase, $table));
				
				$result = ($result !== false);
				
				return $result;
			}
		
	// Execute SQL file
		
		public function executeLocalSQLFile ($inputFile)
		{
			$result = false;
				
			$connectionSettings = $this->connection->getSettings();
			
			$user = $connectionSettings['user'];
			$pass = $connectionSettings['pass'];
			
			$mysqlCommand = '';
			$mysqlCommand .= 'mysql';
			if (XXX_OperatingSystem::$platformName == 'windows')
			{
				$mysqlDumpCommand .= '.exe';
			}
			$mysqlDumpCommand .= ' -h localhost --user=' . $user . ' --password=' . $pass;
			$mysqlCommand .= ' < ' . $inputFile;
			
			$commandResponse = XXX_CommandLineHelpers::executeCommand($mysqlCommand);
			
			XXX_CommandLineHelpers::clearHistory();
				
			if ($commandResponse)
			{
				if ($commandResponse['statusCode'] == 0)
				{
					$result = true;
				}
			}
						
			return $result;
		}
}

?>