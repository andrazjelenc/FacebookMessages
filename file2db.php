<?php

//my id and name used for  determing whether who's who
$myId = '100000292515765';
$myName = 'Andraž Jelenc';

$allowWrite = false;	//filtering group chats away

$user = '';
$send_received_flag = 0;	//am I the sender ? 0 - yes
							//					1 - no
$datetime = '';		//datetime of message in MySQL format
							
$allUsers = array();	//dictionary connecting ids and names

//
//	Connection to MySQL DB
//
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

//
//	Controlling parsing data in corrent form
//
function procced($value, $type)
{
	global $allowWrite;
	
	global $user;
	global $send_received_flag;
	global $datetime;
	
	global $myId;
	global $myName;
	
	global $allUsers;
		
	switch ($type) {
		case 0: //all participant
			if (substr_count($value, ', ') == 1) //not a group chat
			{
				$allowWrite = true;
				
				//set person we talk to $user
				$users = explode(', ', $value);
				$users[0] = str_replace('&#064;facebook.com', '', $users[0]);
				$users[1] = str_replace('&#064;facebook.com', '', $users[1]);
				
				if($users[0] == $myId)
				{
					$user = $users[1];
				}
				else
				{
					$user = $users[0];
				}
			}
			else	//group chat
			{
				$allowWrite = false;
			}
			break;
			
		case 1: //message sender
			$value = str_replace('&#064;facebook.com', '', $value);
			if($value == $myId || $value == $myName)
			{
				//I am the sender
				$send_received_flag = 0;
			}
			else
			{
				//we receive
				$send_received_flag = 1;

				//value = is sender name here
				
				//connect name and id in $allUsers dictionary
				if(array_key_exists($user, $allUsers))	//this ID is already in dict
				{
					if($user != $value && !in_array($value, $allUsers[$user]))
					{
						$allUsers[$user][] = $value;
					}
				}
				else	//new ID
				{
					if($user == $value)
					{
						$allUsers[$user] = array();
					}
					else
					{
						$allUsers[$user] = array("0" =>$value);
					}
					
				}
				
			}
			break;
			
		case 2: //date and time
			$datetime = transformMeta($value);
			break;
			
		case 3: //message body
			$value = trim($value);
			insertIntoDatabase($user, $datetime, $value, $send_received_flag);
			break;
	}
}

//
// Convert facebook datetime to Y-m-d G:i:s MySQL format
//
function transformMeta($string)
{
	$date = date_create_from_format('l, F j, Y \a\t g:ia', substr($string,0, strlen($string)-7));
	return date_format($date, 'Y-m-d G:i:s');
}

//
//	Insertion into database (utf8_mb4)
//
function insertIntoDatabase($user, $datetime, $message, $send_received_flag)
{
	global $PDO;
	
	$stms = $PDO->prepare("
			INSERT INTO `messages` (`person_id`, `type`, `datetime`, `value`)
			VALUES (:person_id, :type, :datetime, :value);
		");
	
	$stms->bindValue(':person_id', $user);
	$stms->bindValue(':type', $send_received_flag);
	$stms->bindValue(':datetime', $datetime);
	$stms->bindValue(':value', $message);
	$stms->execute();
	
	//error handling...
	$error = $stms->errorInfo();
	if($error[0] != "00000")
	{
		var_dump($error);
	}
}

function insertName($id, $name)
{
	global $PDO;
	$stms = $PDO->prepare("
			INSERT INTO `people` (`person_id`, `name`)
			VALUES (:person_id, :name);
		");
	
	$stms->bindValue(':person_id', $id);
	$stms->bindValue(':name', $name);
	$stms->execute();
	
	//error handling...
	$error = $stms->errorInfo();
	if($error[0] != "00000")
	{
		var_dump($error);
	}
}
//open message file
$handle = fopen('messages.htm', 'r')
    or die ("Cannot open messages file!");


$tagOpen = false; //reading tag
$tag = '';

$valueOpen = false; //reading value
$value = '';
$type = ''; //value type: 	0 - all participants
			//				1 - message sender
			//				2 - date and time of message
			//				3 - message body

//
//	Reading file char by char
//
while (($buffer = fgets($handle, 4096)) !== false) 
{
    for($i = 0; $i < strlen($buffer); $i++)
	{
		//work done with this char
		$char = $buffer[$i];
		
		switch ($char) {
			case '<':	//tag open
				$tagOpen = true;
				
				//previous value
				if(strlen($value) > 0)
				{
					procced($value, $type);
				}
				
				$valueOpen = false;
				$value = '';
				break;
				
			case '>':	//tag close				
				switch ($tag) {
					case 'div class="thread"':	//read all participant
						$valueOpen = true;
						$type = 0;
						break;
						
					case 'span class="user"':	//read user
						if($allowWrite == true)
						{
							$valueOpen = true;
							$type = 1;
						}
						break;
						
					case 'span class="meta"':	//read date and time
						if($allowWrite == true)
						{
							$valueOpen = true;
							$type = 2;
						}
						break;
					case 'p':	//read message body		
						if($allowWrite == true)
						{
							$valueOpen = true;
							$type = 3;
						}
						break;
				}
				
				$tagOpen = false;
				$tag  = '';
				break;
				
			default:
				if($tagOpen == true)	//reading tag
				{
					$tag .= $char;
				}
				else if($valueOpen == true)	//reading value
				{
					$value .= $char;
				}
		}
	}
	
}
fclose($handle);

//
//	insert dictionary entries into database
//
foreach($allUsers as $id => $values)
{
	//$id is user facebook id
	//$values is array of names
	$vCount = count($values);
	
	$name = '';	
	switch ($vCount) {
		case 0:
			//no name
			$name = $id; //keep it simple for now
			break;
		case 1:
			//one name, love this situation
			$name = $values[0];
			break;
		default:
			//more names, ask user witch one is the one
			echo "------------------\r\n";
			echo "ID: $id\r\n";
			echo "Name to use:\r\n";
			foreach($values as $i => $value)
			{
				echo "[$i] - $value\r\n";
			}
			$input = fopen ("php://stdin","r");
			$line = fgets($input);
			$name = $values[trim($line)];				
	}
	insertName($id, $name);
	
}
?>
