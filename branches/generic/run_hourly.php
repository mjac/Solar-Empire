<?php

require_once('inc/maint.inc.php');

$startArr = explode(' ', microtime());
$startTime = (double)$startArr[0] + (double)$startArr[1];

$games = mysql_query("SELECT `db_name` FROM `se_games` WHERE `status` >= 1 && " .
 "`paused` != 1");
while (list($game) = mysql_fetch_row($games)) {
    print "- Game $game -\n";


	/* Remove clans that have no members */
	mysql_query("DELETE FROM `{$game}_clans` WHERE `members` = 0");
	$clansRemoved = mysql_affected_rows();
	if ($clansRemoved > 0) {
		print "Removed $clansRemoved empty clan(s)\n";
	}


	/* Set missile launch pads 1hr nearer completion */
	mysql_query("UPDATE `{$game}_planets` SET `launch_pad` = `launch_pad` - 1 " .
	 "WHERE `launch_pad` > 1");
	$launchPads = mysql_affected_rows();
	if ($launchPads > 0) {
		print "Updated $launchPads missile launch pad(s)\n";;
	}


	$safeTurns = (int)getVar($game, 'turns_safe');
	$scatter = (int)getVar($game, 'keep_sol_clear');

	$starQuery = mysql_query("SELECT `num_stars` FROM `se_games` WHERE " .
	 "`db_name` = '$game'");
	$systemNo = mysql_result($starQuery, 0);

	if ($scatter == 1) {
		/**
		 * select users who have at least one ship in sol, update the db so as
		 * to give them a warning their ships will be scattered, or if this is
		 * their first hour in sol then update to give them a second hour.
		 */

		$warning = '<p>You left at least one of your ships in the Sol (#1) ' .
		 "Star-System during the last hourly maintenence.</p>\n<p>" .
		 'Should the ship(s) be in there during the next maintence they ' .
		 'will scattered around the universe.</p>';

		$action = '<p>Your <em>%s</em> has been moved to <strong>system #%d</strong>' .
		 "from system <strong>#1</strong>.<p>\n<p>The governer has decided to keep " .
		 'system Sol clear, you failed to respond to the warning.</p>';


		$scatterWarn = array();

		$warn = mysql_query('SELECT `s`.`login_id`, `s`.`login_name` FROM ' .
		 "`{$game}_ships` AS `s`, `{$game}_users` AS `u` WHERE `s`.`location` " .
		 "= 1 && `s`.`login_id` > 3 && `u`.`turns_run` > $safeTurns && " .
		 "`u`.`login_id` = `s`.`login_id` && `s`.`ship_id` != 1 GROUP BY " .
		 "`u`.`login_id`");

		while (list($id, $name) = mysql_fetch_row($warn)) {
			mysql_query("INSERT INTO `{$game}_messages` (`timestamp`, " .
			 "`sender_name`, `sender_id`, `login_id`, `text`) VALUES (" .
			 time() . ", '" . addslashes($name) . "', $id, $id, '" .
			 addslashes(sprintf($warning, $id, $name)) . "')");

			mysql_query("UPDATE `{$game}_users` SET `second_scatter` = " .
			 "`second_scatter` + 1 WHERE `login_id` = $id");

			$scatterWarn[] = $id;
		}


		$scattered = array();

		$toScatter = mysql_query("SELECT `s`.`login_name`, `s`.`login_id`, " .
		 "`s`.`ship_name`, `s`.`ship_id`, `u`.`ship_id` AS `command` FROM " .
		 "`{$game}_ships` AS `s` LEFT JOIN `{$game}_users` AS `u` ON " .
		 "`u`.`login_id` = `s`.`login_id` WHERE `s`.`location` = 1 && " .
		 "`s`.`login_id` > 3 && `u`.`turns_run` > $safeTurns && " .
		 "`s`.`ship_id` != 1 && `u`.`second_scatter` = 2");

		while (list($ownerName, $ownerId, $shipName, $shipId, $inCommand) =
		       mysql_fetch_row($toScatter)) {
			$goto = mt_rand(0, $systemNo - 1);

			mysql_query("INSERT INTO `{$game}_messages` (`timestamp`, " .
			 "`sender_name`, `sender_id`, `login_id`, `text`) VALUES (" . time() .
			 ", '" . addslashes($ownerName) . "', $ownerId, $ownerId, '" .
			 addslashes(sprintf($action, $shipName, $goto)) . "')");

			mysql_query("UPDATE `{$game}_ships` SET `location` = $goto, " .
			 "`towed_by` = 0, `mine_mode` = 0 WHERE `ship_id` = $shipId");
			mysql_query("UPDATE `{$game}_users` SET `second_scatter` = 0 WHERE " .
			 "`login_id` = $ownerId");

			/* If in command, set user[location] to the new location */
			if($shipId == $inCommand){
				mysql_query("UPDATE `{$game}_users` SET `location` = $goto WHERE " .
				 "`login_id` = $ownerId");
			}

			$scattered[] = "$ownerName: $shipName";
		}

		if (!empty($scattered)) {
			print count($scattered) . " ship(s) have been scattered:\n\t" .
			 implode("\n\t", $scattered);
		}

		/* Update the users that do not have ships in sol: scatter code is set to 0 */
		if (!empty($scatterWarn)) {
			mysql_query("UPDATE `{$game}_users` SET `second_scatter` = 0 WHERE " .
			 "`login_id` != " . implode(' && `login_id` != ', $scatterWarn));

			print "Warned " . count($scatterWarn) . " players about being scattered:\n\t" .
			 implode("\n\t", $scatterWarn);
		}
	}

	/* Shield generation */
	$hourlyShields = getVar($game, 'hourly_shields');
	mysql_query("UPDATE `{$game}_ships` SET `shields` = `shields` + " .
	 $hourlyShields . " WHERE `config` REGEXP 'fr'");
	mysql_query("UPDATE `{$game}_ships` SET `shields` = `shields` + " .
	 ($hourlyShields / 2) . " WHERE `config` REGEXP 'bs'");
	mysql_query("UPDATE `{$game}_ships` SET `shields` = `shields` + " .
	 ($hourlyShields * 1.5) . " WHERE `config` REGEXP 'sv'");
	mysql_query("UPDATE `{$game}_ships` SET `shields` = `shields` + " .
	 ($hourlyShields * 2) . " WHERE config REGEXP 'sw'");
	mysql_query("UPDATE `{$game}_ships` SET `shields` = `shields` + " .
	 ($hourlyShields  / 4) . " WHERE config REGEXP 'sh'");

	mysql_query("UPDATE `{$game}_ships` SET `shields` = `shields` + " .
	 $hourlyShields);

    mysql_query("UPDATE `{$game}_planets` SET `shield_charge` = `shield_charge` + " .
     "$hourlyShields * `shield_gen` WHERE `shield_gen` > 0");
    mysql_query("UPDATE `{$game}_ships` SET `shields` = `max_shields` WHERE " .
     "`shields` > `max_shields`");
	mysql_query("UPDATE `{$game}_planets` SET `shield_charge` = `shield_gen` * 1000 " .
	 "WHERE `shield_charge` > `shield_gen` * 1000");


	/*/* Random events * / commented for now
	$randomEvents = (int)getVar($game, 'random_events');
	if($randomEvents >= 2){
		/* Remove shields of ships in systems with random_event 2 or 12 * /
		$removeShields = mysql_query("SELECT `star_id` FROM `{$game}_stars` WHERE " .
		 "`event_random` = 2 || `event_random` = 12");
		while(list($id) = mysql_fetch_row($removeShields)) {
			mysql_query("UPDATE `{$game}_ships` SET `shields` = 0 WHERE " .
			 "`location` = $id && `login_id` != 1");
		}


		/* Damage/destroy ships in nebulae * /
		$nebShips = mysql_query("SELECT `s`.`fighters`, `s`.`ship_id`, `s`.`login_id`, " .
		 "`s`.`ship_name`, `s`.`location`, `s`.`login_name`, `s`.`class_name` " .
		 "FROM `{$game}_ships` AS `s` LEFT JOIN `{$game}_users` AS `u` ON " .
		 "`s`.`login_id` = `u`.`login_id` LEFT JOIN `{$game}_stars` AS `t` ".
		 "ON `s`.`location` = `t`.`star_id` WHERE `t`.`event_random` = 2 && " .
		 "`s`.`shipclass` != 2 && `u`.`turns_run` > $safeTurns && `u`.`login_id` != 1");

		$damageMsg = '<p>The nebulae in system <em>#%d</em> did <em>%d</em> " .
		 "damage to your <strong>%s</strong> (%s).%s</p>';

		while (list($figs, $shipId, $ownerId, $shipName, $location, $ownerName,
		       $className) = mysql_fetch_row($nebShips)) {
			$figLoss = mt_rand(0, 9) + 1;
			if ($figLoss > $figs) {
				mysql_query("DELETE FROM `{$game}_ships` WHERE `ship_id` = $shipId");

				$transferTo = mysql_query("SELECT `ship_id`, `location`, `ship_name` " .
				 "FROM `{$game}_ships` WHERE `login_id` = $ownerId");

				if (mysql_num_rows($transferTo) > 0) {
					list($toId, $toSys, $toName) = mysql_fetch_row($transferTo);

					mysql_query("UPDATE `{$game}_users` SET `ship_id` = $toId, " .
					 "`location` = $toSys WHERE `login_id` = $ownerId");

					mysql_query("INSERT INTO `{$game}_messages` (`timestamp`, " .
					 "`sender_name`, `sender_id`, `login_id`, `text`) VALUES (" .
					 time() . ", 'Nebulae', $ownerId, $ownerId, '" .
					 addslashes(sprintf($damageMsg, $location, $figLoss, $shipName,
					 $className, "Command was transfered to <strong>$toName</strong>, " .
					 "your ship was destroyed.")) . "')");

					mysql_query("INSERT INTO `{$game}_news` (`timestamp`, `headline`, " .
					 "`login_id`) values (" . time() . ", '<strong>$ownerName</strong> " .
					 "lost a <em>$className</em> to a nebulae.', $ownerId)");
				} else {
					$randStar = $location - 1;

					mysql_query("INSERT INTO `{$game}_messages` (`timestamp`, " .
					 "`sender_name`, `sender_id`, `login_id`, `text`) VALUES (" .
					 time() . ", 'Nebulae', $ownerId, $ownerId, '" .
					 addslashes(sprintf($damageMsg, $location, $figLoss, $shipName,
					 $className, "You ejected in an escape pod.")) . "')");

					mysql_query("INSERT INTO `{$game}_ships` (`ship_name`, " .
					 "`login_id`, `login_name`, `shipclass`, `class_name`, " .
					 "`location`, `point_value`) VALUES ('Escape Pod', $ownerId, " .
					 "'$ownerName', 2, 'Escape Pod', $randStar, 5)");

					$theId = mysql_query("SELECT `ship_id` FROM `{$game}_ships` " .
					 "WHERE `login_id` = $ownerId");

					mysql_query("UPDATE `{$game}_users` SET `ship_id` = " .
					 mysql_result($theId, 0) . ", `location` = $randStar " .
					 "WHERE `login_id` = $ownerId");
				}
			} else {
				mysql_query("UPDATE `{$game}_ships` SET `fighters` = " .
				 "`fighters` - $figLoss WHERE `ship_id` = $shipId");

				mysql_query("INSERT INTO `{$game}_messages` (`timestamp`, " .
				 "`sender_name`, `sender_id`, `login_id`, `text`) VALUES (" .
				 time() . ", 'Nebulae', $ownerId, $ownerId, '" .
				 addslashes(sprintf($damageMsg, $location, $figLoss, $shipName,
				 $className, '')) . "')");
			}
		}
	}
	*/




	//Mining Metal
	print 'Mining metal... ';
	$ships = mysql_query("select s.ship_id, s.location, `s`.`mine_rate_metal` as mine_rate, s.cargo_bays,s.metal,s.fuel,s.elect,s.organ,s.colon,star.metal AS star_metal from {$game}_stars star, {$game}_ships s, {$game}_users u where s.mine_mode = 1 && u.login_id = s.login_id&& star.star_id = s.location && s.location != 1 && star.metal > 0 && (s.cargo_bays - s.metal - s.fuel - s.elect - s.organ - s.colon) > 0 && `mine_rate_metal` > 0 group by s.ship_id");

	$count = 0;

	while ($ship = mysql_fetch_assoc($ships)) {
		switch (mt_rand(0, 3)) {
			case 0:
				$mins_mined = $ship['mine_rate'] + 1;
				break;
			case 1:
				$mins_mined = $ship['mine_rate'] - 1;
				break;
			default:
				$mins_mined = $ship['mine_rate'];
		}

		if ($mins_mined > $ship['star_metal']) {
			$mins_mined = $ship['star_metal'];
		}

		$space = $ship['cargo_bays'] - $ship['fuel'] - $ship['metal'] -
		 $ship['organ'] - $ship['elect'] - $ship['colon'];
		if ($mins_mined > $space) {
			$mins_mined = $space;
		}

		mysql_query("update {$game}_ships set metal = metal + $mins_mined where ship_id = " . $ship['ship_id']);
		mysql_query("update {$game}_stars set metal = metal - $mins_mined where star_id = " . $ship['location']);
		++$count;
	}
	print "done\n";

	//Mining Fuel
	print 'Mining fuel... ';
	$ships = mysql_query("select s.ship_id,s.location,s.mine_rate_fuel as mine_rate,s.cargo_bays,s.metal,s.fuel,s.elect,s.organ,s.colon,star.fuel AS star_fuel from {$game}_stars star, {$game}_ships s, {$game}_users u where s.mine_mode =2 && u.login_id = s.login_id && star.star_id = s.location && star.fuel > 0 && (s.cargo_bays - s.metal - s.fuel - s.elect - s.organ - s.colon) > 0 && mine_rate_fuel > 0 group by s.ship_id");

	$count = 0;

	while ($ship = mysql_fetch_assoc($ships)) {
		switch (mt_rand(0, 3)) {
			case 0:
				$mins_mined = $ship['mine_rate'] + 1;
				break;
			case 1:
				$mins_mined = $ship['mine_rate'] - 1;
				break;
			default:
				$mins_mined = $ship['mine_rate'];
		}

		if ($mins_mined > $ship['star_fuel']) {
			$mins_mined = $ship['star_fuel'];
		}

		$space = $ship['cargo_bays'] - $ship['fuel'] - $ship['metal'] -
		 $ship['organ'] - $ship['elect'] - $ship['colon'];
		if ($mins_mined > $space) {
			$mins_mined = $space;
		}

		mysql_query("update {$game}_ships set fuel = fuel + $mins_mined where ship_id = " . $ship['ship_id']);
		mysql_query("update {$game}_stars set fuel = fuel - $mins_mined where star_id = " . $ship['location']);
		++$count;
	}
	print "done\n";



	mysql_query("update {$game}_stars set fuel = 0 where fuel < 0");
	mysql_query("update {$game}_stars set metal = 0 where metal < 0");

	$hourlyTurns = getVar($game, 'hourly_turns');
	mysql_query("UPDATE `{$game}_users` SET `turns` = `turns` + $hourlyTurns");

	$maxTurns = getVar($game, 'max_turns');
	mysql_query("update `{$game}_users` SET `turns` = $maxTurns WHERE `turns` > $maxTurns");


	#bilkos auction house:
	mysql_query("delete from `{$game}_bilkos` where `timestamp` <= " . (time() - 172800) . " && bidder_id = 0 && active=1");
	$bombsOn = getVar($game, 'flag_bomb');
	$db = mysql_query("select bidder_id,item_name,item_id from {$game}_bilkos where timestamp <= ".(time()-86400) ." && active = 1 && bidder_id > 0");
	while($lots = mysql_fetch_assoc($db)){
		mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'Bilkos','$lots[bidder_id]','$lots[bidder_id]','You have successfully won lot #<b>$lots[item_id]</b> (<b class=b1>$lots[item_name]</b>). <p>You should come to the Auction House in <b class=b1>Sol</b> to collect your goods.')");
		mysql_query("update {$game}_bilkos set active=0 where item_id = '$lots[item_id]'");
	}


#number generated is random. between 0 and 99.
#3 per 4 hours or so a new item may be added. (75 = 3/4 of 100)
#if > 90, then planetary item.
# > 78 then misc
# > 55 then ships
# > 45 then equipment
# > 25 then upgrades

if (mt_rand(0, 3)) {
	$turnip = mt_rand(0, 5);
	if($turnip == 5){ #planetary.
		$i_type = 5;
		if(mt_rand(0, 1)){
			$i_code = mt_rand(4, 9);
			$i_name = "Shield Gen Lvl <b>$i_code</b>";
			$i_price = $i_code * 4000;
			$i_descr = "A level <b>$i_code</b> Shield Generater for a planet (Normal lvl is 3). <br>Increases Shield Capacity, and Shield Generation Rate. Can be used as an upgrade, or a new Generator.";
		} else{
			$i_code = "MLPad";
			$i_name = "Missile Launch Pad";
			$i_price = 100000;
			$i_descr = "Missile Launch Pad. Used once. No build time necassary, just install and go.";
		}
	} elseif($turnip >= 3) { #misc - turns.
		$i_type = 4;
		$i_code = mt_rand(10, 80);
		$i_name = "Turns <b>$i_code</b>";
		$i_price = $i_code * 110;
		$i_descr = "<b>$i_code</b> turns that can be used for whatever you want.";
/*	} elseif($turnip == 2) { #ships
		#get a random ship and put it up for auction.
		$db = $dbh->prepare("SELECT COUNT(*) as `maths` FROM `{$game}_ship_types` WHERE `config` NOT LIKE '%oo%' && `type_id`>2 && `auction`=1");
		$db->execute();
		$temp = $db->fetchrow_hashref();
		$things = int(rand($temp->{maths})) + 1;

		$db = $dbh->prepare("SELECT `type_id` FROM `{$game}_ship_types` WHERE `config` NOT LIKE '%oo%' && `type_id`>2 && `auction`=1");
		$db->execute();
		$stuff = $db->fetchrow_hashref();

		for ($i = 1; $i < $things; ++$i) {
			$stuff = $db->fetchrow_hashref();
		}
		$go_id = $stuff->{type_id};

		#Put stuff into DB
		$i_type = 1;
		$db = mysql_query("select type_id,name,cost,max_shields,fighters,max_fighters,upgrades,config,descr from {$game}_ship_types where type_id = $go_id");
		$ships = mysql_fetch_assoc($db);
		$i_code = "ship" . $ships['type_id'];
		$i_name = $ships['name'];
		$i_price = $ships['cost'];
		$i_descr = "<b class=b1>Specs:</b> $ships->{max_shields} Shield Capacity; $ships->{fighters}/$ships->{max_fighters} Fighters; $ships->{upgrades} Upgrade Pods; Config: $ships->{config}.<p>$ships->{descr}";
	} elseif($turnip == 1){ #equipment
		$i_type = 2;
		$db = $dbh->prepare("select value from {$game}_db_vars where name = 'cost_bomb'");
		$db->execute();
		$bomb_cost = $db->fetchrow_hashref();
		if($turnip > 49 && $bombs_enabled->{value} < 2){
			$i_code="warpack";
			$i_name="WarPack";
			$i_price=$bomb_cost->{value}*4;
			$i_descr="A collection of 2 Alpha bombs and 4 Gamma Bombs, all in one package.";
		} else {
			if($bombs_enabled->{value} < 2)
			{
				$i_code="deltabomb";
				$i_name="Delta Bomb";
				$i_price=15*$bomb_cost->{value};
				$i_descr="One Bomb that will nullify all shields on all ships in the system AND then do <b>5000</b> damage to each of the ships!.<br><br>Note: Player may only own one Delta Bomb at a time!";
			}
		}
*/	} else { # upgrades
		$i_type = 3;
		if($turnip > 41) {
			$i_code = "fig1500";
			$i_name = "1500 Fighter Bays";
			$i_price = 50000;
			$i_descr = "Capable of fitting 1500 fighters into one upgrade pod this is a must for the war-hungry.";
		} elseif($turnip > 37) {
			$i_code="attack_pack";
			$i_name="Attack Pack";
			$i_price=20000;
			$i_descr="Increases a ships Shield capacity by 200 and fighter capacity by 700, all with one upgrade.";
		} elseif($turnip > 32.5) {
			$i_code="fig500";
			$i_name="500 Fighter Bays";
			$i_price=10000;
			$i_descr="This Nifty little upgrade allows you to squeeze 500 fighters into one upgrade pod.";
		} elseif($turnip > 28) {
			$i_code="upbs";
			$i_name="Battleship Conversion";
			$i_price=20000;
			$i_descr="Enables a ship to do more damage when attacking, and increases shields per hour by <b>50%</b>.<br>(already installed on normal battleships).";
		} else {
			$i_code="up2";
			$i_name="Terra Maelstrom Upgrade";
			$i_price=1000000;
			$i_descr="The only Upgrade for the Brobdingnagian. (Can only be used on Brobdingnagians) Rare, but extremely potent, this replaces the Quark Disrupter with a weapon that is capable of crippling planets.<br>Get it while its available.";
		}
	}

	mysql_query("insert into {$game}_bilkos (timestamp,item_type,item_code,item_name,going_price,descr,active) values(".time().",'$i_type','$i_code','$i_name','$i_price','$i_descr',1)");
}


/*
#random event things.
if($r_e->{value} > 0){	#ensure the random events var is set, otherwise could get a divide by 0 error
	#remove mining rush.
	$db = $dbh->prepare("select star_id from {$game}_stars where event_random = '4'");
	$db->execute();
	$star_var = $db->fetchrow_hashref();
	if($star_var) {
		$temp = (1000 / ($r_e->{value} * $systemNo->{num_stars})) + 4;
		$temp2 = int(rand($temp));
		if($temp2 == 0) {
			mysql_query("update {$game}_stars set event_random = 0 where star_id = $star_var->{star_id}");
			mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'The rich metal deposits in <b class=b1>system $star_var->{star_id}</b> have been exhausted. Mining rates in that system have returned to normal.','-10')");
			print "Mining Rush Added.\n";
		}
	}


	#remove Solar Storm
	$db = $dbh->prepare("select star_id,star_name from {$game}_stars where event_random = '12'");
	$db->execute();
	while($star_var = $db->fetchrow_hashref()){
		$temp = (1800 / ($r_e->{value} * $systemNo->{num_stars})) + 4;
		$temp2 = int(rand($temp));
		if($temp2 == 0) {
			mysql_query("update {$game}_stars set event_random = 0 where star_id = $star_var->{star_id}");
			mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'The Solar Activity in the <b class=b1>$star_var->{star_name}</b> system (#<b>$star_var->{star_id}</b>), has gone back to more normal levels, meaning the Solar Storm has abated.','-10')");
			print "Solar Storm Removed.\n";
		}
	}
}

#Solar Storm
if ($r_e->{value} > 1) {
	$temp = (2000 / ($r_e->{value} * $systemNo->{num_stars})) + 1;

	$chance = int(rand($temp));
	if ($chance < 2) {
		$to_go = int(rand($systemNo->{num_stars}));
		$db = $dbh->prepare("select event_random,star_name from {$game}_stars where star_id = '$to_go'");
		$db->execute();
		$is_it = $db->fetchrow_hashref();
		if ($is_it->{event_random} == 0) {
			mysql_query("update {$game}_stars set event_random = 12 where star_id = '$to_go'");
			mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'Due to increased Solar Activity in the <b class=b1>$is_it->{star_name}</b> system (#<b>$to_go</b>), a Solar Storm has been created.','-10')");
			print "Solar Storm added.\n";
		}
	}
}


/ Senator re-calculation /
$politics = getVar($game, 'enable_politics');

if($politics == 1) {
	#Military Senator >>> positon_id = 3
	$db = $dbh->prepare("select login_name,login_id from {$game}_users where fighters_killed > '5000' && politics = 0 && login_id > 3 order by fighters_killed desc");
	$db->execute();
	$mil_min = $db->fetchrow_hashref();
	if ($mil_min) {
		$db = $dbh->prepare("select login_id from {$game}_politics where position_id = 3");
		$db->execute();
		$pol_mil = $db->fetchrow_hashref();
		if ($pol_mil->{login_id} != $mil_min->{login_id}) {
			mysql_query("update {$game}_users set politics = 3 where login_id = '$mil_min->{login_id}'");
			mysql_query("update {$game}_politics set login_name = '$mil_min->{login_name}', timestamp = ".time().", login_id = '$mil_min->{login_id}' where position_id = '3'");
			mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'A new Senator has been declared. <b class=b1>$mil_min->{login_name}</b> is now a <b class=b1>Senator</b>','2')");
			mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$mil_min->{login_name}','$mil_min->{login_id}','$mil_min->{login_id}','You are the new <b class=b1>Military Senator</b>.')");
			if ($pol_min) {
				mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$pol_min->{login_name}','$pol_min->{login_id}','$pol_min->{login_id}','You are no longer the <b class=b1>Military Senator</b>.')");
				mysql_query("update {$game}_users set politics = 0 where login_id = '$pol_min->{login_id}'");
			}
		}
	}

	#Defense Senator >>> positon_id = 4
	$db = $dbh->prepare("select p.owner_name,p.owner_id from {$game}_planets p, {$game}_users u where (u.politics = 0 || u.politics = 4) && (u.login_id = p.owner_id && p.fighters > '5000' && p.planet_type >= '0' && p.owner_id > '3') order by p.fighters desc");
	$db->execute();
	$mil_min = $db->fetchrow_hashref();
	if ($mil_min) {
		$db = $dbh->prepare("select login_id from {$game}_politics where position_id = 4");
		$db->execute();
		$pol_mil = $db->fetchrow_hashref();
		if ($pol_mil->{login_id} != $mil_min->{owner_id}) {
			mysql_query("update {$game}_users set politics = 4 where login_id = '$mil_min->{owner_id}'");
			mysql_query("update {$game}_politics set login_name = '$mil_min->{owner_name}', timestamp = ".time().", login_id = '$mil_min->{owner_id}' where position_id = '4'");
			mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'A new Senator has been declared. <b class=b1>$mil_min->{owner_name}</b> is now a <b class=b1>Senator</b>','2')");
			mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$mil_min->{owner_name}','$mil_min->{owner_id}','$mil_min->{owner_id}','You are the new <b class=b1>Defense Senator</b>.')");
			if ($pol_min) {
				mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$pol_min->{login_name}','$pol_min->{login_id}','$pol_min->{login_id}','You are no longer the <b class=b1>Defense Senator</b>.')");
				mysql_query("update {$game}_users set politics = 0 where login_id = '$pol_min->{login_id}'");
			}
		}
	}

	#Industry Senator >>> positon_id = 2
	$db = $dbh->prepare("select p.owner_name,p.owner_id from {$game}_planets p, {$game}_users u where (u.politics = 0 || u.politics = 2) && (u.login_id = p.owner_id && p.colon > '50000' && p.planet_type >= '0' && p.owner_id > '3') order by p.colon desc");
	$db->execute();
	$mil_min = $db->fetchrow_hashref();
	if ($mil_min) {
		$db = $dbh->prepare("select login_id from {$game}_politics where position_id = 2");
		$db->execute();
		$pol_mil = $db->fetchrow_hashref();
		if ($pol_mil->{login_id} != $mil_min->{owner_id}) {
			mysql_query("update {$game}_users set politics = 2 where login_id = '$mil_min->{owner_id}'");
			mysql_query("update {$game}_politics set login_name = '$mil_min->{owner_name}', timestamp = ".time().", login_id = '$mil_min->{owner_id}' where position_id = '2'");
			mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'A new Senator has been declared. <b class=b1>$mil_min->{owner_name}</b> is now a <b class=b1>Senator</b>','2')");
			mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$mil_min->{owner_name}','$mil_min->{owner_id}','$mil_min->{owner_id}','You are the new <b class=b1>Industry Senator</b>.')");
			if ($pol_min) {
				mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$pol_min->{login_name}','$pol_min->{login_id}','$pol_min->{login_id}','You are no longer the <b class=b1>Industry Senator</b>.')");
				mysql_query("update {$game}_users set politics = 0 where login_id = '$pol_min->{login_id}'");
			}
		}
	}

	#Trade Senator >>> positon_id = 5
	$db = $dbh->prepare("select count(s.ship_id) as tempx5x, s.login_name as login_name, s.login_id as login_id from {$game}_ships s, {$game}_users u where (u.politics = 0 || u.politics = 5) && (u.login_id = s.login_id && s.config REGEXP 'fr' && s.login_id > 3) GROUP BY s.login_id order by tempx5x desc");
	$db->execute();
	$mil_min = $db->fetchrow_hashref();
	if ($mil_min->{tempx5x} > 20) {
		$db = $dbh->prepare("select login_id from {$game}_politics where position_id = 5");
		$db->execute();
		$pol_mil = $db->fetchrow_hashref();
		if ($pol_mil->{login_id} != $mil_min->{login_id}) {
			mysql_query("update {$game}_users set politics = 5 where login_id = '$mil_min->{login_id}'");
			mysql_query("update {$game}_politics set login_name = '$mil_min->{login_name}', timestamp = ".time().", login_id = '$mil_min->{login_id}' where position_id = '5'");
			mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'A new Senator has been declared. <b class=b1>$mil_min->{login_name}</b> is now a <b class=b1>Senator</b>','2')");
			mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$mil_min->{login_name}','$mil_min->{login_id}','$mil_min->{login_id}','You are the new <b class=b1>Trade Senator</b>.')");
			if ($pol_min) {
				mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$pol_min->{login_name}','$pol_min->{login_id}','$pol_min->{login_id}','You are no longer the <b class=b1>Trade Senator</b>.')");
				mysql_query("update {$game}_users set politics = 0 where login_id = '$pol_min->{login_id}'");
			}
		}
	}

	#War Senator >>> positon_id = 6
	$db = $dbh->prepare("select count(s.ship_id) as tempx5x, s.login_name as login_name, s.login_id as login_id from {$game}_ships s, {$game}_users u where (u.politics = 0 || u.politics = 6) && (u.login_id = s.login_id && s.config REGEXP 'bs' && s.login_id > 3) GROUP BY s.login_id order by tempx5x desc");
	$db->execute();
	$mil_min = $db->fetchrow_hashref();
	if ($mil_min->{tempx5x} > 50) {
		$db = $dbh->prepare("select login_id from {$game}_politics where position_id = 6");
		$db->execute();
		$pol_mil = $db->fetchrow_hashref();
		if ($pol_mil->{login_id} != $mil_min->{login_id}) {
			mysql_query("update {$game}_users set politics = 6 where login_id = '$mil_min->{login_id}'");
			mysql_query("update {$game}_politics set login_name = '$mil_min->{login_name}', timestamp = ".time().", login_id = '$mil_min->{login_id}' where position_id = '6'");
			mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'A new Senator has been declared. <b class=b1>$mil_min->{login_name}</b> is now a <b class=b1>Senator</b>','2')");
			mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$mil_min->{login_name}','$mil_min->{login_id}','$mil_min->{login_id}','You are the new <b class=b1>War Senator</b>.')");
			if ($pol_min) {
				mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$pol_min->{login_name}','$pol_min->{login_id}','$pol_min->{login_id}','You are no longer the <b class=b1>War Senator</b>.')");
				mysql_query("update {$game}_users set politics = 0 where login_id = '$pol_min->{login_id}'");
			}
		}
	}

	#Espionage Senator >>> positon_id = 7
	$db = $dbh->prepare("select count(s.ship_id) as tempx5x, s.login_name as login_name, s.login_id as login_id from {$game}_ships s, {$game}_users u where (u.politics = 0 || u.politics = 7) && (u.login_id = s.login_id && (s.config REGEXP 'ls' || s.config REGEXP 'hs') && s.login_id > 3) GROUP BY s.login_id order by tempx5x desc");
	$db->execute();
	$mil_min = $db->fetchrow_hashref();
	if ($mil_min->{tempx5x} > 30) {
		$db = $dbh->prepare("select login_id from {$game}_politics where position_id = 6");
		$db->execute();
		$pol_mil = $db->fetchrow_hashref();
		if ($pol_mil->{login_id} != $mil_min->{login_id}) {
			mysql_query("update {$game}_users set politics = 6 where login_id = '$mil_min->{login_id}'");
			mysql_query("update {$game}_politics set login_name = '$mil_min->{login_name}', timestamp = ".time().", login_id = '$mil_min->{login_id}' where position_id = '6'");
			mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'A new Senator has been declared. <b class=b1>$mil_min->{login_name}</b> is now a <b class=b1>Senator</b>','2')");
			mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$mil_min->{login_name}','$mil_min->{login_id}','$mil_min->{login_id}','You are the new <b class=b1>War Senator</b>.')");
			if ($pol_min) {
				mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'$pol_min->{login_name}','$pol_min->{login_id}','$pol_min->{login_id}','You are no longer the <b class=b1>War Senator</b>.')");
				mysql_query("update {$game}_users set politics = 0 where login_id = '$pol_min->{login_id}'");
			}
		}
	}


}
*/

	/* Maintenance for game is complete */
	mysql_query("INSERT INTO `{$game}_news` (`timestamp`, `headline`, `login_id`) values (" . time() . ",'Hourly Maintenance Run','1')");
}


mysql_close();
