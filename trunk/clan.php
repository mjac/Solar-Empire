<?php

require_once('inc/user.inc.php');

$output = "";

deathCheck($user);


if (isset($join)) { // Join clan
	if ($user['clan_id']) {
		print_page('Join Clan','You are already a member of a clan.',"?clans=1");
	}
	$clanInfo = $db->query('SELECT *, COUNT(*) AS members FROM [game]_clans ' .
	 'AS c LEFT JOIN [game]_users AS u ON u.clan_id = c.clan_id WHERE ' .
	 'c.clan_id = %u GROUP BY c.clan_id', array($join));
	$clan = $db->fetchRow($clanInfo);

	if ($db->hasError($clanInfo) || $db->numRows($clanInfo) < 1) {
		print_page('Join Clan', 'Invalid clan.');
	}

	if ($clan['members'] >= $gameOpt['clan_member_limit'] && !IS_ADMIN) {
		print_page('Join Clan', 'That clan already has the maximum number of members allowed.',"?clans=1");
	} elseif($user['clan_id'] !== NULL){
		print_page('Join Clan', 'You are already a member of a clan.',"?clans=1");
	}

	$invites = $db->query('SELECT COUNT(*) FROM [game]_clan_invites WHERE ' .
	 'login_id = %u AND clan_id = %u', array($user['login_id'], $join));
	if (current($db->fetchRow($invites)) < 1) {
		print_page('Join Clan', 'You are not invited.');
	} else {
		$db->query('UPDATE [game]_users SET clan_id = %u WHERE ' .
		 'login_id = %u', array($join, $user['login_id']));
		$db->query('DELETE FROM [game]_clan_invites WHERE login_id = %u AND ' .
		 'clan_id = %u', array($user['login_id'], $join));

		checkPlayer($user['login_id']);

		msgSendSys($clan['leader_id'], "<b class=b1>$user[login_name]</b> has joined your clan.");

		insert_history($user['login_id'], "Joined $clan[clan_name] clan.");
	}
} elseif (isset($create) && $user['clan_id'] === NULL) { //Create a new clan
	$clanAmount = $db->query('SELECT COUNT(*) FROM [game]_clans');
	$result_max_clans = (int)current($db->fetchRow($clanAmount));

	if ($result_max_clans >= $gameOpt['max_clans'] && !IS_ADMIN) {
		$output .= "The clan limit has been reached.";
	} elseif (empty($name) || empty($symbol) || empty($sym_color)) {
		$colTable = "<table>\n\t<tr>";
		$i = 0;
		foreach ($msgColours as $name => $hex) {
			$thisColour = "\n\t\t<td><input type=\"radio\" name=\"sym_color\" value=\"$hex\"> <span style=\"color: #$hex;\">$name</span></td>";
			if ($i !== 0 && !($i % 4)) {
				$colTable .= "\n\t</tr>\n\t<tr>" . $thisColour . "";
			} else {
				$colTable .= $thisColour;
			}
			++$i;
		}
		$colTable .= "\n\t</tr>\n</table>";

	    $tempstr = <<<END
<h1>Create a clan</h1>
<form action="clan.php" method="post">
	<dl>
		<dt><label for="name">Name of the clan</label></dt>
		<dd><input type="text" name="name" id="name" class="text" /></dd>

		<dt><label for="symbol">Three letter ASCII symbol</label></dt>
		<dd><input type="text" name="symbol" id="symbol" class="text" size="3" maxlength="3" /></dd>

		<dt><label for="sym_color">Symbol colour</label></dt>
		<dd><input type="text" name="sym_color" size="15" class="text" />
		Enter your own (six character hexadecimal)</dd>
		<dd>$colTable</dd>

		<dt><input type="submit" name="create" value="Create" class="button" /></dt>
	</dl>
</form>

END;
		print_page('Choose symbol color', $tempstr, "?clans=1");

	} elseif (!(strlen($symbol) === 3 && valid_input($symbol))) {
		print_page('Create Clan', 'Your clan symbol may contain only 3 ASCII letters, numbers, and punctuation',"?clans=1");
	} elseif (!preg_match('/^[a-f0-9]{6}$/i', $sym_color)) {
		print_page('Create Clan', 'Invalid symbol colour!',"?clans=1");
	} elseif (!valid_spaced_name($name)) {
		print_page('Create Clan', 'Invalid name', "?clans=1");
	} else {
		$sExists = $db->query('SELECT COUNT(*) FROM [game]_clans WHERE ' .
		 'symbol = \'%s\'', array($db->escape($symbol)));

		if (current($db->fetchRow($sExists)) > 0) {
			print_page('Create Clan','That symbol is already in use.',"?clans=1");
		}

		$clan_id = newId('[game]_clans', 'clan_id');

		$db->query('INSERT INTO [game]_clans (clan_id, clan_name, ' .
		 'leader_id, symbol, sym_color) VALUES (%u, \'%s\', %u, \'%s\', %u)', 
		 array($clan_id, $db->escape($name), $user['login_id'], 
		 $db->escape($symbol), (int)hexdec($sym_color)));

		$db->query('UPDATE [game]_users SET clan_id = %u WHERE ' .
		 'login_id = %u', array($clan_id, $user['login_id']));

		post_news("$user[login_name] formed the $name clan ($symbol)");
		insert_history($user['login_id'], "Created the $name clan.");

		checkPlayer();
	}
}

#################
#Default clan page - if not in a clan.
################

if (isset($ranking) || ($user['clan_id'] === NULL && empty($clan_info))) { // Clan Ranking
	$cAmount = $db->query('SELECT COUNT(*) FROM [game]_clans');
	$clan_count = (int)current($db->fetchRow($cAmount));

	$orderByOpt = array(
		'name' => 'c.clan_name',
		'members' => 'members',
		'figkills' => 'fkilled',
		'figlosses' => 'flost',
		'shipkills' => 'skilled',
		'shiplosses' => 'slost',
		'turnsrun' => 'trun',
		'score' => 'score'
	);
	$orderOpt = array(
		'asc' => 'ASC',
		'desc' => 'DESC'
	);
	
	$orderBy = isset($_REQUEST['orderBy']) && in_array($_REQUEST['orderBy'],
	 array_keys($orderByOpt)) ? $orderByOpt[$_REQUEST['orderBy']] :
	 $orderByOpt['score'];

	$order = isset($_REQUEST['order']) && in_array($_REQUEST['order'],
	 array_keys($orderOpt)) ? $orderOpt[$_REQUEST['order']] :
	 $orderOpt['asc'];

	$oOrder = '&amp;order=' . ($orderOpt['asc'] === $order ? 'desc' : 'asc') .
	 (isset($ranking) ? '&amp;ranking=1' : '');

	#get details of each clan
	$clans = $db->query("select c.clan_id,c.clan_name,c.symbol,c.sym_color, count(u.login_id) as members, sum(u.fighters_killed) as fkilled, sum(u.fighters_lost) as flost, sum(u.ships_killed) as skilled, sum(u.ships_lost) as slost, sum(u.turns_run) as trun, sum(u.score) as score from [game]_clans c, [game]_users u where u.clan_id = c.clan_id GROUP by c.clan_id ORDER BY $orderBy $order");
	$amount = $db->numRows($clans);

	$output .= <<<END
<h1>Clan rankings</h1>
<p>$amount clan(s) out of a possible $gameOpt[max_clans] are active in this 
galaxy.</p>

END;

	if (($user['clan_id'] === NULL && $amount < $gameOpt['max_clans']) || IS_ADMIN) {
		$output .= "<p><a href=clan.php?create=1>Create a new clan</a></p>\n";
	} elseif ($amount >= $gameOpt['max_clans']) {
		$output .= "<p>You may not create a clan because the limit of " .
		 "$gameOpt[max_clans] has been reached.</p>\n";
	}

	if ($amount > 0) {
		$output .= <<<END
<table class="simple">
	<tr>
		<th><a href="clan.php?orderBy=score$oOrder">Ranking</a></th>
		<th><a href="clan.php?orderBy=score$oOrder">Score</a></th
		<th><a href="clan.php?orderBy=name$oOrder">Clan Name</a></th>
		<th><a href="clan.php?orderBy=members$oOrder">Members</a></th>
		<th><a href="clan.php?orderBy=figkills$oOrder">Fig. Kills</a></th>
		<th><a href="clan.php?orderBy=figlosses$oOrder">Fig. Losses</a></th>
		<th><a href="clan.php?orderBy=shipkills$oOrder">Ship Kills</a></th>
		<th><a href="clan.php?orderBy=shiplosses$oOrder">Ship Losses</a></th>
		<th><a href="clan.php?orderBy=turnsrun$oOrder">Turns Run</a></th>
		<th>Details</th>
	</tr>

END;

		for ($i = 1; $clan = $db->fetchRow($clans); ++$i) {
			$details = "<a href=\"clan.php?clan_info=1&amp;target=$clan[clan_id]\">View</a>";
			if(($user['clan_id'] === NULL && ($clan['members'] < $gameOpt['clan_member_limit'])) || IS_ADMIN) {
				$details .= " - <a href=clan.php?join=$clan[clan_id]>Join</a>";
			} elseif($clan['members'] >= $gameOpt['clan_member_limit']) {
				$details .= " - <em>Full</em>";
			}
			$name = clanName($clan['clan_name'], $clan['symbol'],
			 $clan['sym_color']);

			$output .= <<<END
	<tr>
		<td>$i</td>
		<td>$clan[score]</td>
		<td>$name</td>
		<td>$clan[members]</td>
		<td>$clan[fkilled]</td>
		<td>$clan[flost]</td>
		<td>$clan[skilled]</td>
		<td>$clan[slost]</td>
		<td>$clan[trun]</td>
		<td>$details</td>
	</tr>

END;
		}

		$output .= "</table>\n";
	}

	print_page("Clan Rankings", $output);
}


$cInfo = $db->query('SELECT * FROM [game]_clans WHERE clan_id = %u', 
 array($user['clan_id']));
if ($db->hasError($cInfo)) {
	print_page('Invalid clan', '<p>Invalid clan</p>');
}

$clan = $db->fetchRow($cInfo, ROW_ASSOC);

function clanInvite($invite)
{
	global $db, $user;

	if (!is_numeric($invite)) {
		return "<p>Invitation failed: invalid player.</p>\n";
	}

	$uInfo = $db->query('SELECT clan_id FROM [game]_users ' .
	 'WHERE login_id = %u', array($user['login_id']));
	if ($db->hasError($uInfo) || $db->numRows($uInfo) < 1) {
		break;
	}

	$clanId = current($db->fetchRow($uInfo));
	if ($clanId === NULL) {
		return "<p>Invitation failed: that player is already a " .
		 "clan member.</p>\n";
	}

	$invited = $db->query('SELECT COUNT(*) FROM [game]_clan_invites WHERE ' .
	 'clan_id = %u AND login_id = %u', array($user['clan_id'], $invite));
	if ($db->hasError($invited) || current($db->fetchRow($invited)) > 0) {
		return "<p>Invitation failed: player is already invited.</p>\n";
	}

	$db->query('INSERT INTO [game]_clan_invites (clan_id, login_id, invited) ' .
	 'VALUES (%u, %u, %u)', array($user['clan_id'], $invite, time()));

	return "<p>Player invited.</p>\n";
}


function clanUninvite($invite)
{
	global $db, $user;

	$result = $db->query('DELETE FROM [game]_clan_invites WHERE clan_id = %u ' .
	 'AND login_id = %u', array($user['clan_id'], $invite));

	return !$db->hasError($result) && $db->affectedRows($result) > 0;
}

if (isset($action)) {
	$action = strtolower(trim($action));
}

if (isset($action) && $user['login_id'] == $clan['leader_id']) {
	if ($action === 'invite' && isset($_REQUEST['player'])) {
		$output .= clanInvite($_REQUEST['player']);
	}

	if ($action === 'uninvite' && isset($_REQUEST['player'])) {
		$output .= clanUninvite($_REQUEST['player']) ?
		 "<p>Player has been removed from the invite list.</p>\n" : 
		 "<p>Removal failed: the player is probably not on the invite list.</p>\n";
	}
}


if (isset($leave)) { // Leave clan
	db("select leader_id,clan_name from [game]_clans where clan_id = $user[clan_id]");
	$clan = dbr(1);
	if($clan['leader_id'] == $user['login_id']) {
		$output .= "<p>The clan leader may not leave the clan: assign a new leader first.</p>";
	} else {
		$db->query('UPDATE [game]_users SET clan_id = NULL WHERE login_id = %u',
		 array($user['login_id']));

		msgSendSys($clan['leader_id'], '<em>' . esc($user['login_name']) .
		 '</em> has left your clan.');
	}

	insert_history($user['login_id'],"Left $clan[clan_name] clan.");

	header('Location: system.php');
	exit;
} elseif (isset($kick)) { // Kick a clan member
	db("select leader_id,clan_name from [game]_clans where clan_id = $user[clan_id]");
	$clan = dbr(1);
	db2("select clan_id,login_name from [game]_users where login_id='$kick'");
	$kick_clan = dbr2();
	if($clan['leader_id'] != $user['login_id'] && $user['login_id'] !=1) {
		$output .= "You are not the leader of this clan.<p>";
	} elseif($user['clan_id'] === NULL) {
		$output .= "You are not in a clan as such.<p>";
	} elseif($kick_clan['clan_id'] != $user['clan_id']) {
		$output .= "You can only kick members of your own clan.<p>";
	} elseif($kick == $clan['leader_id']) {
		$output .= "You may not kick the clan leader.<p>";
	} elseif(!isset($sure)) {
		get_var('Kick Clan Member','clan.php','Are you sure you want to kick this clan member out?','sure','yes');
	} else {
		$db->query('UPDATE [game]_users SET clan_id = NULL WHERE ' .
		 'login_id = %u', array($kick));
		$output .= "User <b class=b1>$kick_clan[login_name]</b> kicked out of the clan.<p>";
		insert_history($user['login_id'],"Thrown out of $clan[clan_name] clan.");
	}
} elseif (isset($disband)) { // Disband clan
	if (($clan['leader_id'] != $user['login_id']) && !IS_ADMIN) {
		$output .= "<p>You are not the leader of this clan.</p>";
	} elseif ($user['clan_id'] === NULL) {
		$output .= "<p>You are not in a clan.</p>";
	} elseif (!isset($sure)) {
		get_var('Disband Clan', 'clan.php', 'Are you sure you want to disband this clan?', 'sure', 'yes');
	} else {
		post_news("$user[login_name] disbanded $clan[clan_name] ($clan[symbol])");

		$db->query('UPDATE [game]_users SET clan_id = NULL WHERE clan_id = %u',
		 array($user['clan_id']));
		$db->query('DELETE FROM [game]_clans WHERE clan_id = %u',
		 array($user['clan_id']));
		$db->query('DELETE FROM [game]_clan_invites WHERE clan_id = %u',
		 array($user['clan_id']));
		$db->query('DELETE FROM [game]_messages WHERE clan_id = %u',
		 array($user['clan_id']));

		checkPlayer($user['login_id']);
		insert_history($user['login_id'], "Disbanded $clan[clan_name] clan.");
		print_page('Disband clan', 'You have disbanded your clan.');
	}
} elseif (isset($lead_change)) { // Assign new leader
	db("select leader_id from [game]_clans where clan_id = $user[clan_id]");
	$clan = dbr(1);
	if($user['clan_id'] === NULL) {
		$output .= "You are not in a clan as such.<p>";
	} elseif(($clan['leader_id'] != $user['login_id']) && !IS_ADMIN) {
		$output .= "You are not the leader of this clan.<p>";
	} elseif(!isset($leader_id)) {
		db2("select login_id, login_name from [game]_users where clan_id = '$user[clan_id]' AND login_id != '$clan[leader_id]'");
		$member_name = dbr2(1);
		if($member_name) {
			$output .= "<form action=clan.php method=POST>";
			$output .= "Please choose another clan member to be the leader:<p>";
			foreach ($_REQUEST as $var => $value) {
				$output .= "<input type=\"hidden\" name=\"$var\" value=\"$value\" />";
			}
			$output .= "<select name=leader_id>";
			while ($member_name) {
				$output .= "<option value=$member_name[login_id]>$member_name[login_name]</option>";
				$member_name = dbr2(1);
			}
			$output .= "</select>";
			//$output .= "<input type=hidden name=sure value='no'>";
			$output .= ' <input type=submit value=Submit></form>';
			print_page('Choose new clan leader',$output,"?clans=1");
		} else {
			print_page('Error',"No-one in your clan can become clan leader. That means u're stuck as clan leader.","?clans=1");
		}
	} elseif(!isset($sure) && !IS_ADMIN) {
		get_var('Change Clan Leader','clan.php','Are you sure you want to relinquish leadership of this clan?','sure','yes');
	} else {
		$db->query("update [game]_clans set leader_id = $leader_id where clan_id = $user[clan_id]");
		$clan['leader_id'] = $leader_id;
		$output .= "Clan leader changed<p>";
	}
}




if (isset($clan_info) && isset($target)) { // show clan info
	$clanLinks = "<p><a href=\"clan.php\">Clan Control</a></p>\n";

	if (IS_ADMIN || $user['clan_id'] == $target) { #admin can see all, as can clan members.
		$full = 1;
	} else {
		$full = 0;
	}

	#list some statistics about the clan, as user is a member (or admin).
	if($full == 1){
		#planet details
		db("select sum(p.cash) as cash, sum(p.fighters) as pfigs, count(p.planet_id) as planets, count(p.launch_pad) as lpads, count(p.shield_gen) as sgens, sum(p.shield_charge) as scharge, sum(p.colon) as colon FROM [game]_planets AS p LEFT JOIN [game]_users AS u ON p.login_id = u.login_id WHERE u.clan_id = '$target'");
		$res1 = dbr(1);


		#planet percentages
		db("select sum(cash) as cash,sum(fighters) as pfigs, count(planet_id) as planets from [game]_planets where login_id > '5'");
		$maths1 = dbr(1);


		#ship detals
		db("SELECT SUM(s.fighters) AS sfigs, SUM(s.max_fighters) AS max_figs, COUNT(*) AS ships, SUM(s.cargo_bays) AS cargo from [game]_ships AS s LEFT JOIN [game]_users AS u ON s.login_id = u.login_id WHERE u.clan_id = '$target'");
		$res2 = dbr(1);

		#used for ship percentages
		db("select sum(fighters) as sfigs, count(ship_id) as ships from [game]_ships where login_id > '5'");
		$maths2 = dbr(1);


		#get user detals.
		db("select count(login_id) as members, sum(cash) as cash, sum(genesis) as gen, sum(fighters_killed) as fkilled, sum(fighters_lost) as flost, sum(bounty) as bounty, sum(score) as score, sum(alpha) as alpha, sum(gamma) as gamma, sum(delta) as delta, sum(ships_killed) as skilled, sum(ships_lost) as slost, sum(turns_run) as trun, sum(turns) as turns, sum(ships_killed_points) as spkilled, sum(ships_lost_points) as splost from [game]_users where clan_id = '$target'");
		$res3 = dbr(1);

		#used to calculate percentages
		db("select count(login_id) as members, sum(cash) as cash, sum(fighters_killed) as fkilled, sum(fighters_lost) as flost, sum(bounty) as bounty, sum(score) as score, sum(ships_killed) as skilled, sum(ships_lost) as slost, sum(ships_killed_points) as spkilled, sum(ships_lost_points) as splost, sum(turns_run) as trun, sum(turns) as turns from [game]_users where login_id != $gameInfo[admin]");
		$maths3 = dbr(1);

		$output .= $clanLinks; #link to clan control
	} else {#only partial listing given, so only get small amounts of data.
		db("select count(login_id) as members, sum(fighters_killed) as fkilled, sum(fighters_lost) as flost, sum(ships_killed) as skilled, sum(ships_lost) as slost, sum(turns_run) as trun from [game]_users where clan_id = '$target'");
		$res3 = dbr(1);

		#for percentages
		db("select count(login_id) as members, sum(fighters_killed) as fkilled, sum(fighters_lost) as flost, sum(ships_killed) as skilled, sum(ships_lost) as slost, sum(turns_run) as trun from [game]_users where login_id > '5'");
		$maths3 = dbr(1);
	}

	$cInfo = $db->query('SELECT clan_name, leader_id, symbol, sym_color ' .
	 'FROM [game]_clans WHERE clan_id = %u', array($target));
	$cd = $db->fetchRow($cInfo);

	if (!$cd) die("Clan is missing!");

	$name = clanName($cd['clan_name'], $cd['symbol'], $cd['sym_color']);
	$output .= <<<END
<h1>$name clan statistics</h1>
<table>
	<tr>
	    <th>Member Amount</th>
	    <td>$res3[members]</td>
	</tr>

END;

	if($full == 0){
		$output .= quick_row("Fighters Killed",calc_perc($res3['fkilled'],$maths3['fkilled']));
		$output .= quick_row("Fighters Lost",calc_perc($res3['flost'],$maths3['flost']));
		$output .= quick_row("Ships Killed",calc_perc($res3['skilled'],$maths3['skilled']));
		$output .= quick_row("Ships Lost",calc_perc($res3['slost'],$maths3['slost']));
		$output .= quick_row("Turns Run",calc_perc($res3['trun'],$maths3['trun']));
		$output .= "</table><br /><br />Below is a listing of the members of the <b class=b1>$cd[clan_name]</b1> clan. ".make_table(array("User","Turns Run","Fighters Killed","Fighters Lost","Ships Killed","Ships Lost"));

		db("select login_id,turns_run, fighters_killed,fighters_lost, ships_killed, ships_lost from [game]_users where clan_id = '$target'");
		while($clan_members = dbr(1)){
			$clan_members['login_id'] = print_name($clan_members);
			$clan_members['fighters_killed'] = calc_perc($clan_members['fighters_killed'],$maths3['fkilled']);
			$clan_members['fighters_lost'] = calc_perc($clan_members['fighters_lost'],$maths3['flost']);
			$clan_members['ships_killed'] = calc_perc($clan_members['ships_killed'],$maths3['skilled']);
			$clan_members['ships_lost'] = calc_perc($clan_members['ships_lost'],$maths3['slost']);
			$clan_members['turns_run'] = calc_perc($clan_members['turns_run'],$maths3['trun']);
			$output .= make_row($clan_members);
		}

	} else {
		$output .= quick_row("Cash",calc_perc($res3['cash'] + $res1['cash'],$maths3['cash'] + $maths1['cash']));
		$output .= quick_row("Turns",calc_perc($res3['turns'],$maths3['turns']));
		$output .= quick_row("Turns Run",calc_perc($res3['trun'],$maths3['trun']));
		$t_figs = $res1['pfigs'] + $res2['sfigs'];
		$t_fcap = $maths1['pfigs'] + $maths2['sfigs'];
		$output .= quick_row("Total Fighters",calc_perc($t_figs,$t_fcap));

		$output .= quick_row("Ships Killed",calc_perc($res3['skilled'],$maths3['skilled']));
		$output .= quick_row("Ships Lost",calc_perc($res3['slost'],$maths3['slost']));
		$output .= quick_row("Ship Points Killed",calc_perc($res3['spkilled'],$maths3['spkilled']));
		$output .= quick_row("Ship Points Lost",calc_perc($res3['splost'],$maths3['splost']));
		$output .= quick_row("Fighters Killed",calc_perc($res3['fkilled'],$maths3['fkilled']));
		$output .= quick_row("Fighters Lost",calc_perc($res3['flost'],$maths3['flost']));
		$output .= quick_row("Score",calc_perc($res3['score'],$maths3['score']));

		$output .= quick_row("Bounty",calc_perc($res3['bounty'],$maths3['bounty']));

		$output .= quick_row("Planets",calc_perc($res1['planets'],$maths1['planets']));
		$output .= quick_row("Planetary Fighters",calc_perc($res1['pfigs'],$maths1['pfigs']));
		$output .= quick_row("Launch Pads",$res1['lpads']);
		$output .= quick_row("Shield Generators",$res1['sgens']);
		$output .= quick_row("Shield Charges",$res1['scharge']);
		$output .= quick_row("Colonists",$res1['colon']);

		$output .= quick_row("Ships",calc_perc($res2['ships'],$maths2['ships']));
		$output .= quick_row("Ship Fighters",calc_perc($res2['sfigs'],$maths2['sfigs']));
		$output .= quick_row("Fleet Fighter Capacity",$res2['max_figs']." Fighters");
		$output .= quick_row("Fleet Cargo Capacity",$res2['cargo']." Units");

		$output .= quick_row("Genesis Devices",$res3['gen']);
		$output .= quick_row("Alpha Bombs",$res3['alpha']);
		$output .= quick_row("Gamma Bombs",$res3['gamma']);
		$output .= quick_row("Delta Bombs",$res3['delta']);
	}

	$output .= "</table>";

	print_page("Clan Info",$output . $clanLinks);
} else {
	$cInfo = $db->query('SELECT c.*, COUNT(*) AS members FROM ' .
	 '[game]_users AS u LEFT JOIN [game]_clans AS c ON ' .
	 'u.clan_id = c.clan_id WHERE u.clan_id = %u GROUP BY ' .
	 'u.clan_id', array($user['clan_id']));
	$clan = $db->fetchRow($cInfo);


	#change a ship's fleet
	if(isset($fleet_type) && $user['login_id'] == $clan['leader_id']){
		if($join_fleet_id_2 != 0){
			$join_fleet_id = $join_fleet_id_2;
		}

		$output .= "<br />".change_fleet_num($join_fleet_id,1,$do_ship,"ship_id")."<p><br />";
	}




	$output .= "<h1>" . clanName($clan['clan_name'], $clan['symbol'],
	 $clan['sym_color']) . " clan overview</h1>\n<h2>Members</h2>\n";

	$output .= make_table(array("Member", "Turns", "Cash", "Kills", "Status"));
	db("select login_name,turns,cash,ships_killed,last_request,login_id from [game]_users where clan_id = $user[clan_id] order by login_name,ships_killed");
	while ($clan_member = dbr(1)) {
	    $name = print_name($clan_member);
		if ($clan['leader_id'] == $clan_member['login_id']) {
			$name .= ' (L)';
		}

        $status = $clan_member['last_request'] > (time() - 300) ? 
		 'Online' : 'Offline';

		$options = array();

		if($clan_member['login_id'] != $user['login_id']){
			$options[] = "<a href=\"message.php?target=$clan_member[login_id]\">message</a>";
		}

		if ($user['login_id'] == $clan['leader_id'] || IS_ADMIN &&
			 $clan_member['login_id'] != $clan['leader_id']) {
			$options[] = "<a href=\"clan.php?kick=$clan_member[login_id]\">kick</a>";
		}

		if (!empty($options)) {
			$name .= ' - ' . implode(' - ', $options);
		}

		$output .= make_row(array($name, $clan_member['turns'], $clan_member['cash'], $clan_member['ships_killed'], $status));
	}
	$output .= "</table>\n<h2>Invited players</h2>\n";

	$names = $db->query('SELECT u.login_id, u.login_name, c.clan_id, ' .
	 'c.clan_name, c.symbol, c.sym_color FROM [game]_clan_invites AS i LEFT ' .
	 'JOIN [game]_users AS u ON i.login_id = u.login_id LEFT JOIN ' .
	 '[game]_clans AS c ON u.clan_id = c.clan_id WHERE i.clan_id = %u ORDER ' .
	 'BY u.login_name ASC', array($user['clan_id']));
	if ($db->numRows($names) > 0) {
		$output .= "<ul>\n";
		while ($info = $db->fetchRow($names, ROW_NUMERIC)) {
			$output .= "\t<li>" . formatName($info[0], $info[1], $info[2],
			 $info[3], $info[4], $info[5]) . 
			 ($user['login_id'] == $clan['leader_id'] ? 
			 " - <a href=\"$self?action=uninvite&amp;player=$info[0]\">" .
			 "remove</a>" : "") . "</li>\n";
		}
		$output .= "</ul>\n";
	} else {
		$output .= "<p>There are no invitations.</p>\n";
	}

	if ($user['login_id'] == $clan['leader_id']) {
		$output .= <<<END
<h3>Invite a player</h3>
<form action="$self" method="post">
	<p><select name="player">

END;
		$players = $db->query('SELECT login_id, login_name FROM [game]_users ' .
		 'WHERE clan_id IS NULL OR clan_id != %u', array($user['clan_id']));

		if (!$db->hasError($players)) {
			while ($pInfo = $db->fetchRow($players, ROW_NUMERIC)) {
				$output .= "\t\t<option value=\"$pInfo[0]\">" . 
				 esc($pInfo[1]) . "</option>\n";
			}
		}
$output .= <<<END
	</select>
	<input type="submit" name="action" value="Invite" class="button" /></p>
</form>
END;
	}

	$output .= <<<END
<h2>Clan options</h2>
<ul>
	<li><a href="clan.php?ranking=1">Rankings</a></li>
	<li><a href="clan.php?clan_info=1&amp;target=$user[clan_id]"> 
	Information</a></li>

END;
	if ($clan['members'] > 1) {
		$output .= "\t<li><a href=\"message.php?target=-2&amp;clan_id=$user[clan_id]\">Message clan</a></li>\n";
	}
	if ($user['login_id'] != $clan['leader_id']) {
		$output .= "\t<li><a href=\"clan.php?leave=1\">Leave clan</a></li>\n";
	}
	$output .= <<<END
</ul>

END;

	if ($user['login_id'] == $clan['leader_id'] || IS_ADMIN) {
		$output .= <<<END
<h2>Leader options</h2>
<ul>

END;
		if ($clan['members'] > 1) {
			$output .= "\t<li><a href=\"clan.php?lead_change=1\">Change Clan Leader</a></li>\n";
		}
		if($user['login_id'] == 1 && $user['login_id'] != $clan['leader_id']) {
			$output .= "\t<li><a href=\"clan.php?leave=1\">Leave Clan</a></li>\n";
		}
		$output .= <<<END
	<li><a href="clan.php?disband=1">Disband clan</a></li>
</ul>

END;
	}


	$output .= "<h2>Ships</h2>\n";

	/*************
	* List Clan Ships
	**************/

	#show all ships, not just other clan members.
	if ($userOpt['show_clan_ships'] || isset($show_clan_ships)) {
		#determine if users want to see the abbreviation or not of ship types..
		if($userOpt['show_abbr_ship_class'] == 1){ #abbriviate class names
			$class_temp_var = "t.abbr AS class_name_abbr";
		} else {
			$class_temp_var = "t.name AS class_name";
		}

		db("select login_name, ship_name, $class_temp_var, s.location, s.fighters, s.shields, s.ship_id FROM [game]_ships AS s LEFT JOIN [game]_users AS u ON s.login_id = u.login_id LEFT JOIN [game]_ship_types AS t ON s.type_id = t.type_id WHERE u.clan_id = $user[clan_id] order by u.login_name DESC");

		$clan_ship = dbr(1);
		$clan_page_tab = array("Ship Owner", "Ship Name", "Ship Class", "Location", "Fighters", "Shields");

		$output .= make_table($clan_page_tab);
		while($clan_ship) {
			unset($clan_ship['ship_id']);
			$clan_ship['login_name'] = "<b class=b1>$clan_ship[login_name]</b>";
			$output .= make_row($clan_ship);
			$clan_ship = dbr(1);
		}
		$output .= "</table><p>";
	/*************
	* Summary of Clan ships
	**************/
	} else {
		db("select count(ship_id) as total, sum(fighters) as fighters, login_name from [game]_ships where clan_id = $user[clan_id] group by login_id order by login_name, fighters desc, ship_name desc");
		$clan_ship = dbr(1);

		$output .= "<br /><br /><a href=clan.php?show_clan_ships=1>Show All Clan Ships</a><p>";

		while($clan_ship){
			$output .= "<b class=b1>$clan_ship[login_name]</b> has <b>$clan_ship[total]</b> Ship(s) w/ <b>$clan_ship[fighters]</b> Total Fighters<br />";
			$clan_ship = dbr(1);
		}
		$output .= "<br /><br />";
	}



	#little code to allow users to sort planets asc, desc in a number of criteria
	if (isset($sorted) && $sorted == 1) {
		$going = "asc";
		$sorted = 2;
	} else {
		$going = "desc";
		$sorted = 1;
	}
	if(isset($sort_planets)){
		db("SELECT u.login_name, planet_name, p.location, fighters, colon, p.cash, metal, fuel, elect, organ FROM [game]_planets AS p LEFT JOIN [game]_users AS u ON p.login_id = u.login_id where clan_id = $user[clan_id] AND p.location != 1 order by '$sort_planets' $going");
	} else {
		db("SELECT u.login_name, planet_name, p.location, fighters, colon, p.cash, metal, fuel, elect, organ FROM [game]_planets AS p LEFT JOIN [game]_users AS u ON p.login_id = u.login_id where clan_id = $user[clan_id] AND p.location != 1 order by u.login_name asc, fighters desc, planet_name asc");
	}

	$clan_planet = dbr(1);
	if($clan_planet) {
		$output .= "<h2>Planets</h2>\n";
		$output .= make_table(array("<a href=clan.php?sort_planets=login_name&sorted=$sorted>Planet Owner</a>","<a href=clan.php?sort_planets=planet_name&sorted=$sorted>Planet Name</a>","<a href=clan.php?sort_planets=location&sorted=$sorted>Location</a>","<a href=clan.php?sort_planets=fighters&sorted=$sorted>Fighters</a>","<a href=clan.php?sort_planets=colon&sorted=$sorted>Colonists</a>","<a href=clan.php?sort_planets=cash&sorted=$sorted>Cash</a>","<a href=clan.php?sort_planets=metal&sorted=$sorted>Metal</a>","<a href=clan.php?sort_planets=fuel&sorted=$sorted>Fuel</a>","<a href=clan.php?sort_planets=elect&sorted=$sorted>Electronics</a>","<a href=clan.php?sort_planets=organ&sorted=$sorted>Organics</a>"));
		while($clan_planet) {
			$clan_planet['login_name'] = "<b class=b1>$clan_planet[login_name]</b>";
			$output .= make_row($clan_planet);
			$clan_planet = dbr(1);
		}
		$output .= "</table><br />";
	}
}

print_page('Clan', $output, '?clans=1');

?>
