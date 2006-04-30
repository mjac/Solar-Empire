<?php

ob_start();

if (!(file_exists('install') && is_dir('install'))) {
	exit('The install directory must exist.');
}

require_once('inc/external/mcdb/mcdb.class.php');

$configTpl = 'install/config.inc.php';
$configNew = 'inc/config.inc.php';

$problems = array();
$installed = false;

function checkOption($name, $var)
{
	global $_REQUEST;

	if (isset($_REQUEST[$name]) && 
	     strtolower($_REQUEST[$name]) === strtolower($var)) {
		echo ' selected="selected"';
	}
}

function currentValue($name)
{
	global $_REQUEST;

	if (isset($_REQUEST[$name])) {
		echo ' value="' . htmlentities(substr($_REQUEST[$name], 0, 256)) . '"';
	}
}

$db = new mcdb;
$dbFine = false;
$dbDsn = '';

if (isset($_REQUEST['dbType'])) {
	switch ($_REQUEST['dbType']) {
		case 'mysql':
			if (!(isset($_REQUEST['dbHostname']) && 
			     isset($_REQUEST['dbName']) && 
			     isset($_REQUEST['dbUsername']) && 
			     isset($_REQUEST['dbPassword']))) {
				$problems[] = 'MySQL requires a hostname, database name, ' .
				 'username and password.';
				break;
			}

			$dbDsn = 'mysql://' . rawurlencode($_REQUEST['dbUsername']) .
			 ':' . rawurlencode($_REQUEST['dbPassword']) . '@' . 
			 rawurlencode($_REQUEST['dbHostname']) . 
			 (isset($_REQUEST['dbPort']) ? (':' . (int)$_REQUEST['dbPort']) : 
			 '') . '/' . rawurlencode($_REQUEST['dbName']);

			$result = $db->connect($dbDsn);

			if (!$db->hasError($result)) {
				$dbFine = true;
				break;
			}

			$problems[] = 'Could not connect to the database: ' . 
			 $db->error($result);
			break;

		default:
			$problems[] = 'That database type is not supported.';
	}
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Solar Empire: System Wars Installation</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" media="screen" 
 href="install/clear.css" />
</head>
<body>
<h1>Solar Empire: System Wars Installation</h1>
<?php

if (is_dir('doc') && is_readable('doc/licence.txt') && 
     is_readable('doc/readme.txt')) {

	$fpReadme = fopen('doc/readme.txt', 'r');
	$readme = fread($fpReadme, filesize('doc/readme.txt'));
	fclose($fpReadme);

	$fpLicence = fopen('doc/licence.txt', 'r');
	$licence = fread($fpLicence, filesize('doc/licence.txt'));
	fclose($fpLicence);

?>

<h2>Documents</h2>

<h3>Readme</h3>
<p><textarea rows="10" cols="80"><?php echo htmlentities($readme); ?></textarea></p>

<h3>Licence</h3>
<p><textarea rows="10" cols="80"><?php echo htmlentities($licence); ?></textarea></p>
<?php
	
}

?>

<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post">
<h2>Database</h2>
<dl>
	<dt>Type</dt>
	<dd><select name="dbType">
		<option value="mysql"<?php checkOption('dbType', 'mysql'); ?>>MySQL</option>
		<!--<option value="postgresql"<?php checkOption('dbType', 'postgresql'); ?>>PostgreSQL</option>
		<option value="sqlite"<?php checkOption('dbType', 'sqlite'); ?>>Sqlite</option>-->
	</select></dd>

	<dt>Hostname or file</dt>
	<dd><input name="dbHostname"<?php currentValue('dbHostname'); ?> class="text" /></dd>

	<dt>Name</dt>
	<dd><input name="dbName"<?php currentValue('dbName'); ?> class="text" /></dd>

	<dt>Username</dt>
	<dd><input name="dbUsername"<?php currentValue('dbUsername'); ?> class="text" /></dd>

	<dt>Password</dt>
	<dd><input name="dbPassword"<?php currentValue('dbPassword'); ?> class="text" /></dd>
</dl>
<?php

if ($dbFine) {

?>
<h3>Connected!</h3>
<p>The generated DSN string is <code><?php echo htmlentities($dbDsn); ?></code></p>
<?php
}

if (isset($_REQUEST['sure']) && $dbFine) {
?>
<h2>Writing configuration</h2>
<?php
	if (is_readable($configTpl)) {
		$fp = fopen($configTpl, 'r');
		$src = fread($fp, filesize($configTpl));
		fclose($fp);

		require_once($configTpl);

		if (is_writable($configNew)) {
			$fp = fopen($configNew, 'w');
			fwrite($fp, str_replace(DB_DSN, $dbDsn, $src));
			fclose($fp);
?>
<p>Success! Now install the <a href="install_tables.php">database tables</a>.</p>
<?php
		} else {
			$problems[] = 'Could not save the configuration file; check the ' .
			 'write permissions on inc/config.inc.php.';
		}
	} else {
		$problems[] = 'Configuration file template does not exist.';
	}
}

?><p><input type="submit" value="Try settings" class="button" /><?php
if ($dbFine) {
?>

<input type="submit" value="Install" class="button" name="sure" /><?php
}
?></p>
<?php

if (!empty($problems)) {
?>
<h3>Problems</h3>
<ul>
<?php
	foreach ($problems as $problem) {
?>
	<li><?php echo htmlentities($problem); ?></li>
<?php
	}
?>
</ul>
<?php
}

?>
</form>
</body>
</html><?php

ob_end_flush();

?>
