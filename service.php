<?php

require_once('inc/common.inc.php');
require_once('inc/db.inc.php');

function taskAmount($last, $frequency)
{
	return floor((time() - $last) / $frequency);
}

function dbTaskAmount($name)
{
	global $db, $gameOpt, $gameInfo;

	$do = taskAmount($gameInfo['processed_' . $name],
	 $gameOpt['process_' . $name]);

	if ($do > 0) {
		$db->query('UPDATE se_games SET processed_%s = %u WHERE ' .
		 'db_name = \'[game]\'', array($db->escape($name),
		 $gameInfo['processed_' . $name] + $do *
		 $gameOpt['process_' . $name]));
	}

	return $do;
}

function processSystems()
{
	global $db, $gameOpt, $gameInfo;

	$db->query('UPDATE [game]_stars SET metal = metal + (RAND() * ' .
	 ($gameOpt['rr_metal_chance_max'] - $gameOpt['rr_metal_chance_min']) .
	 ') + ' . $gameOpt['rr_metal_chance_min'] . ' WHERE (RAND() * 100) < ' .
	 $gameOpt['rr_metal_chance'] . ' && star_id != 1');

	$db->query('UPDATE [game]_stars SET fuel = fuel + (RAND() * ' .
	 ($gameOpt['rr_fuel_chance_max'] - $gameOpt['rr_fuel_chance_min']) .
	 ') + ' . $gameOpt['rr_fuel_chance_min'] . ' WHERE (RAND() * 100) < ' .
	 $gameOpt['rr_fuel_chance'] . ' && star_id != 1');
}

function mineMaterial($material)
{
	global $db;

	$sys = $db->query('SELECT star_id, SUM(mining_rate) AS rate, ' .
	 'COUNT(v.location) AS amount, s.%s FROM [game]_stars AS s ' .
	 'LEFT JOIN [game]_ships AS v ON s.star_id = v.location WHERE ' .
	 'task = \'mine\' && mining_mode = \'%s\' GROUP BY v.location',
	 array($material, $material));

	while ($s = $db->fetchRow($sys)) {
		if ($s['rate'] == 0 || $s['amount'] == 0 || $s[$material] == 0) {
			continue;
		}

		$max = $s[$material] > $s['rate'] ? $s['rate'] : $s[$material];

		$factor = $max / $s['rate'];

		$db->query('UPDATE [game]_stars SET %s = %s - %u WHERE ' .
		 'star_id = %u', array($material, $material, $max, $s['star_id']));

		$db->query('UPDATE [game]_ships SET %s = %s + ' .
		 'FLOOR(mining_rate * %f) WHERE task = \'mine\' && ' .
		 'mining_mode = \'%s\' && location = %u',
		 array($material, $material, $factor, $material, $s['star_id']));

		$db->query('UPDATE [game]_ships SET %s = cargo_bays - ' .
		 'metal - fuel - elect - colon - organ + %s WHERE ' .
		 'task = \'mine\' && mining_mode = \'%s\' && location = %u && ' .
		 'cargo_bays < (metal + fuel + elect + colon + organ)',
		 array($material, $material, $material, $s['star_id']));
	}
}

function processShips()
{
	global $db, $gameOpt;

	// SHIELDS
	$db->query('UPDATE [game]_ships SET shields = shields + ' .
	 'FLOOR(max_shields * %u / 100)', $gameOpt['increase_shields']);
	$db->query('UPDATE [game]_ships SET shields = max_shields ' .
	 'WHERE shields > max_shields');

	mineMaterial('metal');
	mineMaterial('fuel');

	// Fuel and metal cannot be less than 0
	$db->query('UPDATE [game]_stars SET fuel = 0 WHERE fuel < 0');
	$db->query('UPDATE [game]_stars SET metal = 0 WHERE metal < 0');
}

function processPlanets()
{
	global $db, $gameOpt;

    $db->query('UPDATE [game]_planets SET shield_charge = shield_charge + ' .
	 '%u * shield_gen WHERE shield_gen > 0', array($gameOpt['increase_shields'] * 10));
	$db->query('UPDATE [game]_planets SET shield_charge = shield_gen * 1000 ' .
	 'WHERE shield_charge > shield_gen * 1000');

	// PLANET BUILDS
	$planets = $db->query('SELECT planet_id, planet_name, p.login_id,  ' .
	 'tax_rate, fuel, metal, elect, colon, alloc_fight, alloc_elect, ' .
	 'alloc_organ, u.planet_report FROM [game]_planets AS p LEFT JOIN ' .
	 '[game]_user_options AS u ON u.login_id = p.login_id');

	while ($p = $db->fetchRow($planets, ROW_ASSOC)) {
		// For the user report
		$reportStr = "<p><strong>Manufacturing report for $p[planet_name]" .
		 "</strong></p>\n";

		// Fighters
		$fighterMax = floor($p['alloc_fight'] / 100);

		$resourceUsed = $p['fuel'] > $p['metal'] ? $p['metal'] : $p['fuel'];
		$resourceUsed = $resourceUsed > $p['elect'] ? $p['elect'] : $resourceUsed;
		$resourceUsed = $resourceUsed > $fighterMax ? $fighterMax : $resourceUsed;

		$figsProduced = floor($resourceUsed / 10 * $gameOpt['planet_fighters']);

		if ($figsProduced > 0) {
			$db->query('UPDATE [game]_planets SET fighters = ' .
			 'fighters + %u, fuel = fuel - %u, metal = metal - %u, ' .
			 'elect = elect - %u WHERE planet_id = %u',
			 array($figsProduced, $resourceUsed, $resourceUsed, $resourceUsed, $id));

			$elect -= $resourceUsed;
			$fuel  -= $resourceUsed;
			$metal -= $resourceUsed;

			$reportStr .= "<p>Fuel used: <b>$resourceUsed</b><br />\n" .
			 "Metal used: <b>$resourceUsed</b><br />\nElectronics used: " .
			 "<b>$resourceUsed</b><br />\n<em>$p[alloc_fight] colonists</em> " .
			 "produced <strong>$figsProduced fighters</strong>.</p>\n";
		}

		// Electronics
		$electBudget = floor($p['alloc_elect'] / 50);
		$electBudget = $p['fuel'] < $electBudget ? $p['fuel'] : $electBudget;
		$electBudget = $p['metal'] < $electBudget ? $p['metal'] : $electBudget;

		$eMade = floor($electBudget / 10 * $gameOpt['planet_elect']);

		if ($made > 0) {
			$db->query("UPDATE [game]_planets SET elect = elect + $eMade, " .
			 "fuel = fuel - $electBudget, metal = metal - $electBudget " .
			 "WHERE planet_id = $id");

			$reportStr .= "<p>Fuel used: <b>$electBudget</b><br />\n" .
			 "Metal used: <b>$electBudget</b><br />\n" .
			 "<em>$p[alloc_elect] colonists</em> produced <strong>$eMade " .
			 "electronics</strong>.</p>\n";
		}

		// Organics production
		$organicProduce = floor($p['alloc_organ'] / $gameOpt['planet_organ']);
		if ($organicProduce > 0) {
			$db->query("UPDATE [game]_planets SET organ = organ + " .
			 "$organicProduce WHERE planet_id = $id");

			$reportStr .= "<p><em>$p[alloc_organ] colonists</em> produced " .
			 "<strong>$organicProduce organics</strong>.</p>\n";
		}


		#Confirm if anything happens to colonists.
		$boredCols = $p['colon'] - ($p['alloc_fight'] + $p['alloc_elect'] + $p['alloc_organ']);
		$taxed = floor($boredCols * $p['tax_rate'] / 100);
		if ($taxed > 0) {
			$reportStr .= "<p><em>$boredCols colonists</em> taxed for " .
			 "<strong>$taxed</strong> credits ($p[tax_rate]%).</p>\n";
		}

		$perc = 0.3 - 0.03 * $p['tax_rate'];
		$percStr = floor($perc * 100) . '%';
		$newPop = floor($boredCols * $perc);
		if ($perc != 0) {
			$reportStr .= "<p><em>population</em> increased by $newPop ($percStr).</p>\n";
		}

		/* Send report message */
		if ($p['planet_report'] >= 1) {
			$newId = newId('[game]_messages', 'message_id');
			$db->query('INSERT INTO [game]_messages (message_id, timestamp, ' .
			 'sender_name, sender_id, login_id, text) VALUES (%u, %u, ' .
			 '\'The Universe\', %u, %u, \'%s\')', array($newId, time(),
			 $p['login_id'], $p['login_id'], $db->escape($reportStr)));
		}
	}

	// Process planet taxes!
	$db->query('UPDATE [game]_planets SET cash = cash + FLOOR((colon - ' .
	 '(alloc_fight + alloc_elect + alloc_organ)) * tax_rate / 100)');

	$db->query('UPDATE [game]_planets SET colon = colon + FLOOR((colon - ' .
	 '(alloc_fight + alloc_elect + alloc_organ)) * (0.3 - tax_rate * 0.03))');

	$db->query('UPDATE [game]_planets SET alloc_fight = 0, alloc_elect = 0, ' .
	 'alloc_organ = 0 WHERE (alloc_fight + alloc_elect + alloc_organ) > colon');
}

function processGovernment()
{
	global $db, $gameOpt;

	// AUCTION HOUSE
	$db->query('DELETE FROM [game]_bilkos WHERE timestamp <= %u AND ' .
	 'bidder_id = 0 AND active = 1', array(time() - 172800));
	$lots = $db->query('SELECT bidder_id, item_name, item_id FROM ' .
	 '[game]_bilkos WHERE timestamp <= %u AND active = 1 AND bidder_id > 0', 
	 array(time() - 86400));
	while($lot = $db->fetchRow($lots)){
		$newId = newId('[game]_messages', 'message_id');
		$db->query('INSERT INTO [game]_messages (message_id, timestamp, ' .
		 'sender_name, sender_id, login_id, text) VALUES (%u, \'Bilkos\', ' .
		 '%u, %u, \'%s\')', array($newId, time(), $lot['bidder_id'],
		 $lot['bidder_id'], "You have successfully won lot #<b>$lot[item_id]</b> (<b class=b1>$lot[item_name]</b>). <p>You should come to the Auction House in <b class=b1>Sol</b> to collect your goods."));

		$db->query('UPDATE [game]_bilkos SET active = 0 WHERE item_id = %u', 
		 array($lot['item_id']));
	}

	if (mt_rand(0, 3)) {
		$turnip = mt_rand(0, 5);
		if($turnip == 5){ #planetary.
			$i_type = 5;
			if (mt_rand(0, 1)) {
				$i_code = mt_rand(4, 9);
				$i_name = "Shield Gen Lvl <b>$i_code</b>";
				$i_price = $i_code * 4000;
				$i_descr = "A level <b>$i_code</b> Shield Generater for a planet (Normal lvl is 3). <br />Increases Shield Capacity, and Shield Generation Rate. Can be used as an upgrade, or a new Generator.";
			} else {
				$i_code = "MLPad";
				$i_name = "Missile Launch Pad";
				$i_price = 100000;
				$i_descr = "Missile Launch Pad. Used once. No build time necassary, just install and go.";
			}
		} elseif ($turnip >= 3) {
			$i_type = 4;
			$i_code = mt_rand(10, 80);
			$i_name = "Turns <b>$i_code</b>";
			$i_price = $i_code * 110;
			$i_descr = "<b>$i_code</b> turns that can be used for whatever you want.";
		} elseif ($turnip == 2) {
			#Put stuff into DB
			$ship = $db->query('SELECT type_id, name, cost, max_shields, fighters, max_fighters, upgrades, config, description FROM [game]_ship_types WHERE type_id > 2 && auction = 1 ORDER BY RAND() LIMIT 1');
			$s = $db->fetchRow($ship);

			$i_type = 1;
			$i_code = 'ship' . $s['type_id'];
			$i_name = $s['name'];
			$i_price = $s['cost'];
			$i_descr = "<b class=b1>Specs:</b> $s[max_shields] Shield Capacity; $s[fighters]/$s[max_fighters] Fighters; $s[upgrades] Upgrade Pods; Config: $s[config].<p>$s[description]";
		} elseif ($turnip == 1 && $gameOpt['bomb_level_auction'] >= 1) {
			$i_type = 2;
			if (mt_rand(0, 3) && $gameOpt['bomb_level_auction'] >= 1) {
				$i_code = "warpack";
				$i_name = "WarPack";
				$i_price = $gameOpt['bomb_cost'] * 4;
				$i_descr = "A collection of 2 Alpha bombs and 4 Gamma Bombs, all in one package.";
			} elseif ($gameOpt['bomb_level_auction'] >= 2) {
	     		$i_code = "deltabomb";
				$i_name = "Delta Bomb";
				$i_price = 16 * $gameOpt['bomb_cost'];
				$i_descr = "One Bomb that will nullify all shields on all ships in the system AND then do <b>5000</b> damage to each of the ships!.<br /><br />Note: Player may only own one Delta Bomb at a time!";
			}
		} else { # upgrades
			$i_type = 3;
			$which = mt_rand(0, 10);
			if ($which > 9) {
				$i_code = "fig1500";
				$i_name = "1500 Fighter Bays";
				$i_price = 50000;
				$i_descr = "Capable of fitting 1500 fighters into one upgrade pod this is a must for the war-hungry.";
			} elseif ($which > 6) {
				$i_code = "attack_pack";
				$i_name = "Attack Pack";
				$i_price = 20000;
				$i_descr = "Increases a ships Shield capacity by 200 and fighter capacity by 700, all with one upgrade.";
			} elseif ($which > 3) {
				$i_code = "fig500";
				$i_name = "500 Fighter Bays";
				$i_price = 10000;
				$i_descr = "This Nifty little upgrade allows you to squeeze 500 fighters into one upgrade pod.";
			} elseif ($which > 0) {
				$i_code = "upbs";
				$i_name = "Battleship Conversion";
				$i_price = 20000;
				$i_descr = "Enables a ship to do more damage when attacking, and increases shields per hour by <b>50%</b>.<br />(already installed on normal battleships).";
			} else {
				$i_code = "up2";
				$i_name = "Terra Maelstrom Upgrade";
				$i_price = 1000000;
				$i_descr = "The only Upgrade for the Brobdingnagian. (Can only be used on Brobdingnagians) Rare, but extremely potent, this replaces the Quark Disrupter with a weapon that is capable of crippling planets.<br />Get it while its available.";
			}
		}

		$newId = newId('[game]_bilkos', 'item_id');
		$db->query('INSERT INTO [game]_bilkos (item_id, timestamp, ' .
		 'item_type, item_code, item_name, going_price, descr, active) ' .
		 'VALUES (%u, %u, \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', 1)',
		 array($newId, time(), $db->escape($i_type), $db->escape($i_code),
		 $db->escape($i_name), $db->escape($i_price), $db->escape($i_descr)));
	}



	// Interest on bounties: 4% increase
	$db->query('UPDATE [game]_users SET bounty = bounty * 1.04');
}

function processTurns()
{
	global $db, $gameOpt;

	$db->query('UPDATE [game]_users SET turns = turns + %u',
	 array($gameOpt['increase_turns']));
	$db->query('UPDATE [game]_users SET turns = %u WHERE turns > %u',
	 array($gameOpt['max_turns'], $gameOpt['max_turns']));
}

function processCleanup()
{
	global $db, $gameInfo;

	// DELETE UNVALIDATED NEW USERS AND OPTIMISE (move to separate function)
	$db->query('DELETE FROM user_accounts WHERE login_count = 0 AND ' .
	 'signed_up < %u', array(time() - USER_VALIDATION_LENGTH));

	$db->query('OPTIMIZE TABLE user_accounts, user_history, se_games, ' .
	 'se_central_forum, se_star_names, option_list, daily_tips');

	// RETIRE INACTIVE PLAYERS
	$limit = 6; // days not played the game
	$removed = array(); // array: names of removed players

	$playerInfo = $db->query('SELECT clan_id, login_id, login_name ' .
	 'FROM [game]_users WHERE login_id != %u && last_request < %u',
	 array($gameInfo['admin'], time() - 60 * 60 * 24 * $limit));

	while (list($clan, $id, $name) = $db->fetchRow($playerInfo, ROW_NUMERIC)) {
		if ($clan !== NULL) {
			$leader = $db->query('SELECT leader_id FROM [game]_clans ' .
			 'WHERE clan_id = %u', array($clan));
			if (current($db->fetchRow($leader)) == $id) {
				$db->query('UPDATE [game]_users SET clan_id = NULL ' .
				 'WHERE clan_id = %u', array($clan));
				$db->query('DELETE FROM [game]_clans WHERE clan_id = %u',
				 array($clan));
			}
		}

		$db->query('DELETE FROM [game]_ships WHERE login_id = %u',
		 array($id));
		$db->query('DELETE FROM [game]_diary WHERE login_id = %u',
		 array($id));
		$db->query('INSERT INTO user_history VALUES (%u, %u, \'[game]\', ' .
		 '\'Removed from game after %u days of idling.\', \'\', \'\')',
		 array($id, time(), $limit));
		$db->query('DELETE FROM [game]_user_options WHERE login_id = %u', 
		 array($id));
		$db->query('DELETE FROM [game]_users WHERE login_id = %u',
		 array($id));

		$removed[] = $name;

	}

	if (!empty($removed)) {
		$newId = newId('[game]_news', 'news_id');
		$db->query('INSERT INTO [game]_news (news_id, timestamp, headline, ' .
		 'login_id) VALUES (%u, %u, \'Players retired after %u days of ' .
		 'in-activity: %s.\', 1)', array($newId, time(), $limit,
		$db->escape(implode(', ', $removed))));
	}

	// OPTIMISE ALL TABLES
	$db->query('OPTIMIZE TABLE [game]_bilkos, [game]_clans, [game]_diary, ' .
	 '[game]_clan_invites, [game]_messages, [game]_news, [game]_planets, ' .
	 '[game]_ships, [game]_stars, [game]_user_options, [game]_users');
}

$games = $db->query('SELECT db_name FROM se_games WHERE status = \'running\'');

while ($name = $db->fetchRow($games, ROW_NUMERIC)) {
	$db->addVar('game', $name[0]);

	$gameInfo = selectGame($name[0]);
	gameVars($name[0]);

    $amount = dbTaskAmount('systems');
	while (--$amount >= 0) {
	    processSystems();
	}

    $amount = dbTaskAmount('ships');
	while (--$amount >= 0) {
	    processShips();
	}

    $amount = dbTaskAmount('planets');
	while (--$amount >= 0) {
	    processPlanets();
	}

    $amount = dbTaskAmount('government');
	while (--$amount >= 0) {
	    processGovernment();
	}

    $amount = dbTaskAmount('turns');
	while (--$amount >= 0) {
	    processTurns();
	}

    $amount = dbTaskAmount('cleanup');
	while (--$amount >= 0) {
	    processCleanup();
	}
}

?>
