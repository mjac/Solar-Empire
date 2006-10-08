<?php
defined('PATH_SAVANT') || exit();

$this->pageName = 'System';
$this->title = 'The system does not exist';
$this->description = '';

include($this->loadTemplate('game/inc/header_game.tpl.php'));

?><h1>Missing star system</h1>

<p>This star system does not exist.  The universe should be created.</p>
<?php

include($this->loadTemplate('game/inc/footer_game.tpl.php'));

?>
