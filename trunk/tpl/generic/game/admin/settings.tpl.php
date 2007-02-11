<?php
class_exists('Savant2') || exit;

$this->pageName = 'Settings';
$this->title = 'Modify game settings';
$this->description = '';

include($this->loadTemplate('game/inc/header_game.tpl.php'));

?><h1>Game settings</h1>

<?php

if (isset($this->changed) && !empty($this->changed)) {
?><h2>Changes</h2>
<ul>
<?php

	if (isset($this->changed['status'])) {
?><p><?php
	    if ($this->changed['status']) {
?>The game status was updated successfully to <?php
			$this->eprint($this->changed['status']);
?>.<?php
	    } else {
?>The game status was not updated successfully: either the given status was invalid or the database value could not be changed.<?php
	    }
?></p>
<?php
	}

	if (isset($this->changed['finishes'])) {
?><p><?php
	    if ($this->changed['finishes']) {
?>The game finishing date was updated successfully to <?php
			$this->eprint(date('Y-m-d H:i:s', $this->changed['finishes']));
?>.<?php
	    } else {
?>The game finishing date was not updated successfully: please supply the date in the format YYYY-MM-DD HH:MM:SS.<?php
	    }
?></p>
<?php
	}

	if (isset($this->changed['introduction'])) {
?><p><?php
	    if ($this->changed['introduction']) {
?>The game introduction was updated successfully.<?php
	    } else {
?>The game introduction was not updated.<?php
	    }
?></p>
<?php
	}

	if (isset($this->changed['description'])) {
?><p><?php
	    if ($this->changed['description']) {
?>The game description was updated successfully.<?php
	    } else {
?>The game description was not updated.<?php
	    }
?></p>
<?php
	}
	
?></ul>
<?php
}

?><h2><label for="gameStatus">Status</label></h2><form action="<?php $this->eprint($this->url['self']); ?>" method="post">
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
