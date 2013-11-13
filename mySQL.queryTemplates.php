<?php

global $XXX_DataBase_MySQL_QueryTemplates;
	
$XXX_DataBase_MySQL_QueryTemplates = array();

// Configuration

	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>getConfiguration', array
	(
		'query' => '
			SHOW VARIABLES;
		',
		'responseType' => 'records',
		'requiredConnectionType' => 'administration'
	));

// User & Rights

	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>createUser', array
	(
		'query' => '
			CREATE USER
				?@?
			IDENTIFIED BY
				?;
		',
		'inputFilters' => array
		(
			'string',
			'string',
			'string'
		),
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>getUsersForDataBase', array
	(
		'query' => '
			SELECT
				User,
				Host
			FROM
				mysql.db
			WHERE
				LOWER(Db) = LOWER(?)
			AND
				User LIKE "x%"
		',
		'inputFilters' => array
		(
			'string'
		),
		'responseType' => 'records',
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>deleteUser', array
	(
		'query' => '
			DROP USER
				?@?;
		',
		'inputFilters' => array
		(
			'string',
			'string'
		),
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>doesUserExist', array
	(
		'query' => '
			SELECT
				user
			FROM
				mysql.user
			WHERE
				user = ?
			AND
				host = ?;
		',
		'inputFilters' => array
		(
			'string',
			'string'
		),
		'responseType' => 'records',
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>grantDataBaseReadContentRightsToUser', array
	(
		'query' => '
			GRANT
				SELECT
			ON
				?.*
			TO
				?@?;
		',
		'inputFilters' => array
		(
			'raw',
			'string',
			'string'
		),
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>grantDataBaseWriteContentRightsToUser', array
	(
		'query' => '
			GRANT
				INSERT,
				UPDATE,
				DELETE
			ON
				?.*
			TO
				?@?;
		',
		'inputFilters' => array
		(
			'raw',
			'string',
			'string'
		),
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>grantDataBaseContentRightsToUser', array
	(
		'query' => '
			GRANT
				SELECT,
				INSERT,
				UPDATE,
				DELETE
			ON
				?.*
			TO
				?@?;
		',
		'inputFilters' => array
		(
			'raw',
			'string',
			'string'
		),
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>revokeAllRightsFromUser', array
	(
		'query' => '
			REVOKE
				ALL PRIVILEGES,
				GRANT OPTION
			FROM
				?@?;
		',
		'inputFilters' => array
		(
			'string',
			'string'
		),
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>grantRemoteAccessToRootUser', array
	(
		'query' => '
			GRANT
				ALL PRIVILEGES
			ON
				*.*
			TO
				\'root\'@\'%\'
			IDENTIFIED BY
				?
			WITH GRANT OPTION;
		',
		'inputFilters' => array
		(
			'string'
		),
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>revokeRemoteAccessToRootUser', array
	(
		'query' => '
			DELETE FROM
				mysql.user
			WHERE
				User = \'root\'
			AND
				Host = \'%\';
		',
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>flushPrivileges', array
	(
		'query' => '
			FLUSH PRIVILEGES;
		',
		'requiredConnectionType' => 'administration'
	));

// DataBase

	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>createDataBase', array
	(
		'query' => '
			CREATE DATABASE IF NOT EXISTS
				?
			DEFAULT CHARACTER SET
				?
			COLLATE
				?;
		',
		'inputFilters' => array
		(
			'raw',
			'raw',
			'raw'
		),
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>deleteDataBase', array
	(
		'query' => '
			DROP DATABASE
				?;
		',
		'inputFilters' => array
		(
			'raw'
		),
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>doesDataBaseExist', array
	(
		'query' => '
			SELECT
				SCHEMA_NAME
			FROM
				INFORMATION_SCHEMA.SCHEMATA
			WHERE
				SCHEMA_NAME = ?;
		',
		'inputFilters' => array
		(
			'string'
		),
		'responseType' => 'records',
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>getDataBaseSize', array
	(
		'query' => '
			SELECT
				table_schema AS "dataBase",
				SUM(data_length + index_length) AS size,
				SUM(data_length) AS dataSize,
				SUM(max_data_length) AS maxDataSize,
				SUM(index_length) AS indexSize,
				SUM(data_free) AS dataFree
			FROM
				INFORMATION_SCHEMA.tabLES
			WHERE
				TABLE_SCHEMA = ?;
		',
		'inputFilters' => array
		(
			'string'
		),				
		'responseType' => 'records',
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>getDataBases', array
	(
		'query' => '
			SHOW DATABASES
		',
		'responseType' => 'records',
		'requiredConnectionType' => 'administration'
	));

// Table

	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>doesTableExist', array
	(
		'query' => '
			SELECT
				TABLE_NAME
			FROM
				INFORMATION_SCHEMA.tabLES 
			WHERE
				TABLE_SCHEMA = ? 
			AND
				TABLE_NAME = ?;
		',
		'inputFilters' => array
		(
			'string',
			'string'
		),
		'responseType' => 'records',
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>deleteTable', array
	(
		'query' => '
			DROP TABLE
				?.?;
		',
		'inputFilters' => array
		(
			'raw',
			'raw'
		),
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>resetTable', array
	(
		'query' => '
			TRUNCATE TABLE
				?.?;
		',
		'inputFilters' => array
		(
			'raw',
			'raw'
		),
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>getTableSize', array
	(
		'query' => '
			SELECT
				table_name AS "table",
				(data_length + index_length) AS size,
				data_length AS dataSize,
				max_data_length AS maxDataSize,
				index_length AS indexSize,
				data_free AS dataFree
			FROM
				INFORMATION_SCHEMA.tabLES
			WHERE
				TABLE_SCHEMA = ?
			AND
				TABLE_NAME = ?;
		',
		'inputFilters' => array
		(
			'string',
			'string'
		),				
		'responseType' => 'records',
		'requiredConnectionType' => 'administration'	
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>getTables', array
	(
		'query' => '
			SHOW TABLES
			FROM
				?
		',
		'inputFilters' => array
		(
			'raw'
		),
		'responseType' => 'records',
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>renameTable', array
	(
		'query' => '
			RENAME TABLE
				?.? TO ?.?
		',
		'inputFilters' => array
		(
			'raw',
			'raw',
			'raw',
			'raw'
		),
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>swapTablesWithIdenticalStructure', array
	(
		'query' => '
			RENAME TABLE
				?.? TO ?.temporarySwapTable,
				?.? TO ?.?,
				?.temporarySwapTable TO ?.?;
		',
		'inputFilters' => array
		(
			'raw',
			'raw',
			'raw',
			'raw',
			'raw',					
			'raw',
			'raw',
			'raw',
			'raw',
			'raw'
		),
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>getCreateTableQuery', array
	(
		'query' => '
			SHOW CREATE TABLE
				?.?
		',
		'inputFilters' => array
		(
			'raw',
			'raw'
		),
		'responseType' => 'record',
		'requiredConnectionType' => 'administration'
	));

// Import & Export

	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>exportTableDataToLocalFile', array
	(
		'query' => '
			SELECT
				*
			INTO OUTFILE ?
			FIELDS TERMINATED BY \',\'
			OPTIONALLY ENCLOSED BY \'"\'
			LINES TERMINATED BY \'' . "\r\n" . '\'
			FROM
				?.?
		',
		'inputFilters' => array
		(
			'string',
			'raw',
			'raw'
		),
		'requiredConnectionType' => 'administration'
	));
	
	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>importTableDataFromLocalFile', array
	(
		'query' => '
			LOAD DATA INFILE ?
			INTO TABLE ?.?
			FIELDS TERMINATED BY \',\'
			OPTIONALLY ENCLOSED BY \'"\'
			LINES TERMINATED BY \'' . "\r\n" . '\'
		',
		'inputFilters' => array
		(
			'string',
			'raw',
			'raw'
		),
		'requiredConnectionType' => 'administration'
	));
	
// Columns

	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>getColumns', array
	(
		'query' => '
			SHOW COLUMNS
			FROM
				?
			FROM
				?
		',
		'inputFilters' => array
		(
			'raw',
			'raw'
		),
		'responseType' => 'records',
		'requiredConnectionType' => 'administration'
	));

// Test connection

	XXX_DataBase_MySQL_QueryTemplate::createByArray('Administration>testConnection', array
	(
		'query' => '
			SELECT
				NOW() as testConnection
		',
		'inputFilters' => array
		(
		),
		'responseType' => 'records',
		'requiredConnectionType' => 'administration'
	));

?>