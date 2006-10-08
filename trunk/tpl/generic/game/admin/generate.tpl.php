<?php
defined('PATH_SAVANT') || exit();

$this->pageName = 'Administration';
$this->title = 'Game administration panel';
$this->description = '';

include($this->loadTemplate('game/inc/header_game.tpl.php'));

?><h1>Generate universe</h1>

<p><label for="actPreview">Preview the universe</label>, changing the universe variables, until you find a good combination before <label for="actCreate">generating the universe</label> to create and store all the entries and graphics for the current game.</p>
<form action="<?php $this->eprint($this->url['self']); ?>" method="get">
	<p><input type="submit" id="actPreview" name="action" value="Preview" class="button" /> then 
	<input type="submit" id="actCreate" name="action" value="Create" class="button" /></p>
</form>
<p>You can also <a href="<?php
$this->eprint($this->url['self'] . '?action=maps'); 
?>">recreate the maps</a> using the current univerese schema without resetting the entire universe.</p>
<?php

if (!isset($this->action)) {
	include($this->loadTemplate('game/inc/footer_game.tpl.php'));
	return;
}

if ($this->action === 'create') {
?><h2>Creating the game universe</h2>
<?php
} elseif ($this->action === 'preview') {
?><h2>Preview of current settings</h2>
<?php
} elseif ($this->action === 'maps') {
?><h2>Creating universe maps</h2>
<?php
}

include($this->loadTemplate('game/inc/footer_game.tpl.php'));

?>
