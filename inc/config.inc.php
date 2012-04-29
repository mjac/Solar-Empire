<?php

//Host of the computer that holds the database. if the same computer as the web-host, leave as "localhost".
define("DATABASE_HOST", "localhost");

//The name of the database within which SE resides
define("DATABASE", "database name");

//The username required to access the database
define("DATABASE_USER", "database user");

//The password required to access the database.
define("DATABASE_PASSWORD", "database password");


//Send the authorisation mail. Set to 1 to send, and 0 not to send.
define("SENDMail", 0);

//Whatever you want to call the server
define("SERVER_NAME", "My First Server");

if (isset($_SERVER['HTTP_HOST'])) {
	//the part of the URL that you have to type in to get to SE should go here.
	define('URL_SHORT', dirname($_SERVER['SCRIPT_NAME']));
	define('URL_FULL', 'http://' . $_SERVER['HTTP_HOST'] . URL_SHORT);
}

//version of the code. Suffix an 'M' if modified from a release
$code_base = 'Generic SE 2.9.1';

//lenth of a user may be inactive for before they are automatically logged out. In seconds.
define("SESSION_TIME_LIMIT", 3600);


define("ADMIN_ID", 1);

/* The in-game login-id of the server owner. Enter -1 if you do not know it,
   or do not want it. It is NOT advised u use the admin's ID. When you first
   create the server - make an account and set this to that account's id (2?) */
define("OWNER_ID", 1);

// PHP errors that will be reported when the script is run.
defined('E_STRICT') or define('E_STRICT', 0x800);
error_reporting(E_ALL & ~(E_NOTICE | E_STRICT));

?>
