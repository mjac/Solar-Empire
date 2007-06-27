<?php

require('inc/config.inc.php');
require(PATH_INC . '/statemember.inc.php');

$session->destroy();

header('Location:  ' . URL_BASE . '/index.php');

?>
