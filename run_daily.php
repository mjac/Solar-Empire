<?php

require_once('inc/maint.inc.php');


$tips = mysql_query("SELECT `tip_id` FROM `daily_tips` ORDER BY RAND() LIMIT 1");
$newTip = mysql_result($tips, 0);
mysql_query('UPDATE `se_games` SET `todays_tip` = ' . $newTip);


$games = mysql_query("SELECT db_name FROM se_games WHERE status >= 1 && status != 'paused'");

while (list($game) = mysql_fetch_row($games)) {
    print "- Game $game -\n";

	/* Print to news that maint is running */
	mysql_query("INSERT INTO `{$game}_news` (`timestamp`, `headline`, `login_id`) values (" .
	 time() . ", 'Daily Maintenance Running...', '1')");

	/* Retire OOD players */
	$limit = 6; // days not played the game
	$removed = array(); // array: names of removed players

	$playerInfo = mysql_query('SELECT `clan_id`, `login_id`, `login_name` FROM `' .
	 $game . '_users` WHERE `login_id` > 5 && `last_request` < ' .
	 (time() - 60 * 60 * 24 * $limit));

	while (list($clan, $id, $name) = mysql_fetch_row($playerInfo)) {
		if ($clan > 1) {
			$leader = mysql_query("SELECT `leader_id` FROM `{$game}_clans` WHERE `clan_id` = $clan");
			if (mysql_result($leader, 0) == $id) {
				mysql_query("UPDATE `{$game}_users` SET `clan_id` = 0 WHERE `clan_id` = $clan");
				mysql_query("UPDATE `{$game}_planets` SET `clan_id` = -1 WHERE `clan_id` = $clan");
				mysql_query("DELETE FROM `{$game}_clans` WHERE `clan_id` = $clan");
			} else {
				mysql_query("UPDATE `{$game}_planets` SET `clan_id` = -1 WHERE `owner_id` = $id");
				mysql_query("UPDATE `{$game}_clans` SET `members` = `members` - 1 WHERE `clan_id` = $clan");
			}
		}

		mysql_query("DELETE FROM `{$game}_ships` WHERE `login_id` = $id");
		mysql_query("DELETE FROM `{$game}_diary` WHERE `login_id` = $id");
		mysql_query("INSERT INTO `user_history` VALUES ($id, " . time() . ', ' . addslashes($game) .
		 ", 'Removed from game after $limit days of in-activity.', '', '')");
		mysql_query("DELETE FROM `{$game}_user_options` WHERE `login_id` = $id");
		mysql_query("DELETE FROM `{$game}_users` WHERE `login_id` = $id");
		mysql_query("UPDATE `{$game}_politics` set `login_id` = 0, `login_name` = 0, `timestamp` = 0 WHERE `login_id` = $id");

		$removed[] = $name;

	}

	if (!empty($removed)) {
		mysql_query("INSERT INTO `{$game}_news` (`timestamp`, `headline`, " .
		 "`login_id`) VALUES (" . time() . ", 'Players retired after $limit " .
		 "days of in-activity:\n<ul>\n\t<li>" .
		 addslashes(implode("</li>\n\t<li>", $removed)) . "</li>\n</ul>', 1)");

		print "Players retired after $limit days of in-activity:\n\t" .
		 implode("\n\t", $removed) . "\n";
	}

/*	print "Reset political standing... ";
	Resets political standing if politics is disabled
	$politics = (int)getVar($game, 'enable_politics');
	if($politics === 0) {
		mysql_query("DELETE FROM `{$game}_politics`");
		mysql_query("INSERT INTO `{$game}_politics` VALUES (1, 'Monarch', 0, '', 0) " .
		 "(2, 'Industry Senator', 0, '', 0) " .
		 "(3, 'Military Senator', 0, '', 0) " .
		 "(4, 'Defense Senator', 0, '', 0) " .
		 "(5, 'Trade Senator', 0, '', 0) " .
		 "(6, 'War Senator', 0, '', 0) " .
		 "(7, 'Espionage Senator', 0, '', 0)");

		mysql_query("UPDATE `{$game}_users` SET `politics` = 0");
	}
	print "done\n"; */
	
	print "Increasing Bounties... ";
	/* Interest on bounties: 4% increase */
	mysql_query("UPDATE {$game}_users SET bounty = `bounty` * 1.04");
	print "done\n";

	print "Planet stuff... ";
	/* Planet builds */
	$planets = mysql_query("SELECT planet_id, planet_name, p.login_id, tax_rate, " .
	 "fuel, metal, elect, colon, alloc_fight, alloc_elect, alloc_organ, " .
	 "u.planet_report FROM {$game}_planets AS p LEFT JOIN {$game}_user_options " .
	 "AS u ON u.login_id = p.login_id WHERE p.planet_id != 1") or die(mysql_error());
	print "done\n";
	 
	 
	while (list($id, $name, $ownerId, $tax, $fuel, $metal, $elect,
	        $colonists, $allocFigs, $allocElect, $allocOrgan, $report) =
	        mysql_fetch_row($planets)) {
		print "Planet #$id ($name)\n";

		/* For the user report, if wanted */
		$reportStr = "<p><strong>Manufacturing report for $name</strong></p>\n";

		/* Fighters */
		$fighterMax = floor($allocFigs / 100);

		$resourceUsed = $fuel > $metal ? $metal : $fuel;
		$resourceUsed = $resourceUsed > $elect ? $elect : $resourceUsed;
		$resourceUsed = $resourceUsed > $fighterMax ? $fighterMax : $resourceUsed;

		$figsProduced = floor($resourceUsed * 10);

		if ($figsProduced > 0) {
			mysql_query("UPDATE `{$game}_planets` SET `fighters` = `fighters` + " .
			 "$figsProduced, `fuel` = `fuel` - $resourceUsed, `metal` = `metal` - " .
			 "$resourceUsed, `elect` = `elect` - $resourceUsed WHERE `planet_id` = $id");

			$elect -= $resourceUsed;
			$fuel  -= $resourceUsed;
			$metal -= $resourceUsed;

			$reportStr .= "<p>Fuel Used: <b>$resourceUsed</b><br />\n" .
			 "Metal Used: <b>$resourceUsed</b><br />\nElectronics Used: " .
			 "<b>$resourceUsed</b><br />\n<em>$allocFigs colonists</em> " .
			 "produced <strong>$figsProduced fighters</strong>.</p>\n";

			print "\t$figsProduced fighters\n";
		}

		/* Electronics: 1 in 50 */
		$electBudget = floor($allocElect / 50);
		$electBudget = $fuel < $electBudget ? $fuel : $electBudget;
		$electBudget = $metal < $electBudget ? $metal : $electBudget;
		if ($electBudget > 0) {
			mysql_query("UPDATE `{$game}_planets` SET `elect` = `elect` + $electBudget, " .
			 "`fuel` = `fuel` - $electBudget, `metal` = `metal` - $electBudget " .
			 "WHERE `planet_id` = $id");

			$reportStr .= "<p>Fuel Used: <b>$electBudget</b><br />\n" .
			 "Metal Used: <b>$electBudget</b><br />\n" .
			 "<em>$allocElect colonists</em> produced <strong>$electBudget " .
			 "electronics</strong>.</p>\n";

			print "\t$electBudget electronics\n";
		}

		/* Organics production. 1 per 500 colonists assigned */
		$organicProduce = floor($allocOrgan / 500);
		if ($organicProduce > 0) {
			mysql_query("UPDATE `{$game}_planets` SET `organ` = `organ` + " .
			 "$organicProduce WHERE `planet_id` = $id");

			$reportStr .= "<p><em>$allocOrgan colonists</em> produced " .
			 "<strong>$organicProduce organics</strong>.</p>\n";

			print "\t$organicProduce organics\n";
		}


		#Confirm if anything happens to colonists.
		$boredCols = $colonists - ($allocFigs + $allocElect + $allocOrgan);
		$taxed = floor($boredCols * $tax / 100);
		if ($taxed > 0) {
			$reportStr .= "<p><em>$boredCols colonists</em> taxed for " .
			 "<strong>$taxed</strong> credits ($tax%).</p>\n";

			print "\ttaxed colonists for $taxed credits ($tax%)\n";
		}

		$perc = 0.3 - 0.03 * $tax;
		$percStr = floor($perc * 100) . "%";
		$newPop = floor($boredCols * $perc);
		if ($perc != 0) {
			$reportStr .= "<p><em>population</em> increased by $newPop ($percStr).</p>\n";

			print "\tpopulation changed by $newPop ($percStr)\n";
		}

		/* Send report message */
		if ($report >= 1) {
			mysql_query("INSERT INTO `{$game}_messages` (`timestamp`, `sender_name`, " .
			 "`sender_id`, `login_id`, `text`) values(" . time() . ", 'The Universe', " .
			 "$ownerId, $ownerId, '" . addslashes($reportStr) . "')");
		}
	}

	/* Process planet taxes! */
	mysql_query("UPDATE `{$game}_planets` SET `cash` = `cash` + FLOOR((`colon` - " .
	 "(`alloc_fight` + `alloc_elect` + `alloc_organ`)) * `tax_rate` / 100)");

	mysql_query("UPDATE `{$game}_planets` SET `colon` = `colon` + FLOOR((`colon` - " .
	 "(`alloc_fight` + `alloc_elect` + `alloc_organ`)) * (0.3 - `tax_rate` * 0.03))");

	mysql_query("UPDATE `{$game}_planets` SET `alloc_fight` = 0, `alloc_elect` = 0, " .
	 "`alloc_organ` = 0 WHERE (`alloc_fight` + `alloc_elect` + `alloc_organ`) > `colon`");


	$metalChance = getVar($game, 'rr_metal_chance');
	$metalChanceMin = getVar($game, 'rr_metal_chance_min');
	$metalChanceMax = getVar($game, 'rr_metal_chance_max');

	$fuelChance = getVar($game, 'rr_fuel_chance');
	$fuelChanceMin = getVar($game, 'rr_fuel_chance_min');
	$fuelChanceMax = getVar($game, 'rr_fuel_chance_max');

	$stars = mysql_query("SELECT COUNT(*) FROM `{$game}_stars`");
	$numStars = mysql_result($stars, 0);

	mysql_query("UPDATE `{$game}_stars` SET `metal` = `metal` + (RAND() * " .
	($metalChanceMax - $metalChanceMin) . ") + $metalChanceMin WHERE (RAND() * 100) " .
	 "< $metalChance && `star_id` <> 1");

	mysql_query("UPDATE `{$game}_stars` SET `fuel` = `fuel` + (RAND() * " .
	($fuelChanceMax - $fuelChanceMin) . ") + $fuelChanceMin WHERE (RAND() * 100) " .
	 "< $fuelChance && `star_id` <> 1");


	/* Days left in game */
	mysql_query("UPDATE `{$game}_db_vars` SET `value`=`value`-1 WHERE " .
	 "`name` = 'count_days_left_in_game' and `value` > 0");


	$randomEvents = getVar($game, 'random_events');

/*

#supernova remnant to blackhole
$db = $dbh->prepare("select star_id from {$game}_stars where event_random = '6'");
$db->execute();
$bh_sys = $db->fetchrow_hashref();

if ($bh_sys) {
	$chance = rand(5);
	if ($chance < 1) {
		mysql_query("update {$game}_stars set event_random = 1, metal = '0', fuel='0', star_name = 'BlackHole' where star_id = '$bh_sys->{star_id}'");
		mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'The <b>SuperNova Remnant</b> in <b class=b1>system $bh_sys->{star_id}</b> has formed into a <b>blackhole</b>. Being a slow process, all ships managed to get out to system #<b>1</b>. We expect no further trouble from that system. <font color=lime>- - - Science Institute of Sol - - -</font>','-11')");

		$db = $dbh->prepare("select location,login_id,ship_id,ship_name from {$game}_ships where location = '$bh_sys->{star_id}'");
		$db->execute();
		while ($ship_bh = $db->fetchrow_hashref()) {
			mysql_query("update {$game}_ships set location = '1' where ship_id = '$ship_bh->{ship_id}'");
			mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'BlackHole','$ship_bh->{login_id}','$ship_bh->{login_id}','Your ship the <b class=b1>$ship_bh->{ship_name}</b> escaped a blackhole forming from a SuperNova Remnant in system #<b>$ship_bh->{location}</b>. It is now in system #<b>1</b>')");
		}
		mysql_query("update {$game}_users set location = '1' where location = '$bh_sys->{star_id}'");
		print "\nSN remnant in $bh_sys->{star_id} to blackhole\n";
	} elsif ($chance > 2.5) {
		mysql_query("update {$game}_stars set event_random = '14' where star_id = '$bh_sys->{star_id}'");
		mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'After much study, we have decided that the star in system <b>$bh_sys->{star_id}</b> will <b class=b1>not</b> become a Blackhole, as it was not massive enough. This system will remain a harmless Super-Nova Remnant, with lots of minerals in. <font color=lime>- - - Science Institute of Sol - - -</font>','-11')");
		print "\nSN remnant in $bh_sys->{star_id} safe\n";
	}
}


#SuperNova > supernova remnant
$db = $dbh->prepare("select star_id,event_random from {$game}_stars where event_random = '5' || event_random = '11'");
$db->execute();
$sn_sys = $db->fetchrow_hashref();

if ($sn_sys->{event_random} == 5) {
	$chance = rand(2);
	if ($chance > 1.7) {
		mysql_query("update {$game}_stars set event_random = 0, star_name = 'Slimane' where star_id = '$sn_sys->{star_id}'");
		mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'The scare about the Supernova in system <b>$sn_sys->{star_id}</b> is over. It seems a technician (<b>first class</b>) called <b class=b1>\"Rimmer\"</b> spilt some coffee over an instrument panel causing a false reading. We apologise for any terror caused. <font color=lime>- - - Science Institute of Sol - - -</font>','-11')");
		print "\nSupernova in $sn_sys->{star_id} was a dud.\n";
	} elsif ($chance < .8) {
		&explode_sn($sn_sys);
	}

} elsif($sn_sys->{event_random} == 11) {
	&explode_sn($sn_sys);
}

#Sets num 10 random events to lvl 11.
mysql_query("update {$game}_stars set event_random = '11' where event_random = '10'");


# adds random things
if ($rand > 1) {

	$temp = (1000 / ($rand * $num_stars->{num_stars})) + 1;
	$chance = int(rand($temp));

	if ($chance ==0) { #metal rush
		$to_go = int(rand($num_stars->{num_stars} - 3)) +2;
		$db = $dbh->prepare("select event_random from {$game}_stars where star_id = '$to_go'");
		$db->execute();
		$is_it = $db->fetchrow_hashref();
		if ($is_it->{event_random} == 0) {
			mysql_query("update {$game}_stars set event_random = 4, metal ='99999', fuel='0' where star_id = '$to_go'");
			mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'Breaking news. A huge metal depost has been found in <b class=b1>system $to_go</b> this deposit seems limitless, but could run out at any time. Metal mining rates for all ships in that system will be <b>quadrupled</b> until the deposit runs out.','-10')");
			print "\nRandom event, type 4 placed.\n";
		}
	}

	if ($rand > 2) {#togosupernova
		$temp = 40 / (int($num_stars->{num_stars} / 100) + 1);
		$chance = int(rand($temp));
		#supernova!!!!!!!
		if ($chance ==1) {
			$to_go = int(rand($num_stars->{num_stars} -3)) +2;
			mysql_query("update {$game}_stars set event_random = 5, metal = 0, fuel =0, star_name = 'SuperNova' where star_id = '$to_go' && event_random = 0");
			mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'Scientists report that the star in <b class=b1>system $to_go</b> is most likely going to go <b>Supernova(Explode)</b> in the next 24-72 hours, destroying <b class=b1>EVERYTHING</b> in the system. <font color=lime>- - - Science Institute of Sol - - -</font>','-11')");
			print "\nSupernova in $to_go.\n";
		}
	}

} elsif($rand < 1) {
	mysql_query("update {$game}_stars set event_random = 0 where event_random > 0");
}



#SuperNova Going bang.
sub explode_sn {
		mysql_query("update {$game}_stars set event_random = 6, metal = '583720', fuel='948372', star_name = 'SuperNova Remnant' where star_id = '$sn_sys->{star_id}'");
		mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'The star in <b class=b1>system $sn_sys->{star_id}</b> has exploded destroying everything in the system, and leaving a <b class=b1>Supernova Remnant</b> which is extremly rich in metals and fuel. All adjoining systems have also recieved generous quantities of minerals. We believe the SuperNova Remnant will turn into a <b>Blackhole</b> over due course.<font color=lime>- - - Science Institute of Sol - - -</font>','-11')");

		#take out non-eps.
		$db = $dbh->prepare("select * from {$game}_ships where location = '$sn_sys->{star_id}' && login_id !='1'");
		$db->execute();
		while ($ship_sn = $db->fetchrow_hashref()) {
			if ($ship_sn->{shipclass} != 2) {
				mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'SuperNova','$ship_sn->{login_id}','$ship_sn->{login_id}','Your ship the <b class=b1>$ship_sn->{ship_name}</b> was destroyed by an exploding star (<b>Supernova</b>) in system #<b>$ship_sn->{location}</b>')");
				mysql_query("delete from {$game}_ships where ship_id = '$ship_sn->{ship_id}'");
				mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'$ship_sn->{login_name} lost a $ship_sn->{class_name} to the SuperNova in system #<b>$ship_sn->{location}<b>','$ship_sn->{login_id}')");
			} else {
				mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'SuperNova','$ship_sn->{login_id}','$ship_sn->{login_id}','Your <b>Escape Pod</b> was near a star when it exploded. Fortunatly is was not hurt by the explosion, but was flung to a different system. Some well-wisher then brought you to system #1.')");
				mysql_query("update {$game}_users set location = '1' where ship_id = '$ship_sn->{ship_id}'");
				mysql_query("update {$game}_ships set location = '1' where login_id = '$ship_sn->{login_id}'");
			}
		}

		#take out non admin planets
		$db = $dbh->prepare("select * from {$game}_planets where location = '$sn_sys->{star_id}' && owner_id != '1'");
		$db->execute();
		while ($planet_sn = $db->fetchrow_hashref()) {
			mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'SuperNova','$planet_sn->{owner_id}','$planet_sn->{owner_id}','Your planet (<b class=b1>$planet_sn->{planet_name}</b>) was oblterated by an exploding star (<b>Supernova</b>) in system #<b>$planet_sn->{location}. It no longer excists, nor does anything that was on it.')");
			mysql_query("delete from {$game}_planets where planet_id = $planet_sn->{planet_id}");
			mysql_query("insert into {$game}_news (timestamp, headline, login_id) values (".time().",'The planet $planet_sn->{planet_name} was totally destroyed by the SuperNova in system #<b>$planet_sn->{location}<b>','$planet_sn->{owner_id}')");
		}

	#move users to sol or next ship
	$db2 = $dbh->prepare("select * from {$game}_users where location = '$sn_sys->{star_id}' && login_id > '3' && ship_id != 1");
	$db2->execute();

	while($users = $db2->fetchrow_hashref()) {
		$db = $dbh->prepare("select * from {$game}_ships where login_id = '$users->{login_id}' && login_id != 1");
		$db->execute();
		if($other = $db->fetchrow_hashref()) {
			mysql_query("update {$game}_users set ship_id = '$other->{ship_id}', location = '$other->{location}' where login_id = '$other->{login_id}'");
			mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'SuperNova','$users->{login_id}','$users->{login_id}','Command was transfered to the <b class=b1>$other->{ship_name}</b>.')");
		} else {
			mysql_query("insert into {$game}_messages (timestamp,sender_name,sender_id, login_id, text) values(".time().",'SuperNova','$users->{login_id}','$users->{login_id}','You ejected in an escape pod.')");
			mysql_query("insert into {$game}_ships (ship_name, login_id, login_name, shipclass, class_name, location, point_value) values('Escape Pod','$users->{login_id}','$users->{login_name}',2,'Escape Pod','1',5)");
			#mysql_query($q_string);
			$db = $dbh->prepare("select * from {$game}_ships where login_id = '$users->{login_id}'");
			$db->execute();
			$ship_id = $db->fetchrow_hashref();
			mysql_query("update {$game}_users set location = '1', ship_id ='$ship_id->{ship_id}' where login_id = '$users->{login_id}'");
		}
	}


		$db = $dbh->prepare("select * from {$game}_stars where star_id = '$sn_sys->{star_id}'");
		$db->execute();
		$link = $db->fetchrow_hashref();
		$link_1 = $link->{link_1};
		$link_2 = $link->{link_2};
		$link_3 = $link->{link_3};
		$link_4 = $link->{link_4};
		$link_5 = $link->{link_5};
		$link_6 = $link->{link_6};

		mysql_query("update {$game}_stars set fuel= fuel +'103482', metal= metal+'12354' where star_id != '1' && star_id != 0 && star_id = '$link_1'");
		mysql_query("update {$game}_stars set fuel= fuel +'95444', metal= metal+'56484' where star_id != '1' && star_id != 0 && star_id = '$link_2'");
		mysql_query("update {$game}_stars set fuel= fuel +'74452', metal= metal+'46877' where star_id != '1' && star_id != 0 && star_id = '$link_3'");
		mysql_query("update {$game}_stars set fuel= fuel +'37353', metal= metal+'106210' where star_id != '1' && star_id != 0 && star_id = '$link_4'");
		mysql_query("update {$game}_stars set fuel= fuel +'74523', metal= metal+'68757' where star_id != '1' && star_id != 0 && star_id = '$link_5'");
		mysql_query("update {$game}_stars set fuel= fuel +'63452', metal= metal+'83254' where star_id != '1' && star_id != 0 && star_id = '$link_6'");

		print "\nSupernova in $sn_sys->{star_id} went bang.\n";
}

*/


	mysql_query("OPTIMIZE TABLE `{$game}_bilkos, {$game}_clans, {$game}_diary, " .
	 "{$game}_messages, {$game}_news, {$game}_planets, {$game}_ships, " .
	 "{$game}_stars, {$game}_user_options, {$game}_users");
	 
	print "Daily maintenance for $game is... ";
	mysql_query("INSERT INTO {$game}_news (timestamp, headline, login_id) values (" .
	 time() . ", '...Daily maintenance complete', 1)");
	print "complete!\n";
	print "------------\n\n";
}

mysql_close();

?>
