<?php
class_exists('Savant2') || exit;

$this->pageName = 'System';
$this->title = 'The system does not exist';
$this->description = '';

include($this->loadTemplate('game/inc/header_game.tpl.php'));

include($this->loadTemplate('game/inc/location.tpl.php'));

?><div id="locInfo">
<h1>Star system <?php $this->eprint($this->star['id']); ?></h1>

<p>Star system information</p>
</div>
<?php

include($this->loadTemplate('game/inc/footer_game.tpl.php'));

?>
