<?php

$data = json_decode(file_get_contents('data.json'), true);

$peopleIndex = $data[0];
$messages = $data[1];

 //count all messages
$sum = 0;

foreach($messages as $message)
{
	foreach($messages as $block)
	{
		$sum += count($block);
	}
	
}
echo $sum;
?>
