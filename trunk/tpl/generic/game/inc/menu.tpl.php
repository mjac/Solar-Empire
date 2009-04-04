<?php
class_exists('Savant3') || exit;

if (!function_exists('popupHelp')) {
	require($this->template('inc/popupHelp.inc.php'));
}

if (!function_exists('formatName')) {
	require($this->template('game/inc/formatNames.inc.php'));
}

if (!function_exists('shipCargoReport')) {
	require($this->template('game/inc/formatShips.inc.php'));
}

?><h1><em><?php 

echo popupHelp('game_info.php?db_name=' . $this->game['dbName'], 600, 450, 
 $this->game['name'], $this) . ($this->game['status'] === 'running' ? '' : 
 (" (" . $this->escape($this->game['status']) . ")"));

?></em></h1>

<p><?php

echo (IS_ADMIN || IS_OWNER ? ('<a href="' . $this->escape($this->url['base'] . 
 '/admin_active_users.php') . '">') : '') . $this->escape($this->activeUsers) .
 ' active user(s)' . (IS_ADMIN || IS_OWNER ? '</a>' : '') . "</p>\n<p>" . 
 date('<\a \t\i\t\l\e="T">M d - H:i</\a>');

?></p>
<?php

if ($this->game['status'] === 'running') {
	echo "<p>" . ceil(($this->game['finishes'] - time()) / 86400) . 
	 " day(s) left</p>\n";
}

?><h2><em>Places</em></h2>
<ul>
	<li><a href="<?php $this->eprint($this->url['base'] . '/system.php'); 
?>">Star system</a></li>
	<li><a href="<?php $this->eprint($this->url['base'] . '/news.php'); 
?>">Game news</a></li>
	<li><a href="<?php $this->eprint($this->url['base'] . '/player_stat.php'); 
?>">Player ranking</a></li>
</ul>
<ul>
	<li><a href="<?php $this->eprint($this->url['base'] . '/diary.php'); 
?>">Fleet journal</a></li>
<?php

if ($this->game['clans']) {
?>	<li><a href="<?php $this->eprint($this->url['base'] . '/clan.php'); 
?>">Clan control</a></li>
<?php
}
?>	<li><a href="<?php $this->eprint($this->url['base'] . '/message_inbox.php'); 
?>"><?php $this->eprint(number_format($this->messageAmount)); ?> msg(s)</a> - 
	<a href="<?php $this->eprint($this->url['base'] . '/message.php'); 
?>">send</a></li>
	<li><a href="<?php $this->eprint($this->url['base'] . '/forum.php'); 
?>">Game forum</a><?php 

if ($this->forumNewMsgs > 0) {
	echo ' (' . $this->escape($this->forumNewMsgs) . ' <a href="' . 
	 $this->escape('forum.php?find_last=1&last_time=' . 
	 $this->forumLastAccess) . '">new</a>)';
}


?></li>
<?php

if ($this->viewAdminForum) {
?>
	<li><a href="<?php
	$this->eprint($this->url['base'] . '/forum.php?view_a_forum=1'); 
?>">Admin forum</a><?php 
	if ($this->adminForumNewMsgs > 0) {
		echo ' (' . $this->escape($this->adminForumNewMsgs) . ' <a href="' .
		 $this->escape($this->url['base'] . 
		 'forum.php?view_a_forum=1&last_time=' . $this->adminForumLastAccess) . 
		 '">new</a>)';
	}
?></li>
<?php
}

if ($this->viewClanForums) { // View all forums (admin etc)
?>	<li><a href="<?php 
	$this->eprint($this->url['base'] . '/forum.php?clan_forum=1'); 
?>">Clan forums</a></li>
<?php
} elseif ($this->player['clanId'] !== NULL) {
?>	<li><a href="<?php 
	$this->eprint($this->url['base'] . '/forum.php?clan_forum=1'); 
?>"><?php
	echo clanSymbol($this->player['clanSymbol'], 
	 $this->player['clanSymbolColour']);
?> Forum</a><?php
	if ($this->clanForumNewMsgs > 0) {
		echo ' (' . $this->escape($this->clanForumNewMsgs) . ' <a href="' .
		 $this->escape($this->url['base'] . 
		 'forum.php?clan_forum=1&find_last=1&last_time=' . 
		 $this->clanForumLastAccess) . '">new</a>)';
	}
?></li>
<?php
}

?>
	<li><a href="http://forum.syswars.com/">Global forum</a></li>
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
	<li><a href="<?php $this->eprint($this->url['base'] . '/help.php'); 
?>">Help files</a></li>
	<li><a href="<?php $this->eprint($this->url['base'] . '/options.php'); 
?>">Player options</a></li>
<?php

	if ($this->viewAdminPanel) {
?>	<li><a href="<?php $this->eprint($this->url['base'] . '/admin.php'); 
?>">Game admin</a></li>
<?php
	}

?>
	<li><a href="<?php
	$this->eprint($this->url['base'] . '/logout.php?logout_single_game=1'); 
?>">Game list</a></li>
	<li><a href="<?php
	$this->eprint($this->url['base'] . '/logout.php?comp_logout=1'); 
?>">Logout</a></li>
</ul>

<?php

if ($this->player['shipId'] === NULL) {

?><h2><em>Your ship is destroyed!</em></h2>
<p><a href="<?php $this->eprint($this->url['base'] . '/earth.php'); 
?>">Buy one</a> to continue playing.</p>
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
		<td><?php
	$this->eprint($this->ship['hull'] . ' / ' . $this->ship['maxHull']);
?></td>
	</tr>
	<tr>
		<th>Shields</th>
		<td><?php
	$this->eprint($this->ship['shields'] . ' / ' . $this->ship['maxShields']);
?></td>
	</tr>
	<tr>
		<th>Fighters</th>
		<td><?php
	$this->eprint($this->ship['fighters'] . ' / ' . $this->ship['maxFighters']);
?></td>
	</tr>
	<tr>
		<th>Specials</th>
		<td><?php
	echo empty($this->ship['config']) ? '<em>none</em>' :
	 $this->escape($this->ship['config']);
?></td>
	</tr>
	<tr>
		<th>Storage</th>
		<td><?php echo shipCargoReport($this->ship['cargo']); ?></td>
	</tr>
</table></div>
<?php

}

?>
