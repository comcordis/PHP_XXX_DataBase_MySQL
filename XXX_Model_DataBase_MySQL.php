<?php

abstract class XXX_Model_DataBase_MySQL
{
	public static $project = false;
	
	public static function initialize ($project = '')
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
	}
}

?>