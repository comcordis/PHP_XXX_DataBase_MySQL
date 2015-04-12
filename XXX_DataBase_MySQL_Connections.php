<?php

abstract class XXX_DataBase_MySQL_Connections
{
	public static $defaultPrefix = 'XXX';
	
	public static $dataBases = array();	
	public static $connections = array();
	public static $abstractionLayers = array();
	
	public static $validConnectionTypes = array
	(
		'readContent',
		'writeContent',
		'content',
		'administration'
	);
	
	public static function setDefaultPrefix ($defaultPrefix = '')
	{
		self::$defaultPrefix = $defaultPrefix;
	}
	
	public static function add ($prefix = '', $name = '', $settings = array(), $deployEnvironment = false, $inheritFromName = false)
	{
		global $XXX_DataBase_MySQL_QueryTemplates;

		$inheritedSettings = array();

		if ($inheritFromName !== false && array_key_exists($inheritFromName, self::$dataBases))
		{
			$inheritedSettings = self::$connections[$inheritFromName]->getSettings();
		}

		if ($prefix)
		{
			$settings['prefix'] = $prefix;
		}
		else if ($inheritedSettings['prefix'])
		{
			$settings['prefix'] = $inheritedSettings['prefix'];
		}
		else
		{
			$settings['prefix'] = self::$defaultPrefix;
		}
						
		if ($deployEnvironment)
		{
			$settings['deployEnvironment'] = XXX::normalizeDeployEnvironment($deployEnvironment);
		}
		else if ($inheritedSettings['deployEnvironment'])
		{
			$settings['deployEnvironment'] = $inheritedSettings['deployEnvironment'];
		}
		else
		{
			$settings['deployEnvironment'] = XXX::$deploymentInformation['deployEnvironment'];
		}

		if ($settings['defaultDataBase'] == '')
		{
			$dataBase = $settings['prefix'] . '_';
			$dataBase .= $settings['deployEnvironment'] . '_';
			$dataBase .= $name;

			$settings['defaultDataBase'] = $dataBase;
		}
		
		if ($settings['characterSet'] == '')
		{
			if ($inheritedSettings['characterSet'])
			{
				$settings['characterSet'] = $inheritedSettings['characterSet'];
			}
			else
			{
				$settings['characterSet'] = 'utf8';
			}
		}
		
		if ($settings['collation'] == '')
		{
			if ($inheritedSettings['collation'])
			{
				$settings['collation'] = $inheritedSettings['collation'];
			}
			else
			{
				$settings['collation'] = 'utf8_unicode_ci';
			}
		}
		
		if ($settings['connectionType'] == '')
		{
			if ($inheritedSettings['connectionType'])
			{
				$settings['connectionType'] = $inheritedSettings['connectionType'];
			}
			else
			{
				$settings['connectionType'] = 'readContent';
			}
		}
		
		if (!XXX_Array::hasValue(self::$validConnectionTypes, $settings['connectionType']))
		{
			$settings['connectionType'] = 'readContent';
		}
		
		if ($settings['port'] == '')
		{
			if ($inheritedSettings['port'])
			{
				$settings['port'] = $inheritedSettings['port'];
			}
			else
			{
				$settings['port'] = 3306;
			}
		}
		
		if ($settings['address'] == '')
		{
			if ($inheritedSettings['address'])
			{
				$settings['address'] = $inheritedSettings['address'];
			}
			else
			{
				$settings['address'] = '127.0.0.1';
			}
		}

		$settings['name'] = $name;
		
		if (!XXX_Type::isArray($XXX_DataBase_MySQL_QueryTemplates[$settings['prefix']]))
		{
			$XXX_DataBase_MySQL_QueryTemplates[$settings['prefix']] = array();
		}
		if (!XXX_Type::isArray($XXX_DataBase_MySQL_QueryTemplates[$settings['prefix']][$name]))
		{
			$XXX_DataBase_MySQL_QueryTemplates[$settings['prefix']][$name] = array();
		}
		
		self::$dataBases[$name] = $settings['defaultDataBase'];
		
		if ($inheritFromName !== false && array_key_exists($inheritFromName, self::$dataBases))
		{
			self::$connections[$name] =& self::$connections[$inheritFromName];
			
			self::$abstractionLayers[$name] =& self::$abstractionLayers[$inheritFromName];
		}
		else
		{
			self::$connections[$name] = new XXX_DataBase_MySQL_Extension_MySQL($settings);
			
			self::$abstractionLayers[$name] = new XXX_DataBase_MySQL_AbstractionLayer_Administration();
			self::$abstractionLayers[$name]->open(self::$connections[$name]);
		}			
	}
	
	public static function initialize ()
	{
		$settings = array
		(
			'user' => 'root',
			'pass' => '',
			'connectionType' => 'administration'
		);
		
		self::add('XXX', 'local', $settings);
		self::add('XXX', 'default', $settings, false, 'local');
		
		self::setDefaultPrefix(XXX::$deploymentInformation['project']);
	}
}

?>