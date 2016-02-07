<?php

$database['host'] = 'localhost';
$database['user'] = 'root';
$database['pass'] = '';
$database['dbName'] = 'traffic_data';

$PDO = new PDO('mysql:dbname='.$database['dbName'].';host='.$database['host'], $database['user'], $database['pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));


function cleanNames($name)
{
	$name = str_replace('100000292515765','Andraž Jelenc',$name);
	return str_replace('&#064;facebook.com','',$name);
}
function convertDate($data)
{
	$months = array('januar','februar','marec','april','maj','junij','julij','avgust','september','oktober','november','december');
	
	//23. avgust 2012 ob 22:25 UTC+02
	$data = explode(' ',$data);
	
	$day = substr($data[0],0,-1);
	$month = 1+array_search($data[1], $months);
	$year = $data[2];
	
	$oblika = new DateTime($year.'-'.$month.'-'.$day.' '.$data[4]);
	
	if($data[5] == 'UTC+02')
	{
		$oblika->sub(new DateInterval('PT1H'));
	}
	return $oblika->format('Y-m-d H:i:s');
}

$data = json_decode(file_get_contents('data.json'), true);

$peopleIndex = $data[0];
$messages = $data[1];

/*
 * Messages sorted and ordered
 */

foreach($messages as $message)
{
	if(count($message[0]) == 2)
	{
		$person1 = cleanNames($message[0][0]);
		$person2 = cleanNames($message[0][1]);
		echo $person1."\t".$person2."\r\n";
		//export
		for($i = 1; $i < count($message); $i +=1)
		{
			//sprehod
			foreach($message[$i] as $sporocilo)
			{
				$from = cleanNames($sporocilo[0]);
				$date = convertDate($sporocilo[1]);
				
				$to = '';
				if($from == $person1)
				{
					$to = $person2;
				}
				else
				{
					$to = $person1;
				}
				
				//echo $from.' '.$to.' '.$date;
				//echo "\r\n";
				
				$sql = "INSERT INTO `traffic` VALUES('".$from."', '".$to."', '".$date."')";
				$PDO->query($sql);
			}
		}//*/
	}
	
}
?>
