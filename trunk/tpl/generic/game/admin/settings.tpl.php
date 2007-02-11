<?php
class_exists('Savant2') || exit;

$this->pageName = 'Settings';
$this->title = 'Modify game settings';
$this->description = '';

include($this->loadTemplate('game/inc/header_game.tpl.php'));

?><h1>Game settings</h1>

<h2><label for="gameStatus">Status</label></h2>
<form action="<?php $this->eprint($this->url['self']); ?>" method="post">
	<p><select name="status" id="gameStatus">
		<option value="hidden">Hidden</option>
		<option value="paused">Paused</option>
		<option value="running">Running</option>
	</select>
	<input type="submit" value="Change status" class="button" /></p>
</form>

<h2>Finish date</h2>
<form action="<?php $this->eprint($this->url['self']); ?>" method="post">
	<p><input type="text" name="finishes" value="YYYY-MM-DD HH:MM:SS"
	 class="text" />
	<input type="submit" value="Change finish date" class="button" /></p>
</form>

<h2><label for="gameIntro">Introduction</label></h2>
<p>Enter a message that all new players will recieve when they join.  XHTML can be used, ensure that it is valid.</p>
<form action="<?php $this->eprint($this->url['self']); ?>" method="post">
	<p><textarea name="introduction" id="gameIntro" cols="50" rows="20"><?php
$this->eprint($this->gameIntroduction);
?></textarea></p>
	<p><input type="submit" value="Change message" class="button" /></p>
</form>

<h2><label for="gameDesc">Description</label></h2>
<p>Enter a message that explains the purpose of this specific game. XHTML can be used, ensure that it is valid.</p>
<form action="<?php $this->eprint($this->url['self']); ?>" method="post">
	<p><textarea name="description" id="gameDesc" cols="50" rows="20"><?php
$this->eprint($this->gameDescription);
?></textarea></p>
	<p><input type="submit" value="Change message" class="button" /></p>
</form>
<?php

include($this->loadTemplate('game/inc/footer_game.tpl.php'));

?>
