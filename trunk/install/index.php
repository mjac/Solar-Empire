<?php

require('config.inc.php');

define('PATH_INSTALL', PATH_BASE . '/install');
define('URL_INSTALL', URL_BASE . '/install');

require(PATH_INC . '/db.inc.php');

if (!class_exists('Savant2')) {
	require(PATH_SAVANT);
}
$tpl = new Savant2();
$tpl->addPath('template', PATH_INSTALL . '/tpl');

$configTpl = PATH_INSTALL . '/config.inc.php';
$configNew = PATH_INC . '/config.inc.php';

session_start();

$instProbs = array();

function displayInst()
{
	global $instProbs, $tpl;

	if (!empty($instProbs)) {
		$tpl->assign('instProbs', $instProbs);
	}

	$tpl->display('install.tpl.php');
	exit;
}

// Assign useful documents
if (is_dir(PATH_DOC)) {
	if (is_readable(PATH_DOC . '/licence.txt')) {
		$fpReadme = fopen(PATH_DOC . '/readme.txt', 'r');
		$readme = @fread($fpReadme, filesize(PATH_DOC . '/readme.txt'));
		fclose($fpReadme);

		if ($readme) {
			$tpl->assign('readme', $readme);
		}
	}

	if (is_readable(PATH_DOC . '/readme.txt')) {
		$fpLicence = fopen(PATH_DOC . '/licence.txt', 'r');
		$licence = @fread($fpLicence, filesize(PATH_DOC . '/licence.txt'));
		fclose($fpLicence);

		if ($licence) {
			$tpl->assign('licence', $licence);
		}
	}
}

// Initial steps -- check if writable and exit if not
if (!is_writable($configNew)) {
	$instProbs[] = 'configWrite';
}

if (!empty($instProbs)) {
	displayInst();
}


// Go back to database stage if required
if (isset($_REQUEST['dbReset']) && isset($_SESSION['DSN'])) {
	unset($_SESSION['DSN']);
}

// Sort out the database
$dbFine = false;
$dbDsn = '';

function dbCheck($dbDsn)
{
	global $db, $tpl, $instProbs;
	
	$dbConnected = $db->connect($dbDsn);

	if (!$db->hasError($dbConnected)) {
		return true;
	}

	$instProbs[] = 'dbConnect';
	$tpl->assign('dbConnError', $db->error($result));

	return false;
}

if (isset($_REQUEST['dbType'])) {
	switch ($_REQUEST['dbType']) {
		case 'mysql':
			$tpl->assign('dbType', 'MySQL');
			if (!(isset($_REQUEST['dbHostname']) && 
			     isset($_REQUEST['dbName']) && 
			     isset($_REQUEST['dbUsername']) && 
			     isset($_REQUEST['dbPassword']))) {
				$tpl->assign('dbRequires', array('hostname', 'database name',
				 'username', 'password'));
				$instProbs[] = 'dbRequirements';
				break;
			}

			$dbDsn = 'mysql://' . rawurlencode($_REQUEST['dbUsername']) .
			 ':' . rawurlencode($_REQUEST['dbPassword']) . '@' . 
			 rawurlencode($_REQUEST['dbHostname']) . 
			 (isset($_REQUEST['dbPort']) ? (':' . (int)$_REQUEST['dbPort']) : 
			 '') . '/' . rawurlencode($_REQUEST['dbName']);

			$dbFine = dbCheck($dbDsn);
			break;

		case 'postgresql':
			$tpl->assign('dbType', 'PostgreSQL');
			if (!(isset($_REQUEST['dbHostname']) && 
			     isset($_REQUEST['dbName']) && 
			     isset($_REQUEST['dbUsername']) && 
			     isset($_REQUEST['dbPassword']))) {
				$tpl->assign('dbRequires', array('hostname', 'database name',
				 'username', 'password'));
				$instProbs[] = 'dbRequirements';
				break;
			}

			$dbDsn = 'postgresql://' . rawurlencode($_REQUEST['dbUsername']) .
			 ':' . rawurlencode($_REQUEST['dbPassword']) . '@' . 
			 rawurlencode($_REQUEST['dbHostname']) . 
			 (isset($_REQUEST['dbPort']) ? (':' . (int)$_REQUEST['dbPort']) : 
			 '') . '/' . rawurlencode($_REQUEST['dbName']);

			$dbFine = dbCheck($dbDsn);
			break;

		default:
			$instProbs[] = 'dbType';
	}
}

// Need to connect for the next few steps
if ($dbFine) { // already connected, maybe edited DSN
	$_SESSION['DSN'] = $dbDsn;
} elseif (isset($_SESSION['DSN'])) {
	$dbFine = dbCheck($_SESSION['DSN']);
}

$tpl->assign('dbConnected', $dbFine);


if (isset($_REQUEST['sure']) && $dbFine) {
?>
<h2>Writing configuration</h2>
<?php
	if (is_readable($configTpl)) {
		$fp = fopen($configTpl, 'r');
		$src = fread($fp, filesize($configTpl));
		fclose($fp);

		require_once($configTpl);

		if (is_writable($configNew) || !is_file($configNew)) {
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
