<?php

abstract class XXX_DataBase_MySQL_QueryTemplate
{
	const CLASS_NAME = 'XXX_DataBase_MySQL_QueryTemplate';
	
	public static $validFilterTypes = array
	(
		'integer',
		'integerOptions',
		'integerBetween',
		'float',
		'floatBetween',
		'string',
		'stringOptions',
		'stringBetween',
		'boolean',
		'like',
		'pattern',
		'order',
		'raw'
	);	
	public static $validResponseTypes = array
	(
		false,
		'ID',
		'record',
		'records',
		'affected',
		'success'
	);
	
	public static $queryTemplates = array();
	
	public static function getByName ($name = '')
	{
		$result = false;
		
		for ($i = 0, $iEnd = XXX_Array::getFirstLevelItemTotal(self::$queryTemplates); $i < $iEnd; ++$i)
		{
			$queryTemplate = self::$queryTemplates[$i];
			
			if ($queryTemplate['name'] == $name)
			{
				$result = $queryTemplate;
				
				break;
			}
		}
		
		return $result;
	}
	
	public static function checkExistenceByName ($name = '')
	{
		$result = false;
		
		for ($i = 0, $iEnd = XXX_Array::getFirstLevelItemTotal(self::$queryTemplates); $i < $iEnd; ++$i)
		{
			$queryTemplate = self::$queryTemplates[$i];
			
			if ($queryTemplate['name'] == $name)
			{
				$result = true;
				
				break;
			}
		}
		
		return $result;
	}
	
	public static function getIDByName ($name = '')
	{
		global $XXX_DataBase_MySQL_QueryTemplates;
		
		$result = false;
		
		for ($i = 0, $iEnd = XXX_Array::getFirstLevelItemTotal(self::$queryTemplates); $i < $iEnd; ++$i)
		{
			$queryTemplate = self::$queryTemplates[$i];
			
			if ($queryTemplate['name'] == $name)
			{
				$result = $i;
				
				break;
			}
		}
		
		if ($result === false)
		{
			$queryTemplate = XXX_Array::traverseKeyPath($XXX_DataBase_MySQL_QueryTemplates, $name);
			
			if ($queryTemplate)
			{
				$result = self::create($name, $queryTemplate['query'], $queryTemplate['inputFilters'], $queryTemplate['responseType'], $queryTemplate['requiredConnectionType'], $queryTemplate['dataBase'], $queryTemplate['responseColumnTypeCasting']);
			}
		}
		
		return $result;
	}
	
	public static function getByID ($ID = 0)
	{
		$result = false;
		
		for ($i = 0, $iEnd = XXX_Array::getFirstLevelItemTotal(self::$queryTemplates); $i < $iEnd; ++$i)
		{
			if ($i == $ID)
			{
				$result = self::$queryTemplates[$i];
			}
		}
		
		return $result;
	}
	
	public static function createByArray ($name = '', $array = array())
	{
		return self::create($name, $array['query'], $array['inputFilters'], $array['responseType'], $array['requiredConnectionType'], $array['dataBase'], $array['responseColumnTypeCasting']);
	}
	
	public static function create ($name = '', $query = '', $inputFilters = array(), $responseType = 'success', $requiredConnectionType = 'readContent', $dataBase = '', $responseColumnTypeCasting = array())
	{
		$result = false;
		
		if (!self::checkExistenceByName($name))
		{		
			if (!XXX_Type::isArray($inputFilters))
			{
				$inputFilters = array();
			}
			
			if ($responseType == '')
			{
				$responseType = 'success';
			}
			
			if ($requiredConnectionType == '')
			{
				$requiredConnectionType = 'readContent';
			}
			
			if (!XXX_Type::isArray($responseColumnTypeCasting))
			{
				$responseColumnTypeCasting = array();
			}
			
			if (XXX_Array::hasValue(XXX_DataBase_MySQL_Connections::$validConnectionTypes, $requiredConnectionType))
			{		
				if (XXX_Array::hasValue(self::$validResponseTypes, $responseType) || $responseType === false)
				{		
					$questionMarks = XXX_String_Pattern::getMatches($query, '\?');
					$variableValueTotal = XXX_Array::getFirstLevelItemTotal($questionMarks[0]);
					
					$inputFilterTotal = XXX_Array::getFirstLevelItemTotal($inputFilters);
					
					if ($variableValueTotal == $inputFilterTotal)
					{
						$validInputFilters = true;
						
						foreach ($inputFilters as $inputFilter)
						{
							if (!XXX_Array::hasValue(self::$validFilterTypes, $inputFilter))
							{
								$validInputFilters = false;
								break;
							}
						}
						
						if ($validInputFilters)
						{
							$queryParts = XXX_String::splitToArray($query, '?');
							
							$queryTemplateID = XXX_Array::getFirstLevelItemTotal(self::$queryTemplates);
							
							self::$queryTemplates[] = array
							(
								'ID' => $queryTemplateID,
								'name' => $name,
								'query' => $query,
								'queryParts' => $queryParts,
								'inputFilters' => $inputFilters,
								'responseType' => $responseType,
								'requiredConnectionType' => $requiredConnectionType,
								'dataBase' => $dataBase,
								'responseColumnTypeCasting' => $responseColumnTypeCasting
							);
													
							$result = $queryTemplateID;						
						}
						else
						{
							$result = false;
							trigger_error('Invalid input filter(s) specified: "' . XXX_Array::joinValuesToString($filters, '|') . '"', E_USER_ERROR);
						}
					}
					else
					{
						$result = false;
						trigger_error('Number of variable values doesn\'t match the number of input filters', E_USER_ERROR);
					}
				}
				else
				{
					$result = false;
					trigger_error('Invalid responseType: "' . $responseType . '"', E_USER_ERROR);
				}
			}
			else
			{
				$result = false;
				trigger_error('Invalid requiredConnectionType "' . $requiredConnectionType . '"', E_USER_ERROR);
			}
		}
		
		return $result;
	}
	
	public static function processInput ($queryTemplateID, array $values = array())
	{
		$result = false;
		
		$queryTemplate = self::$queryTemplates[$queryTemplateID];
		
		if ($queryTemplate)
		{
			$inputFilterTotal = XXX_Array::getFirstLevelItemTotal($queryTemplate['inputFilters']);
			$valueTotal = XXX_Array::getFirstLevelItemTotal($values);
			
			$errorDescription = '';
			
			if ($inputFilterTotal == $valueTotal)
			{
				$validValues = true;
				$invalidValueKey = -1;
								
				for ($i = 0, $iEnd = $inputFilterTotal; $i < $iEnd; ++$i)
				{
					$inputFilter = $queryTemplate['inputFilters'][$i];
					$value = $values[$i];
					
					switch ($inputFilter)
					{
						case 'integer':							
							if (XXX_Type::isNumeric($value))
							{
								$value = XXX_Type::makeInteger($value);
								
								if (!XXX_Type::isInteger($value))
								{
									$validValues = false;
									$invalidValueKey = $i;
									break;
								}
								else
								{
									$value = XXX_DataBase_MySQL_Filter::filterInteger($value);
								}
							}
							else
							{
								$validValues = false;
								$invalidValueKey = $i;
								break;
							}
							break;
						case 'integerOptions':
							$options = $value;
							
							if (XXX_Type::isArray($options))
							{
								if (XXX_Type::isEmptyArray($options))
								{
									$options = array(0);
								}
									
								$filteredOptions = array();
								
								$validOptions = true;
								
								foreach ($options as $option)
								{
									if (XXX_Type::isNumeric($option))
									{
										$option = XXX_Type::makeInteger($option);
										
										if (!XXX_Type::isInteger($option))
										{
											$validOptions = false;
											break;
										}
										else
										{
											$filteredOptions[] = XXX_DataBase_MySQL_Filter::filterInteger($option);
										}
									}
									else
									{
										$validOptions = false;
										break;
									}
								}
								
								if ($validOptions)
								{
									// Sort ascending to optimize searching
									sort($filteredOptions);
									
									$value = '(' . XXX_Array::joinValuesToString($filteredOptions, ',') . ')';
								}
								else
								{
									$validValues = false;
									$invalidValueKey = $i;
									break;
								}
							}
							else
							{
								$validValues = false;
								$invalidValueKey = $i;
								break;
							}
							break;
						case 'integerBetween':
							$limits = $value;
							
							if (XXX_Type::isArray($limits) && XXX_Array::getFirstLevelItemTotal($limits) == 2)
							{
								$filteredLimits = array();
								
								$validLimits = true;
								
								foreach ($limits as $limit)
								{
									if (XXX_Type::isNumeric($limit))
									{
										$limit = XXX_Type::makeInteger($limit);
										
										if (!XXX_Type::isInteger($limit))
										{
											$validLimits = false;
											break;
										}
										else
										{
											$filteredLimits[] = XXX_DataBase_MySQL_Filter::filterInteger($limit);
										}
									}
									else
									{
										$validLimits = false;
										break;
									}
								}
								
								if ($validLimits)
								{
									$value = $filteredLimits[0] . ' AND ' . $filteredLimits[1];
								}
								else
								{
									$validValues = false;
									$invalidValueKey = $i;
									break;
								}
							}
							else
							{
								$validValues = false;
								$invalidValueKey = $i;
								break;
							}
							break;
						case 'float':
							if (XXX_Type::isNumeric($value))
							{
								$value = XXX_Type::makeFloat($value);
								
								if (!XXX_Type::isFloat($value))
								{
									$validValues = false;
									$invalidValueKey = $i;
									break;
								}
								else
								{
									$value = XXX_DataBase_MySQL_Filter::filterFloat($value);
								}
							}
							else
							{
								$validValues = false;
								$invalidValueKey = $i;
								break;
							}
							break;
						case 'floatBetween':
							$limits = $value;
							
							if (XXX_Type::isArray($limits) && XXX_Array::getFirstLevelItemTotal($limits) == 2)
							{
								$filteredLimits = array();
								
								$validLimits = true;
								
								foreach ($limits as $limit)
								{
									if (XXX_Type::isNumeric($limit))
									{
										$limit = XXX_Type::makeFloat($limit);
										
										if (!XXX_Type::isFloat($limit))
										{
											$validLimits = false;
											break;
										}
										else
										{
											$filteredLimits[] = XXX_DataBase_MySQL_Filter::filterFloat($limit);
										}
									}
									else
									{
										$validLimits = false;
										break;
									}
								}
								
								if ($validLimits)
								{
									$value = $filteredLimits[0] . ' AND ' . $filteredLimits[1];
								}
								else
								{
									$validValues = false;
									$invalidValueKey = $i;
									break;
								}
							}
							else
							{
								$validValues = false;
								$invalidValueKey = $i;
								break;
							}
							break;
						case 'string':
							if (XXX_Type::isString($value) || XXX_Type::isNumber($value) || XXX_Type::isBoolean($value) || $value == '')
							{
								$value = XXX_Type::makeString($value);
								
								if (!XXX_Type::isString($value))
								{
									$validValues = false;									
									$invalidValueKey = $i;
									break;
								}
								else
								{
									$value = '"' . XXX_DataBase_MySQL_Filter::filterString($value) . '"';
								}
							}
							else
							{
								$validValues = false;
								$invalidValueKey = $i;
								break;
							}
							break;
						case 'order':
							$value = XXX_String::convertToLowerCase($value);
							
							if (XXX_Array::hasValue(array('asc', 'ascending', 'desc', 'descending'), $value))
							{
								$order = 'ASC';
								
								if ($value == 'desc' || $value == 'descending')
								{
									$order = 'DESC';
								}
								
								$value = $order;
							}
							else
							{
								$validValues = false;
								$invalidValueKey = $i;
							}
							break;
						case 'stringOptions':
							$options = $value;
							
							if (XXX_Type::isArray($options))
							{	
								if (XXX_Type::isEmptyArray($options))
								{
									$options = array('');
								}
														
								$filteredOptions = array();
								
								$validOptions = true;
								
								foreach ($options as $option)
								{
									if (XXX_Type::isString($option) || XXX_Type::isNumber($option) || XXX_Type::isBoolean($option) || $option == '')
									{
										$option = XXX_Type::makeString($option);
										
										if (!XXX_Type::isString($option))
										{
											$validOptions = false;
											break;
										}
										else
										{
											$filteredOptions[] = '"' . XXX_DataBase_MySQL_Filter::filterString($option) . '"';
										}
									}
									else
									{
										$validOptions = false;
										break;
									}
								}
								
								if ($validOptions)
								{
									$value = '(' . XXX_Array::joinValuesToString($filteredOptions, ',') . ')';
								}
								else
								{
									$validValues = false;
									$invalidValueKey = $i;
									break;
								}
							}
							else
							{
								$validValues = false;
								$invalidValueKey = $i;
								break;
							}
							break;
						case 'stringBetween':
							$limits = $value;
							
							if (XXX_Type::isArray($limits) && XXX_Array::getFirstLevelItemTotal($limits) == 2)
							{
								$filteredLimits = array();
								
								$validLimits = true;
								
								foreach ($limits as $limit)
								{
									if (XXX_Type::isString($limit) || XXX_Type::isNumber($limit) || XXX_Type::isBoolean($limit) || $limit == '')
									{
										$limit = XXX_Type::makeString($limit);
										
										if (!XXX_Type::isString($limit))
										{
											$validLimits = false;
											break;
										}
										else
										{
											$filteredLimits[] = XXX_DataBase_MySQL_Filter::filterString($limit);
										}
									}
									else
									{
										$validLimits = false;
										break;
									}
								}
								
								if ($validLimits)
								{
									$value = $filteredLimits[0] . ' AND ' . $filteredLimits[1];
								}
								else
								{
									$validValues = false;
									$invalidValueKey = $i;
									break;
								}
							}
							else
							{
								$validValues = false;
								$invalidValueKey = $i;
								break;
							}
							break;
						case 'boolean':
							if (($value === false || $value === true) || XXX_Type::isBoolean($value) || (XXX_Type::isNumber($value) && ($value === 0 || $value === 1)) || (XXX_Type::isString($value) && ($value === '0' || $value === '1')))
							{
								$value = $value ? 1 : 0;
							}
							else
							{
								$validValues = false;
								$invalidValueKey = $i;
								break;
							}
							break;
						case 'like':
							if (XXX_Type::isString($value) || XXX_Type::isNumber($value) || XXX_Type::isBoolean($value))
							{
								$value = XXX_DataBase_MySQL_Filter::filterLike($value);
							}
							else
							{
								$validValues = false;
								$invalidValueKey = $i;
								break;
							}
							break;
						case 'pattern':
							if (XXX_Type::isString($value) || XXX_Type::isNumber($value) || XXX_Type::isBoolean($value))
							{
								$value = XXX_DataBase_MySQL_Filter::filterPattern($value);
							}
							else
							{
								$validValues = false;
								$invalidValueKey = $i;
								break;
							}
							break;
						case 'raw':
							$value = $value;
							break;
					}
										
					$values[$i] = $value;
				}
									
				if ($validValues)
				{				
					$parsedQueryString = '';
					
					for ($i = 0, $iEnd = XXX_Array::getFirstLevelItemTotal($queryTemplate['queryParts']); $i < $iEnd; ++$i)
					{
						if (XXX_Type::isValue($queryTemplate['queryParts'][$i]))
						{
							$parsedQueryString .= $queryTemplate['queryParts'][$i];
						}
						
						if ($i < $iEnd - 1)
						{
							$parsedQueryString .= $values[$i];
						}
					}
					
					$result = array
					(
						'queryString' => $parsedQueryString,
						'responseType' => $queryTemplate['responseType'],
						'requiredConnectionType' => $queryTemplate['requiredConnectionType'],
						'dataBase' => $queryTemplate['dataBase'],
						'responseColumnTypeCasting' => $queryTemplate['responseColumnTypeCasting']
					);
				}
				else
				{
					$result = false;
					trigger_error('Invalid value(s), type mismatch (Potential SQL injection). queryTemplateID "' . $queryTemplateID . '" - query: "' . $queryTemplate['query'] . '" at value ' . $invalidValueKey, E_USER_ERROR);
				}
			}
			else
			{
				$result = false;
				trigger_error('Number of values (' . XXX_Array::getFirstLevelItemTotal($values) . ') doesn\'t match the number of input filters  (' . XXX_Array::getFirstLevelItemTotal($queryTemplate['inputFilters']) . '). queryTemplateID "' . $queryTemplateID . '" - query: "' . $queryTemplate['query'] . '"', E_USER_ERROR);
			}
		}
		else
		{
			$result = false;
			trigger_error('Invalid queryTemplateID "' . $queryTemplateID . '"', E_USER_ERROR);
		}
		
		return $result;
	}
	
	// Needed because mysql_fetch etc. return only strings, not the original type...
	public static function processResult ($result, $responseColumnTypeCasting)
	{
		if ($result !== false && $result['total'] > 0 && XXX_Type::isFilledArray($responseColumnTypeCasting))
		{
			if (XXX_Type::isArray($result['record']))
			{
				foreach ($responseColumnTypeCasting as $key => $type)
				{
					switch ($type)
					{
						case 'integer':
							$result['record'][$key] = XXX_Type::makeInteger($result['record'][$key]);
							break;
						case 'float':
							$result['record'][$key] = XXX_Type::makeFloat($result['record'][$key]);
							break;
						case 'boolean':
							$result['record'][$key] = XXX_Type::makeBoolean($result['record'][$key]);
							break;
					}
				}
			}
			else if (XXX_Type::isArray($result['records']))
			{
				for ($i = 0, $iEnd = XXX_Array::getFirstLevelItemTotal($result['records']); $i < $iEnd; ++$i)
				{
					foreach ($responseColumnTypeCasting as $key => $type)
					{
						switch ($type)
						{
							case 'integer':
								$result['records'][$i][$key] = XXX_Type::makeInteger($result['records'][$i][$key]);
								break;
							case 'float':
								$result['records'][$i][$key] = XXX_Type::makeFloat($result['records'][$i][$key]);
								break;
							case 'boolean':
								$result['records'][$i][$key] = XXX_Type::makeBoolean($result['records'][$i][$key]);
								break;
						}
					}
				}
			}
			
		}
		
		return $result;
	}
}

?>