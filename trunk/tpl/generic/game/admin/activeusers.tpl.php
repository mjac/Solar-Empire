<?php
class_exists('Savant2') || exit;

$this->pageName = 'Active users';
$this->title = 'Players currently online';
$this->description = '';

if (!function_exists('formatName')) {
	require($this->loadTemplate('game/inc/formatNames.inc.php'));
}

include($this->loadTemplate('game/inc/header_game.tpl.php'));

?><h1>Active users</h1>

<p>Showing players active between <?php
$this->eprint(date('H:i:s (M d)', $this->fromTime));
?> and <?php
$this->eprint(date('H:i:s (M d)', $this->currentTime));
?>.  <a href="<?php $this->eprint($this->url['self']); ?>">Reload this page</a> for a more current list of players online.</p>
<?php

if (empty($this->playersOnline)) {
?><p>There are no active players at the moment.</p>
<?php
} else {
?><table class="simple">
	<tr>
		<th>Last request</th>
		<th>Player name</th>
	</tr>
<?php
	foreach ($this->playersOnline as $player) {
?>	<tr>
		<td><?php
		$this->eprint(date('H:i:s (M d)', $player['lastRequest']));
?></td>
		<td><?php
		echo formatName($player['id'], $player['name'], $player['clan']['id'],
		 $player['clan']['symbol'], $player['clan']['colour']);
?></td>
	</tr>
<?php
	}
?></table>
<?php
}

include($this->loadTemplate('game/inc/footer_game.tpl.php'));

?>
