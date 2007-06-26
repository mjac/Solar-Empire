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
		if (!include('config.inc.php')) {
			exit('Configuration template must exist.');
		}
		
		define('PATH_INSTALL', PATH_BASE . '/install');
		define('URL_INSTALL', URL_BASE);
		
		if (!(class_exists('Savant2') || include(PATH_SAVANT))) {
			exit('Savant2 template system missing.');
		}
		$this->tpl = new Savant2();
		$this->tpl->addPath('template', PATH_INSTALL . '/tpl');

		if (!(class_exists('swDatabase') ||
		     @include(PATH_INC . '/swDatabase.class.php'))) {
			exit('Database include missing.');
		}
		$this->db = new swDatabase;

		session_start();
	}

	/** Process install */
	function process()
	{
		$stage = 'complete';

		if ($this->licenceCheck()) {
			if ($this->dbCheck()) {
				if ($this->configCheck()) {
					if ($this->tableCheck()) {
						$stage = 'complete';
					} else {
						$stage = 'tables';
					}
				} else {
					$stage = 'config';
				}
			} else {
				$stage = 'database';
			}
		} else {
			$this->readme();
			$stage = 'licence';
		}

		if (!empty($this->problems)) {
			$this->tpl->assign('instProbs', $this->problems);
		}

		$this->tpl->assign('stage', $stage);
		$this->tpl->display($stage . '.tpl.php');
	}


	// LICENCE

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

	/** Make sure licence has been accepted */
	function licenceCheck()
	{
		if (isset($_REQUEST['licence'])) {
			switch ($_REQUEST['licence']) {
				case 'accept':
					$_SESSION['licenceAccept'] = true;
					return true;
				case 'reject':
					$this->configReset();
					$this->dbReset();
					$this->licenceReset();
			}
		}

		if (isset($_SESSION['licenceAccept'])) {
			return true;
		}

		// Ensure they accept the licence
		$licenceOkay = false;
		if (is_readable(PATH_DOC . '/licence.txt')) {
			$fpLicence = fopen(PATH_DOC . '/licence.txt', 'rb');
	
			if ($fpLicence) {
				$this->tpl->assign('licence', fread($fpLicence,
				 filesize(PATH_DOC . '/licence.txt')));
				fclose($fpLicence);
				$licenceOkay = true;
			}
		}

		// Could not open and assign the licence
		if (!$licenceOkay) {
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


	// DATABASE

	/** Check for submitted database information */
	function dbCheck()
	{
		if (isset($_REQUEST['db'])) {
			if (isset($_REQUEST['db']['reset'])) {
				$this->configReset();
				$this->dbReset();
			}

			$this->dbTypeCheck();
			$this->dbPrefix();
		}

		// Could be set by $this->dbTypeCheck, true only if success though
		if (isset($_SESSION['dbDSN']) &&
		     !$this->db->hasError($this->db->connect($_SESSION['dbDSN']))) {
			$this->db->addVar('server', isset($_SESSION['dbPrefix']) ?
			 $_SESSION['dbPrefix'] : '');
			return true;
		}

		return false;
	}

	/** Reset database submission */
	function dbReset()
	{
		unset($_SESSION['dbDSN']);
		unset($_SESSION['dbPrefix']);
		unset($_REQUEST['db']);
	}

	/** Connect to a database or assign errors */
	function dbConnect($dbDsn)
	{
		$dbConnected = $this->db->connect($dbDsn);
	
		if ($this->db->hasError($dbConnected)) {
			$this->tpl->assign('dbConnectErr', $this->db->error($dbConnected));
			return false;
		}

		$this->db->close();
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
				$this->tpl->assign('dbType', 'MySQL');
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

			default:
				$this->problems[] = 'dbType';
				return false;
		}

		if (empty($dbDsn)) {
			$this->problems[] = 'dbConnect';
			return false;
		}

		$_SESSION['dbDSN'] = $dbDsn;
		return true;
	}

	/** Ensure all variables are provided */
	function dbRequireCheck($dbType)
	{
		if (!isset($this->dbRequires[$dbType])) {
			$this->problems[] = 'dbType';
			return false;
		}

		$reqMissing = array();
		foreach ($this->dbRequires[$dbType] as $reqName) {
			if (!isset($_REQUEST['db'][$reqName])) {
				$reqMissing[] = $reqName;
			}		
		}
		
		if (!empty($reqMissing)) {	
			$this->problems[] = 'dbDetails';
			$tpl->assign('dbRequires', $reqMissing);
			return false;
		}

		return true;
	}

	/** Assign some kind of database table prefix */
	function dbPrefix()
	{
		if (isset($_REQUEST['db']) && isset($_REQUEST['db']['prefix'])) {
			if (preg_match('/[a-z0-9_]/i', $_REQUEST['db']['prefix'])) {
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


	// CONFIGURATION

	/** Write configuration */
	function configCheck()
	{
		if (isset($_REQUEST['configWrite'])) {
			$openConfig = fopen('config.inc.php', 'rb');
			$writeConfig = fopen(PATH_INC . '/config.inc.php', 'wb');

			if (!$openConfig) {
				$this->problems[] = 'configOpen';
			}
			if (!$writeConfig) {
				$this->problems[] = 'configWrite';
			}
			if (!($openConfig && $writeConfig)) {
				return false;
			}

			$configSrc = fread($openConfig, filesize('config.inc.php'));
			fclose($openConfig);

			fwrite($writeConfig, str_replace(array(DB_DSN, DB_PREFIX),
			 array($_SESSION['dbDSN'], $_SESSION['dbPrefix']), $configSrc));
			fclose($writeConfig);

			$_SESSION['configWritten'] = true;
		}

		return isset($_SESSION['configWritten']) && $_SESSION['configWritten'];
	}

	/** Reset configuration */
	function configReset()
	{
		unset($_SESSION['configWritten']);
		if (file_exists(PATH_INC . '/config.inc.php')) {
			unlink(PATH_INC . '/config.inc.php');
		}
	}


	// TABLES

	/** Perform database table and structure installation */
	function tableCheck()
	{
		if (isset($_REQUEST['tableReset'])) {
		    if (isset($_SESSION['tableComplete'])) {
		        unset($_SESSION['tableComplete']);
		    }
		}

        if (isset($_SESSION['tableComplete']) && $_SESSION['tableComplete']) {
			return true;
        }

		// Check for input before processing anything
		if (!isset($_REQUEST['adminPassword'])) {
			return false;
		}

		return $this->tableStructure() && $this->tableData();
	}

	/** Install database table structure */
	function tableStructure()
	{
		// Insert the table schemas
		$schema = fopen(PATH_INSTALL . '/sql/server.' .
		 strtolower($this->db->type) . '.sql', 'rb');
		if (!$schema) {
			$this->problems[] = 'tableSchemaOpen';
			return false;
		}
	
		$schemaTables = 0;
		$schemaTablesDone = 0;
	
		$currentQuery = '';
		while (!feof($schema)) {
			$currentQuery .= fgets($schema);
			$trimmedQuery = trim($currentQuery);
			if ($trimmedQuery !== '' &&
			     $trimmedQuery[strlen($trimmedQuery) - 1] === ';') {
				$createTable = $this->db->query($currentQuery);
				$currentQuery = '';
	
				++$schemaTables;
				if (!$this->db->hasError($createTable)) {
					++$schemaTablesDone;
				}
			}
		}

		if (!($schemaTables && $schemaTables === $schemaTablesDone)) {
			$this->problems[] = 'tableSchemaInsert';
			return false;
		}

		return true;
	}

	/** Install database table data */
	function tableData()
	{
		// Insert star names
		$delStarName = $this->db->query('DELETE FROM [server]starname');

		$starNames = 0;
		$starNamesDone = 0;

		$starNameFp = fopen(PATH_INSTALL . '/starnames.txt', 'rb');
		if ($starNameFp) {
			while (!feof($starNameFp)) {
				++$starNames;
				$starName = $this->db->query('INSERT INTO [server]starname VALUES (%[1])',
				 trim(fgets($starNameFp)));
				if (!($this->db->hasError($starName) || 
				     $this->db->affectedRows($starName) < 1)) {
					++$starNamesDone;
				}
			}
		}

		if (!($starNames && $starNames === $starNamesDone)) {
			$this->problems[] = 'tableStarNames';
		}

		// Data
		if (include(PATH_INSTALL . '/data.inc.php')) {
			// Insert all the tips
			$delTip = $this->db->query('DELETE FROM [server]tip');
	
			$tipNo = 0;
			$tipNoDone = 0;
			foreach ($dat['tips'] as $tipContent) {
				$tipQuery = $this->db->query('INSERT INTO [server]tip (tip_id, tip_content) VALUES (%[1], %[2])',
				 ++$tipNo, $tipContent);
				if (!($this->db->hasError($tipQuery) || 
				     $this->db->affectedRows($tipQuery) < 1)) {
					++$tipNoDone;
				}
			}
	
			if (!($tipNo && $tipNo === $tipNoDone)) {
				$this->problems[] = 'tableTips';
			}
		} else {
			$this->problems[] = 'tableData';
		}


		// Add administrator account
		if (!class_exists('sha256')) {
			require(PATH_LIB . '/sha256/sha256.class.php');
		}

		// Should use some kind of user class instead of raw insert
		$delAccount = $this->db->query('DELETE FROM [server]account');
		$newAdmin = $this->db->query('INSERT INTO [server]account (acc_id, acc_handle, acc_password, acc_created, acc_accessed, acc_accesses, acc_requests, acc_ip) VALUES (1, \'Admin\', 0x' .
		 sha256::hash($_REQUEST['adminPassword']) . 
		 ', %[1], %[1], 1, 1, %[2])', time(),
		 (double)sprintf('%u', ip2long($_SERVER['REMOTE_ADDR'])));
		if ($this->db->hasError($newAdmin)) {
			$this->problems[] = 'tableAdmin';
		}

		return empty($this->problems);
	}
}

$installer = new swInstall;
$installer->process();

?>
