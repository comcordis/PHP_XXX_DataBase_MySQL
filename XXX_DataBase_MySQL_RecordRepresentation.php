<?php

/*

protected $columns = array
(
	'key' => array
	(
		'value' => array
		(
			'default' => '',
			'current' => '',
			'storage' => ''
		),		
		'valid' => true,
		'changed' => false,
		'actions' => array
		(
			'operation' => array(),
			'validation' => array()
		),
		'messages' => array
		(
			'operation' => array(),
			'validation' => array()
		)
	)
);

*/

class XXX_DataBase_MySQL_RecordRepresentation
{
	protected $templateName = '';
	
	protected $shard_ID = 0;
	protected $sharded = false;	
	
	protected $dataBase = '';
	protected $table = '';	
	protected $columns = array();
	
	protected $new = false;
	protected $changed = false;
	protected $valid = true;
	
	public function __construct ($templateName = false, $new = false)
	{
		$this->new = $new ? true: false;
		
		$this->setupDefaults($templateName);
	}
	
	public function setupDefaults ($templateName = false)
	{
		global $XXX_DataBase_MySQL_RecordRepresentationTemplates;
		
		if ($templateName)
		{
			$this->templateName = $templateName;
			
			$template = XXX_Array::traverseKeyPath($XXX_DataBase_MySQL_RecordRepresentationTemplates, $templateName);
			
			if ($template && $template['dataBase'])
			{
				if ($template['sharded'])
				{
					$this->sharded = true;
				}
				
				if ($template['dataBase'])
				{
					$this->dataBase = $template['dataBase'];
				}
				
				if ($template['table'])
				{
					$this->table = $template['table'];
				}
				
				if ($template['columns'])
				{
					$this->columns = $template['columns'];
				}
			}
			else
			{
				$template = false;
			}
		}
		
		foreach ($this->columns as $columnName => $column)
		{
			if (!XXX_Type::isArray($column))
			{
				$this->columns[$columnName] = array
				(
					'value' => array
					(						
						'default' => $column,
						'current' => $column,
						'storage' => $column
					)
				);
				
				$column = $this->columns[$columnName];
			}
			
			if ($column['value']['current'] == '')
			{
				$this->columns[$columnName]['value']['current'] = $column['value']['default'];
			}
			
			if ($column['value']['storage'] == '')
			{
				$this->columns[$columnName]['value']['storage'] = $column['value']['default'];
			}
			
			if ($column['mainDataType'] == '' || !XXX_Array::hasValue($column['mainDataType'], 'string', 'integer', 'boolean'))
			{
				$this->columns[$columnName]['mainDataType'] = 'integer';
			}
			
			$column['onStorageChange'] = XXX_Default::toOption($column['onStorageChange'], array('overwrite', 'rebaseDifference', 'leaveAsIs'), 'overwrite');
			
			$this->columns[$columnName]['changed'] = false;
			$this->columns[$columnName]['valid'] = true;
			
			if (!XXX_Type::isArray($this->columns[$columnName]['actions']))
			{
				$this->columns[$columnName]['actions'] = array();
			}
									
				if (!XXX_Type::isArray($this->columns[$columnName]['actions']['operation']))
				{
					$this->columns[$columnName]['actions']['operation'] = array();
				}
				
				if (!XXX_Type::isArray($this->columns[$columnName]['actions']['validation']))
				{
					$this->columns[$columnName]['actions']['validation'] = array();
				}
			
			if (!XXX_Type::isArray($this->columns[$columnName]['messages']))
			{
				$this->columns[$columnName]['messages'] = array();
			}
			
				if (!XXX_Type::isArray($this->columns[$columnName]['messages']['operation']))
				{
					$this->columns[$columnName]['messages']['operation'] = array();
				}
				
				if (!XXX_Type::isArray($this->columns[$columnName]['messages']['validation']))
				{
					$this->columns[$columnName]['messages']['validation'] = array();
				}
		}
				
		if ($template)
		{
			if ($template['defaultColumnActions'])
			{
				foreach ($template['defaultColumnActions'] as $columnName => $defaultColumnActions)
				{
					if (XXX_Type::isArray($defaultColumnActions))
					{
						foreach ($defaultColumnActions as $defaultColumnAction)
						{
							$this->addDefaultColumnActions($columnName, $defaultColumnAction);
						}
					}
					else
					{	
						$this->addDefaultColumnActions($columnName, $defaultColumnActions);
					}	
				}
			}
		}
		
		//$this->processActions();
	}
	
	public function resetAfterDelete ()
	{
		foreach ($this->columns as $columnName => $column)
		{
			$this->columns[$columnName]['value']['current'] = $this->columns[$columnName]['value']['default']; 
			$this->columns[$columnName]['value']['storage'] = $this->columns[$columnName]['value']['default']; 
			
			$this->columns[$columnName]['changed'] = false;
			$this->columns[$columnName]['valid'] = true;
			
			$this->columns[$columnName]['messages']['operation'] = array();
			$this->columns[$columnName]['messages']['validation'] = array();			
		}
		
		$this->shard_ID = 0;
		
		$this->new = false;
		$this->changed = false;
		$this->valid = true;
	}
	
	public function updateAfterSaveOrRetrieve ()
	{
		foreach ($this->columns as $columnName => $column)
		{
			$this->columns[$columnName]['value']['storage'] = $this->columns[$columnName]['value']['current'];
			
			$this->columns[$columnName]['changed'] = false;
			$this->columns[$columnName]['valid'] = true;
		}
		
		$this->new = false;
		$this->changed = false;
		$this->valid = true;
	}
	
	public function addDefaultColumnActions ($columnName = '', $defaultActions = '')
	{
		switch ($defaultActions)
		{
			case 'TINYINT_SIGNED':				
				$this->setColumnMainDataType($columnName, 'integer');
				
				$this->addColumnAction($columnName, 'validation', 'integer', '');
				$this->addColumnAction($columnName, 'validation', 'minimumInteger', '', -128);
				$this->addColumnAction($columnName, 'validation', 'maximumInteger', '', 127);
				break;
			case 'TINYINT_UNSIGNED':
				$this->setColumnMainDataType($columnName, 'integer');
				
				$this->addColumnAction($columnName, 'validation', 'integer', '');
				$this->addColumnAction($columnName, 'validation', 'minimumInteger', '', 0);
				$this->addColumnAction($columnName, 'validation', 'maximumInteger', '', 255);				
				break;
				
			case 'SMALLINT_SIGNED':
				$this->setColumnMainDataType($columnName, 'integer');
				
				$this->addColumnAction($columnName, 'validation', 'integer', '');
				$this->addColumnAction($columnName, 'validation', 'minimumInteger', '', -32768);
				$this->addColumnAction($columnName, 'validation', 'maximumInteger', '', 32767);
				break;
			case 'SMALLINT_UNSIGNED':
				$this->setColumnMainDataType($columnName, 'integer');
				
				$this->addColumnAction($columnName, 'validation', 'integer', '');
				$this->addColumnAction($columnName, 'validation', 'minimumInteger', '', 0);
				$this->addColumnAction($columnName, 'validation', 'maximumInteger', '', 65535);				
				break;
				
			case 'MEDIUMINT_SIGNED':
				$this->setColumnMainDataType($columnName, 'integer');
				
				$this->addColumnAction($columnName, 'validation', 'integer', '');
				$this->addColumnAction($columnName, 'validation', 'minimumInteger', '', -8388608);
				$this->addColumnAction($columnName, 'validation', 'maximumInteger', '', 8388607);
				break;
			case 'MEDIUMINT_UNSIGNED':
				$this->setColumnMainDataType($columnName, 'integer');
				
				$this->addColumnAction($columnName, 'validation', 'integer', '');
				$this->addColumnAction($columnName, 'validation', 'minimumInteger', '', 0);
				$this->addColumnAction($columnName, 'validation', 'maximumInteger', '', 16777215);				
				break;
				
			case 'INT_SIGNED':
				$this->setColumnMainDataType($columnName, 'integer');
				
				$this->addColumnAction($columnName, 'validation', 'integer', '');
				$this->addColumnAction($columnName, 'validation', 'minimumInteger', '', -2147483648);
				$this->addColumnAction($columnName, 'validation', 'maximumInteger', '', 2147483647);				
				break;
			case 'INT_UNSIGNED':
				$this->setColumnMainDataType($columnName, 'integer');
				
				$this->addColumnAction($columnName, 'validation', 'integer', '');
				$this->addColumnAction($columnName, 'validation', 'minimumInteger', '', 0);
				$this->addColumnAction($columnName, 'validation', 'maximumInteger', '', 16777215);				
				break;
				
			case 'BIGINT_SIGNED':
				$this->setColumnMainDataType($columnName, 'integer');
				
				$this->addColumnAction($columnName, 'validation', 'integer', '');
				$this->addColumnAction($columnName, 'validation', 'minimumInteger', '', -9223372036854775808);
				$this->addColumnAction($columnName, 'validation', 'maximumInteger', '', 9223372036854775807);	
				break;
			case 'BIGINT_UNSIGNED':
				$this->setColumnMainDataType($columnName, 'integer');
				
				$this->addColumnAction($columnName, 'validation', 'integer', '');
				$this->addColumnAction($columnName, 'validation', 'minimumInteger', '', 0);
				$this->addColumnAction($columnName, 'validation', 'maximumInteger', '', 18446744073709551614);	
				break;
				
			case 'BOOL':
				$this->setColumnMainDataType($columnName, 'boolean');
				
				$this->addColumnAction($columnName, 'validation', 'isPredefinedValue', '', array('predefinedValues' => array(0, 1)));
				break;
				
			case 'VARCHAR_16':
				$this->setColumnMainDataType($columnName, 'string');
				
				$this->addColumnAction($columnName, 'operation', 'string', '');
				$this->addColumnAction($columnName, 'validation', 'minimumCharacterLength', '', 0);
				$this->addColumnAction($columnName, 'validation', 'maximumCharacterLength', '', 16);
				break;
			case 'VARCHAR_24':
				$this->setColumnMainDataType($columnName, 'string');
				
				$this->addColumnAction($columnName, 'operation', 'string', '');
				$this->addColumnAction($columnName, 'validation', 'minimumCharacterLength', '', 0);
				$this->addColumnAction($columnName, 'validation', 'maximumCharacterLength', '', 24);
				break;
			case 'VARCHAR_32':
				$this->setColumnMainDataType($columnName, 'string');
				
				$this->addColumnAction($columnName, 'operation', 'string', '');
				$this->addColumnAction($columnName, 'validation', 'minimumCharacterLength', '', 0);
				$this->addColumnAction($columnName, 'validation', 'maximumCharacterLength', '', 32);
				break;
			case 'VARCHAR_64':
				$this->setColumnMainDataType($columnName, 'string');
				
				$this->addColumnAction($columnName, 'operation', 'string', '');
				$this->addColumnAction($columnName, 'validation', 'minimumCharacterLength', '', 0);
				$this->addColumnAction($columnName, 'validation', 'maximumCharacterLength', '', 64);
				break;
			case 'VARCHAR_128':
				$this->setColumnMainDataType($columnName, 'string');
				
				$this->addColumnAction($columnName, 'operation', 'string', '');
				$this->addColumnAction($columnName, 'validation', 'minimumCharacterLength', '', 0);
				$this->addColumnAction($columnName, 'validation', 'maximumCharacterLength', '', 128);
				break;
			case 'VARCHAR_140':
				$this->setColumnMainDataType($columnName, 'string');
				
				$this->addColumnAction($columnName, 'operation', 'string', '');
				$this->addColumnAction($columnName, 'validation', 'minimumCharacterLength', '', 0);
				$this->addColumnAction($columnName, 'validation', 'maximumCharacterLength', '', 255);
				break;
			case 'VARCHAR_255':
				$this->setColumnMainDataType($columnName, 'string');
				
				$this->addColumnAction($columnName, 'operation', 'string', '');
				$this->addColumnAction($columnName, 'validation', 'minimumCharacterLength', '', 0);
				$this->addColumnAction($columnName, 'validation', 'maximumCharacterLength', '', 255);
				break;
				
			case 'CHAR_32':
				$this->setColumnMainDataType($columnName, 'string');
				
				$this->addColumnAction($columnName, 'operation', 'string', '');
				$this->addColumnAction($columnName, 'validation', 'minimumCharacterLength', '', 32);
				$this->addColumnAction($columnName, 'validation', 'maximumCharacterLength', '', 32);
				break;
				
			case 'CHAR_128':
				$this->setColumnMainDataType($columnName, 'string');
				
				$this->addColumnAction($columnName, 'operation', 'string', '');
				$this->addColumnAction($columnName, 'validation', 'minimumCharacterLength', '', 128);
				$this->addColumnAction($columnName, 'validation', 'maximumCharacterLength', '', 128);
				break;
						
			case 'TEXT':
				$this->setColumnMainDataType($columnName, 'string');
				
				$this->addColumnAction($columnName, 'operation', 'string', '');
				$this->addColumnAction($columnName, 'validation', 'minimumCharacterLength', '', 0);
				$this->addColumnAction($columnName, 'validation', 'maximumCharacterLength', '', 65535);
				break;
		}
	}
	
	public function setShard_ID ($shard_ID = 0)
	{
		$this->shard_ID = $shard_ID;
	}
	
	public function getShard_ID ()
	{
		return $this->shard_ID;
	}
		
	public function setSharded ($sharded = true)
	{
		$this->sharded = $sharded ? true : false;
	}
	
	public function getSharded ()
	{
		return $this->sharded;
	}
	
	public function setDataBase ($dataBase = 'dataBase')
	{
		$this->dataBase = $dataBase;
	}
	
	public function getDataBase ()
	{
		return $this->dataBase;
	}
	
	public function setTable ($table = 'table')
	{
		$this->table = $table;
	}
	
	public function getTable ()
	{
		return $this->table;
	}
	
	public function setColumnDefaultValue ($columnName = '', $columnDefaultValue = '')
	{
		$result = false;
			
		if (XXX_Array::hasKey($this->columns, $columnName))
		{
			$this->columns[$columnName]['value']['default'] = $columnDefaultValue;
			
			$result = true;
		}
		
		return $result;
	}
	
	public function setColumnMainDataType ($columnName = '', $columnMainDataType = '')
	{
		$result = false;
			
		if (XXX_Array::hasKey($this->columns, $columnName))
		{
			$this->columns[$columnName]['mainDataType'] = $columnMainDataType;
			
			$result = true;
		}
		
		return $result;
	}
	
	public function getQueryTemplateInformation ()
	{
		$result = array
		(
			'dataBase' => $this->dataBase,
			'table' => $this->table,
			'sharded' => $this->sharded,
			'shard_ID' => $this->shard_ID,
			'columns' => array()
		);
		
		$columnTotal = 0;
		foreach ($this->columns as $columnName => $column)
		{
			$result['columns'][$columnName] = $column['mainDataType'];
			++$columnTotal;
		}
		
		$result['columnTotal'] = $columnTotal;
		
		return $result;
	}
					
	// Actions
	
		public function resetColumnActions ($columnName = '')
		{
			$result = false;
			
			if (XXX_Array::hasKey($this->columns, $columnName))
			{
				$this->columns[$columnName]['actions'] = array
				(
					'operation' => array(),
					'validation' => array()
				);
				
				$result = true;
			}
			
			return $result;
		}
		
		public function addColumnAction ($columnName = '', $actionType = 'validation', $action = '', $texts = '', $parameters = array(), $side = 'both')
		{
			$result = false;
			
			if ($actionType == 'operation' || $actionType == 'validation')
			{
				if ($side == 'both' || $side == 'client' || $side == 'server')
				{
					if (XXX_Array::hasKey($this->columns, $columnName))
					{			
						$this->columns[$columnName]['actions'][$actionType][] = array
						(
							'action' => $action,
							'texts' => $texts,
							'parameters' => $parameters,
							'side' => $side
						);
						
						$result = true;
					}
				}
			}
			
			return $result;
		}
	
	// Messages    
	
		public function resetColumnMessages ($columnName = '')
		{
			$result = false;
			
			if (XXX_Array::hasKey($this->columns, $columnName))
			{
				$this->columns[$columnName]['messages'] = array
				(
					'operation' => array(),
					'validation' => array()
				);
				
				$result = true;
			}
			
			return $result;
		}
		
		public function addColumnMessage ($columnName = '', $messageType = 'validation', $message = '')
		{
			$result = false;
			
			if (XXX_Array::hasKey($this->columns, $columnName))
			{
				if ($messageType == 'operation' || $messageType == 'validation' || $messageType == 'information' || $messageType == 'confirmation')
				{
					$this->columns[$columnName]['messages'][$messageType][] = $message;
					
					$result = true;
				}
			}
			
			return $result;
		}
		
		public function doesColumnHaveMessages ($columnName = '')
		{
			$result = false;
			
			if (XXX_Array::hasKey($this->columns, $columnName))
			{
				$count = 0;
				
				$count += XXX_Array::getFirstLevelItemTotal($this->columns[$columnName]['messages']['operation']);
				
				if ($count > 0)
				{
					$result = true;
				}
				else
				{				
					$count += XXX_Array::getFirstLevelItemTotal($this->columns[$columnName]['messages']['validation']);
					
					if ($count > 0)
					{
						$result = true;
					}
				}
			}
			
			return $result;
		}
		
		public function getColumnMessages ()
		{
			$result = false;
			
			if (XXX_Array::hasKey($this->columns, $columnName))
			{
				$result = $this->columns[$columnName]['messages'];
			}
			
			return $result;
		}
		
	// Process
	
		public function processActionsForColumn ($columnName = '')
		{
			$result = false;
			
			if (XXX_Array::hasKey($this->columns, $columnName))
			{
				$validated = true;
				$operated = false;
							
				$this->resetColumnMessages($columnName);
				
				$value = $this->columns[$columnName]['value']['current'];
				
				foreach ($this->columns[$columnName]['actions'] as $actionType => $actions)
				{
					if (XXX_Array::getFirstLevelItemTotal($actions) > 0)
					{
						for ($i = 0, $iEnd = XXX_Array::getFirstLevelItemTotal($actions); $i < $iEnd; ++$i)
						{
							$actionResponse = false;
							
							$action = $actions[$i];
							
							if ($action['side'] != 'client')
							{									
								switch ($actionType)
								{
									case 'operation':
										//$actionResponse = XXX_Client_Input::operateOnValue($value, $action['action'], $action['texts'], $action['parameters']);
									
										if ($actionResponse)
										{									
											if ($actionResponse['operated'])
											{
												$operated = true;
												$value = $actionResponse['value'];
																						
												$this->addColumnMessage($columnName, 'operation', $actionResponse['message']);
											}
										}
										break;
									case 'validation':
										if ($validated)
										{
											//$actionResponse = XXX_Client_Input::validateValue($value, $action['action'], $action['texts'], $action['parameters']);
										
											if ($actionResponse)
											{
												if (!$actionResponse['validated'])
												{
													$validated = false;
													
													$this->addColumnMessage($columnName, 'validation', $actionResponse['message']);
												}
											}
										}
										break;
								}
							}
						}
					}
				}
				
				if ($operated)
				{
					$this->changed = true;
					$this->columns[$columnName]['changed'] = true;
					
					$this->columns[$columnName]['value']['current'] = $value;
				}
				
				$this->columns[$columnName]['valid'] = $validated;
				
				$result = $validated;
			}
			
			return $result;
		}
		
		public function processActions ()
		{
			$result = false;
			
			$validated = true;
			
			foreach ($this->columns as $columnName => $column)
			{
				$temp = $this->processActionsForColumn($columnName);
				
				if (!$temp)
				{
					$validated = false;
				}
			}
			
			$this->valid = $validated;
			
			return $result;
		}
	
	// Value
	
		public function setColumnValue ($columnName = '', $value = '', $columnValueType = 'current', $byPassActions = false, $rollBackIfInvalid = false)
		{
			$result = false;
			
			if (XXX_Array::hasKey($this->columns, $columnName))
			{
				$columnValueType = XXX_Default::toOption($columnValueType, array('storage', 'current'), 'current');
								
				$oldColumn = $this->columns[$columnName];
				$oldChanged = $this->changed;
				$oldValid = $this->valid;
								
				$checkForChangedFalse = false;
				
				switch ($columnValueType)
				{
					case 'storage':
						$this->columns[$columnName]['value']['storage'] = $value;
										
						switch ($oldColumn['onStorageChange'])
						{
							case 'overwrite':
								$this->columns[$columnName]['value']['current'] = $value;
								$this->columns[$columnName]['changed'] = false;
								
								$byPassActions = true;	
								$checkForChangedFalse = true;
								break;
							case 'rebaseDifference':
								$oldDifference = $oldColumn['value']['current'] - $oldColumn['value']['storage'];
								
								if ($oldDifference == 0)
								{
									$this->columns[$columnName]['value']['current'] = $value;
									$this->columns[$columnName]['changed'] = false;
									
									$byPassActions = true;
									$checkForChangedFalse = true;
								}
								else
								{
									$this->columns[$columnName]['value']['current'] = $value + $oldDifference;
									$this->columns[$columnName]['changed'] = true;
									$this->changed = true;
								}
								break;
							case 'leaveAsIs':
								if ($value == $oldColumn['value']['current'])
								{
									$this->columns[$columnName]['changed'] = false;
																		
									$byPassActions = true;
									$checkForChangedFalse = true;
								}
								else
								{
									$this->columns[$columnName]['changed'] = true;
									$this->changed = true;
								}
								break;
						}						
						
						break;
					case 'current':
						if ($value == $oldColumn['value']['storage'])
						{
							$this->columns[$columnName]['changed'] = false;
							
							$byPassActions = true;
							$checkForChangedFalse = true;
						}
						else
						{
							$this->columns[$columnName]['value']['current'] = $value;
							
							$this->columns[$columnName]['changed'] = true;
							$this->changed = true;
						}
						break;
				}
				
				if ($checkForChangedFalse)
				{							
					$changedCount = 0;
					
					foreach ($this->columns as $columnName => $column)
					{
						if ($column['changed'])
						{
							++$changedCount;
						}
					}
					
					if ($changedCount == 0)
					{
						$this->changed = false;
					}
				}
				
				if ($byPassActions)
				{
					$result = true;
				}
				else
				{
					$validated = $this->processActionsForColumn($columnName);
					
					if ($validated)
					{
						$result = true;
					}
					else
					{
						if ($rollBackIfInvalid)
						{						
							$this->columns[$columnName] = $oldColumn;
							$this->changed = $oldChanged;
							$this->valid = $oldValid;						
						}
					}
				}
			}
			
			return $result;
		}
		
		public function setColumnValues (array $columns = array(), $columnValueType = 'current', $byPassActions = false, $rollBackIfInvalid = false)
		{
			$result = false;
			
			$oldColumns = $this->columns;
			$oldChanged = $this->changed;
			$oldValid = $this->valid;
			
			$tempResult = true;
			
			foreach ($columns as $columnName => $value)
			{
				$tempResult2 = $this->setColumnValue($columnName, $value, $columnValueType, $byPassActions, $rollBackIfInvalid);
				
				if (!$tempResult2)
				{
					$tempResult = false;
					
					break;
				}
			}
			
			if ($tempResult)
			{
				$result = true;
			}
			else
			{
				if ($rollBackIfInvalid)
				{
					$this->columns = $oldColumns;
					$this->changed = $oldChanged;
					$this->valid = $oldValid;
				}
			}	
			
			return $result;
		}
		
		public function getColumnValue ($columnName = '', $columnValueType = 'current', $onlyIfValid = false)
		{
			$result = false;
						
			if (XXX_Array::hasKey($this->columns, $columnName))
			{
				$columnValueType = XXX_Default::toOption($columnValueType, array('storage', 'current'), 'current');
				
				if (!$onlyIfValid || $this->columns[$columnName]['valid'])
				{
					$result = $this->columns[$columnName]['value'][$columnValueType];
				}
			}
			
			return $result;
		}
		
		public function getColumnValues ($columnValueType = 'current', $onlyIfValid = false)
		{
			$result = false;
			
			if (!$onlyIfValid || $this->valid)
			{
				$columnValueType = XXX_Default::toOption($columnValueType, array('storage', 'current'), 'current');
				
				$result = array();
				
				foreach ($this->columns as $columnName => $column)
				{
					$result[$columnName] = $column['value'][$columnValueType];
				}
			}
			
			return $result;
		}
		
	// State
		
		public function hasColumnChanged ()
		{
			$result = false;
			
			if (XXX_Array::hasKey($this->columns, $columnName))
			{
				$result = $this->columns[$columnName]['changed'];
			}
			
			return $result;
		}
		
		public function isColumnValid ()
		{
			$result = false;
			
			if (XXX_Array::hasKey($this->columns, $columnName))
			{
				$result = $this->columns[$columnName]['valid'];
			}
			
			return $result;
		}
	
	
	public function hasMessages ()
	{
		$result = false;
		
		$count = 0;
		
		foreach ($this->columns as $columnName => $column)
		{
			$tempResult = $this->doesColumnHaveMessages($columnName);
			
			if ($tempResult)
			{
				$count += $tempResult;
				
				break;
			}
		}
		
		if ($count > 0)
		{
			$result = true;
		}
		
		return $result;
	}
	
	public function getMessages ()
	{
		$result = false;
		
		$messages = array
		(
			'operation' => array(),
			'validation' => array()
		);
		
		foreach ($this->columns as $columnName => $column)
		{
			for ($i = 0, $iEnd = XXX_Array::getFirstLevelItemTotal($column['messages']['operation']); $i < $iEnd; ++$i)
			{
				$messages['operation'][$columnName] = $column['messages']['operation'][$i];
			}
			
			for ($i = 0, $iEnd = XXX_Array::getFirstLevelItemTotal($column['messages']['validation']); $i < $iEnd; ++$i)
			{
				$messages['validation'][$columnName] = $column['messages']['validation'][$i];
			}
		}
		
		$result = $messages;
		
		return $result;
	}
	
	public function isNew ()
	{
		return $this->new;
	}
	
	public function isValid ()
	{
		return $this->valid;
	}
	
	public function hasChanged ()
	{
		return $this->changed;
	}
	
	public function isSharded ()
	{
		return $this->sharded;
	}
}

?>