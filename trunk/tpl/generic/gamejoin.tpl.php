<?php
class_exists('Savant2') || exit;

$this->pageName = 'Join game';
$this->title = 'Enter both your player and ship names to begin';

include($this->loadTemplate('inc/headersplash.tpl.php'));

?><h1>Join <?php $this->eprint($this->gameName); ?></h1>

<form action="<?php $this->eprint($this->url['base'] . '/gamejoin.php'); ?>" method="post">
	<dl>
		<dt><label for="playerName">Name you would like to play
		under</label></dt>
		<dd><input name="playerName" id="playerName" value="<?php
$this->eprint($this->accountName); ?>" class="text" /></dd>

		<dt><label for="shipName">Title of your first ship</label></dt>
		<dd><input name="shipName" id="shipName" class="text" /></dd>

		<dt><input type="submit" value="Join game" class="button" />
		<input type="hidden" name="gameid" value="<?php
$this->eprint($this->gameSelected); ?>" /></dt>
	</dl>
</form>
<?php

include($this->loadTemplate('inc/footersplash.tpl.php'));

?>
