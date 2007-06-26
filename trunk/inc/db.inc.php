<?php
defined('PATH_INC') || exit;

if (class_exists('swDatabase') ||
     @include(PATH_INC . '/swDatabase.class.php')) {
	exit('Cannot find swDatabase class');
}

$db = new swDatabase;
$db->start();

?>
