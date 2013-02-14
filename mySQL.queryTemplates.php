<?php

global $XXX_DataBase_MySQL_QueryTemplates;
	
$XXX_DataBase_MySQL_QueryTemplates = array();

$XXX_DataBase_MySQL_QueryTemplates['Administration'] = array
(
 	// Configuration
		
		'getConfiguration' => array
		(
			'query' => '
				SHOW VARIABLES;
			',
			'responseType' => 'records',
			'requiredConnectionType' => 'administration'
		),
		
	// User & Rights
		
		'createUser' => array
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
		),
		
		'getUsersForDataBase' => array
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
		),
				
		'deleteUser' => array
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
		),
		
		'doesUserExist' => array
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
		),
		
		'grantDataBaseReadContentRightsToUser' => array
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
		),
		
		'grantDataBaseWriteContentRightsToUser' => array
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
		),
		
		'grantDataBaseContentRightsToUser' => array
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
		),
		
		'revokeAllRightsFromUser' => array
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
		),
		
		'grantRemoteAccessToRootUser' => array
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
		),
		
		'revokeRemoteAccessToRootUser' => array
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
		),
		
		'flushPrivileges' => array
		(
			'query' => '
				FLUSH PRIVILEGES;
			',
			'requiredConnectionType' => 'administration'
		),
		
	// DataBase
	
		'createDataBase' => array
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
		),
		
		'deleteDataBase' => array
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
		),
		
		'doesDataBaseExist' => array
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
		),
		
		'getDataBaseSize' => array
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
		),
		
		'getDataBases' => array
		(
			'query' => '
				SHOW DATABASES
			',
			'responseType' => 'records',
			'requiredConnectionType' => 'administration'
		),
	
	// Table
	
		'doesTableExist' => array
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
		),
				
		'deleteTable' => array
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
		),
		
		'resetTable' => array
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
		),
					
		'getTableSize' => array
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
		),
		
		'getTables' => array
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
		),
		
		'renameTable' => array
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
		),
		
		'swapTablesWithIdenticalStructure' => array
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
		),
		
		'getCreateTableQuery' => array
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
		),
		
		// Import & Export
		
			'exportTableDataToLocalFile' => array
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
			),
			
			'importTableDataFromLocalFile' => array
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
			),
					
	// Columns
	
		'getColumns' => array
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
		),
	
	// Test connection
		
		'testConnection' => array
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
		)
);
	
	

?>