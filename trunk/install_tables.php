<?php

if (!(file_exists('install') && is_dir('install'))) {
	exit('The install directory must exist.');
}

require_once('inc/config.inc.php');
require_once('inc/db.inc.php');
require_once('install/data.inc.php');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Solar Empire: System Wars Table Installation</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" media="screen" 
 href="install/clear.css" />
</head>
<body>
<h1>Solar Empire: System Wars Table Installation</h1>

<?php

if (isset($_REQUEST['sure'])) {
?>
<h2>Creating structure</h2><?php 

$schema = fopen('install/server.' . $db->type . '.sql', 'r');
$all = fread($schema, filesize('install/server.' . $db->type . '.sql'));
fclose($schema);

$queries = explode(';', $all);

$count = 0;
foreach ($queries as $query) {
	$done = $db->query('%s', $query);
	++$count;
}

?>
<p><?php echo $count; ?> queries executed.</p>

<h2>Inserting star-names</h2>
<?php

$count = 0;
$stars = fopen('install/star_names.txt', 'r');
while (!feof($stars)) {
	$db->query('INSERT INTO se_star_names VALUES (\'%s\')', 
	 array(fgets($stars)));
	++$count;
}

?>
<p><?php echo $count; ?> star-names have been inserted.</p>

<h2>Adding daily tips</h2>
<?php

$tipId = 0;
foreach ($dat['tips'] as $tips) {
	$db->query('INSERT INTO daily_tips (tip_id, tip_content) VALUES ' .
	 '(%u, \'%s\')', array(++$tipId, $db->escape($tips)));
}

?>
<p><?php echo $tipId; ?> daily tips have been inserted.</p>
<h2>Adding user options</h2>
<?php

$count = 0;
foreach ($dat['options'] as $option) {
	$db->query('INSERT INTO option_list (option_name, option_min, ' .
	 'option_max, option_desc, option_type) VALUES (\'%s\', %d, %d, \'%s\', ' .
	 '%u)', array($option[0], $option[1], $option[2], $option[3], $option[4]));
	++$count;
}

?>
<p><?php echo $count; ?> user options have been inserted.</p>

<h2>Adding administrator account</h2>
<p><?php

$newAdmin = $db->query('INSERT INTO user_accounts (login_id, login_name, ' .
 'passwd, session_exp, session_id, in_game, email_address, signed_up, ' .
 'last_login, login_count, last_ip, num_games_joined, page_views, ' .
 'real_name, total_score, style) VALUES (1, \'Admin\', ' .
 '\'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855\', 0, ' .
 '\'\', NULL, \'Tyrant of the Universe\', 1, 1, 1, \'\', 0, 0, ' .
 '\'Game Administrator\', 0, NULL)');	

echo $db->hasError($newAdmin) ? 'Failure' : 'Success';

?></p>
<h2>Result</h2>
<p>If all of the queries completed successfully, delete the install directory
and sign-in as Admin (no password).</p>
<?php
} else {
?>
<p><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>?sure=1">Install all the 
database tables</a> &#8212; this will wipe all server-level data including 
<strong>all user accounts</strong>.</p>
<?php
}

?>
</body>
</html>
