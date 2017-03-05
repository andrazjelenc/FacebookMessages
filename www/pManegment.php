<?php
require_once('basic.php');

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'selectAll')
{
	$sql = "
			UPDATE `people`
			SET `active`=1
		";
	$PDO->query($sql);
	print_r($PDO->errorInfo());
}
else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'deselectAll')
{
	$sql = "
			UPDATE `people`
			SET `active`=0
		";
	$PDO->query($sql);
	print_r($PDO->errorInfo());
}
else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'chChange')
{
	$id = $_REQUEST['id'];
	$status = $_REQUEST['status'];
	
	if($status == "true")
	{
		$status = 1;
	}
	else
	{
		$status = 0;
	}
	$sql = "
			UPDATE `people`
			SET `active`=".$status."
			WHERE person_id = ".$id."
		";
	$PDO->query($sql);
	print_r($PDO->errorInfo());
}
else
{
	echo "ERROR: Undefined action!";
}


?>