<?php
if (!defined('PATH_SAVANT')) exit();

if (!function_exists('makeList')) {
	include('inc/list.inc.php');
}

$title = 'Problems joining game';

include('inc/header_splash.tpl.php');

?><h1>Cannot join game</h1>
<?php

echo makeList($this, 'Problems', $this->problems);

?><h2><a href="<?php $this->eprint($this->returnTo); 
?>" onclick="history.back(); return false;">Go back</a></h2>
<?php

include('inc/footer_splash.tpl.php');

?>
