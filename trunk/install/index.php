<?php

/**
 * Installer for System Wars
 * @author Michael J.A. Clark <mjac@mjac.co.uk>
 */ 
class swInstall
{
	/** Stores the template class */
	var $tpl;
	
	/** Stores the database class */
	var $db;

	/** Installer problems */
	var $problems = array();

	/** Variables each database type requires */
	var $dbRequires = array(
		'mysql' => array('hostname', 'database', 'username', 'password'),
		'postgresql' => array('hostname', 'database', 'username', 'password')
	);

	/** Initialise */
	function swInstall()
	{
		include('config.inc.php') || exit('Configuration template must exist.');
		
		define('PATH_INSTALL', PATH_BASE . '/install');
		define('URL_INSTALL', URL_BASE . '/install');
		
		if (!class_exists('Savant2')) {
			include(PATH_SAVANT) || exit('Savant2 template system missing.');
		}
		$this->tpl = new Savant2();
		$this->tpl->addPath('template', PATH_INSTALL . '/tpl');

		if (!include(PATH_INC . '/db.inc.php')) {
			$this->problems[] = 'dbInclude';
			$this->display();
			return;
		}

		session_start();
	}

	/** Display and exit */
	function display()
	{
		if (!empty($this->problems)) {
			$this->tpl->assign('instProbs', $this->problems);
		}
		$this->tpl->display('install.tpl.php');
		exit;
	}

	/** Process install */
	function process()
	{
		$this->readme();
	}



	/** Assign the readme to the template if it exists */
	function readme()
	{
		if (!is_readable(PATH_DOC . '/readme.txt')) {
			return false;
		}

		$fpReadme = fopen(PATH_DOC . '/readme.txt', 'rb');
		if ($fpReadme) {
			$readme = fread($fpReadme, filesize(PATH_DOC . '/readme.txt'));
			fclose($fpReadme);
		
			if ($readme) {
				$this->tpl->assign('readme', $readme);
				return true;
			}
		}

		return false;
	}

	/** Perform standard file checks on required installer files */
	function fileCheck()
	{
		$probCount = count($this->problems);

		// Only allow if writable or directory writable
		if (!(is_writable(PATH_INC . '/config.inc.php') ||
		     is_writable(PATH_INC))) {
			$this->problems[] = 'configWrite';
		}

		return $probCount === count($this->problems);
	}



	/** Make sure licence has been accepted */
	function licenceCheck()
	{
		if (isset($_REQUEST['licence'])) {
			switch ($_REQUEST['licence']) {
				case 'accept':
					$_SESSION['licenceAccept'] = true;
					return true;
				case 'reject':
					$this->licenceReset();
					return false;
			}
		}

		// Ensure they accept the licence
		$licenceOkay = false;
		if (is_readable(PATH_DOC . '/licence.txt')) {
			$fpLicence = fopen(PATH_DOC . '/licence.txt', 'rb');
	
			if ($fplicence) {
				$this->tpl->assign('licence', fread($fpLicence,
				 filesize(PATH_DOC . '/licence.txt')));
				fclose($fpLicence);
				$licenceOkay = true;
			}
		}

		// Could not open and assign the licence
		if (!$licenseOkay) {
			$this->problems[] = 'licenceOpen';
		}

		return false;
	}

	/** Reset licence state */
	function licenceReset()
	{
		unset($_SESSION['licenceAccept']);
		unset($_REQUEST['licence']);
	}



	/** Check for submitted database information */
	function dbCheck()
	{
		if (isset($_REQUEST['db'])) {
			if (isset($_REQUEST['db']['reset'])) {
				$this->dbReset();
			}

			$this->dbTypeCheck();
			$this->dbPrefix();
		}

		if (isset($_SESSION['DSN'])) {
			$dbConnect = $this->db->connect($_SESSION['DSN']);
			return true;
		}

		return false;
	}

	/** Reset database submission */
	function dbReset()
	{
		unset($_SESSION['DSN']);
		unset($_SESSION['dbPrefix']);
		unset($_REQUEST['db']);
	}

	/** Connect to a database or assign errors */
	function dbConnect($dbDsn)
	{
		$dbConnected = $this->db->connect($dbDsn);
	
		if ($this->db->hasError($dbConnected)) {
			$this->tpl->assign('dbConnectError', $this->db->error($result));
			return false;
		}

		$this->db->close();
		return true;
	}

	/** Ensure all variables are provided */
	function dbRequireCheck($dbType)
	{
		if (!(isset($_REQUEST['db']) && isset($dbRequires[$dbType]))) {
			$this->problems[] = 'dbRequirements';
			return false;
		}

		$reqMissing = array();
		foreach ($dbRequires[$dbType] as $reqName) {
			if (!isset($_REQUEST['db'][$reqName])) {
				$reqMissing[] = $reqName;
			}		
		}
		
		if (!empty($reqMissing)) {	
			$this->problems[] = 'dbRequirements';
			$tpl->assign('dbRequires', $reqMissing);
			return false;
		}

		return true;
	}

	/** Verifies database type is valid and can connect */
	function dbTypeCheck()
	{
		if (!isset($_REQUEST['db']['type'])) {
			return false;
		}

		$dbDsn = '';
		switch ($_REQUEST['db']['type']) {
			case 'mysql':
				$tpl->assign('dbType', 'MySQL');
				if (!$this->dbRequireCheck($_REQUEST['db']['type'])) {
					break;
				}
	
				$dbDsn = 'mysql://' . 
				 rawurlencode($_REQUEST['db']['username']) . ':' .
				 rawurlencode($_REQUEST['db']['password']) . '@' . 
				 rawurlencode($_REQUEST['db']['hostname']) . 
				 (isset($_REQUEST['db']['port']) ? (':' .
				 (int)$_REQUEST['db']['port']) : '') . '/' .
				 rawurlencode($_REQUEST['db']['database']);

				if (!$this->dbConnect($dbDsn)) {
					$dbDsn = '';
				}
				break;
	
			case 'postgresql':
				$tpl->assign('dbType', 'PostgreSQL');
				if (!$this->dbRequireCheck($_REQUEST['db']['type'])) {
					break;
				}

				$dbDsn = 'postgresql://' . 
				 rawurlencode($_REQUEST['db']['username']) . ':' .
				 rawurlencode($_REQUEST['db']['password']) . '@' . 
				 rawurlencode($_REQUEST['db']['hostname']) . 
				 (isset($_REQUEST['db']['port']) ? (':' .
				 (int)$_REQUEST['db']['port']) : '') . '/' .
				 rawurlencode($_REQUEST['db']['database']);

				if (!$this->dbConnect($dbDsn)) {
					$dbDsn = '';
				}
				break;

			default:
				$this->problems[] = 'dbType';
		}

		if (empty($dbDsn)) {
			$this->problems[] = 'dbConnect';
			return false;
		}

		$_SESSION['DSN'] = $dbDsn;
		return true;
	}

	/** Assign some kind of database table prefix */
	function dbPrefix()
	{
		if (isset($_REQUEST['db']) && isset($_REQUEST['db']['prefix'])) {
			if (preg_match('/[a-z_]/i', $_REQUEST['db']['prefix'])) {
				$_SESSION['dbPrefix'] = $_REQUEST['db']['prefix'];
				return true;
			}
			$this->problems[] = 'dbPrefix';
		}

		// Blank if invalid or missing
		if (!isset($_SESSION['dbPrefix'])) {
			$_SESSION['dbPrefix'] = '';
			return false;
		}

		return true;
	}



	function configCheck()
	{
		// Write the configuration file
		if (isset($_REQUEST['configWrite'])) {
			$writeConfig = fopen($configNew, 'wb');
			if (!$writeConfig) {
				$this->problems[] = 'configWrite';
				displayInst();
			}
		
			fwrite($writeConfig, str_replace(DB_DSN, $dbDsn, $src));
			fclose($writeConfig);
		}
		
		$_SESSION['configWritten'] = true;
		$tpl->assign('configWritten', $_SESSION['configWritten']);
	}

	function tables()
	{
		require(PATH_INSTALL . '/data.inc.php');
	
		// Insert the table schemas
		$schema = fopen(PATH_INSTALL . '/sql/server.' .
		 strtolower($db->type) . '.sql', 'rb');
		if (!$schema) {
			$this->problems[] = 'dbSchema';
			displayInst();
		}
	
		$schemaTables = 0;
		$schemaTablesDone = 0;
	
		$currentQuery = '';
		while (!feof($schema)) {
			$currentQuery .= fgets($schema);
			if ($currentQuery[strlen($currentQuery) - 1] === ';') {
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
		$starNames = 0;
		$starNamesDone = 0;
		$stars = fopen(PATH_INSTALL . '/starnames.txt', 'rb');
		while (!feof($stars)) {
			++$starNames;
			$starName = $db->query('INSERT INTO [server]starname VALUES (%[1])',
			 trim(fgets($stars)));
			if (!($db->hasError($starName) || 
			     $db->affectedRows($starName) < 1)) {
				++$starNamesDone;
			}
		}
	
		// Insert all the tips
		$tipNo = 0;
		$tipNoDone = 0;
		foreach ($dat['tips'] as $tipContent) {
			$tipQuery = $db->query('INSERT INTO [server]tip (tip_id, tip_content) VALUES (%[1], \'%[2]\')',
			 ++$tipNo, $tipContent);
			if (!($db->hasError($starName) || 
			     $db->affectedRows($starName) < 1)) {
				++$tipNoDone;
			}
		}
	
		// Add administrator account
		if (!class_exists('sha256')) {
			require(PATH_LIB . '/sha256/sha256.class.php');
		}
		require(PATH_LIB . '/sha256/sha256.class.php');
	
		$newAdmin = $db->query('INSERT INTO [server]account (login_id, login_name, passwd, session_exp, session_id, in_game, email_address, signed_up, last_login, login_count, last_ip, num_games_joined, page_views, real_name, total_score, style) VALUES (1, \'Admin\', 0x' . sha256::hash() . ', 0, \'\', NULL, \'Tyrant of the Universe\', 1, 1, 1, \'\', 0, 0, \'Game administrator\', 0, NULL)');
	}
}

/*

*/
?>
