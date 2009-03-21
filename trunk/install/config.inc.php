<?php

// SERVER DEFINITIONS
define('SE_VERSION', 'VERSION');
define('OWNER_ID', 1);
define('DEFAULT_STYLE', 'generic');

// LIMITS
define('SESSION_TIME_LIMIT', 3600);
define('COOKIE_LENGTH', 86400);
define('USER_VALIDATION_LENGTH', 86400);

// DSN String - type://user:pass@host/database
define('DB_DSN', 'DB_DSN=HERE');
define('DB_PREFIX_SERVER', 'DB_PREFIX_SERVER=HERE');
define('DB_PREFIX_GAME', 'DB_PREFIX_GAME=HERE');

// SYSTEM DEFINITIONS
defined('E_STRICT') || define('E_STRICT', 0x800);
error_reporting(E_ALL & ~E_STRICT);

// PATHS
define('PATH_BASE', realpath(dirname(__FILE__) . '/..'));
define('PATH_INC', PATH_BASE . '/inc');
define('PATH_TPL', PATH_BASE . '/tpl');
define('PATH_LIB', PATH_BASE . '/lib');
define('PATH_DOC', PATH_BASE . '/doc');
define('PATH_SDA', PATH_LIB . '/sda2/sda.class.php');
define('PATH_SAVANT', PATH_LIB . '/Savant2-2.4.3/Savant2.php');

// URLS
define('URL_SELF', $_SERVER['SCRIPT_NAME']);
define('URL_BASE', rtrim(dirname(URL_SELF), '\\/'));
define('URL_FULL', isset($_SERVER['HTTP_HOST']) ? 
 ('http://' . $_SERVER['HTTP_HOST'] . URL_BASE) : URL_BASE);
define('URL_TPL', URL_BASE . '/tpl');

// SEED
mt_srand((double)microtime() * (double)0x7FFFFFFF);

?>
