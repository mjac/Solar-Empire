<?php
class_exists('Savant2') || exit;

$this->pageName = 'Join game';
$this->title = 'Enter both your player and ship names to begin';

include($this->loadTemplate('inc/header_splash.tpl.php'));

?><h1>Join <?php $this->eprint($this->gameName); ?></h1>

<form action="<?php echo esc(URL_SELF); ?>" method="post">
	<dl>
		<dt><label for="in_game_name">Name you would like to play 
		under</label></dt>
		<dd><input name="in_game_name" id="in_game_name" value="<?php 
$this->eprint($this->accountName); ?>" class="text" /></dd>

		<dt><label for="ship_name">Title of your first ship</label></dt>
		<dd><input name="ship_name" id="ship_name" class="text" /></dd>

		<dt><input type="submit" value="Join" class="button" />
	<input type="hidden" name="game_selected" value="<?php 
$this->eprint($this->gameSelected); ?>" /></dt>
	</dl>
</form>
<?php

?><h2>Return to <a href="game_listing.php">game listing</a></h2>
<?php

include($this->loadTemplate('inc/footer_splash.tpl.php'));

?>
