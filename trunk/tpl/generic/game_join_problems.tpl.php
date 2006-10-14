<?php
class_exists('Savant2') || exit;

if (!function_exists('makeList')) {
	require($this->loadTemplate('inc/list.inc.php'));
}

$this->pageName = 'Join game';
$this->title = 'Problems with your attempt to join the game';

include($this->loadTemplate('inc/header_splash.tpl.php'));

?><h1>Cannot join game</h1>
<?php

echo makeList($this, 'Problems', $this->problems);

?><h2><a href="<?php $this->eprint($this->returnTo); 
?>" onclick="history.back(); return false;">Go back</a></h2>
<?php

include($this->loadTemplate('inc/footer_splash.tpl.php'));

?>
