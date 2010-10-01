<?php

require_once('inc/user.inc.php');

$text = '';

if (isset($table) && $table == 2) {
	$text .= <<<END
<h1>Player Ranking (miscellaneous)</h1>
<p><a href="$self">General</a> - Misc -
<a href="$self?table=3">Alive Players</a></p>

END;

	$tbQuery = array(
		'score' => 'ORDER BY score DESC',
		'name'  => 'ORDER BY login_name DESC',
		'su'    => 'ORDER BY joined_game DESC',
		'lab'   => 'AND last_attack_by != \'\' ORDER BY last_attack_by',
		'la'    => 'AND last_attack > 1 ORDER BY last_attack DESC',
		'clan'  => 'AND c.clan_id IS NOT NULL ORDER BY clan_name DESC'
	);
	$tbDesc = array(
		'score' => 'Ordered by <b>Score</b>',
		'name'  => 'Ordered by <b>Login Name</b>',
		'su'    => 'Ordered by time of <b>Signing Up</b> (Most Recent at the top)',
		'lab'   => 'Ordered by <b>Last Attack by</b>',
		'la'    => 'Ordered by time of <b>Last Attack</b> (Most Recent at the top)',
		'clan'  => 'Ordered by <b>Clan-Name</b>'
	);

	$allowed = array_keys($tbQuery);
	$tbType = isset($_GET['orderBy']) && in_array($_GET['orderBy'], $allowed) ?
	 $_GET['orderBy'] : $allowed[0];

	$query = "SELECT login_name, joined_game, last_attack_by, last_attack, score, u.clan_id, c.symbol AS clan_sym, c.sym_color AS clan_sym_color, login_id FROM [game]_users AS u LEFT JOIN [game]_clans AS c ON u.clan_id = c.clan_id WHERE login_id != {$gameInfo['admin']} " . $tbQuery[$tbType];

	$text .= <<<END
<p>$tbDesc[$tbType]</p>
<table class="simple">
	<tr>
	    <th>(<a href="$self?table=2&amp;orderBy=clan">Clan</a>)
		<a href="$self?table=2&amp;orderBy=name">Name</a></th>
	    <th><a href="$self?table=2&amp;orderBy=su">Signed Up</a></th>
	    <th><a href="$self?table=2&amp;orderBy=lab">Last Attack</a></th>
	    <th><a href="$self?table=2&amp;orderBy=la">Last Attack Date</a></th>
	    <th><a href="$self?table=2">Score</a></th>
	</tr>

END;
} else {
	$alive = isset($table) && $table == 3;
	$tableId = $alive ? 3 : 1;

	$text .= "<h1>Player Ranking" . ($tableId === 3 ?
	 ' (only alive players)' : '') . "</h1>\n";

	$tbQuery = array(
		'score'    => 'ORDER BY score DESC',
		'name'     => 'ORDER BY login_name',
		'figkills' => 'AND fighters_killed > 0 ORDER BY fighters_killed DESC',
		'kills'    => 'AND ships_killed > 0 ORDER BY ships_killed DESC',
		'lost'     => 'AND ships_lost > 0 ORDER BY ships_lost DESC',
		'turns'    => 'AND turns_run > 0 ORDER BY turns_run DESC',
		'clan'  => 'AND c.clan_id IS NOT NULL ORDER BY clan_name DESC'
	);
	$tbDesc = array(
		'score'    => 'Ordered by <b>Score</b>',
		'name'     => 'Ordered by <b>Login Name</b>',
		'figkills' => 'Ordered by <b>Fighters Killed</b>',
		'kills'    => 'Ordered by <b>Ship Kills</b>',
		'lost'     => 'Ordered by <b>Ship Lost</b>',
		'turns'    => 'Ordered by <b>Turns Run</b>',
		'clan'  => 'Ordered by <b>Clan-Name</b>'
	);
	$allowed = array_keys($tbQuery);
	$tbType = isset($_GET['orderBy']) && in_array($_GET['orderBy'], $allowed) ?
	 $_GET['orderBy'] : $allowed[0];

	if ($alive) {
		$text .= "<p><a href=$self>General</a> - <a href=$self?table=2>Misc</a> - Alive Players</p>\n";
		$queryAdd = ' ship_id IS NOT NULL AND';
	} else {
		$text .= "<p>General - <a href=$self?table=2>Misc</a> - <a href=$self?table=3>Alive Players</a></p>\n";
		$queryAdd = '';
	}

	$query = "SELECT cash, login_name, fighters_killed, ships_killed, ships_lost, turns_run, score, login_id, u.clan_id, c.symbol AS clan_sym, c.sym_color AS clan_sym_color, login_id FROM [game]_users AS u LEFT JOIN [game]_clans AS c ON u.clan_id = c.clan_id WHERE$queryAdd login_id != {$gameInfo['admin']}  " . $tbQuery[$tbType];

	$text .= <<<END
<p>$tbDesc[$tbType]</p>
<table class="simple">
	<tr>
	    <th><a href="$self?table=$tableId">Rank</a></th>
	    <th>(<a href="$self?table=$tableId&amp;orderBy=clan">Clan</a>)
		<a href="$self?table=$tableId&amp;orderBy=name">Name</a></th>
	    <th><a href="$self?table=$tableId&amp;orderBy=figkills">Fig Kills</a></th>
	    <th><a href="$self?table=$tableId&amp;orderBy=kills">Ship Kills</a></th>
	    <th><a href="$self?table=$tableId&amp;orderBy=lost">Ship Losses</a></th>
	    <th><a href="$self?table=$tableId&amp;orderBy=turns">Turns Used</a></th>
	    <th><a href="$self?table=$tableId">Score</a></th>
	</tr>

END;
}

$i = 0;
db($query);
while ($player = dbr()) {
	$player['cash'] = $i;
	$player['login_name'] = formatName($player['login_id'],
	 $player['login_name'], $player['clan_id'], $player['clan_sym'],
	 $player['clan_sym_color']);

	if (isset($table) && $table == 2) {
		if (!$player['last_attack_by']) {
			$player['last_attack_by'] = "<em>nobody</em>";
		}
		if ($player['last_attack'] == 1) {
			$player['last_attack'] = "<em>never</em>";
		} elseif ($player['last_attack']) {
			$player['last_attack'] = date( "M d - H:i",$player['last_attack']);
		}
		if ($player['joined_game']) {
			$player['joined_game'] = date( "M d - H:i",$player['joined_game']);
		}

		$text .= <<<END
	<tr>
	    <td>{$player['login_name']}</td>
		<td>{$player['joined_game']}</td>
		<td>{$player['last_attack_by']}</td>
		<td>{$player['last_attack']}</td>
		<td>{$player['score']}</td>
	</tr>

END;
	} else {
		$text .= <<<END
	<tr>
	    <td>$i</td>
		<td>{$player['login_name']}</td>
		<td>{$player['fighters_killed']}</td>
		<td>{$player['ships_killed']}</td>
		<td>{$player['ships_lost']}</td>
		<td>{$player['turns_run']}</td>
		<td>{$player['score']}</td>
	</tr>

END;
	}
}

$text .= '</table>';
print_page('Player Ranking',$text);

?>
