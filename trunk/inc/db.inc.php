<?php
defined('PATH_SDA') || exit;

if (!class_exists('sda')) {
	require(PATH_SDA);
}

class swDatabase extends sda
{
	function swDatabase()
	{
		$this->varFormat = '[%]'; // [game] for instance
		$this->rowType = ROW_ASSOC;
		$this->debug(true);
	}

	function newId($table, $field)
	{
		$idInfo = $this->query("SELECT MIN($field), MAX($field) FROM $table");
		$range = $this->fetchRow($idInfo, ROW_NUMERIC);
	
		if ($range[0] === NULL) {
		    return 1;
		}
	
		return $range[0] <= 1 ? ($range[1] + 1) : ($range[0] - 1);
	}

	function start()
	{
		$connect = $this->connect(DB_DSN);
		if ($this->hasError($connect)) {
			trigger_error($this->error($connect), E_USER_ERROR);
			exit;
		}
	}

	function stop()
	{
		$this->close();
	}
};

$db = new swDatabase;

?>
