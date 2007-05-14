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
if (!(is_writable($configNew) || is_writable(dirname($configNew)))) {
	$instProbs[] = 'configWrite'; // ensure exists and is writable
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
	$dbFine = dbCheck($_SESSION['DSN']); // assign dbConnect prob if needed
}

$tpl->assign('dbConnected', $dbFine);

// Display if not fine or config is not going to be written
if (!($dbFine && isset($_REQUEST['writeConfig']))) {
	displayInst();
}

// Write the configuration file
if (isset($_REQUEST['writeConfig'])) {
	$writeConfig = fopen($configNew, 'w');
	if (!$writeConfig) {
		$instProbs[] = 'writeConfig';
		displayInst();
	}

	fwrite($writeConfig, str_replace(DB_DSN, $dbDsn, $src));
	fclose($writeConfig);
}

$_SESSION['configWritten'] = true;
$tpl->assign('configWritten', $_SESSION['configWritten']);

// Install tables
if (isset($_REQUEST['writeTables'])) {
	require(PATH_INSTALL . '/data.inc.php');

	// Insert the table schemas
	$schema = fopen(PATH_INSTALL . '/sql/server.' . strtolower($db->type) .
	 '.sql', 'r');
	if (!$schema) {
		$instProbs[] = 'dbSchema';
		displayInst();
	}

	$schemaTables = 0;
	$schemaTablesDone = 0;

	$currentQuery = '';
	while ($schemaLine = fgets($schema)) {
		$currentQuery .= $schemaLine;
		if ($schemaLine[strlen($schemaLine - 1)] === ';') {
			$createTable = $db->query($currentQuery);
			$currentQuery = '';

			++$schemaTables;
			if (!($db->hasError($createTable) || 
			     $db->affectedRows($createTable) < 1)) {
				++$schemaTablesDone;
			}
		}
	}

	// Insert star names
	$count = 0;
	$stars = fopen(PATH_INSTALL . '/starnames.txt', 'r');
	while (!feof($stars)) {
		$db->query('INSERT INTO starname VALUES (\'%[1]\')', fgets($stars));
		++$count;
	}

	// Insert all the tips
	$tipId = 0;
	foreach ($dat['tips'] as $tips) {
		$db->query('INSERT INTO daily_tips (tip_id, tip_content) VALUES (%[1], \'%[2]\')',
		 ++$tipId, $tips);
	}

	/*// Option list stuff
	$count = 0;
	foreach ($dat['options'] as $option) {
		$db->query('INSERT INTO option_list (option_name, option_min, option_max, option_desc, option_type) VALUES (\'%[1]\', %[2], %[3], \'%[4]\', %[5])',
		 $option[0], $option[1], $option[2], $option[3], $option[4]);
		++$count;
	}*/

	// Add administrator account
	$newAdmin = $db->query('INSERT INTO user_accounts (login_id, login_name, passwd, session_exp, session_id, in_game, email_address, signed_up, last_login, login_count, last_ip, num_games_joined, page_views, real_name, total_score, style) VALUES (1, \'Admin\', \'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855\', 0, \'\', NULL, \'Tyrant of the Universe\', 1, 1, 1, \'\', 0, 0, \'Game Administrator\', 0, NULL)');
} else {
	displayInst();
}

displayInst();

?>
