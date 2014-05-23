<?php

require_once 'XXX_DataBase_MySQL_AbstractionLayer.php';
require_once 'XXX_DataBase_MySQL_AbstractionLayer_Administration.php';
require_once 'XXX_DataBase_MySQL_Extension_MySQL.php';
require_once 'XXX_DataBase_MySQL_Extension_MySQLi.php';
require_once 'XXX_DataBase_MySQL_Extension_PDO.php';
require_once 'XXX_DataBase_MySQL_Factory.php';
require_once 'XXX_DataBase_MySQL_Filter.php';
require_once 'XXX_DataBase_MySQL_Model.php';
require_once 'XXX_DataBase_MySQL_QueryTemplate.php';
//require_once 'XXX_DataBase_MySQL_RecordCRUD.php';
//require_once 'XXX_DataBase_MySQL_RecordRepresentation.php';
//require_once 'XXX_DataBase_MySQL_Search.php';
require_once 'XXX_DataBase_MySQL_Connections.php';

require_once 'XXX_Model_DataBase_MySQL.php';
require_once 'XXX_Model_DataBase_MySQL_DataBase.php';
require_once 'XXX_Model_DataBase_MySQL_DataBase_Table.php';

require_once 'mySQL.queryTemplates.php';

XXX_DataBase_MySQL_Connections::initialize();

?>