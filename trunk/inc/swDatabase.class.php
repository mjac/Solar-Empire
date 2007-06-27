<?php
defined('PATH_SDA') || exit;

if (!class_exists('sda')) {
	require(PATH_SDA);
}

/** System Wars database wrapper */
class swDatabase extends sda
{
	/** Customise sda */
	function swDatabase()
	{
		$this->varFormat = '[%]'; // [game] for instance
		$this->rowType = ROW_ASSOC;
		$this->debug(true);

		$this->addVar('server', DB_PREFIX);
	}

	/** Find a new ID for tables/databases that do not support auto_increment */
	function newId($table, $field)
	{
		$idInfo = $this->query("SELECT MAX($field) FROM $table");
		if ($this->hasError($idInfo)) {
		    return false;
		}

		list($idMax) = $this->fetchRow($idInfo, ROW_NUMERIC);
		if ($idMax === NULL || $idMax < 1) {
		    return 1;
		}

		return $idMax + 1;
	}

	/** Connect to the database or exit */
	function start()
	{
		$connect = $this->connect(DB_DSN);
		if ($this->hasError($connect)) {
			trigger_error($this->error($connect), E_USER_ERROR);
			exit;
		}
	}

	/** Disconnect from the database */
	function stop()
	{
		$this->close();
	}
};

?>
