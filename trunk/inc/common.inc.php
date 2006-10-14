<?php

header('Cache-control: no-cache'); // HTTP 1.1
mt_srand((double)microtime() * 0x7FFFFFFF);

require_once('inc/config.inc.php');


// REMOVE QUOTES

if (get_magic_quotes_gpc() == 1) {
	recursive_stripslashes($_GET);
	recursive_stripslashes($_POST);
	recursive_stripslashes($_COOKIE);
	recursive_stripslashes($_REQUEST);
}


// REGISTER GLOBALS...

extract($_REQUEST);

$login_id = isset($_REQUEST['login_id']) ? (int)$_REQUEST['login_id'] : NULL;
$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : NULL;

$self = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';


/**********************
Input Checking Functions
**********************/

//allows alphanumeric and the some other characters but no spaces
function valid_name($input)
{
	return preg_match('/^[[:alnum:][:punct:]]{4,32}$/i',$input) &&
	 !is_numeric($input);
}
//allows alphanumeric and the some other characters and spaces
function valid_spaced_name($input)
{
	return preg_match('/^[[:alnum:][:punct:] ]{4,32}$/i', $input) &&
	 !is_numeric($input);
}

//allows alphanumeric and the some other characters but no spaces
function valid_input($input)
{
	return preg_match('/^[[:alnum:][:punct:]]+$/i', $input);
}

//allows alphanumeric and the some other characters, as well as spaces. removes HTML and PHP.
function correct_name($input)
{
	$input = preg_replace('/[^[[:alnum:][:punct:]] ]/i', '', trim($input));
	return empty($input) ? 'Nameless' : $input;
}

function isEmailAddr($address)
{
	$qText = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
	$dText = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';

	$atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e' .
	 '\\x40\\x5b-\\x5d\\x7f-\\xff]+';
	$domainRef =& $atom;

	$quotedPair = '\\x5c[\\x00-\\x7f]';
	$domainLiteral = "\\x5b($dText|$quotedPair)*\\x5d";

	$quotedStr = "\\x22($qText|$quotedPair)*\\x22";
	$word = "($atom|$quotedStr)";

	$subDomain = "($domainRef|$domainLiteral)";
	$domain = "$subDomain(\\x2e$subDomain)*";

	$localPart = "$word(\\x2e$word)*";

	$addrSpec = "$localPart\\x40$domain";

	return preg_match('/^' . $addrSpec . '$/', $address) ? true : false;
}


//function to remove all slashes (useful for magic quotes);
function recursive_stripslashes(&$var)
{
	foreach ($var as $key => $value) {
		if (is_array($value)) {
			recursive_stripslashes($var[$key]);
		} else {
			$var[$key] = stripslashes($value);
		}
	}
}


/**********************
HTML Table Functions
***********************/

// will output the beginning of a properly formatted table putting
//the values of the passed array in as the table headers;
// - expects an array.
function make_table($input)
{
	$ret_str = "<table class=\"simple\">\n\t<tr>\n";
	foreach($input as $value) {
		$ret_str .= "\t\t<th>$value</th>\n";
	}
	return $ret_str."\t</tr>\n";
}

//outputs a row of a table with the number values made bold;
// -- expects a array.
function make_row($input)
{
	$ret_str = "\t<tr>\n";

	foreach ($input as $value) {
		$ret_str .= "\t\t<td>$value</td>\n";
	}

	return $ret_str."\t</tr>\n";
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
function checkAuth()
{
	global $session_id, $login_id, $account, $gameInfo, $db;

	//get all details for the user with that sessionid/login_id combo
	//if the admin, don't use the session_id as a key
	$info = $db->query('SELECT * FROM user_accounts WHERE login_id = %[1] AND session_id = \'%[2]\'', 
	 $login_id, $session_id);
	$account = $db->fetchRow($info);

	$next_exp = time() + SESSION_TIME_LIMIT;

	// Session is invalid.
	if ($session_id == '' || $login_id == 0 || 
	     $session_id != $account['session_id'] ||
	     $account['session_exp'] < time()) {//session expired or invalid
		unset($account, $login_id, $session_id, $gameInfo);
		return false;
	}

	$db->query('UPDATE user_accounts SET session_exp = %[1], page_views = page_views + 1 WHERE login_id = %[2]', 
	 $next_exp, $login_id);

	define('IS_OWNER', $account['login_id'] == OWNER_ID);

	++$account['page_views'];

	if ($account['in_game'] !== NULL) {
		if (!$gameInfo = selectGame($account['in_game'])) {
			$db->query('UPDATE user_accounts SET in_game = NULL WHERE login_id = %[1]', 
			 $login_id);
			$account['in_game'] = NULL;
		    return false;
		}

		define('IS_ADMIN', $account['login_id'] == $gameInfo['admin']);

		$account['session_exp'] = $next_exp;
	}

	return true;
}

function selectGame($db_name)
{
	global $db;

	$db->addVar('game', $db->escape($db_name));

	$gQuery = $db->query('SELECT * FROM se_games WHERE db_name = \'[game]\'');
	$gameInfo = $db->fetchRow($gQuery);
	if (empty($gameInfo)) {
	    return false;
	}

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

?>
