<?php
if (!defined('PATH_SAVANT')) exit();

if (!function_exists('popupHelp')) {
	include('popup_help.inc.php');
}

if (!function_exists('formatName')) {
	include('format_names.inc.php');
}

if (!function_exists('shipCargoReport')) {
	include('format_ships.inc.php');
}

?><h1><em><?php 

echo popupHelp('game_info.php?db_name=' . $this->game['dbName'], 600, 450, 
 $this->game['name'], $this) . ($this->game['status'] === 'running' ? '' : 
 (" (" . $this->escape($this->game['status']) . ")"));

?></em></h1>

<?php

if (IS_ADMIN || IS_OWNER) {
	$start = '<a href="admin.php?show_active=1">';
	$end = '</a>';
} else {
	$start = $end = '';
}

echo "<p>$start" . $this->escape($this->activeUsers) . " active user(s)$end" .
 "</p>\n<p>" . date('<\a \t\i\t\l\e="T">M d - H:i</\a>') . "</p>\n";

if ($this->game['status'] === 'running') {
	echo "<p>" . ceil(($this->game['finishes'] - time()) / 86400) . 
	 " day(s) left</p>\n";
}

?><h2><em>Places</em></h2>
<ul>
	<li><a href="system.php">Star system</a></li>
	<li><a href="news.php">Game news</a></li>
	<li><a href="player_stat.php">Player ranking</a></li>
</ul>
<ul>
	<li><a href="diary.php">Fleet journal</a></li>
<?php

if ($this->game['clans']) {
?>	<li><a href="clan.php">Clan control</a></li>
<?php
}
?>	<li><a href="message_inbox.php"><?php $this->eprint($this->messageAmount); 
	?> msg(s)</a> - 
	<a href="message.php">send</a></li>
	<li><a href="forum.php">Game forum</a><?php 

if ($this->forumNewMsgs > 0) {
	echo ' (' . $this->escape($this->forumNewMsgs) . ' <a href="' . 
	 $this->escape("forum.php?find_last=1&last_time=" . 
	  $this->forumLastAccess) . '">new</a>)';
}


?></li>
<?php

if ($this->viewAdminForum) {
?>
	<li><a href="forum.php?view_a_forum=1">Admin forum</a><?php 
	if ($this->adminForumNewMsgs > 0) {
		echo ' (' . $this->escape($this->adminForumNewMsgs) . ' <a href="' .
		 $this->escape('forum.php?view_a_forum=1&last_time=' . 
		 $this->adminForumLastAccess) . '">new</a>)';
	}
?></li>
<?php
}

if ($this->viewClanForums) { // View all forums (admin etc)
?>	<li><a href="forum.php?clan_forum=1">Clan forums</a></li>
<?php
} elseif ($this->player['clanId'] !== NULL) {
?>	<li><a href="forum.php?clan_forum=1"><?php
	echo clanSymbol($this->player['clanSymbol'], 
	 $this->player['clanSymbolColour']);
?> Forum</a><?php
	if ($this->clanForumNewMsgs > 0) {
		echo ' (' . $this->escape($this->clanForumNewMsgs) . ' <a href="' .
		 $this->escape('forum.php?clan_forum=1&find_last=1&last_time=' . 
		 $this->clanForumLastAccess) . '">new</a>)';
	}
?></li>
<?php
}

?>
	<li><a href="http://forum.solar-empire.net/">Global forum</a></li>
</ul>

<h2><em><?php

echo formatName($this->player['id'], $this->player['name'], 
 $this->player['clanId'], $this->player['clanSymbol'], 
 $this->player['clanSymbolColour']) . "</em></h2>\n";

if ($this->player['turnsUsed'] < $this->turnsSafe) {
	echo "<p>" . ($this->turnsSafe - $this->player['turnsUsed']) . 
	 " safe turn(s) left</p>\n";
} elseif ($this->player['turnsUsed'] == $this->turnsSafe) {
	echo "<p><em>Leaving</em> newbie safety!</p>\n";
}

?><div><table>
	<tr>
		<th>Turns</th>
		<td><?php $this->eprint($this->player['turns'] . ' / ' . 
 $this->turnsMax); 
?></td>
	</tr>
	<tr>
		<th>Credits</th>
		<td><?php $this->eprint(number_format($this->player['credits'], 0));
?></td>
	</tr>
	<tr>
		<th>Kills</th>
		<td><?php $this->eprint($this->player['shipsKilled'] . ' / ' . 
 $this->player['shipsLost']); ?></td>
	</tr>
	<tr>
		<th>Score</th>
		<td><?php $this->eprint($this->player['score']); ?></td>
	</tr>
</table></div>
<ul>
	<li><a href="help.php">Help files</a></li>
	<li><a href="options.php">Player options</a></li>
<?php

	if ($this->viewAdminPanel) {
?>	<li><a href="admin.php">Game admin</a></li>
<?php
	}
	if ($this->viewOwnerPanel) {
?>	<li><a href="owner.php">Server info</a></li>
<?php
	}

?>
	<li><a href="logout.php?logout_single_game=1">Game list</a></li>
	<li><a href="logout.php?comp_logout=1">Logout</a></li>
</ul>

<?php

if ($this->player['shipId'] === NULL) {

?><h2><em>Your ship is destroyed!</em></h2>
<p><a href="earth.php">Buy one</a> to continue playing.</p>
<?php

} else {

?><h2><em><?php 
echo popupHelp('help.php?popup=1&ship_info=1&shipno=' .
 $this->ship['typeId'], 300, 600, $this->ship['name'], $this);
?></em></h2>
<div><table>
	<tr>
		<th>Class</th>
		<td><?php $this->eprint($this->ship['class']); ?></td>
	</tr>
	<tr>
		<th>Hull</th>
		<td><?php $this->eprint($this->ship['hull'] . ' / ' . 
 $this->ship['maxHull']); ?></td>
	</tr>
	<tr>
		<th>Shields</th>
		<td><?php $this->eprint($this->ship['shields'] . ' / ' . 
 $this->ship['maxShields']); ?></td>
	</tr>
	<tr>
		<th>Fighters</th>
		<td><?php $this->eprint($this->ship['fighters'] . ' / ' . 
 $this->ship['maxFighters']); ?></td>
	</tr>
	<tr>
		<th>Specials</th>
		<td><?php echo empty($this->ship['config']) ? '<em>none</em>' :
 $this->escape($this->ship['config']); ?></td>
	</tr>
	<tr>
		<th>Storage</th>
		<td><?php echo shipCargoReport($this->ship['cargo']); ?></td>
	</tr>
</table></div>
<?php

}

?>
