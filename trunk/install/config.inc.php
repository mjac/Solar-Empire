<?php

// DSN String - type://user:pass@host/database
define('DB_DSN', 'mysql://USER:PASSWORD@localhost/DATABASE');

// SERVER DEFINITIONS
define('SE_VERSION', 'VERSION');
header('X-Powered-By: System Wars ' . SE_VERSION);

define('OWNER_ID', 1);
define('DEFAULT_STYLE', 'generic');

// LIMITS
define('SESSION_TIME_LIMIT', 3600);
define('COOKIE_LENGTH', 86400);
define('USER_VALIDATION_LENGTH', 86400);

// SYSTEM DEFINITIONS
defined('E_STRICT') || define('E_STRICT', 0x800);
error_reporting(E_ALL & ~E_STRICT);

// PATHS
define('PATH_BASE', dirname(__FILE__) . '/..');
define('PATH_INC', PATH_BASE . '/inc');
define('PATH_TPL', PATH_BASE . '/tpl');
define('PATH_LIB', PATH_BASE . '/lib');
define('PATH_SDA', PATH_INC . '/sda/sda.class.php');
define('PATH_SAVANT', PATH_INC . '/Savant2-2.4.3/Savant2.php');

$dir = dirname($_SERVER['SCRIPT_NAME']);
define('URL_BASE', rtrim($dir, '\\/'));
define('URL_FULL', isset($_SERVER['HTTP_HOST']) ? 
 ('http://' . $_SERVER['HTTP_HOST'] . URL_BASE) : URL_BASE);
define('URL_SELF', $_SERVER['SCRIPT_NAME']);
define('URL_TPL', 'tpl');

?>
