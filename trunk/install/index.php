<?php

require('config.inc.php');

require(PATH_INC . '/db.inc.php');

if (!class_exists('Savant2')) {
	require(PATH_SAVANT);
}
$tpl = new Savant2();
$tpl->addPath('template', PATH_INSTALL . '/tpl');

define('PATH_INSTALL', PATH_BASE . '/install');
define('URL_INSTALL', URL_BASE . '/install');

$configTpl = PATH_INSTALL . '/config.inc.php';
$configNew = PATH_INC . '/config.inc.php';

$problems = array();
$installed = false;

$dbFine = false;
$dbDsn = '';

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

if (isset($_REQUEST['dbType'])) {
	switch ($_REQUEST['dbType']) {
		case 'mysql':
			if (!(isset($_REQUEST['dbHostname']) && 
			     isset($_REQUEST['dbName']) && 
			     isset($_REQUEST['dbUsername']) && 
			     isset($_REQUEST['dbPassword']))) {
				$problems[] = 'MySQL requires a hostname, database name, username and password.';
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

		case 'postgresql':
			if (!(isset($_REQUEST['dbHostname']) && 
			     isset($_REQUEST['dbName']) && 
			     isset($_REQUEST['dbUsername']) && 
			     isset($_REQUEST['dbPassword']))) {
				$problems[] = 'PostgreSQL requires a hostname, database name, username and password.';
				break;
			}

			$dbDsn = 'postgresql://' . rawurlencode($_REQUEST['dbUsername']) .
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
