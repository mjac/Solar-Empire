<?php

function resolve_difficulty($diff)
{
	$txt = array(
		'Training',
		'Beginner',
		'Intermediate',
		'Challenge',
		'Advanced',
		'All Levels'
	);
	return $txt[($diff - 1) % count($txt)];
}

require_once('inc/common.inc.php');
require_once('inc/db.inc.php');

$gameInfo = selectGame(isset($_REQUEST['db_name']) ? $_REQUEST['db_name'] : '');
if (!$gameInfo) {
	print_page('Error', 'Invalid game!');
}

print_header('Game information');

$gInfo = $db->query('SELECT COUNT(*), SUM(cash), SUM(turns), ' .
 'SUM(turns_run), SUM(ships_killed), SUM(fighters_lost), ' .
 'SUM(fighters_killed) FROM [game]_users WHERE ' .
 'login_id != %u', array($gameInfo['admin']));
$playerStats = $db->fetchRow($gInfo, ROW_NUMERIC);

$gInfo = $db->query('SELECT g.name, g.description, g.paused, ' .
 'g.last_reset, g.difficulty, u.login_name FROM se_games AS g ' .
 'LEFT JOIN user_accounts AS u ON g.admin = u.login_id WHERE ' .
 'g.db_name = \'[game]\'');
$info = $db->fetchRow($gInfo);

?>
<h1><?php echo esc($info['name']); ?></h1>

<table class="simple">
	<tr>
	    <th>Admin name</th>
	    <td><?php echo esc($info['login_name']); ?></td>
	</tr>
	<tr>
	    <th>Status</th>
	    <td><?php echo $info['paused'] == 1 ? 'Paused' : 'Running'; ?></td>
	</tr>
	<tr>
	    <th>Players / Max</th>
	    <td><?php echo esc($playerStats[0] . ' / ' . $max_players); ?></td>
	</tr>
	<tr>
	    <th>Difficulty</th>
	    <td><?php echo esc(resolve_difficulty($info['difficulty'])); ?></td>
	</tr>
	<tr>
	    <th>Started</th>
	    <td><?php echo date("M d - H:i", $info['last_reset']); ?></td>
	</tr>
</table>
<?php

if ($playerStats[0] >= $max_players) {
?>
<p>Player Limit for game reached. No New players allowed to join game.</p>
<?php
} elseif ($gameOpt['new_logins'] == 0 || $gameOpt['sudden_death'] == 1) {
?>
<p>Signups are disabled.</p>
<?php
}

if ($admin_var_show == 1) {
?>
<p><a href="<?php echo esc('game_vars.php?db_name=' . $db_name);
?>">Game Variables</a></p>

<?php
}

if (!empty($info['description'])) {
?>
<h2>Admin description</h2>
<div><?php echo $info['description']; ?></div>
<?php
}


// Admin board
$latestNews = $db->query("SELECT headline, timestamp FROM [game]_news WHERE login_id = -1 OR login_id = -11 ORDER BY timestamp DESC LIMIT 5");
if ($db->numRows($latestNews) > 0) {
?>
<h2>Last 5 news headlines</h2>
<table class="simple">
	<tr>
		<th>Date</th>
		<th>Headline</th>
	</tr>

<?php
	while ($article = $db->fetchRow($latestNews)) {
?>
	<tr>
		<td><?php echo date('M d - H:i', $article['timestamp']); ?></td>
		<td><?php echo esc($article['headline']); ?></td>
	</tr>

<?php
	}
?>
</table>
<?php
}


$aQuery = $db->query('SELECT COUNT(*) FROM [game]_users WHERE ' .
 'ship_id IS NOT NULL AND login_id != %u', array($gameInfo['admin']));
$alive = (int)current($db->fetchRow($aQuery));

if ($playerStats[0] > 0) {
?>
<h2>Player information</h2>
<table class="simple">
	<tr>
	    <th>Players (alive)</th>
	    <td><?php echo $playerStats[0] . ' (' , $alive . ')'; ?></td>
	</tr>
	<tr>
	    <th>Cash</th>
	    <td><?php echo number_format($playerStats[1]); ?></td>
	</tr>
	<tr>
	    <th>Cash average</th>
	    <td><?php echo number_format($playerStats[1] / $playerStats[0]); ?></td>
	</tr>
	<tr>
	    <th>Turns</th>
	    <td><?php echo number_format($playerStats[2]); ?></td>
	</tr>
	<tr>
	    <th>Turns average</th>
	    <td><?php echo number_format($playerStats[2] / $playerStats[0]); ?></td>
	</tr>
	<tr>
	    <th>Turns used</th>
	    <td><?php echo number_format($playerStats[3]); ?></td>
	</tr>
	<tr>
	    <th>Turns used average</th>
	    <td><?php echo number_format($playerStats[3] / $playerStats[0]); ?></td>
	</tr>
	<tr>
	    <th>Ship kills</th>
	    <td><?php echo number_format($playerStats[4]); ?></td>
	</tr>
	<tr>
	    <th>Ship kill average</th>
	    <td><?php echo number_format($playerStats[4] / $playerStats[0]); ?></td>
	</tr>
	<tr>
	    <th>Fighters killed</th>
	    <td><?php echo number_format($playerStats[6]); ?></td>
	</tr>
	<tr>
	    <th>Fighters killed average</th>
	    <td><?php echo number_format($playerStats[6] / $playerStats[0]); ?></td>
	</tr>
</table>
<?php
}

$shipInfo = $db->query('SELECT COUNT(*), SUM(fighters) FROM [game]_ships ' .
 'WHERE login_id != %u', array($gameInfo['admin']));
$shipStats = $db->fetchRow($shipInfo, ROW_NUMERIC);
if ($shipStats[0] > 0) {
?>
<h2>Ship statistics</h2>
<table class="simple">
	<tr>
	    <th>Amount</th>
	    <td><?php echo $shipStats[0]; ?></td>
	</tr>
	<tr>
	    <th>Average per player</th>
	    <td><?php echo round($shipStats[0] / $playerStats[0]); ?></td>
	</tr>
	<tr>
	    <th>Fighters</th>
	    <td><?php echo $shipStats[1]; ?></td>
	</tr>
	<tr>
	    <th>Average fighters</th>
	    <td><?php echo round($shipStats[1] / $shipStats[0]); ?></td>
	</tr>
</table>
<?php
}

$planetInfo = $db->query('SELECT COUNT(*), SUM(fighters), SUM(cash) ' .
 'FROM [game]_planets WHERE login_id != %u',
 array($gameInfo['admin']));
$planetStats = $db->fetchRow($planetInfo, ROW_NUMERIC);

if ($planetStats[0] > 0) {
?>
<h2>Planet statistics</h2>
<table class="simple">
	<tr>
	    <th>Amount</th>
	    <td><?php echo $planetStats[0]; ?></td>
	</tr>
	<tr>
	    <th>Average per player</th>
	    <td><?php echo round($planetStats[0] / $playerStats[0]); ?></td>
	</tr>
	<tr>
	    <th>Fighters</th>
	    <td><?php echo $planetStats[1]; ?></td>
	</tr>
	<tr>
	    <th>Average fighters</th>
	    <td><?php echo round($planetStats[1] / $planetStats[0]); ?></td>
	</tr>
	<tr>
	    <th>Cash</th>
	    <td><?php echo $planetStats[2]; ?></td>
	</tr>
	<tr>
	    <th>Average cash</th>
	    <td><?php echo round($planetStats[2] / $planetStats[0]); ?></td>
	</tr>
</table>
<?php
}


$topPlayers = $db->query('SELECT login_id, login_name, u.clan_id, ' .
 'c.symbol AS clan_sym, c.sym_color AS clan_sym_color, ' .
 'u.score FROM [game]_users AS u LEFT JOIN [game]_clans AS c ON ' .
 'u.clan_id = c.clan_id WHERE login_id != %u ORDER BY score DESC, ' .
 'login_name LIMIT 10', array($gameInfo['admin']));

if ($db->numRows($topPlayers) > 0) {
?>
<h2>Top 10 Players</h2>
<table class="simple">
	<tr>
	    <th>Name</th>
	    <th>Score</th>
	</tr>

<?php
	while ($player = $db->fetchRow($topPlayers)) {
?>
	<tr>
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

print_footer();

?>
