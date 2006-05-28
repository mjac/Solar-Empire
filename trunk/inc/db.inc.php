<?php

defined('PATH_MCDB') or exit('Create constant PATH_MCDB!');

require_once(PATH_MCDB);

$db = new mcdb;

//$db->debug();
$db->varFormat = '[%]'; // [game] for instance
$db->rowType = ROW_ASSOC;
$db->debug();

$connect = $db->connect(DB_DSN);
if ($db->hasError($connect)) {
	trigger_error($db->error($connect), E_USER_ERROR);
	exit();
}

unset($connect, $driver);


function newId($table, $field)
{
	global $db;

	$idInfo = $db->query('SELECT MIN(' . $field . '), MAX(' . $field .
	 ') FROM ' . $table . '');
	$range = $db->fetchRow($idInfo, ROW_NUMERIC);

	if ($range[0] === NULL) {
	    return 1;
	}

	return $range[0] <= 1 ? ($range[1] + 1) : ($range[0] - 1);
}


// Emulation of old DB abstraction
$dbRes = array(NULL, NULL);
function db($query, $res = 0)
{
	global $db, $dbRes;
	$dbRes[$res] = $db->query($query);
}
function dbr($type = 0, $res = 0)
{
	global $db, $dbRes;
	return $db->fetchRow($dbRes[$res], ROW_ASSOC);
}
function dbn($query)
{
	global $db;
	$db->query($query);
}
function db2($query)
{
	db($query, 1);
}
function dbr2($type = 0)
{
	return dbr($type, 1);
}

?>
