<?php
if (!defined('PATH_SAVANT')) exit();

require_once('inc/list.inc.php');

$title = 'Problems logging in';

include('inc/header_splash.tpl.php');

?><h1>Sign-in failed</h1>
<?php

echo makeList($this, 'Problems', $this->problems);

?><h2><a href="register.php">Register</a> or 
<a href="index.php" onclick="history.back(); return false;">Try Again</a></h2>
<?php

include('inc/footer_splash.tpl.php');

?>
