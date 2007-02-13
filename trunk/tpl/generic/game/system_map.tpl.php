<?php
class_exists('Savant2') || exit;

$this->pageName = 'System map';
$this->title = 'Map of the known universe';
$this->description = '';

include($this->loadTemplate('game/inc/header_game.tpl.php'));

?><h1>Star map</h1>
<p><img src="<?php
$this->eprint($this->map);
?>" alt="Complete map of the known universe" /></p>
<ul>
	<li><a href="$self">Normal Map</a></li>
	<li><a href="$self?print=1">Printable Map</a></li>
</ul>

<?php

if ($this->canSearch) {
?><h2>Find system</h2>
<form action="<?php $this->eprint($this->url['self']); ?>" method="get">
	<input type="text" name="find" id="find" size="4" class="text" />
	<input type="submit" value="Search for system" class="button" />
</form>
<?php
}

include($this->loadTemplate('game/inc/footer_game.tpl.php'));

?>
