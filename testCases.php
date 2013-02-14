<?php

$serverSettings = array
(
	'address' => 'localhost',
	'port' => 3306,
	'user' => 'root',
	'pass' => '',
	'connectDirectly' => true,
	'defaultDataBase' => 'test',
	'characterSet' => 'utf8',
	'collation' => 'utf8_unicode_ci',
	'connectionType' => 'administration'
);

$connection = new XXX_DataBase_MySQL_Extension_MySQL($serverSettings);
$connection->establishConnection();

$abstractionLayer = new XXX_DataBase_MySQL_AbstractionLayer_Administration();
$abstractionLayer->open($connection);

$configuration = $abstractionLayer->getConfiguration();

print_r($connection);
print_r($abstractionLayer);
print_r($configuration);

?>