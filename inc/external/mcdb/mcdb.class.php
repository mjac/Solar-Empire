<?php

defined('PATH_CLASS_MCDB') or define('PATH_CLASS_MCDB', dirname(__FILE__));


// Database action types, using binary arithmatic
define('DBA_NONE',     0);
define('DBA_QUERY',    1);
define('DBA_INTERNAL', 2);
define('DBA_FUNCTION', 4);
define('DBA_ALL',      8 - 1);

// Row index types
define('ROW_NUMERIC', 1);
define('ROW_ASSOC',   2);
define('ROW_OBJECT',  3);


/**
 * @author Michael Clark <mjac@mjac.co.uk>
 * @brief  External interface of the mcdb abstraction library.
 *
 * Basic queries can be performed using various database systems, such as MySQL.
 * Functions can be used to provide abstraction from the inner workings of the
 * interface, or separating different application layers.
 */

class mcdb
{
	/**
	 * @brief Driver object, stored after class initiation.
	 */
	var $driver = NULL;

	/**
	 * @brief Holder for abstraction classes.
	 */
	var $abs = array();

	/**
	 * @brief The database type.
	 */
	var $type;

	/**
	 * @brief The state of internal action debugging.
	 *
	 * Query information is added to each actions.
	 */
	var $debug = false;

	/**
	 * @brief Items to replace in queries.
	 *
	 * This is an associative array in the form name => value.  Queries will be
	 * replaced according to the mcdb::varFormat, or mcdb_absraction::varFormat
	 * if the query is called by that abstraction function.
	 */
	var $vars = array();

	/**
	 * Holds action objects extended from the basic action
	 */
	var $actions = array();

	/**
	 * @brief The variable format to use in global queries.
	 *
	 * {='%'} for instance, where % represents the variable name.
	 */
	var $varFormat = '{$%}';

	/**
	 * @brief Default row-type, used if one isn't specified.
	 */
	var $rowType = ROW_NUMERIC;

	/**
	 * @brief Increases speed, reduces memory usage.
	 *
	 * Only use for phrases that may be used a lot.
	 */
	var $phrase = array(
		'fine'    => 'Completed successfully',
		'qFailed' => 'Query failed',
		'qTpl'    => 'Query template (sprintf)',
		'qParsed' => 'Parsed query ready for execution'
	);


	/**
	 * @brief Connect to a database.
	 */

	function connect($dsnStr)
	{
		$act =& $this->createAction(DBA_INTERNAL);

		$dsn = @parse_url($dsnStr);
		if (!($dsn && isset($dsn['scheme']))) {
			$act->error = true;
			$act->result = 'Invalid DSN string.';
			return $act->id;
		}

		$this->type = $dsn['scheme'];

		$conn = new mcdb_connection;

		if (isset($dsn['host'])) {
			$conn->host = rawurldecode($dsn['host']);
		}
		if (isset($dsn['user'])) {
			$conn->username = rawurldecode($dsn['user']);
		}
		if (isset($dsn['pass'])) {
			$conn->password = rawurldecode($dsn['pass']);
		}
		if (isset($dsn['path'])) {
			$conn->database = rawurldecode(substr($dsn['path'], 1));
		}
		if (isset($dsn['port'])) {
			$conn->port = (int)$dsn['port'];
		}

		$options = array();
		if (isset($dsn['query'])) {
			$flags = explode('&', $dsn['query']);
			foreach($flags as $flag) {
				$flag = explode('=', $flag);
				$options[rawurldecode($flag[0])] = isset($flag[1]) ? rawurldecode($flag[1]) : true;
			}
		}

		foreach($options as $option => $value) {
			switch (strtolower($option)) {
				case 'persist':
				case 'persistent':
					$persist = true;
					break;
			}
		}

		$className = 'mcdb_driver_' . $this->type;

		$act->request = "Connect to database of type $this->type";
		$act->action  = "Create $className class";

		$cPath = PATH_CLASS_MCDB . '/drivers/' . $this->type . '.class.php';
		if (is_file($cPath)) {
			require_once($cPath);

			if (class_exists($className)) {
				$this->driver = new $className;
				if ($this->driver->libExists()) {
					$act->result = 'Loaded successfully';
				} else {
					$act->result = 'PHP functions unavailable';
					$act->error  = true;
				}
			} else {
				$act->result = "Class $className does not exist in $cPath";
				$act->error  = true;
			}
		} else {
			$act->result = "File $cPath does not exist";
			$act->error  = true;
		}

		if (!is_object($this->driver)) {
			return $act->id;
		}

        $connected = $this->driver->connect($conn);
        if (!$connected) {
            $act->result = 'Connection attempt failed';
            $act->error = true;
            return $act->id;
        }

		$act->result =& $this->phrase['fine'];

		return $act->id;
	}


	/**
	 * @brief Determines whether the given action has produced an error.
	 */

	function hasError($action)
	{
		return isset($this->actions[$action]) ? $this->actions[$action]->error : false;
	}


	/**
	 * @brief Close the database connection.
	 */

	function close()
	{
		if ($this->linkId && $this->driver->close($this->linkId)) {
			$this->linkId = false;

			/* Get rid of class variables */
			$this->driver = NULL;
			$this->type = '';
			return true;
		} else {
			return false;
		}
	}


	/**
	 * @brief Query the database.
	 *
	 * @param queryTpl The formatted query to execute (using vsprintf)
	 * @param args     Arguements to pass to vsprintf
	 */

	function query($queryTpl, $args = array())
	{
		static $fine = 'Completed successfully';

		$act =& $this->createAction(DBA_QUERY);

		if ($this->debug) {
			$act->request = $queryTpl;
		} else {
			$act->request =& $this->phrase['qTpl'];
		}

		$varParts = explode('%', $this->varFormat);
		$baseQuery = $queryTpl;
		foreach ($this->vars as $find => $replacement) {
			$baseQuery = str_replace(implode($find, $varParts), $replacement,
			 $baseQuery);
		}
		$action = vsprintf($baseQuery, $args);

		if ($this->debug) {
			$act->action = $action;
		} else {
			$act->action =& $this->phrase['qParsed'];
		}

		$before = explode(' ', microtime());
		$act->resource = $this->driver->query($action);
		$after  = explode(' ', microtime());

		$act->period = (double)((int)$after[1] - (int)$before[1]) +
		 ((double)$after[0] - (double)$before[0]);

		if ($act->resource) {
			$act->result =& $this->phrase['fine'];
		} else {
			if ($this->debug) {
				$act->result = $this->driver->error();
			} else {
				$act->result =& $this->phrase['qFailed'];
			}
			$act->error  = true;
		}

		return $act->id;
	}


	/**
	 * @brief Amount of rows returned by the database action.
	 */

	function numRows($actId)
	{
		if (!$res = $this->action($actId)) {
			return false;
		}
		return $this->driver->numRows($res->resource);
	}


	/**
	 * @brief Numeric array derived from the database action result.
	 */

	function fetchRow($actId, $type = false)
	{
		if (!$res = $this->action($actId)) {
			return false;
		}
		return $this->driver->fetchRow($res->resource,
		 $type ? $type  : $this->rowType);
	}


	/**
	 * @brief Affected rows from previous query.
	 * @todo  Base it on separate actions, even though mysql doesn't support this.
	 */

	function affectedRows($actId)
	{
		if (!$res = $this->action($actId)) {
			return false;
		}
		return $this->driver->affectedRows($res->resource);
	}



	/* ACTIONS */


	/**
	 * @brief An action referenced by an id.
	 */

	function action($actId)
	{
		return isset($this->actions[$actId]) ? $this->actions[$actId] : NULL;
	}


	/**
	 * @brief All actions by type.
	 */

	function allActions($type = DBA_ALL)
	{
		$acts = array();
		foreach ($this->actions as $act) {
			if ($type & $act->type) {
				$acts[] = $act->id;
			}
		}

		return $acts;
	}


	/**
	 * @brief The amount of actions per type.
	 */

	function actionNo($type = DBA_ALL)
	{
		if ($type === DBA_ALL) {
			return count($this->actions);
		}

		$amount = 0;
		foreach ($this->actions as $act) {
			if ($type & $act->type) {
				++$amount;
			}
		}

		return $amount;
	}


	/**
	 * @brief Period taken by a collection of items.
	 */

	function period($type = DBA_ALL)
	{
		$length = 0.0;

		foreach ($this->actions as $act) {
			if ($type & $act->type) {
				$length += $act->period;
			}
		}

		return $length;
	}


	/**
	 * @brief Create a new action.
	 */

	function &createAction($type)
	{
		static $id = 0;

		$act =& $this->actions[];
		$act = new mcdb_action;

		$act->id   = $id;
		$act->type = $type;

		++$id;

		return $act;
	}


	/**
	 * @brief The last database action according to a given type.
	 */

	function lastAction($type = DBA_ALL)
	{
		for ($i = count($this->actions); --$i >= 0; ) {
			if ($this->actions[$i]->type & DBA_ALL) {
				return $i;
			}
		}

		return false;
	}


	/**
	 * @brief Deletes actions - useful for saving memory.
	 */

	function deleteAction($actId)
	{
		if ($this->action($actId) !== NULL) {
		    unset($this->actions[$actId]);
		}
	}



	/* ABSTRACTION */


	/**
	 * @brief Add a variable for use in queries.
	 */

	function addVar($name, $value)
	{
		$this->vars[$name] = $value;
	}



	/* UTILITY */


	/**
	 * @brief Set debugging on and off.
	 */

	function debug($switch = true)
	{
		$this->debug = $switch ? true : false;
	}


	/**
	 * @brief Escape a string for safe usage in a query.
	 */

	function escape($str)
	{
		return $this->driver->escape($str);
	}


	/**
	 * @brief Returns the error of an action or the last error.
	 */

	function error($id = false)
	{
	    if ($id === false) {
			$id = $this->lastAction();
		}

		return ($obj = $this->action($id)) && $obj->error ? $obj->result : false;
	}
}


/**
 * @author Michael Clark <mjac@mjac.co.uk>
 * @brief  All database drivers extend this.
 *
 * Contains methods and variables representing every database driver.
 * The methods return false until they are redefined in the respective driver
 * file.
 */

class mcdb_driver
{
	/**
	 * @brief Checks whether the client libraries exist.
	 */

	function libExists()
	{
		return false;
	}


	/**
	 * @brief Driver connection routine.
	 *
	 * Establishes a link to the database.
	 */

	function connect($conn)
	{
		return false;
	}


	/**
	 * @brief Close an open database connection.
	 */

	function close()
	{
		return false;
	}


	/**
	 * @brief Send a raw query.
	 */

	function query($query)
	{
		return false;
	}


	/**
	 * @brief The number of rows assiated with a result resource.
	 */

	function numRows($res)
	{
		return false;
	}


	/**
	 * @brief Numerical row derived from a result resource.
	 */

	function fetchRow($res, $type = ROW_NUMERIC)
	{
		return false;
	}


	/**
	 * @brief Single field derived from a result resource.
	 */

	function fetchField($res, $index = 0)
	{
		return false;
	}


	/**
	 * @brief Affected rows from previous query.
	 */

	function affectedRows($res)
	{
		return false;
	}


	/**
	 * @brief Interface to the database error system.
	 *
	 * Returns the last error encountered by the database.
	 */

	function error()
	{
		return '';
	}


	/**
	 * @brief Prepare a string for use in a query.
	 */

	function escape($str)
	{
		return str_replace('\'', '\'\'', addslashes($str));
	}
}


/**
 * @author Michael Clark <mjac@mjac.co.uk>
 * @brief  Basic abstraction class.
 *
 * A basis for any database abstraction class.  This contains functions
 * which are designed to return the same data-types using different
 * databases.
 */

class mcdb_abstraction
{
	/**
	 * @brief A reference to the database class.
	 */
	var $db;

	/**
	 * @brief Tables required by this class.
	 *
	 * These are assigned as tbl_name = prefix_value
	 */
	var $tables = array();

	/**
	 * @brief Variables required by this class.
	 *
	 * These are assigned as name = value
	 */
	var $vars = array();

	/**
	 * @brief Inbuilt variables are accessed using this format.
	 *
	 * If this is empty (default), the standard variable syntax (held
	 * in mcdb::varFormat) is used.
	 */
	var $varFormat = '';

	/**
	 * @brief Table prefix to use in queries used by this mcdb_abstraction.
	 */
	var $tblPrefix = '';
}


/**
 * @author Michael Clark <mjac@mjac.co.uk>
 * @brief  Database action.
 *
 * Template for any database action.
 */

class mcdb_action
{
	/**
	 * @brief Internal identification number.
	 */
	var $id = 0;

	/**
	 * @brief Type of action.
	 */
	var $type = DBA_NONE;

	/**
	 * @brief Whether an error has occured in this action.
	 */
	var $error = false;

	/**
	 * @brief Request description.
	 */
	var $request = '';

	/**
	 * @brief Description of the action taken.
	 */
	var $action  = '';

	/**
	 * @brief Description of the action result.
	 */
	var $result  = '';

	/**
	 * @brief Time in seconds that the action took.
	 */
	var $period   = 0.0;

	/**
	 * @brief Raw object returned by the driver.
	 */
	var $resource = NULL;
}


/**
 * @author Michael Clark <mjac@mjac.co.uk>
 * @brief  Connection settings for database drivers to use.
 */

class mcdb_connection
{
	var $host = '';
	var $port = false;

	var $username = '';
	var $password = '';
	var $database = '';

	var $persist = false;
}

?>
