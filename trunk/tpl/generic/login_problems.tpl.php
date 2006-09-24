<?php
defined('PATH_SAVANT') || exit();

if (!function_exists('makeList')) {
	require('inc/list.inc.php');
}

$title = 'Problems logging in';

include($this->loadTemplate('inc/header_splash.tpl.php'));

?><h1>Sign-in failed</h1>
<?php

echo makeList($this, 'Problems', $this->problems);

?><h2><a href="register.php">Register</a> or 
<a href="index.php" onclick="history.back(); return false;">Try Again</a></h2>
<?php

include($this->loadTemplate('inc/footer_splash.tpl.php'));

?>
