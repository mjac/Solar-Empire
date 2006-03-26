<?php

// SERVER DEFINITIONS
define('MCDB_PATH', dirname(__FILE__) . '/external/mcdb/mcdb.class.php');

// DSN String - type://user:pass@host/database
define('DB_DSN', 'mysql://USER:PASSWORD@localhost/DATABASE');

define('SE_VERSION', 'VERSION');
header('X-Powered-By: System Wars ' . SE_VERSION);

define('SESSION_TIME_LIMIT', 3600);

define('COOKIE_LENGTH', 86400);

define('USER_VALIDATION_LENGTH', 86400);

define('OWNER_ID', 1);

// SYSTEM DEFINITIONS
defined('E_STRICT') || define('E_STRICT', 0x800);
error_reporting(E_ALL & ~E_STRICT);

	//the part of the URL that you have to type in to get to SE should go here.
$dir = dirname($_SERVER['SCRIPT_NAME']);
define('URL_SHORT', rtrim($dir, '\\/'));
define('URL_FULL', isset($_SERVER['HTTP_HOST']) ? ('http://' . 
 $_SERVER['HTTP_HOST'] . URL_SHORT) : URL_SHORT);
define('URL_SELF', $_SERVER['SCRIPT_NAME']);

?>
