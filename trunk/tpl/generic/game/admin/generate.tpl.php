<?php
defined('PATH_SAVANT') || exit();

$this->pageName = 'Administration';
$this->title = 'Game administration panel';
$this->description = '';

include($this->loadTemplate('game/inc/header_game.tpl.php'));

?><h1>Generate universe</h1>

<p><label for="actPreview">Preview the universe</label>, changing the 
universe variables, until you find a good combination of settings.  After this 
<label for="actCreate">generate the universe</label> to create all the stars, 
ports and graphics.</p>
<form action="<?php $this->eprint($this->url['self']); ?>" method="get">
	<p><input type="submit" id="actPreview" name="action" value="Preview" /> #
	then <input type="submit" id="actCreate" name="action" value="Create" /></p>
</form>
<?php

include($this->loadTemplate('game/inc/footer_game.tpl.php'));

?>
