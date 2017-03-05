<?php
//create table used by this script:
/*INSERT INTO daily_count (person_id, `date`, cnt)
SELECT m.person_id, date(m.datetime), COUNT(m.message_id) AS cnt
FROM messages m
WHERE m.type = 0
GROUP BY m.person_id, YEAR(m.datetime), MONTH(m.datetime), DAY(m.datetime)*/

require_once('basic.php');

function splitDates($min, $max, $parts, $output = "Y-m-d") 
{
	$dataCollection[] = date($output, strtotime($min));
	$diff = (strtotime($max) - strtotime($min)) / $parts;
	$convert = strtotime($min) + $diff;

	for ($i = 1; $i < $parts; $i++) 
	{
		$dataCollection[] = date($output, $convert);
		$convert += $diff;
	}
	$dataCollection[] = date($output, strtotime($max));

	return $dataCollection;
}

//order $idsByTotalSum by field "total"
function compareIds($a, $b) {
	global $data;
	if ($a == $b) 
	{
		return 0;
	}
	return ($data[$a]['total'] < $data[$b]['total']) ? 1 : -1;
}

/*
 *	Get variables from UI
 */
$minDay = $_POST['minDay'];
$minMonth = $_POST['minMonth'];
$minYear = $_POST['minYear'];
$maxDay = $_POST['maxDay'];
$maxMonth = $_POST['maxMonth'];
$maxYear = $_POST['maxYear'];
$limit = $_POST['limit'];
$stStolpcev = $_POST['stStolpcev'];
$nacin = $_POST['nacin'];

//build valid SQL dates
$fromDate = "$minYear-$minMonth-$minDay";
$toDate = "$maxYear-$maxMonth-$maxDay";

if($nacin == 0 || $nacin == 1)
{
	$intervals = splitDates($fromDate, $toDate, $stStolpcev);
			
	$sql = 'SELECT  m.person_id, 
					p.name,
					SUM(m.cnt) AS partSum, 
					CEIL(
						(DATEDIFF(
								m.date, 
								"'.$fromDate.'"
							) + 1)
						/ 
						DATEDIFF(
							"'.$toDate.'",
							"'.$fromDate.'"
						) 
						* 
						'.$stStolpcev.'
					) AS part
			FROM daily_count m INNER JOIN (
				SELECT ms.person_id
				FROM daily_count ms
				LEFT JOIN people pt ON pt.person_id = ms.person_id
				WHERE ms.date BETWEEN "'.$fromDate.'" AND DATE_SUB("'.$toDate.'", INTERVAL 1 DAY)
					AND pt.active = 1
				GROUP BY ms.person_id
				HAVING SUM(ms.cnt) > 0
				ORDER BY SUM(ms.cnt) DESC
				LIMIT '.$limit.'
			) AS idTable ON m.person_id = idTable.person_id
			LEFT JOIN people p ON p.person_id = m.person_id
			WHERE m.date BETWEEN "'.$fromDate.'" AND DATE_SUB("'.$toDate.'", INTERVAL 1 DAY)
			GROUP BY m.person_id, part';
		
	$query = $PDO->query($sql);

	$data = array();
	$idsByTotalSum = array();
	foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row)
	{
		$to_id = $row['person_id'];
		$name = $row['name'];
		$partSum = $row['partSum'];
		$part = $row['part'] - 1; //counting from 0
		
		if(array_key_exists($to_id, $data))
		{
			//add
			$data[$to_id]['data'][$part] = intval($partSum);
			$data[$to_id]['total'] += intval($partSum);
		}
		else
		{
			//create
			$newArray = array();
			for($i = 0; $i < $stStolpcev; $i++)
			{
				$newArray[$i] = 0;
			}
			$data[$to_id] = array('name' => $name, 'total' => intval($partSum), 'data' => $newArray);
			$data[$to_id]['data'][$part] = intval($partSum);
			
			$idsByTotalSum[] = $to_id;
		}
	}

	
	uasort($idsByTotalSum, 'compareIds');

	if($nacin == 0) //graph type=SUMMED
	{
		foreach($data as $index => $person)
		{
			for($i = 0; $i < count($data[$index]['data']); $i++)
			{
				if(isset($data[$index]['data'][$i -1]))
				{
					$data[$index]['data'][$i] += $data[$index]['data'][$i -1];
				}
			}
		}
	}

	$csv = "Day";

	$fromDate2 = "$minDay/$minMonth/$minYear";
	$toDate2 = "$maxDay/$maxMonth/$maxYear";

	foreach($idsByTotalSum as $to_id)
	{
		$csv .= ",".$data[$to_id]['name'];
	}
	$csv .="\n$fromDate2";
	foreach($idsByTotalSum as $to_id)
	{
		$csv .= ",0";
	}

	for ($i = 0; $i < $stStolpcev; $i++)
	{
		//build data string
		$string = "";	
		foreach($idsByTotalSum as $to_id)//$data as $value)
		{
			$string .= $data[$to_id]['data'][$i].",";
		}
		
		//append to csv
		$csv .= "\n".$intervals[$i+1].",".trim($string, ",");
	}
	echo $csv;
}
else if($nacin == 2)
{
	$date2index = array('Monday' => 0, 'Tuesday' => 1, 'Wednesday' => 2, 'Thursday' => 3, 'Friday' => 4, 'Saturday' => 5, 'Sunday' => 6);
	$sql = '
		SELECT DAYNAME(m.datetime) as day, HOUR(m.datetime) as hour, CEIL((MINUTE(m.datetime)+1)*0.2) as part, COUNT(m.message_id) as cnt
		FROM `messages` m LEFT JOIN people p ON p.person_id = m.person_id
		
		WHERE m.datetime BETWEEN "'.$fromDate.'" AND DATE_SUB("'.$toDate.'", INTERVAL 1 DAY) 
			AND p.active = 1
		GROUP BY day, hour, part
	';
	$query = $PDO->query($sql);

	$maxValue = 0;
	$dbData = array();
	foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row)
	{
		$day = $row['day'];
		$hour = $row['hour'];
		$part = $row['part'] -1;
		$cnt = intval ($row['cnt']);
		
		$dbData[$hour*12 + $part][$date2index[$day]] = $cnt;
		if($cnt > $maxValue)
		{
			$maxValue = $cnt;
		}
	}
	
	$csv = 'Interval,Dan,Vrednost';
	$data = array();			//array of arrays (hour,day, value)
	for($i = 0; $i <  12*24; $i++)
	{
		//for every interval
		for($j = 0; $j < 7; $j++)
		{
			//for every day
			$value = 0;
			if(isset($dbData[$i][$j]))
			{
				$value = $dbData[$i][$j];
			}
			$data[] = array($i, $j, $value);
		}
	}

	$output = array();
	$output['max'] = $maxValue;
	$output['data'] = $data;
	echo json_encode($output);//$csv;
}
?>