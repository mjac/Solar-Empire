<?php

require('config.inc.php');

require(PATH_INC . '/db.inc.php');

if (!class_exists('Savant2')) {
	require(PATH_SAVANT);
}
$tpl = new Savant2();
$tpl->addPath('template', PATH_INSTALL . '/tpl');

define('PATH_INSTALL', PATH_BASE . '/install');
define('URL_INSTALL', URL_BASE . '/install');

?>
