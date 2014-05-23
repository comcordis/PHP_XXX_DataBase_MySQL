<?php

class XXX_DataBase_MySQL_Model
{
	public $project = false;
	
	public function __construct ($project = '')
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
	}
}

?>