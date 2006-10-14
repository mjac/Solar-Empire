<?php

require_once('inc/common.inc.php');
require_once('inc/db.inc.php');
require_once('inc/template.inc.php');

if (checkAuth()) {
	header('Location: game_listing.php');
	exit;
}

$tpl->display('index.tpl.php');
exit;

?>
