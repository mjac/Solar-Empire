<?php

require_once('user.inc.php');

if (!(IS_ADMIN || IS_OWNER)) {
	print_page('Error', 'Only the admin or owner is allowed to use this.');
	exit;
}

?>
