<?php
defined('PATH_SAVANT') || exit();

$this->pageName = 'Game over';
$this->title = 'All of your ships have been destroyed';
$this->description = '';

include($this->loadTemplate('game/inc/header_game.tpl.php'));

?><h1>Your ship has been destroyed</h1>

<p>Your ship was destroyed by <strong><?php 
$this->eprint($this->attackedBy); 
?></strong> at <em><?php $this->eprint(date('M d - H:s', $this->attackedAt)); ?></em>.</p>
<?php

if (isset($this->suddenDeath) && $this->suddenDeath) {

?><p>You have no ship and this game is in <em>Sudden Death</em>.  As such you are out of the game.  You may still access certain sections of the game and 
send/receive messages.</p>
<?php

} else {

?><p><a href="earth.php">Buy a ship</a> to continue playing.</p>
<?php

}

include($this->loadTemplate('game/inc/footer_game.tpl.php'));

?>
