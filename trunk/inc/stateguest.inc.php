<?php
defined('PATH_INC') || exit;

require(PATH_INC . '/db.inc.php');
require(PATH_INC . '/input.class.php');
require(PATH_INC . '/session.class.php');

$input = new input;
$session = new session($db, $input);

// Send to game listing if already logged in
if ($session->authenticated()) {
	header('Location: ' . URL_FULL . '/gamelisting.php');
	exit;
}

?>
