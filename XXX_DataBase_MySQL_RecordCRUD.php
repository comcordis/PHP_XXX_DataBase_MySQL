<?php

class XXX_DataBase_MySQL_RecordCRUD
{
	protected $connection = false;
	protected $recordRepresentationTemplateName = 'XXX';
		
	public function __construct ($connection = false, $recordRepresentationTemplateName = false)
	{
		$this->setConnection($connection);
		$this->setRecordRepresentationTemplateName($recordRepresentationTemplateName);
		
		$this->composeAndAddQueryTemplates();
	}
	
	public function setConnection ($connection)
	{
		if ($connection)
		{
			$this->connection = $connection;
		}
	}
	
	public function setRecordRepresentationTemplateName ($recordRepresentationTemplateName)
	{
		if ($recordRepresentationTemplateName)
		{
			$this->recordRepresentationTemplateName = $recordRepresentationTemplateName;
		}
	}
		
	protected function composeAndAddQueryTemplates ()
	{
		if (!XXX_DataBase_MySQL_QueryTemplate::getByName($this->recordRepresentationTemplateName . '>retrieveRecord'))
		{
			$tempRecordRepresentation = new XXX_DataBase_MySQL_RecordRepresentation($this->recordRepresentationTemplateName);
			
			$queryTemplateInformation = $tempRecordRepresentation->getQueryTemplateInformation();
		
			$queryTemplates = array();
					
			$dataBaseAndTableQueryPart = '';
			$dataBaseAndTableQueryPart .= ' ';
			$dataBaseAndTableQueryPart .= $queryTemplateInformation['dataBase'];
			if ($queryTemplateInformation['sharded'])
			{
				$dataBaseAndTableQueryPart .= '_' . '?';
			}
			$dataBaseAndTableQueryPart .= '.';
			$dataBaseAndTableQueryPart .= $queryTemplateInformation['table'];
			if ($queryTemplateInformation['sharded'])
			{
				$dataBaseAndTableQueryPart .= '_' . '?';
			}
			$dataBaseAndTableQueryPart .= ' ';	
			
			// createRecord
				
				$query = '';
				$query .= 'INSERT INTO';
				$query .= $dataBaseAndTableQueryPart;			
				$query .= '(';			
				$i = 0;
				foreach ($queryTemplateInformation['columns'] as $columnName => $columnMainDataType)
				{
					if ($i > 0)
					{
						$query .= ', ';
					}				
					$query .= $columnName;				
					++$i; 
				}
				$query .= ')';			
				$query .= ' ';
				$query .= 'VALUES';
				$query .= ' ';
				$query .= '(';
				for ($i = 0, $iEnd = $queryTemplateInformation['columnTotal']; $i < $iEnd; ++$i)
				{
					if ($i > 0)
					{
						$query .= ', ';
					}
					$query .= '?';
				}
				$query .= ')';
				
				$inputFilters = array();
				if ($queryTemplateInformation['sharded'])
				{
					$inputFilters[] = 'integer';
					$inputFilters[] = 'integer';
				}
				foreach ($queryTemplateInformation['columns'] as $columnMainDataType)
				{
					$inputFilters[] = $columnMainDataType;
				}
				
				$responseType = 'success';			
				
				$queryTemplates['createRecord'] = array
				(
					'query' => $query,
					'inputFilters' => $inputFilters,
					'responseType' => $responseType
				);
				
			// updateRecord
				
				$query = '';
				$query .= 'UPDATE';
				$query .= $dataBaseAndTableQueryPart;
				$query .= 'SET';
				$query .= ' ';			
				$i = 0;
				foreach ($queryTemplateInformation['columns'] as $columnName => $columnMainDataType)
				{
					if ($columnName != 'ID')
					{
						if ($i > 0)
						{
							$query .= ', ';
						}				
						$query .= $columnName . ' = ?';				
						++$i;
					}
				}
				$query .= ' ';
				$query .= 'WHERE';
				$query .= ' ';
				$query .= 'ID = ?';
				
				$inputFilters = array();
				if ($queryTemplateInformation['sharded'])
				{
					$inputFilters[] = 'integer';
					$inputFilters[] = 'integer';
				}
				foreach ($queryTemplateInformation['columns'] as $columnName => $columnMainDataType)
				{
					if ($columnName != 'ID')
					{
						$inputFilters[] = $columnMainDataType;
					}
				}
				$inputFilters[] = 'integer';
				
				$responseType = 'affected';			
				
				$queryTemplates['updateRecord'] = array
				(
					'query' => $query,
					'inputFilters' => $inputFilters,
					'responseType' => $responseType
				);
							
			// retrieveRecord
			
				$query = '';
				$query .= 'SELECT';
				$query .= ' ';
				$i = 0;
				foreach ($queryTemplateInformation['columns'] as $columnName => $columnMainDataType)
				{
					if ($i > 0)
					{
						$query .= ', ';
					}				
					$query .= $columnName ;				
					++$i;
				}
				$query .= ' ';
				$query .= 'FROM';			
				$query .= $dataBaseAndTableQueryPart;
				$query .= 'WHERE';
				$query .= ' ';
				$query .= 'ID = ?';
				
				$inputFilters = array();			
				if ($queryTemplateInformation['sharded'])
				{
					$inputFilters[] = 'integer';
					$inputFilters[] = 'integer';
				}
				$inputFilters[] = 'integer';
				
				$responseType = 'record';
				
				$responseColumnTypeCasting = array();
				foreach ($queryTemplateInformation['columns'] as $columnName => $columnMainDataType)
				{
					if ($columnMainDataType != 'string')
					{
						$responseColumnTypeCasting[$columnName] = $columnMainDataType;
					}
				}
				
				$queryTemplates['retrieveRecord'] = array
				(
					'query' => $query,
					'inputFilters' => $inputFilters,
					'responseType' => $responseType,
					'responseColumnTypeCasting' => $responseColumnTypeCasting
				);
			
			// deleteRecord
				
				$query = '';
				$query .= 'DELETE';
				$query .= ' ';
				$query .= 'FROM';
				$query .= $dataBaseAndTableQueryPart;
				$query .= 'WHERE';
				$query .= ' ';
				$query .= 'ID = ?';
				
				$inputFilters = array();			
				if ($queryTemplateInformation['sharded'])
				{
					$inputFilters[] = 'integer';
					$inputFilters[] = 'integer';
				}
				$inputFilters[] = 'integer';
				
				$responseType = 'affected';
							
				$queryTemplates['deleteRecord'] = array
				(
					'query' => $query,
					'inputFilters' => $inputFilters,
					'responseType' => $responseType
				);
						
			XXX_DataBase_MySQL_QueryTemplate::create($this->recordRepresentationTemplateName . '>createRecord', $queryTemplates['createRecord']['query'], $queryTemplates['createRecord']['inputFilters'], $queryTemplates['createRecord']['responseType'], 'content', '', $queryTemplates['createRecord']['responseColumnTypeCasting']);				
			XXX_DataBase_MySQL_QueryTemplate::create($this->recordRepresentationTemplateName . '>retrieveRecord', $queryTemplates['retrieveRecord']['query'], $queryTemplates['retrieveRecord']['inputFilters'], $queryTemplates['retrieveRecord']['responseType'], 'content', '', $queryTemplates['retrieveRecord']['responseColumnTypeCasting']);				
			XXX_DataBase_MySQL_QueryTemplate::create($this->recordRepresentationTemplateName . '>updateRecord', $queryTemplates['updateRecord']['query'], $queryTemplates['updateRecord']['inputFilters'], $queryTemplates['updateRecord']['responseType'], 'content', '', $queryTemplates['updateRecord']['responseColumnTypeCasting']);				
			XXX_DataBase_MySQL_QueryTemplate::create($this->recordRepresentationTemplateName . '>deleteRecord', $queryTemplates['deleteRecord']['query'], $queryTemplates['deleteRecord']['inputFilters'], $queryTemplates['deleteRecord']['responseType'], 'content', '', $queryTemplates['deleteRecord']['responseColumnTypeCasting']);				
		}
	}
	
	public function saveRecord ($recordRepresentation)
	{
		return false;
	}
	
	public function createRecord ($recordRepresentation)
	{
		return false;
	}
	
	public function retrieveRecord ()
	{
		return false;
	}
	
	public function retrieveRecords ()
	{
		return false;
	}
		
	public function updateRecord ($recordRepresentation)
	{
		return false;
	}
	
	public function deleteRecord ($recordRepresentation)
	{
		return false;
	}
	
	public function convertCustomRetrievedRecordsToRecordRepresentations ()
	{
		return false;
	}
	
	public function convertCustomRetrievedRecordToRecordRepresentation ()
	{
		return false;
	}
	
	
}

?>