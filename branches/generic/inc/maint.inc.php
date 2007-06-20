<?php

if (!preg_match('/c[lg]i/i', php_sapi_name())) {
	exit('CGI or CLI only.');
}

require_once('config.inc.php');

error_reporting(E_ALL);
mt_srand((double)microtime() * 0x7FFFFFF);

header('Content-Type: text/plain');

function randomStr($len)
{
	static $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';

	$max = strlen($chars) - 1;
	$str = '';
	for ($i = 0; $i < $len; ++$i) {
		$str .= $chars[mt_rand(0, $max)];
	}
}

function getVar($game, $name)
{
	$value = mysql_query("SELECT `value` FROM `{$game}_db_vars` WHERE `name`='" .
	 addslashes($name) . "'");

	if (!$value) {
		return false;
	}

	return mysql_result($value, 0, 'value');
}


if (!(mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD) &&
      mysql_select_db(DATABASE))) {
	exit;
}


?>
