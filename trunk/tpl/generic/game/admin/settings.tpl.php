<?php
class_exists('Savant3') || exit;

$this->pageName = 'Settings';
$this->title = 'Modify game settings';
$this->description = '';

$status = array(
	'hidden' => 'Hidden',
	'paused' => 'Paused',
	'running' => 'Running'
);

include($this->template('game/inc/header_game.tpl.php'));

?><h1>Game settings</h1>

<?php

if (isset($this->changed) && !empty($this->changed)) {
?><h2>Changes</h2>
<ul>
<?php

	if (isset($this->changed['status'])) {
?><p><?php
	    if ($this->changed['status'] !== false) {
?>The game status was changed successfully to <?php
			$this->eprint($this->changed['status']);
?>.<?php
	    } else {
?>The game status was not changed successfully: either the given status was invalid or the database value could not be changed.<?php
	    }
?></p>
<?php
	}

	if (isset($this->changed['finishes'])) {
?><p><?php
	    if ($this->changed['finishes'] !== false) {
?>The game finishing date was changed successfully to <?php
			$this->eprint(date('Y-m-d H:i:s', $this->changed['finishes']));
?>.<?php
	    } else {
?>The game finishing date was not changed successfully: please supply the date in the format YYYY-MM-DD HH:MM:SS.<?php
	    }
?></p>
<?php
	}

	if (isset($this->changed['introduction'])) {
?><p><?php
	    if ($this->changed['introduction'] !== false) {
?>The game introduction was changed successfully.<?php
	    } else {
?>The game introduction was not changed.<?php
	    }
?></p>
<?php
	}

	if (isset($this->changed['description'])) {
?><p><?php
	    if ($this->changed['description'] !== false) {
?>The game description was changed successfully.<?php
	    } else {
?>The game description was not changed.<?php
	    }
?></p>
<?php
	}
	
?></ul>
<?php
}



?><h2><label for="gameStatus">Status</label></h2><form action="<?php $this->eprint($this->url['self']); ?>" method="post">
	<p><select name="status" id="gameStatus">
<?php
foreach ($status as $value => $display) {
?>	<option value="<?php $this->eprint($value); ?>"<?php
	if ($this->game['status'] === $value) {
?> selected="selected"<?php
	}
?>><?php
	$this->eprint($display);
?></option>
<?php
}
?>
	</select>
	<input type="submit" value="Change status" class="button" /></p>
</form>

<h2>Finish date</h2>
<form action="<?php $this->eprint($this->url['self']); ?>" method="post">
	<p><input type="text" name="finishes" value="<?php
$this->eprint(date('Y-m-d H:i:s', $this->game['finishes']));
?>" class="text" />
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

include($this->template('game/inc/footer_game.tpl.php'));

?>
