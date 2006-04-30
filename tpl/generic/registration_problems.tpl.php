<?php
if (!defined('PATH_SAVANT')) exit();

require_once('inc/list.inc.php');

$title = 'Problems while registering';

include('inc/header_splash.tpl.php');

?><h1>Register an account</h1>
<?php

echo makeList($this, 'Problems', $this->problems);

?><h2><a href="register.php" onclick="history.back(); return false;">Try 
again</a></h2>
<?php

include('inc/footer_splash.tpl.php');

?>
