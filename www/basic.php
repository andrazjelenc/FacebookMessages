<?php

$db['host'] = 'localhost';
$db['user'] = 'root';
$db['pass'] = '';
$db['db']	= 'facebook2';

try
{
	$PDO = new PDO('mysql:dbname='.$db['db'].';host='.$db['host'], $db['user'], $db['pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'"));
}
catch (PDOException $e)
{
	die ($e->getMessage());
}


?>