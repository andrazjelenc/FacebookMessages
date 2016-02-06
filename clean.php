<?php
$fp = fopen('messages - Copy.htm', 'r');

$names = "";
$file = 'output.txt';

$fuj = array('<div class="message_header"><span class="user">','<span class="meta">','</div>','<p>');
while (false !== ($line = fgets($fp))) 
{
	$line = str_replace($fuj,'',$line);
	file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
}
fclose($fp);
?>
