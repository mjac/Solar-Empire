<?php

if (!(file_exists('install') && is_dir('install'))) {
	exit('The install directory must exist.');
}

require_once('inc/config.inc.php');
require_once('inc/db.inc.php');

function runSchema($file)
{
	global $db;

	$results = array();

	if (!file_exists($file)) {
		$results[] = 'Unsupported database type: could not find schema (' . 
		 $file . ').';
		return $results;
	}

	$fp = fopen($file, 'r');
	$query = '';

	while (!feof($fp)) {
		$line = fgets($fp);
		if (strpos(ltrim($line), '--') === 0) {
			$result = $db->action($db->query($query));
			$results[] = $result->result;
			$query = '';
		} else {
			$query .= $line;
		}
	}

	$action = $db->action($db->query($query));
	$results[] = $action->result;

	return $results;
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Solar Empire: System Wars Table Installation</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/style1.css" />
</head>
<body>
<h1>Solar Empire: System Wars Table Installation</h1>

<?php

if (isset($_REQUEST['sure'])) {
	$result = runSchema('install/server.' . $db->type . '.sql');
?>
<h2>Executing queries</h2>
<ol>
<?php
	foreach ($result as $query) {
?>
	<li><?php echo htmlentities($query); ?></li>
<?php
	}
?>
</ol>
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
<p><?php echo $count; ?> star names have been inserted.</p>
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