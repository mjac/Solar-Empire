<?php

if (!class_exists('sdaDriver')) exit('Only for internal use by SDA.');


/**
 * @brief  Sqlite database driver.
 * @author Michael Clark <mjac@mjac.co.uk>
 */

class sdaDriver_sqlite extends sdaDriver
{
	/**
	 * @brief Sqlite database resource.
	 */
	var $res = NULL;

	function libExists()
	{
		return function_exists('sqlite_open');
	}

	function connect($conn)
	{
		$function = $conn->persist ? 'sqlite_popen' : 'sqlite_open';
		$res = @$function($conn->database);

		if (!is_resource($linkId)) {
			return false;
		}

		$this->res = $res;

		return true;
	}

	function close()
	{
		return @sqlite_close($this->res);
	}

	function query($query)
	{
		return @sqlite_query($query, $this->res);
	}

	function numRows($res)
	{
		return @sqlite_num_rows($res);
	}

	function fetchRow($res, $type = ROW_NUMERIC)
	{
		switch ($type) {
			case ROW_ASSOC:
				return $this->cleanAssoc(@sqlite_fetch_array($res, SQLITE_ASSOC));
			case ROW_OBJECT:
				return @sqlite_fetch_object($res);
			case ROW_NUMERIC:
			default:
				return @sqlite_fetch_array($res, SQLITE_NUM);
		}
	}

	function affectedRows()
	{
		return @sqlite_changes($this->res);
	}

	function error()
	{
		$no = @sqlite_last_error($this->res);
		$msg = @sqlite_error_string($no);

		return "($no) $msg";
	}

	function formatString($str)
	{
		return '\'' . sqlite_escape_string($str) . '\'';
	}


	/**
	 * @brief Correct the associative array indexes.
	 *
	 * Sqlite does not return the correct index for a field like f.name;
	 * instead it will return f.name, ruining the application in the process.
	 */

	function cleanAssoc($array)
	{
		foreach ($array as $key => $value) {
			$pos = strpos($key, '.');
			if ($pos !== false) {
				$array[substr($key, $pos + 1)] =& $array[$key];
			}
		}
		return $array;
	}
}

?>
