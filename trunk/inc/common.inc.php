<?php

if (!defined('PATH_BASE')) {
	require('inc/config.inc.php');
}

$msgColours = array(
	'blue'   => '0000FF',
	'lime'   => '00FF00',
	'green'  => '00CC00',
	'red'    => 'FF0000',
	'black'  => '000000',
	'white'  => 'FFFFFF',
	'yellow' => 'FFFF00',
	'cyan'   => '00FFFF',
	'pink'   => 'FF00FF',
	'purple' => 'CC66CC',
	'orange' => 'FFCC00'
);

$colImplode = implode('|', array_keys($msgColours));

$smileTypes = array(
	'happy', 'mad', 'sad', 'surp', 'tongue', 'wink', 'oh',
	'unsure', 'cool', 'laugh', 'blush', 'sealed'
);
$smileSets = array('', 'war', 'cow', 'pirate', 'evil');

$smTImplode = implode('|', $smileTypes);
$smSImplode = implode('|', $smileSets);

function msgToHTML($text)
{
	global $msgColours, $colImplode;

	$text = preg_replace('/\[color=(' . $colImplode . ')\]([\S\s]*?)\[\/color\]/ie',
	 '"<span style=\"color: #" . $msgColours["\1"] . ";\">\2</span>"',
	preg_replace('/\[color=(#[0-9A-F]{6})\]([\S\s]*?)\[\/color\]/i',
	 '<span style="color: \1;">\2</span>',

	preg_replace('/\[b\]([\S\s]*?)\[\/b\]/i', '<b>\1</b>',
	preg_replace('/\[i\]([\S\s]*?)\[\/i\]/i', '<i>\1</i>',
	preg_replace('/\[hr\]/i', '<hr />',
	preg_replace('/[^ \n]{128,}/', '<strong>Attempted spam!</strong> ',
	/* Clever function to stop loads of lines being put on a page! */
	preg_replace('/(\n+)/e', 'str_repeat("<br />", strlen("\1") === 1 ? 1 : 2) . "\n"',

	str_replace("\r", '', htmlspecialchars(trim($text))))))))));

	return $text;
}

//outputs a row of a table with the array values bolded in each cell; expects a four-element array.
function quick_row($name, $value)
{
	return "\t<tr align=\"left\">\n\t\t<th>$name</th>\n" .
	 "\t\t<td>$value</td>\n\t</tr>\n";
}

/**********
Data update/insertion Functions
**********/

//function to insert an entry into the user_history table
function insert_history($userId, $text)
{
	global $db;
	$db->query('INSERT INTO user_history VALUES (%[1], %[2], \'[game]\', \'%[3]\', \'%[4]\', \'%[5]\')', 
	 $userId, time(), $text, $_SERVER['REMOTE_ADDR'],
	 $_SERVER['HTTP_USER_AGENT']);
}

//post an entry into the news
function post_news($headline)
{
	global $account, $db;

	$newId = newId('[game]_news', 'news_id');

	$db->query('INSERT INTO [game]_news (news_id, timestamp, headline, login_id) VALUES (%[1], %[2], \'%[3]\', %[4])', 
	 $newId, time(), $headline, $account['login_id']);
}


/********************
Ship Information Functions
********************/

//function to figure out the size of a ship in textual terms
function discern_size($hull)
{
	static $sizes = array('Small', 'Medium', 'Large', 'Immense', 'Gigantic');

	$pos = floor(sqrt($hull) / 10);
	$max = count($sizes) - 1;

	return $sizes[$pos > $max ? $max : $pos];
}



/********************
Authorisation Checking Functions
********************/

// Function that will check to see if a player is logged in using session_id's
// If user is the admin, it will set db_name, and game_info

function selectGame($db_name)
{
	global $db;

	$gQuery = $db->query('SELECT * FROM se_games WHERE db_name = \'%[1]\'', 
	 $db_name);

	$gameInfo = $db->fetchRow($gQuery);
	if (empty($gameInfo)) {
	    return false;
	}

	$db->addVar('game', $db_name);
	gameVars($db_name);

	return $gameInfo;
}


function gameVars($db_name)
{
	global $db, $gameOpt;
	$gameOpt = array();

	$options = $db->query("SELECT name, value from {$db_name}_db_vars");
	while (list($name, $value) = $db->fetchRow($options, ROW_NUMERIC)) {
		$gameOpt[$name] = (int)$value;
	}
}

// Calculate and format the percentage of a fraction
function calc_perc($num, $den)
{
	return $den == 0 ? 'Invalid' : number_format($num) . ' (' . 
	 number_format(($num / $den) * 100, 2, '.', '') .' %)';
}


/*********************
Misc Functions
*********************/


function randomString($length, 
 $characters = 'abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789')
{
	$cMax = strlen($characters) - 1;

	$str = '';
	while (--$length >= 0) { // loop and create password
		$str .= $characters[mt_rand(0, $cMax)];
	}

	return $str;
}


//makes a ship using the parts specified in $ship_parts (array), ship_owner (also array)
//returns id of ship inserted.
function make_ship($parts, $owner)
{
	global $db;

	$newId = newId('[game]_ships', 'ship_id');

	// build the new ship
	$result = $db->query('INSERT INTO [game]_ships (ship_id, ship_name, login_id, type_id, location, fighters, max_fighters, max_shields, cargo_bays, mining_rate, config, upgrades, point_value, hull, max_hull, auxiliary_ship) VALUES (%[1], \'%[2]\', %[3], %[4], %[5], %[6], %[7], %[8], %[9], %[10], \'%[11]\', %[12], %[13], %[14], %[15], %[16])',
	 $newId, $parts['ship_name'], $owner['login_id'], $parts['type_id'], 
	 isset($parts['location']) ? $parts['location'] : 1, $parts['fighters'], 
	 $parts['max_fighters'], $parts['max_shields'], $parts['cargo_bays'], 
	 $parts['mining_rate'], $parts['config'], $parts['upgrades'], 
	 $parts['point_value'], $parts['hull'], $parts['max_hull'], 
	 $parts['auxiliary_ship'] === NULL ? 'NULL' : $parts['auxiliary_ship']);

	return $db->hasError($result) ? false : $newId;
}

// escape a string for xml type documents
function esc($str)
{
	return htmlentities($str, ENT_QUOTES, 'UTF-8');
}

function clearImages($path)
{
	if (!file_exists($path)) {
		return;
	}

	$dir = opendir($path);
	while ($file = readdir($dir)) {
		if (substr($file, -4) === '.png') {
			unlink($path . '/' . $file);
		}
	}

	closedir($dir);
}

?>
