<?php

mt_srand((double)microtime() * 0x7FFFFFFF);

require_once('config.inc.php');

// magic quotes on. take slashes out
if (get_magic_quotes_gpc() == 1) {
	recursive_stripslashes($_GET);
	recursive_stripslashes($_POST);
	recursive_stripslashes($_COOKIE);
	recursive_stripslashes($_REQUEST);
}

// emulate register globals :^(
extract($_GET);
extract($_POST);
extract($_REQUEST);
extract($_COOKIE);

if(isset($_REQUEST['login_id'])){
	$login_id = (int)$_REQUEST['login_id']; //set login_id
} else {
	$login_id = 0;
}

if(isset($_REQUEST['session_id'])){
	$session_id = $_REQUEST['session_id']; //set session_id
} else {
	$session_id = 0;
}

//initial declarations for certain global vars
//not particularly necessary, but just to make sure.
$db_name = "";
$p_user = array();
$game_info = array();


/**********************
Page Display Functions
***********************/

function print_header($title)
{
	global $user_options, $directories;

	$style = esc(URL_SHORT . '/css/style' . (isset($user_options['color_scheme']) ?
	 $user_options['color_scheme'] : 1) . '.css');
	$title = esc($title);
	$js = esc(URL_SHORT . '/js/common.js');

	print <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>$title &laquo; Solar Empire</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="$style" />
<script type="text/javascript" src="$js"></script>
</head>
<body>

END;
}

//prints the bottom of a page.
function print_footer()
{
	print <<<END

</body>
</html>
END;
}


/**********************
Input Checking Functions
**********************/

//allows alphanumeric and the some other characters but no spaces
function valid_input($input)
{
	return preg_match('/^[a-z0-9~@$%&*_+-=£§¥²³µ¶]+$/i',$input);
}


//allows alphanumeric and the some other characters, as well as spaces. removes HTML and PHP.
function correct_name($input)
{
	$input = htmlspecialchars(strip_tags($input));
	return trim(preg_replace('/[^a-z0-9~@$%&*_+-=£§¥²³µ¶ .]/i', '', $input));
}


//function to remove all slashes (useful for magic quotes);
function recursive_stripslashes(&$var) {
	foreach($var AS $key => $value) {
		if (is_array($value)) {
			recursive_stripslashes($value);
		} else {
			$var[$key] = stripslashes($value);
		}
	}
}


/**********************
* Database Functions
**********************/
function dbDie()
{
	headers_sent() or header('Content-Type: text/plain');
	print "ERROR!\n" . mysql_error() . "\nBACKTRACE\n";
	var_dump(debug_backtrace());
	exit();
}
/*
Function: connect to the database. Will write to the error log if cannot connect.
*/
function db_connect(){
	global $database_link;
	$database_link = @mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD) or
	 die("No connection to the Database could be created.<p>The following error was reported:<br><b>".mysql_error()."</b>");
	mysql_select_db(DATABASE, $database_link) or mysql_die("");
}

//send a query to the database.
function db($string)
{
	global $db_func_query, $database_link;
	$db_func_query = mysql_query($string, $database_link) or dbDie();
}

//collect results of query made by db() function
function dbr($rest_type = 0)
{
	global $db_func_query;
	if($rest_type == 0){
		return mysql_fetch_array($db_func_query, MYSQL_BOTH);
	} else {
		return mysql_fetch_assoc($db_func_query);
	}
}

//send a query to the database.
function db2($string)
{
	global $db_func_query2, $database_link;
	$db_func_query2 = mysql_query($string, $database_link) or dbDie();
}

//collect results of query made by db2() function
function dbr2($rest_type = 0)
{
	global $db_func_query2;
	if($rest_type == 0){
		return mysql_fetch_array($db_func_query2, MYSQL_BOTH);
	} else {
		return mysql_fetch_array($db_func_query2, MYSQL_ASSOC);
	}
}

//send an update or insert query to the database. no select's.
function dbn($string)
{
	global $database_link;
	mysql_query($string, $database_link) or dbDie();
}




/**********************
HTML Table Functions
***********************/

// will output the beginning of a properly formatted table putting
//the values of the passed array in as the table headers;
// - expects an array.
function make_table($input, $width = "") {
	$ret_str = "<table cellspacing=1 cellpadding=2 border=0 $width><tr bgcolor=#555555>";
	foreach($input as $value) {
		$ret_str .= "\n<td>$value</td>";
	}
	return $ret_str."\n</tr>";
}

//outputs a row of a table with the number values made bold;
// -- expects a array.
function make_row($input) {
	$ret_str = "\n<tr bgcolor=#333333 align=left>";

	foreach($input as $value) {
		if((ord($value) < 48) || (ord($value) > 57)) { //only make numbers bold
			$ret_str .= "\n<td>$value</td>";
		} else {
			$ret_str .= "\n<td><b>$value</b></td>";
		}
	}
	return $ret_str."\n</tr>";
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

function mcit($text)
{
	global $msgColours, $colImplode;

	$text = preg_replace('/\[link ?\'(.*?)\']([\S\s]*?)\[\/link\]/', '<a href="\1" target="_blank">\2</a>',
	preg_replace('/\[color \'(' . $colImplode . ')\'\]([\S\s]*?)\[\/color\]/ie', '"<span style=\"color: #" . $msgColours["\1"] . ";\">\2</span>"',
	preg_replace('/\[color \'(#[0-9A-F]{6})\'\]([\S\s]*?)\[\/color\]/i', '<span style="color: \1;">\2</span>',

	preg_replace('/\[b\]([\S\s]*?)\[\/b\]/i', '<b>\1</b>',
	preg_replace('/\[i\]([\S\s]*?)\[\/i\]/i', '<i>\1</i>',
	preg_replace('/\[hr\]/i', '<hr />',
	preg_replace('/[^ \n]{128,}/', '<strong>Attempted spam!</strong> ',
	/* Clever function to stop loads of lines being put on a page! */
	preg_replace('/(\n+)/e', 'str_repeat("<br />", strlen("\1") === 1 ? 1 : 2) . "\n"',

	str_replace("\r", '', // windows sucks :(

	htmlentities(trim($text)))))))))));

	return $text;
}

//outputs a row of a table with the array values bolded in each cell; expects a four-element array.
function quick_row($name, $value)
{
	return "\t<tr align=\"left\">\n\t\t<td bgcolor=\"#555555\">$name</td>\n" .
	 "\t\t<td bgcolor=\"#333333\">$value</td>\n\t</tr>\n";
}

/**********
Data update/insertion Functions
**********/

//function to insert an entry into the user_history table
function insert_history($l_id,$i_text)
{
	global $db_name;

	if (empty($db_name)) {
		$db_name = "None";
	}

	dbn('INSERT INTO user_history VALUES (' . (int)$l_id . ', ' . time() .
	 ', \'' . mysql_real_escape_string($db_name) . '\', \'' .
	 mysql_real_escape_string($i_text) . '\', \'' .
	 mysql_real_escape_string($_SERVER['REMOTE_ADDR']) . '\', \'' .
	 mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']) . '\')');
}

//post an entry into the news
function post_news($headline)
{
	global $login_id, $db_name;

	db_connect();

	dbn('INSERT INTO ' . $db_name . 
	 '_news (timestamp, headline, login_id) VALUES (' . time() . ', \'' .
	 mysql_real_escape_string($headline) . '\', ' . (int)$login_id . ')');
}

//function that will send a header correct e-mail, or return failure if it doesn't work
function send_mail($myname, $myemail, $contactname, $contactemail, $subject, $message)
{
	$headers = "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/plain; charset=iso-8859-1\n";
	$headers .= "From: \"".$myname."\" <".$myemail.">\n";
	return (mail("\"".$contactname."\" <".$contactemail.">", $subject, $message, $headers));
}


/********************
Ship Information Functions
********************/

//function to figure out the size of a ship in textual terms
function discern_size ($size)
{
	if($size == 1){
		return "Tiny";
	} elseif($size == 2){
		return "Very Small";
	} elseif($size == 3){
		return "Small";
	} elseif($size == 4){
		return "Medium";
	} elseif($size == 5){
		return "Large";
	} elseif($size == 6){
		return "Very Large";
	} elseif($size == 7){
		return "Huge";
	} elseif($size == 8){
		return "Gigantic";
	}
}



/********************
Authorisation Checking Functions
********************/

//function that will check to see if a player is logged in using session_id's.
//if user is the admin, it will set db_name, and game_info
function check_auth() {
	global $session_id, $login_id, $db_name, $p_user, $game_info;

	//get all details for the user with that sessionid/login_id combo
	//if the admin, don't use the session_id as a key
	db("select * from user_accounts where login_id = '$login_id' && session_id = '$_COOKIE[session_id]'");
	$p_user = dbr(1);

	//admin session id/ session_exp
	if ($login_id == 1) {
		db("select * from se_games where session_id = '$session_id'");
		$game_info = dbr(1);
		$p_user['session_id'] = $game_info['session_id'];
		$p_user['session_exp'] = $game_info['session_exp'];
		$db_name = $game_info['db_name'];
	}

	//echo $p_user['session_exp']."<br>".time();
	$next_exp = time() + SESSION_TIME_LIMIT;

	//session is invalid.
	if ($session_id == '' || $login_id == 0 || $session_id != $p_user['session_id'] ||
	     $p_user['session_exp'] < time()) {//session expired or invalid
		setcookie("p_pass", '', time() - 20);
		setcookie("session_id", '', time() - 20);
		setcookie("login_id", '', time() - 20);
		header('Location: login_form.php');
		exit;
	} elseif ($login_id != 1) { //session o.k.
		dbn("update user_accounts set session_exp = '$next_exp', page_views = page_views + 1 where login_id = '$login_id'");
		++$p_user['page_views'];
		$p_user['session_exp'] = $next_exp;
		$db_name = $p_user['in_game'];
	} elseif($login_id == 1){ //update admin session time
		dbn("update se_games set session_exp = '$next_exp' where db_name = '$db_name'");
		$p_user['session_exp'] = $next_exp;
	}
}

function gameVars($db_name)
{
	$options = mysql_query("SELECT `name`, `value` from `{$db_name}_db_vars`") or die(mysql_error());
	while (list($name, $value) = mysql_fetch_row($options)) {
		$GLOBALS[$name] = (int)$value;
	}
}


/********************
Calculating Functions
*********************/

//function used to work out players scores
function score_func($login_id,$full){
	global $score_method,$db_name;

/*
Listed below are all of the score methods, and some info on them.
0 = Scores are off.
1 = (fighter kills + (ship kills * 10)) - (fighter kills * 0.75 + (ship kills *5))
2 = ship points killed - (ship points lost * 0.5)
3 = total value (ship/planet fighters plus ship point value)
4 = ultimate score. takes everything into account.
*/

	//determines if admin is updateing all scores, or an individual players score is being updated.
	if($full != 1) {
		db("select value from ${db_name}_db_vars where name = 'score_method'");
		$alpha_var = dbr();
		$score_method = $alpha_var['value'];
		$extra_text = "login_id = '$login_id'";
		$plan_text = "login_id = '$login_id'";
	} else {
		$and_text = " && ";
		$extra_text = $plan_text = "login_id != " . ADMIN_ID;
	}
	if($score_method==1){ //scoring method, whereby only kills,are taken into account.
		dbn("update ${db_name}_users set score = (fighters_killed + (ships_killed * 10)) - (fighters_lost * 0.75 + (ships_lost * 5)) where ".$extra_text);
	} elseif($score_method == 2){ //takes into account ships lost, ships killed, fighters lost, fighters killed.
		dbn("update ${db_name}_users set score = ships_killed_points - (ships_lost_points * 0.5) where ".$extra_text);
	} elseif($score_method == 3){ //total fiscal value score.
		db("select sum(fighters) + sum(point_value), login_id from ${db_name}_ships where ".$extra_text." GROUP BY login_id");
		db2("select sum(fighters), login_id from ${db_name}_planets where ".$plan_text." GROUP BY login_id");
		if($full == 1){
			while($plan_array = dbr2()){
				$temp_plan_array[$plan_array['login_id']] = $plan_array[0];
			}
			while($ship_array = dbr()){
				$ship_array[0] += $temp_plan_array[$ship_array['login_id']];
				dbn("update ${db_name}_users set score = '$ship_array[0]' where login_id = '$ship_array[login_id]'");
			}
			dbn("update ${db_name}_users set score = 0 where score < 0");
		} else {
			$ship_array = dbr();
			$plan_array = dbr2();
			$ship_array[0] += $plan_array[0];
			dbn("update ${db_name}_users set score = '$ship_array[0]' where login_id = '$login_id'");
		}
	}
}

//function used to calculate the percentage of something. does not divide things by 0 though.
function calc_perc($num1,$num2){
	if($num1 == 0 || $num2 == 0){
		return "$num1 (0%)";
	} else {
		$result = number_format(($num1 / $num2) * 100, 2, '.','');
		return number_format($num1)." (".$result."%)";
	}
}

//function to figure out how many empty cargo bays there are on the ship.
function empty_bays(&$ship)
{
	$ship['empty_bays'] = $ship['cargo_bays'] - $ship['metal'] -
	 $ship['fuel'] - $ship['elect'] - $ship['colon'] - $ship['organ'];
}




/*********************
Misc Functions
*********************/

//function that will create a help-link.
function popup_help($topic, $height, $width, $string = "Info")
{
	return '<a href="' . esc($topic) . '" onclick="popup(\'' . $topic .
	 '\', ' . (int)$height . ',' . $width . '); return false;">' . $string .
	 '</a>';
}

//pilfered from the net. and altered it a little for good measure.
//creates a alpha-numeric string of $length. contains uper and lower case chars).
function create_rand_string($length)
{
	// salt to select chars from
	$salt = "abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789";
	$ret_str = "";

	for ($i=0;$i<$length;$i++){ // loop and create password
		$ret_str .= substr($salt, mt_rand() % strlen($salt), 1);
	}
	return $ret_str;
}


//makes a ship using the parts specified in $ship_parts (array), ship_owner (also array)
//returns id of ship inserted.
function make_ship($ship_parts, $ship_owner)
{
	global $db_name;
	dbn("insert into ${db_name}_ships (ship_name, login_id, login_name, clan_id, shipclass, class_name, class_name_abbr, fighters, max_fighters, max_shields, cargo_bays, mine_rate_metal, mine_rate_fuel, config, size, upgrades, move_turn_cost, point_value, num_ot, num_dt, num_pc, num_ew) values('".trim((string)$ship_parts['ship_name'])."', '$ship_owner[login_id]', '$ship_owner[login_name]', '$ship_owner[clan_id]', '$ship_parts[type_id]', '$ship_parts[name]', '$ship_parts[class_abbr]', '$ship_parts[fighters]', '$ship_parts[max_fighters]', '$ship_parts[max_shields]', '$ship_parts[cargo_bays]', '$ship_parts[mine_rate_metal]', '$ship_parts[mine_rate_fuel]', '$ship_parts[config]', '$ship_parts[size]', '$ship_parts[upgrades]', '$ship_parts[move_turn_cost]', '$ship_parts[point_value]', '$ship_parts[num_ot]', '$ship_parts[num_dt]', '$ship_parts[num_pc]', '$ship_parts[num_ew]')");
	return mysql_insert_id();
}

// escape a string std
function esc($str)
{
	return htmlspecialchars($str);
}


?>
