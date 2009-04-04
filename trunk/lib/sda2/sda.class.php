<?php

defined('SDA_PATH') || define('SDA_PATH',
 rtrim(str_replace('\\', '/', dirname(__FILE__)), '/'));

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
 * External interface of the SDA abstraction library
 * @author Michael J.A. Clark <mjac@mjac.co.uk>
 *
 * Basic queries can be performed using various database systems.
 * Functions can be used to provide abstraction from the inner workings of the
 * interface, or separating different application layers.
 */

class sda
{
	/** Driver object, stored after class initiation */
	var $driver = NULL;

	/** Holder for abstraction classes */
	var $abs = array();

	/** The database type */
	var $type;

	/**
	 * The state of internal action debugging
	 *
	 * Query information is added to each actions.
	 */
	var $debug = false;

	/**
	 * Items to replace in queries
	 *
	 * This is an associative array in the form name => value.  Queries will be
	 * replaced according to the sda::varFormat.
	 */
	var $vars = array();

	/**
	 * Holds action objects extended from the basic action
	 */
	var $actions = array();

	/**
	 * The variable format to use in global queries
	 *
	 * {='%'} for instance, where % represents the variable name.
	 */
	var $varFormat = '{$%}';

	/** Default row-type, used if one isn't specified */
	var $rowType = ROW_NUMERIC;

	/**
	 * Increases speed, reduces memory usage
	 *
	 * Only use for phrases that may be used a lot.
	 */
	var $phrase = array(
		'fine'    => 'Completed successfully',
		'qFailed' => 'Query failed',
		'qTpl'    => 'Query template (sprintf)',
		'qParsed' => 'Parsed query ready for execution'
	);


	// ACTIONS

	/** Connect to the database */
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

		$conn = new sdaConnection;
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
				$options[rawurldecode($flag[0])] = isset($flag[1]) ?
				 rawurldecode($flag[1]) : true;
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

		$className = 'sdaDriver_' . $this->type;

		$act->request = "Connect to $this->type database";
		$act->action  = "Create $className class";

		$cPath = SDA_PATH . '/drivers/' . $this->type . '.class.php';
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

	/** Determines whether the given action has produced an error */
	function hasError($action)
	{
		return isset($this->actions[$action]) ?
		 $this->actions[$action]->error : false;
	}

	/** Close the database connection */
	function close()
	{
		if ($this->driver->close()) {
			$this->driver = NULL;
			$this->type = '';
			return true;
		}

		return false;
	}

	/** Splits a query up into parts to replace variables */
	function queryPrepare($query)
	{
		$varParts = explode('%', $this->varFormat);
		foreach ($this->vars as $find => $replacement) {
			$query = str_replace(implode($find, $varParts), $replacement,
			 $query);
		}

		$matches = array();

		preg_match_all('/%\[(\d+)\]/', $query, $matches);
		$split = preg_split('/%\[(\d+)\]/', $query);

		$list = array();
		$length = count($split);
		for ($id = 0; $id < $length; ++$id) {
			$list[$id] = new sdaSplitQuery;
			$list[$id]->part = $split[$id];
			$list[$id]->argument = isset($matches[1][$id]) ?
			 (int)$matches[1][$id] : null;

		}

		return $list;
	}

	/** Format argument for the database according to database type */
	function formatArg($arg)
	{
		$type = gettype($arg);
		switch ($type) {
			case 'boolean':
				return $this->driver->formatBoolean($arg);
			case 'integer':
				return $this->driver->formatInteger($arg);
			case 'double':
				return $this->driver->formatReal($arg);
			case 'string':
				return $this->driver->formatString($arg);
			case 'NULL':
				return $this->driver->formatNULL();

			case 'object':
			case 'array':
			case 'resource':
				trigger_error("Invalid argument type: $type is not supported.",
				 E_USER_WARNING);
				return '';
		}
	}

	/** Construct the query */
	function queryConstruct(&$args, &$required)
	{
		$parsed = '';

		foreach ($required as $part) {
			$parsed .= $part->part;

			if ($part->argument === null) {
				continue;
			}

			if (!array_key_exists($part->argument, $args)) {
				trigger_error('Required argument ' . $part->argument .
				 ' is missing.', E_USER_WARNING);
				return false;
			}

			$parsed .= $this->formatArg($args[$part->argument]);
		}

		return $parsed;
	}

	/** Send a query to the database */
	function query($queryTpl)
	{
		static $fine = 'Completed successfully';

		$act =& $this->createAction(DBA_QUERY);

		if ($this->debug) {
			$act->request = $queryTpl;
		} else {
			$act->request =& $this->phrase['qTpl'];
		}

		$args = func_get_args();
		unset($args[0]);

		$parts = $this->queryPrepare($queryTpl);
		$action = $this->queryConstruct($args, $parts);

		if ($this->debug) {
			$act->action = $action;
		} else {
			$act->action =& $this->phrase['qParsed'];
		}

		$before = explode(' ', microtime());
		$act->resource = $this->driver->query($action);
		$after  = explode(' ', microtime());

		$act->period = (double)$after[1] - (double)$before[1] +
		 (double)$after[0] - (double)$before[0];

		if ($act->resource) {
			$act->result =& $this->phrase['fine'];
		} else {
			if ($this->debug) {
				$act->result = $this->driver->error();
			} else {
				$act->result =& $this->phrase['qFailed'];
			}
			$act->error = true;
		}

		return $act->id;
	}

	/** Amount of rows returned by the database action */
	function numRows($actId)
	{
		if (!$res = $this->action($actId)) {
			return false;
		}
		return $this->driver->numRows($res->resource);
	}

	/** Numeric array derived from the database action result */
	function fetchRow($actId, $type = false)
	{
		if (!$res = $this->action($actId)) {
			return false;
		}
		return $this->driver->fetchRow($res->resource,
		 $type ? $type  : $this->rowType);
	}

	/**
	 * Affected rows from previous query.
	 * @todo Base on separate actions
	 */
	function affectedRows($actId)
	{
		if (!$res = $this->action($actId)) {
			return false;
		}
		return $this->driver->affectedRows($res->resource);
	}


	// ACTIONS

	/** An action referenced by an ID */
	function action($actId)
	{
		return isset($this->actions[$actId]) ? $this->actions[$actId] : NULL;
	}

	/** All actions by type */
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

	/** The amount of actions per type */
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

	/** Period taken by a collection of items */
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

	/** Create a new action */
	function &createAction($actionType)
	{
		static $actionId = 0;

		++$actionId;
		$this->actions[$actionId] = new sdaAction($actionId, $actionType);

		return $this->actions[$actionId];
	}

	/** The last database action according to a given type */
	function lastAction($type = DBA_ALL)
	{
	    $lastAction = end($this->actions);
		for ($lastId = $lastAction->id; --$lastId >= 1; ) {
			if (isset($this->actions[$lastId]) && $this->actions[$lastId]->type & DBA_ALL) {
				return $lastId;
			}
		}

		return false;
	}

	/** Deletes actions - useful for saving memory */
	function deleteAction($actId)
	{
		if ($this->action($actId) !== NULL) {
		    unset($this->actions[$actId]);
		}
	}


	// ABSTRACTION

	/** Add a variable for use in queries */
	function addVar($name, $value)
	{
		$this->vars[$name] = $value;
	}


	// UTILITY

	/** Set debugging on and off */
	function debug($switch = true)
	{
		$this->debug = $switch ? true : false;
	}

	/** Returns the error of an action or the last error s*/
	function error($actionId = false)
	{
		$actionObj = $actionId === false ? $this->lastAction() :
		 $this->action($actionId);

		return $actionObj && $actionObj->error ? $actionObj->result : false;
	}
}


/**
 * All database drivers extend this
 * @author Michael J.A. Clark <mjac@mjac.co.uk>
 *
 * Contains methods and variables representing every database driver.
 * The methods return false until they are redefined in the respective driver
 * file.
 */

class sdaDriver
{
	/** Whether the client libraries exist */
	function libExists()
	{
		return false;
	}


	// INITIAL CONNECTION

	/** Establishes a link to the database */
	function connect($conn)
	{
		return false;
	}

	/** Close an open database connection */
	function close()
	{
		return false;
	}


	// PERFORMING ACTIONS

	/** Send a raw query */
	function query($query)
	{
		return false;
	}

	/** Number of rows associated with a result resource */
	function numRows($res)
	{
		return false;
	}

	/** Numerical row derived from a result resource */
	function fetchRow($res, $type = ROW_NUMERIC)
	{
		return false;
	}


	/** Affected rows from previous query */
	function affectedRows($res)
	{
		return false;
	}

	/** Returns the last error encountered by the database */
	function error()
	{
		return '';
	}


	// FORMATTING

	/** Prepare a string for use in a query */
	function formatString($str)
	{
		return '\'' . str_replace('\'', '\'\'', addslashes($str)) . '\'';
	}

	/** Prepare a string for use in a query */
	function formatInteger($integer)
	{
		return $integer;
	}

	/** Prepare a real number for use in a query */
	function formatReal($real)
	{
		return $real;
	}

	/** Prepare a boolean for use in a query */
	function formatBoolean($bool)
	{
		return $bool ? '1' : '0';
	}

	/** Prepare NULL for use in a query */
	function formatNULL()
	{
		return 'NULL';
	}
}


/**
 * Standard database action
 * @author Michael Clark <mjac@mjac.co.uk>
 */

class sdaAction
{
	/** Internal identification number */
	var $id = 0;

	/** Type of action */
	var $type = DBA_NONE;

	/** Whether an error has occured in this action */
	var $error = false;

	/** Request description */
	var $request = '';

	/** Description of the action taken */
	var $action  = '';

	/** Description of the action result */
	var $result  = '';

	/** Time in seconds that the action took */
	var $period   = 0.0;

	/** Raw object returned by the driver */
	var $resource = NULL;

	/** Create new action */
	function sdaAction($actionId, $actionType)
	{
	    $this->id = $actionId;
	    $this->type = $actionType;
	}
}


/**
 * Connection settings for database drivers to use
 * @author Michael J.A. Clark <mjac@mjac.co.uk>
 */

class sdaConnection
{
	var $host = '';
	var $port = false;

	var $username = '';
	var $password = '';
	var $database = '';

	var $persist = false;
}


/**
 * Split queries for processing
 * @author Michael J.A. Clark <mjac@mjac.co.uk>
 */

class sdaSplitQuery
{
	var $part;
	var $argument;
};

?>
