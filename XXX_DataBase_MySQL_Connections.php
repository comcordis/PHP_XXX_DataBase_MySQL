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
	
	public static function add ($prefix = '', $name = '', $settings = array(), $deployEnvironment = false, $recycleName = false)
	{
		global $XXX_DataBase_MySQL_QueryTemplates;
	
		if ($prefix == '')
		{
			$prefix = self::$defaultPrefix;
		}
		
		$deployEnvironment = XXX::normalizeDeployEnvironment($deployEnvironment);
		
		if ($settings['characterSet'] == '')
		{
			$settings['characterSet'] = 'utf8';
		}
		
		if ($settings['collation'] == '')
		{
			$settings['collation'] = 'utf8_unicode_ci';
		}
		
		if ($settings['connectionType'] == '')
		{
			$settings['connectionType'] = 'readContent';
		}
		
		if (!XXX_Array::hasValue(self::$validConnectionTypes, $settings['connectionType']))
		{
			$settings['connectionType'] = 'readContent';
		}
		
		if ($settings['port'] == '')
		{
			$settings['port'] = 3306;
		}
		
		if ($settings['address'] == '')
		{
			$settings['address'] = '127.0.0.1';
		}
		
		$dataBase = $prefix . '_';
		$dataBase .= $deployEnvironment . '_';
		$dataBase .= $name;
		
		if ($settings['defaultDataBase'] == '')
		{
			$settings['defaultDataBase'] = $dataBase;
		}
		
		if (!XXX_Type::isArray($XXX_DataBase_MySQL_QueryTemplates[$prefix]))
		{
			$XXX_DataBase_MySQL_QueryTemplates[$prefix] = array();
		}
		if (!XXX_Type::isArray($XXX_DataBase_MySQL_QueryTemplates[$prefix][$name]))
		{
			$XXX_DataBase_MySQL_QueryTemplates[$prefix][$name] = array();
		}
		
		self::$dataBases[$name] = $dataBase;
		
		if ($recycleName !== false && array_key_exists($recycleName, self::$dataBases))
		{
			self::$connections[$name] =& self::$connections[$recycleName];
			
			self::$abstractionLayers[$name] =& self::$abstractionLayers[$recycleName];
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
		
		self::add('XXX', 'development', $settings);
		self::add('XXX', 'local', $settings, false, 'development');
		
		self::setDefaultPrefix(XXX::$deploymentInformation['project']);
	}
}

?>