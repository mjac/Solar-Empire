<?php
class_exists('Savant3') || exit;

$this->pageName = 'System';
$this->title = 'The system does not exist';
$this->description = '';

include($this->template('game/inc/header_game.tpl.php'));

?><h1>Missing star system</h1>

<p>This star system does not exist.  The universe should be created.</p>
<?php

include($this->template('game/inc/footer_game.tpl.php'));

?>
