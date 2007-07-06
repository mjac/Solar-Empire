<?php

require('inc/config.inc.php');
require(PATH_INC . '/state/member.inc.php');

// Session is destroyed if user is logged in
$session->destroy();

header('Location:  ' . URL_BASE . '/index.php');

?>
