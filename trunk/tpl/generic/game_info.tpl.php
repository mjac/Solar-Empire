<?php
if (!defined('PATH_SAVANT')) exit();

if (!function_exists('formatName')) {
	include($this->loadTemplate('inc/format_names.inc.php'));
}

if (!function_exists('resolveDifficulty')) {
	include($this->loadTemplate('inc/difficulty.inc.php'));
}

$title = 'Game information';

include($this->loadTemplate('inc/header.tpl.php'));

if (!$this->gameExists) {
?><p>This game does not exist.</p>
<?php	
	include($this->loadTemplate('inc/footer.tpl.php'));
	return;
}


?><h1><?php $this->eprint($this->name); ?></h1>
<?php
if (isset($this->description) && !empty($this->description)) {
?><h2>Description</h2>
<p><?php echo $this->description; ?></p>
<?php
}

if (!$this->canRegister) {
?><p>Signups are disabled.</p>
<?php
}

?>
<h2>General information</h2>
<table class="simple">
	<tr>
		<th>Administrator</th>
		<td><?php $this->eprint($this->admin); ?></td>
	</tr>
	<tr>
		<th>Status</th>
		<td><?php $this->eprint($this->status); ?></td>
	</tr>
	<tr>
		<th>Players (alive) / maximum</th>
		<td><?php $this->eprint($this->playerAmount . ' (' . 
 $this->alivePlayers . ') / ' . $this->maxPlayers); 
?></td>
	</tr>
	<tr>
		<th>Difficulty</th>
		<td><?php echo resolveDifficulty($this->difficulty); ?></td>
	</tr>
	<tr>
		<th>Started</th>
		<td><?php echo date('l, j F Y H:i:s O', $this->started); ?></td>
	</tr>
	<tr>
		<th>Finishes</th>
		<td><?php echo date('l, j F Y H:i:s O', $this->finishes); ?></td>
	</tr>
	<tr>
		<th>Variables</th>
		<td><?php 

if ($this->viewVars) {

?>You may view the specific variables for this game on the 
<a href="game_vars.php?db_name=<?php $this->eprint($this->gameSelected);
?>">game variable</a> page.  Studying these can give 
you the competitive advantage.<?php

} else {
	echo "The admin has disabled viewing of game variables.";
}

?></td>
	</tr>
</table>
<?php

if ($this->playerAmount > 0) {

?>
<h2>Player information</h2>
<table class="simple">
	<tr>
		<th>Player</th>
		<th>Amount</th>
		<th>Average</th>
	</tr>
	<tr>
	    <th>Credits</th>
	    <td><?php echo number_format($this->playerCredits, 0); ?></td>
	    <td><?php echo 
 number_format($this->playerCredits / $this->playerAmount, 0); ?></td>
	</tr>
	<tr>
	    <th>Turns used</th>
	    <td><?php echo number_format($this->playerTurns); ?></td>
	    <td><?php echo 
 number_format($this->playerTurns / $this->playerAmount, 0); ?></td>
	</tr>
	<tr>
	    <th>Ship kills</th>
	    <td><?php echo number_format($this->shipsKilled, 0); ?></td>
	    <td><?php echo 
 number_format($this->shipsKilled / $this->playerAmount, 0); ?></td>
	</tr>
	<tr>
	    <th>Fighters killed</th>
	    <td><?php echo number_format($this->fightersKilled, 0); ?></td>
	    <td><?php echo 
 number_format($this->fightersKilled / $this->playerAmount, 0); ?></td>
	</tr>
</table>
<?php

}

if ($this->shipAmount > 0 && $this->playerAmount > 0) {

?>
<h2>Ship statistics</h2>
<table class="simple">
	<tr>
		<th>Ship</th>
		<th>Amount</th>
		<th>Average</th>
	</tr>
	<tr>
	    <th>Amount</th>
	    <td><?php echo number_format($this->shipAmount, 0); ?></td>
	    <td><?php echo 
 number_format($this->shipAmount / $this->playerAmount, 0); ?></td>
	</tr>
	<tr>
	    <th>Fighters</th>
	    <td><?php echo number_format($this->shipFighters, 0); ?></td>
	    <td><?php echo 
 number_format($this->shipFighters / $this->shipAmount, 0); ?></td>
	</tr>
</table>
<?php

}


if ($this->planetAmount > 0 && $this->playerAmount > 0) {
?>
<h2>Planet statistics</h2>
<table class="simple">
	<tr>
		<th>Planet</th>
		<th>Amount</th>
		<th>Average</th>
	</tr>
	<tr>
	    <th>Amount</th>
	    <td><?php echo number_format($this->planetAmount, 0); ?></td>
	    <td><?php echo 
 number_format($this->planetAmount / $this->playerAmount, 0); ?></td>
	</tr>
	<tr>
	    <th>Fighters</th>
	    <td><?php echo number_format($this->planetFighters, 0); ?></td>
	    <td><?php echo 
 number_format($this->planetFighters / $this->planetAmount, 0); ?></td>
	</tr>
	<tr>
	    <th>Credits</th>
	    <td><?php echo number_format($this->planetCredits, 0); ?></td>
	    <td><?php echo 
 number_format($this->planetCredits / $this->planetAmount, 0); ?></td>
	</tr>
</table>
<?php
}


if (!empty($this->topPlayers)) {

?>
<h2>Top 10 Players</h2>
<table class="simple">
	<tr>
	    <th>Rank</th>
	    <th>Name</th>
	    <th>Score</th>
	</tr>

<?php
	$rank = 0;
	foreach ($this->topPlayers as $player) {
?>
	<tr>
		<td><?php echo ++$rank; ?></td>
	    <td><?php
		echo formatName($player['login_id'], $player['login_name'],
		 $player['clan_id'], $player['clan_sym'], $player['clan_sym_color']);
?></td>
	    <td><?php echo $player['score']; ?></td>
	</tr>
<?php
	}
?>
</table>
<?php

}

include($this->loadTemplate('inc/footer.tpl.php'));

?>
