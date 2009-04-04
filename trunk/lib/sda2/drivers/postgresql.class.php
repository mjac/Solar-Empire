<?php

if (!class_exists('sdaDriver')) exit('Only for internal use by SDA.');


/**
 * @brief  PostgreSQL database driver.
 * @author Michael Clark <mjac@mjac.co.uk>
 */

class sdaDriver_postgresql extends sdaDriver
{
	/**
	 * @brief PostgreSQL database link identifier.
	 */
	var $link = NULL;

	function libExists()
	{
		return function_exists('pg_connect');
	}


	/**
	 * @brief Format connection arguments for safe usage.
	 */

	function connArg($name, $value)
	{
		if (gettype($value) === 'string') {
			$value = '\'' . addslashes($value) . '\'';
		}
		return "$name=$value";
	}

	function connect($conn)
	{
		$function = $conn->persist ? 'pg_pconnect' : 'pg_connect';

		$args = array();
		if (!empty($conn->host)) {
			$args[] = $this->connArg('host', $conn->host);
		}
		if (!empty($conn->port)) {
			$args[] = $this->connArg('port', $conn->port);
		}
		if (!empty($conn->database)) {
			$args[] = $this->connArg('dbname', $conn->database);
		}
		if (!empty($conn->username)) {
			$args[] = $this->connArg('user', $conn->username);
		}
		if (!empty($conn->password)) {
			$args[] = $this->connArg('password', $conn->password);
		}

		if (empty($args)) {
			return false;
		}

		$linkId = @$function(implode(' ', $args));
		if (is_resource($linkId)) {
			$this->link = $linkId;
			return true;
		}

		return false;
	}

	function close()
	{
		return @pg_close($this->link);
	}

	function query($query)
	{
		return @pg_query($this->link, $query);
	}

	function numRows($res)
	{
		return @pg_num_rows($res);
	}

	function fetchRow($res, $type = ROW_NUMERIC)
	{
		switch ($type) {
			case ROW_ASSOC:
				return @pg_fetch_assoc($res);
			case ROW_OBJECT:
				return @pg_fetch_object($res);
			case ROW_NUMERIC:
			default:
				return @pg_fetch_row($res);
		}
	}

	function affectedRows($res)
	{
		return @pg_affected_rows($res);
	}

	function error()
	{
		return @pg_last_error();
	}

	function formatString($str)
	{
		return '\'' . pg_escape_string($str) . '\'';
	}
}

?>
