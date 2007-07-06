<?php

require_once('user.inc.php');

if ($user['login_id'] != OWNER_ID) {
	print_page('Error', 'Only the owner is allowed to use this.');
	exit();
}

?>
