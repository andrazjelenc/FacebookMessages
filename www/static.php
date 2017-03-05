<?php
require_once('basic.php');

$minDate = getMinDate($PDO);	//get minimal date from daily_count
$maxDate = getMaxDate($PDO);	//get maximal date from daily_count
$friends = getFriends($PDO);	//build friendlist html table

//select all people from list
$sql = "
	UPDATE `people`
	SET `active`=1
";
$PDO->query($sql);

function getMinDate($PDO)
{
	$sql = "SELECT MIN(`date`) as minTime FROM `daily_count`";
	$query = $PDO->query($sql);
	$data = $query->fetch(PDO::FETCH_ASSOC);
	return explode("-", explode(" ",$data['minTime'])[0]);
}

function getMaxDate($PDO)
{
	$sql = "SELECT MAX(`date`) as maxTime FROM `daily_count`";
	$query = $PDO->query($sql);
	$data = $query->fetch(PDO::FETCH_ASSOC);
	return explode("-", explode(" ",$data['maxTime'])[0]);
}

function getFriends($PDO)
{
	$sql = 'SELECT p.person_id, p.name, SUM(d.cnt) as cnt
			FROM daily_count d left join people p on d.person_id = p.person_id
			GROUP BY p.person_id
			ORDER BY cnt DESC';
			
	$query = $PDO->query($sql);

	$data = '';
	foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row)
	{
		$data .= '<li>
					<h3 class="name"><input type="checkbox" value='.$row['person_id'].' checked onclick="checkboxChange(this)" />'.$row['name'].'</h3>
					<p class="value">'.$row['cnt'].'</p>
				</li>';
	}
	return $data;
}
?>