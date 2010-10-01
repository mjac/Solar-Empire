<?php

defined('PATH_CLASS_MCDB') or exit('Only for use by mcdb.');

require_once(PATH_CLASS_MCDB . '/mcdb.class.php');


/**
 * @brief  MySQL database driver.
 * @author Michael Clark <mjac@mjac.co.uk>
 */

class mcdb_driver_mysql extends mcdb_driver
{
	/**
	 * @brief MySQL database link identifier.
	 */
	var $link = NULL;

	function libExists()
	{
		return function_exists('mysql_connect');
	}

	function connect($conn)
	{
		$function = $conn->persist ? 'mysql_pconnect' : 'mysql_connect';
		$linkId = @$function($conn->host . (is_integer($conn->port) ?
		 ":$conn->port" : ''), $conn->username, $conn->password, true);

		if (!is_resource($linkId)) {
			return false;
		}

		$this->link = $linkId;

		if (@mysql_select_db($conn->database, $this->link)) {
			return true;
		}

		$this->close();

		return false;
	}

	function close()
	{
		return @mysql_close($this->link);
	}

	function query($query)
	{
		return @mysql_query($query, $this->link);
	}

	function numRows($res)
	{
		return @mysql_num_rows($res);
	}

	function fetchRow($res, $type = ROW_NUMERIC)
	{
		switch ($type) {
			case ROW_ASSOC:
				return @mysql_fetch_assoc($res);
			case ROW_OBJECT:
				return @mysql_fetch_object($res);
			case ROW_NUMERIC:
			default:
				return @mysql_fetch_row($res);
		}
	}

	function affectedRows()
	{
		$rows = @mysql_affected_rows($this->link);
		return $rows === -1 ? false : $rows;
	}

	function error()
	{
		$msg = @mysql_error($this->link);
		$no  = @mysql_errno($this->link);

		return $msg ? "($no) $msg" : '';
	}

	function escape($str)
	{
		return mysql_real_escape_string($str);
	}
}

?>
