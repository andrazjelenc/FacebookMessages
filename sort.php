<?php
//true if first date is older
function isOlder($date1, $date2)
{
	//date example:
	//25. oktober 2015 ob 9:58 UTC+01
	//compare only years, months and days
	
	//change into your language
	$months = array('januar','februar','marec','april','maj','junij','julij','avgust','september','oktober','november','december');
	
	/* 
	 * COMPARE
	 */
	$date1 = explode(' ', $date1);
	$date2 = explode(' ', $date2);
	
	if($date1[2] > $date2[2]) //compare years
	{
		return true;
	}
	elseif(array_search($date1[1], $months) > array_search($date2[1], $months)) //compare months
	{
		return true;
	}
	elseif(substr($date1[0], 0, -1) > substr($date2[0], 0, -1)) //compare days
	{
		return true;
	}
	
	return false;
}

function getSorted($content)
{
	$content = explode('<div class="footer">',$content)[0]; //remove footer
	$content = explode('<div class="thread">', $content); //explode per messages blocks
	unset($content[0]); //remove header

	$output = array();
	$names = array();

	foreach($content as $blok)
	{
		$outBlok = array();
		$people = array();
		
		//explode per message
		$blok = explode('<div class="message">',$blok);
		
		$people = trim($blok[0]);
		$people = explode(', ',$people);
		unset($blok[0]);

		foreach($blok as $line)
		{
			$line = explode('</span>',$line);
			
			$author = trim($line[0]);
			$time = trim($line[1]);
			$message = trim($line[2],"</p>");
			
			$outBlok[] = array($author, $time, $message);
		}
		
		//$outBLok: array(autor, time, message)
		//$people: array(person1, person2,...)
		
		if(in_array($people, $names))
		{
			//date of first message in this block
			$myDate = $outBlok[0][1];
			
			//index of this chat group in $output
			$index = array_search($people, $names);
			
			//chat group size (0-people, 1-block1, 2-block2,...)
			$sizeSoFar = count($output[$index]);

			$goodIndex = -1;
			foreach($output[$index] as $iLoc => $data)
			{
				//skip people
				if($iLoc == 0)
				{
					continue;
				}
				
				//block date
				$thisDate = $data[0][1];
				
				if(isOlder($myDate, $thisDate))
				{
					$goodIndex = $iLoc;
					break;
				}
			}
			
			if($goodIndex == -1)
			{
				//we have the earliest block so far, attach to the end
				$output[$index][$sizeSoFar] = $outBlok;
			}
			else
			{
				//attend on $goodIndex
				//make space first
				for($i = $sizeSoFar-1; $i >= $goodIndex; $i --)
				{
					$output[$index][$i+1] = $output[$index][$i];
				}
				$output[$index][$goodIndex] = $outBlok;
			}
		}
		else
		{
			//add new chat group
			$names[] = $people;
			$output[][0] = $people;
			$output[count($output)-1][1] = $outBlok;
		}
	}
	
	return array($names, $output);
}

$content = file_get_contents('people.txt');
$data = getSorted($content);

//save to file
file_put_contents("data.json",json_encode($data));

?>
