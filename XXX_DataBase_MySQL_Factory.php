<?php

abstract class XXX_DataBase_MySQL_Factory
{	
	public static $connections = array();
		/*
	public static function getHostsWithServer ($hostSettingsOrKeyPath = '', $server_ID = '')
	{
		global $XXX_Resources_DataBase_MySQL_Hosts;
		
		if ($server_ID == '')
		{
			$server_ID = XXX_Server::$server_ID;
		}
		
		if ($hostSettingsOrKeyPath == '')
		{
			$hostSettingsOrKeyPath = $XXX_Resources_DataBase_MySQL_Hosts;
		}
		
		if (XXX_Type::isArray($hostSettingsOrKeyPath))
		{
			$hostSettings = $hostSettingsOrKeyPath;
		}
		else
		{
			$hostSettings = XXX_Array::traverseKeyPath($XXX_Resources_DataBase_MySQL_Hosts, $hostSettingsOrKeyPath);
		}
		
		return self::getHostsWithServerSub('', $hostSettings, $server_ID);
	}
	
		public static function getHostsWithServerSub ($keyPath = '', $hostSettings = array(), $server_ID = '')
		{
			$result = false;
			
			$hostsWithServer = array();
			
			if ($hostSettings['server_ID'] || $hostSettings['servers'])
			{
				$hostSettings = self::processHostSettings($hostSettings);
				
				foreach ($hostSettings['servers'] as $serverSettings)
				{
					if ($serverSettings['server_ID'] == $server_ID)
					{
						$hostsWithServer[] = $keyPath;
						break;
					}
				}
			}
			else
			{
				foreach ($hostSettings as $keyPathPart => $hostSettingsSub)
				{
					$tempKeyPath = $keyPath;
					
					if ($tempKeyPath != '')
					{
						$tempKeyPath .= '>';
					}
					
					$tempKeyPath .= $keyPathPart;
				
					$tempResult = self::getHostsWithServerSub($tempKeyPath, $hostSettingsSub, $server_ID);
					
					if ($tempResult)
					{
						foreach ($tempResult as $tempResultSub)
						{
							$hostsWithServer[] = $tempResultSub;
						}
					}
				}
			}
			
			if (XXX_Array::getFirstLevelItemTotal($hostsWithServer))
			{
				$result = $hostsWithServer;
			}
			
			return $result;
		}
		
	public static function doesHostHaveServer ($hostSettingsOrKeyPath = '', $server_ID = '')
	{
		return self::getHostsWithServer($hostSettingsOrKeyPath, $server_ID) !== false;
	}
	
	public static function create ($connectionIdentifier, $hostSettingsOrKeyPath, $connectionType = 'readContent', $server_ID = false)
	{
		global $XXX_Resources_DataBase_MySQL_Hosts;
		
		$result = false;
		
		if (XXX_Type::isArray($hostSettingsOrKeyPath))
		{
			$hostSettings = $hostSettingsOrKeyPath;
			$keyPath = '';
		}
		else
		{
			$hostSettings = XXX_Array::traverseKeyPath($XXX_Resources_DataBase_MySQL_Hosts, $hostSettingsOrKeyPath);
			$keyPath = $hostSettingsOrKeyPath;
		}
						
		if ($connectionType == 'readContent' || $connectionType == 'writeContent' || $connectionType == 'content' || $connectionType == 'administration')
		{
			$connectionIdentifier = $connectionIdentifier . '_' . $connectionType;			
			
			$result = self::testForExistingConnection($connectionIdentifier);
			
			if (!$result)
			{
				$hostSettings['connectionIdentifier'] = $connectionIdentifier;
				$hostSettings['connectionType'] = $connectionType;
				$hostSettings['keyPath'] = $keyPath;
				$hostSettings = self::processHostSettings($hostSettings);
				
				if (XXX_Array::getFirstLevelItemTotal($hostSettings['servers']) > 0)
				{
					$result = self::createNewConnection($hostSettings, $server_ID);
				}
			}
		}
		
		return $result;
	}
	
	public static function testForExistingConnection ($connectionIdentifier = '')
	{
		$result = false;
		
		// Check if a connection already exists
		foreach (self::$connections as $key => $connection)
		{
			if ($connectionIdentifier == $key)
			{
				$result = $connection;
				break;
			}
		}
		
		return $result;
	}
	
	public static function createNewConnection (array $hostSettings = array(), $server_ID = false)
	{
		$result = false;
		
		if ($server_ID)
		{
			foreach ($hostSettings['servers'] as $serverSettings)
			{
				if ($serverSettings['server_ID'] == $server_ID)
				{
					$connection = self::createExtensionConnection($serverSettings);
		
					$valid = true;
					
					if ($serverSettings['connectDirectly'])
					{
						$valid = $connection->establishConnection();
					}
					
					if ($connection !== false && $valid)
					{
						self::$connections[$serverSettings['connectionIdentifier']] = $connection;
						
						$result = $connection;
						break;
					}
				}
			}
		}
		// (Balance between alternatives)
		else
		{
			$masterTotal = 0;
			$slaveTotal = 0;
			
			if (XXX_Array::getFirstLevelItemTotal($hostSettings['servers']) > 0)
			{			
				// Process server settings (defaults etc.)
				foreach ($hostSettings['servers'] as $serverSettings)
				{
					switch ($serverSettings['type'])
					{
						case 'master':
							++$masterTotal;
							break;
						case 'slave':
						case 'slaveMaster':
							++$slaveTotal;
							break;
					}
				}
				
				// Random load balancer...
				$hostSettings['servers'] = XXX_Array::shuffle($hostSettings['servers']);
			}
			
			// If connectDirectly - Hot failover, keep trying to find one that works
			foreach ($hostSettings['servers'] as $serverSettings)
			{
				$try = false;
				
				if ($serverSettings['connectionType'] == 'readContent')
				{
					// There are slaves (Only try slaves & slaveMasters)
					if ($slaveTotal > 0 && ($serverSettings['replicationType'] == 'slave' || $serverSettings['replicationType'] == 'slaveMaster'))
					{
						$try = true;
					}
					// No slaves (Any)
					else
					{
						$try = true;
					}	
				}
				// writeContent, content, administration
				else
				{
					// There are slaves (Only try masters)
					if ($slaveTotal > 0 && $serverSettings['replicationType'] == 'master')
					{
						$try = true;
					}
					// No slaves (Any)
					else
					{
						$try = true;
					}
				}
				
				if ($try)
				{
					$connection = self::createExtensionConnection($serverSettings);
					
					if ($connection !== false)
					{
						$valid = true;
						
						if ($serverSettings['connectDirectly'])
						{
							$valid = $connection->establishConnection();
						}
						
						if ($valid)
						{
							self::$connections[$serverSettings['connectionIdentifier']] = $connection;
							
							$result = $connection;
							break;
						}
					}
				}
			}
		}
		
		return $result;
	}
	
	public static function processHostSettings (array $hostSettings = array())
	{
		// If just 1 server, reformat it.
		if (!$hostSettings['servers'] || (XXX_Array::getFirstLevelItemTotal($hostSettings['servers']) == 0 && XXX_Type::isValue($hostSettings['server_ID'])))
		{
			$hostSettings['servers'] = array($hostSettings);
		}
		
		if (!($hostSettings['extension'] == 'MySQL' || $hostSettings['extension'] == 'PDO' || $hostSettings['extension'] == 'MySQLi'))
		{
			$hostSettings['extension'] = 'MySQLi';
		}
		
		if (!XXX_Type::isPositiveInteger($hostSettings['port']))
		{
			$hostSettings['port'] = 3306;
		}
		
		if (!XXX_Type::isValue($hostSettings['characterSet']))
		{
			$hostSettings['characterSet'] = 'utf8';
		}
		
		if (!XXX_Type::isValue($hostSettings['collation']))
		{
			$hostSettings['collation'] = 'utf8_unicode_ci';
		}
		
		if (!XXX_Type::isBoolean($hostSettings['persistent']))
		{
			$hostSettings['persistent'] = false;
		}
		
		if (!XXX_Type::isBoolean($hostSettings['connectDirectly']))
		{
			$hostSettings['connectDirectly'] = false;
		}
		
		for ($i = 0, $iEnd = XXX_Array::getFirstLevelItemTotal($hostSettings['servers']); $i < $iEnd; ++$i)
		{
			$hostSettings['servers'][$i] = self::processServerSettings($hostSettings['servers'][$i], $hostSettings);
		}
		
		return $hostSettings;
	}
	
	public static function processServerSettings (array $serverSettings = array(), array $hostSettings = array())
	{
		global $XXX_Resources_Servers;
		
			$serverSettings['connectionIdentifier'] = $hostSettings['connectionIdentifier'];
			$serverSettings['connectionType'] = $hostSettings['connectionType'];
			$serverSettings['keyPath'] = $hostSettings['keyPath'];
			
			if (!XXX_Type::isValue($serverSettings['replicationType']))
			{
				$serverSettings['replicationType'] = 'master';
			}
			else
			{
				$serverSettings['replicationType'] = XXX_Default::toOption($serverSettings['replicationType'], array('slave', 'slaveMaster', 'master'), 'master');
			}
			
			if (!XXX_Type::isValue($serverSettings['extension']))
			{
				$serverSettings['extension'] = XXX_Type::isValue($hostSettings['extension']) ? $hostSettings['extension'] : 'MySQLi';
			}
			
			if (!XXX_Type::isValue($serverSettings['characterSet']))
			{
				$serverSettings['characterSet'] = XXX_Type::isValue($hostSettings['characterSet']) ? $hostSettings['characterSet'] : 'utf8';
			}
			
			if (!XXX_Type::isValue($serverSettings['collation']))
			{
				$serverSettings['collation'] = XXX_Type::isValue($hostSettings['collation']) ? $hostSettings['collation'] : 'utf8_unicode_ci';
			}
			
			if (!XXX_Type::isValue($serverSettings['defaultDataBase']))
			{
				if (XXX_Type::isValue($hostSettings['defaultDataBase']))
				{
					$serverSettings['defaultDataBase'] = $hostSettings['defaultDataBase'];
				}
			}
			
			$tempServer = XXX_Server::getServer($serverSettings['server_ID']);
			
			if (!XXX_Type::isValue($serverSettings['address']))
			{
				if (XXX_Server::isCurrentServer($serverSettings['server_ID']))
				{
					$serverSettings['address'] = $tempServer['address']['ipv4']['local']['ip'];
				}
				else
				{
					$currentServer = XXX_Server::getCurrentServer();
					
					// Same VLAN
					if ($currentServer['address']['ipv4']['private']['vlan'] == $tempServer['address']['ipv4']['private']['vlan'])
					{
						$serverSettings['address'] = $tempServer['address']['ipv4']['private']['ip'];
					}
					else
					{
						$serverSettings['address'] = $tempServer['address']['ipv4']['public']['ip'];
					}
				}
			}
			
			$serverSettings['local'] = ($serverSettings['server_ID'] == XXX_Server::$server_ID);
						
			if (!XXX_Type::isValue($serverSettings['port']))
			{
				if (XXX_Type::isValue($hostSettings['port']))
				{
					$serverSettings['port'] = $hostSettings['port'];
				}
			}
			
			$serverSettings['port'] = XXX_Default::toPositiveInteger($serverSettings['port'], 3306);
			
			if (!XXX_Type::isBoolean($serverSettings['persistent']))
			{
				$serverSettings['persistent'] = $hostSettings['persistent'];
			}
			
			$serverSettings['persistent'] = XXX_Default::toBoolean($serverSettings['persistent'], false);
			
			
			if (!XXX_Type::isBoolean($serverSettings['connectDirectly']))
			{
				$serverSettings['connectDirectly'] = $hostSettings['connectDirectly'];
			}
			
			$serverSettings['connectDirectly'] = XXX_Default::toBoolean($serverSettings['connectDirectly'], false);
			
			
			if ($serverSettings['connectionType'] == 'administration')
			{
				$serverSettings['user'] = $tempServer['mySQL']['users']['root']['user'];
				$serverSettings['pass'] = $tempServer['mySQL']['users']['root']['pass'];
			}
			else
			{
				$serverSettings['user'] = $serverSettings['users'][$serverSettings['connectionType']]['user'];
				$serverSettings['pass'] = $serverSettings['users'][$serverSettings['connectionType']]['pass'];
			}
			
			$serverSettings['inTransaction'] = false;
			
		return $serverSettings;
	}	
	
	public static function createExtensionConnection (array $serverSettings)
	{
		$result = false;
		
		if (XXX_Type::isValue($serverSettings['address']) && XXX_Type::isValue($serverSettings['user']) && XXX_Type::isValue($serverSettings['pass']))
		{						
			switch ($serverSettings['extension'])
			{
				case 'MySQL':
					if (XXX_PHP::hasExtension('mysql'))
					{
						$result = new XXX_DataBase_MySQL_Extension_MySQL($serverSettings);
					}
					break;
				case 'PDO':
					if (XXX_PHP::hasExtension('pdo'))
					{
						$result = new XXX_DataBase_MySQL_Extension_PDO($serverSettings);
					}
					break;
				case 'MySQLi':
				default:				
					if (XXX_PHP::hasExtension('mysqli'))
					{
						$result = new XXX_DataBase_MySQL_Extension_MySQLi($serverSettings);
					}
					break;
			}
		}
		
		return $result;
	}
	
*/
	public static function validateConnectionTypeForQuery ($currentConnectionType = 'readContent', $requiredConnectionType = 'administration')
	{
		$result = false;
		
		$currentConnectionType = XXX_Default::toOption($currentConnectionType, array('readContent', 'writeContent', 'content', 'administration'), 'readContent');
		$requiredConnectionType = XXX_Default::toOption($requiredConnectionType, array('all', 'readContent', 'writeContent', 'content', 'administration'), 'administration');
			
		switch ($requiredConnectionType)
		{
			case 'readContent':
				if ($currentConnectionType == 'readContent' || $currentConnectionType == 'content' || $currentConnectionType == 'administration')
				{
					$result = true;
				}
				break;
			case 'writeContent':
				if ($currentConnectionType == 'writeContent' || $currentConnectionType == 'content' || $currentConnectionType == 'administration')
				{
					$result = true;
				}
				break;
			case 'content':
				if ($currentConnectionType == 'content' || $currentConnectionType == 'administration')
				{
					$result = true;
				}
				break;
			case 'administration':				
				if ($currentConnectionType == 'administration')
				{
					$result = true;
				}
				break;
			case 'all':
				if ($currentConnectionType == 'readContent' || $currentConnectionType == 'writeContent' || $currentConnectionType == 'content' || $currentConnectionType == 'administration')
				{
					$result = true;
				}
				break;
		}
			
		return $result;
	}
	
}
?>