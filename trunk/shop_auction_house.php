<?php

require_once('inc/user.inc.php');

deathCheck($user);

$pQuery = $db->query('SELECT COUNT(*) FROM [game]_ports WHERE ' .
 'location = %u', array($userShip['location']));
if ($user['ship_id'] !== NULL && current($db->fetchRow($pQuery)) < 1 &&
     $userShip['location'] != 1) {
	print_page("Error", "An auction house does not exist at this location.");
}

if($user['ship_id'] === NULL || $userShip['location'] == 1){
	$rs = "<p><a href=earth.php>Return to Earth</a>";
} else {
	$rs = "<p><a href=port.php>Back to the Port</a>";
}
#percentage new bids must increase over old ones.
$rate = 5;

#work out the number of seconds an item is to remain in bilkos for.
$bilkos_seconds = $gameOpt['bilkos_time'] * 3600;

$text = <<<END
<h1>Auction house</h1>

END;



$uWon = $db->query('SELECT COUNT(*) FROM [game]_bilkos WHERE ' .
 'active = 0 AND bidder_id = %u', array($user['login_id']));
$countWon = (int)current($db->fetchRow($uWon));

if($countWon > 0 && !isset($show_won) && !isset($collect)){
	$text .= "<p><a href=$self?show_won=1>Collect Item(s)</a> - (<b>$countWon</b>)";
}

/*
upgrades
up2 = terra maelstrom
all below require 1 free upgrade slot:
upbs = battleship
upfig600 = increase fighter cap by 600
upfig1500 = increase fighter cap by 1500
upattack = +200 shield cap. +600 fighter cap.

equip
warpack = 2alpha + 4 gammas
deltabomb = 1 Delta Bomb

planetary
4-9 = shield capacity =X000, charge rate = *X
MLPad = Missile Launch Pad.

misc
10 - 80 = X turns.
*/

if(isset($collect)){
	db("select item_type,bidder_id,item_code,active from [game]_bilkos where item_id = $collect");
	$item=dbr(1);
	$all_done = 0;
	if($user['login_id'] != $item['bidder_id']){
		$text .= "You did not win this item.";
	} elseif($item['active'] == 1) {
		$text .= "You have not won this item yet";
	} elseif($userShip['location'] != 1) {
		$text .= "You may only collect this item from the auction house at Earth, as that is where the central repository of goods is stored.";
	} else {
		if ($item['item_type'] == 1) { #ships
			if ($numships[0] >= $gameOpt['max_ships']) {
				$text .= "You may not collect this item as you are already at the ship limit.";
			} else {
				$item['item_code'] = str_replace('ship', '', $item[item_code]);

				#delete old EP's
				dbn("delete from [game]_ships where login_id = '$user[login_id]' AND class_name REGEXP 'Escape'");

				db("select * from [game]_ship_types where type_id = '$item[item_code]'");
				$ship_stats = dbr();

				$user['ship_id'] = make_ship($ship_stats, $user);

				$db->query('UPDATE [game]_users SET ship_id = %u WHERE ' .
				 'login_id = %u', array($user['ship_id'], $user['login_id']));

				checkShip();

				$text .= "<p>Collection of the <b class=b1>$ship_stats[name]</b> complete.<br />You are now in command of your new ship.<p>Have a nice day.<br /><br />";
				$all_done = 1;
			}
		} elseif($item['item_type'] == 3) { #upgrades
			if ($item['item_code'] == "up2") { #terra maelstrom
				if (shipHas($userShip, 'sw')) {
					$text .= "This ship has already had its Super Weapon upgraded.";
				} elseif (shipHas($userShip, 'sv')) {
					$text .= "Your Quark Disrupter has now been upgraded to a Terra Maelstrom... Have Fun!";
					$userShip['config'] = str_replace('sv', 'sw', $userShip['config']);
					dbn("update [game]_ships set config = '$userShip[config]' where ship_id = '$userShip[ship_id]'");
					$all_done = 1;
				} else {
					$text .= "You must be commanding a ship with a Quark Disrupter on it to be able to collect this item.";
				}
			} elseif($userShip['upgrades'] < 1) { #Ensure enough free slots.
				$text .= "You must be commanding a ship with at least one upgrade pod free to collect this upgrade.";
			} elseif($item['item_code'] == "upbs"){
				if (shipHas($userShip, 'bs')) {
					$text .= "This ship already has the battleship upgrade. Please command a different ship and try again.";
				} else {
					$text .= "This ship has now been upgraded to a <b class=b1>Battleship</b> class ship. This means more damage will be done when attacking, more shields generated per hour, and the maximum fighter capacity can go above $max_non_warship_fighters.";
					$userShip['config'] = $userShip['config'].":bs";
					dbn("update [game]_ships set config = '$userShip[config]', upgrades = upgrades -1 where ship_id = '$userShip[ship_id]'");
					$all_done = 1;
				}
			} elseif($item['item_code'] == "fig600"){
				$text .= "Here's 600 more Fighter Capacity.";
				$userShip['max_fighters'] += 600;
				dbn("update [game]_ships set max_fighters = '$userShip[max_fighters]', upgrades = upgrades -1 where ship_id = '$userShip[ship_id]'");
				$all_done = 1;
			} elseif($item['item_code'] == "fig1500"){
				$text .= "Here's 1500 more Fighter Capacity.";
				$userShip['max_fighters'] += 1500;
				dbn("update [game]_ships set max_fighters = '$userShip[max_fighters]', upgrades = upgrades -1 where ship_id = '$userShip[ship_id]'");
				$all_done = 1;
			} elseif($item['item_code'] == "attack_pack"){
				if (shipHas($userShip, 'sj')) {
					$text .= "Ships with SubSpace Jump Drives cannot have shields on them.";
				} else {
					$text .= "Your ship has been upgraded with a further 200 shield capacity, and 600 fighter capacity.";
					$userShip[max_fighters] = $userShip[max_fighters] + 600;
					$userShip[max_shields] = $userShip[max_shields] + 200;
					dbn("update [game]_ships set max_fighters = '$userShip[max_fighters]',max_shields = '$userShip[max_shields]', upgrades = upgrades -1 where ship_id = '$userShip[ship_id]'");
					$all_done = 1;
				}
			}

		} elseif($item['item_type'] == 2){ #equipment
			if($item[item_code] == "warpack"){
				$text .= "Here's your Warpack. Enjoy.";
				dbn("update [game]_users set gamma = gamma+'4', alpha=alpha+2 where login_id = '$item[bidder_id]'");
				$all_done = 1;
			}elseif($item[item_code] == "deltabomb"){ #delta bomb
				if($user[delta] == 1){
					$text .= "Sorry. You may only have one Delta bomb at a time. These things <b>are</b> Contraband you know!";
				} else {
					$text .= "Here's your Delta Bomb. Enjoy.";
					dbn("update [game]_users set delta = 1 where login_id = '$item[bidder_id]'");
					$all_done = 1;
				}
			}
		} elseif($item['item_type'] == 4){ #misc
			if($item['item_code'] > 9 && $item['item_code'] < 101){
				if ($user['turns'] + $item['item_code'] > $gameOpt['max_turns']) {
					$text .= "You cannot collect your turns because adding these to your present turns would take you over the turn limit.";
				} else {
					$text .= "Here are your <b>$item[item_code]</b> turns.";
					$user['turns'] += $item['item_code'];
					dbn("update [game]_users set turns = turns+'$item[item_code]' where login_id = '$item[bidder_id]'");
					$all_done = 1;
				}
			}
		} elseif($item['item_type'] == 5){ #Planetary
			if(isset($destination)){
				db("select login_id, shield_gen, planet_name, planet_id, launch_pad from [game]_planets where planet_id = '$destination'");
				$planets=dbr(1);
				if($planets['login_id'] != $user['login_id']){
					$text .= "That Planet does not belong to you.";
				} elseif($item['item_code'] >3 && $item['item_code'] <10) {
					if($planets['shield_gen'] > 3) {
						$text .= "This planet has already had its shield generater upgraded.";
					}
					$charge_cap = $item['item_code'] * 1000;
					$text .= "Shield Generater on <b class=b1>$planets[planet_name]</b> is now lvl <b>$item[item_code]</b>.<br />This means Shield Capacity of <b>$charge_cap</b> and <b>$item[item_code]</b>* Shield Generation Rate.";
					dbn("update [game]_planets set shield_gen = '$item[item_code]' where planet_id = '$planets[planet_id]'");
					$all_done = 1;
				} elseif($item[item_code] == "MLPad") {
					if($planets[launch_pad] != 0) {
						$text .= "This planet already has a <b class=b1>Missile Launch Pad</b>";
					}
					$text .= "<b class=b1>Missile Launch Pad</b> fitted on <b class=b1>$planets[planet_name]</b>.";
					dbn("update [game]_planets set launch_pad = '1' where planet_id = '$planets[planet_id]'");
					$all_done = 1;
				}
			} else {
				db("select planet_name,planet_id from [game]_planets where planet_id != 1 AND login_id = '$user[login_id]'");
				$planets=dbr(1);
				if(!$planets){
					$text .= "You have no planets and so cannot collect this lot.";
				} else {
					$text .= "Select Planet to install this Lot on.";
					$text .= "<form method=post action=$self name=despatch_form>";
					$text .= "<input type=hidden name=collect value=$collect>";
					$text .= "<select name=destination>";
					while($planets){
						$text .= "<option value=$planets[planet_id]> $planets[planet_name] ";
						$planets=dbr(1);
					}
					$text .= "</select>";
					$text .= "<p><INPUT type=submit value=Install></form><p>";
				}
			}
		}

		if ($all_done == 1) { #remove lot from auction
			dbn("delete from [game]_bilkos where item_id = $collect");
		}
	}

} elseif(isset($bid)){
	$bidExists = $db->query("select * from [game]_bilkos where item_id = %u", array($bid));
	$item = $db->fetchRow($bidExists);
	if (!$item || $item['active'] = 0) {
		$text .= "This item is not for sale.";
	} elseif ($item['timestamp'] + $bilkos_seconds < time()) {
		$text .= "This item has been removed from sale. Hard luck.";
	} elseif (isset($new_bid)){
		$new_bid = (int)$new_bid;
		$new_price = round($item['going_price'] / 100 * $rate) + $item['going_price'];

		if ($new_price > $new_bid) {
			$text .= "That bid is not large enough. Minimum bid increases of <b>$rate%</b> are accepted.<br />Minimum bid on this item is <b>$new_price</b>";
		} elseif (!giveMoneyPlayer(-$new_bid)) {
			$text .= "Sorry, you do not have enough money";
		} else {
			if($item['bidder_id'] > 0){
				giveMoney($item['bidder_id'], $item['going_price']);
				$newId = newId('[game]_messages', 'message_id');
				dbn("INSERT INTO [game]_messages (message_id, timestamp, sender_name, sender_id, login_id, text) VALUES ($newId, ".time().",'Bilkos','$user[login_id]','$item[bidder_id]','Your bid on the <b class=b1>$item[item_name]</b> has been beaten by <b class=b1>$user[login_name]</b> who has put a new bid of <b>$new_bid</b> Credits on the item.<p>The lot will remain open for a further <b>$gameOpt[bilkos_time] hrs</b>. If there are no new bidders, then <b class=b1>$user[login_name]</b> will take the lot.<p>You have been refunded the money you deposited on the lot.')");
			}
			dbn("update [game]_bilkos set timestamp=".time().", bidder_id=$user[login_id],going_price=$new_bid where item_id = $bid;");
			$text .= <<<END
<h2>Bid successful</h2>
<p>Provided no-one out bids you within the next <em>$gameOpt[bilkos_time] 
hours</em>, you will soon be the proud new owner of a 
<strong>$item[item_name]</strong>.</p>

END;
		}
	} else {
		$new_price = round($item['going_price'] / 100 * $rate) + $item['going_price'];
		$text .= <<<END
<h2>Bid for $item[item_name]</h2>
<p>Present bid stands at <em>$item[going_price] credits</em>; new bid must be
at least <em>$new_price credits</em></p>
<form method="post" action="$self">
	<p><input type="hidden" name="bid" value="$bid" />
	<input type="text" name="new_bid" size="10" value="$new_price" class="text" />
	 - <input type="submit" value="Place bid" class="button" /></p>
</form>
<p><a href="$self">Back to Bilkos</a></p>

END;
		print_page("Make a Bid",$text);
	}
} elseif (isset($view)) { #Show all items in a particular catagory.
	$text .= "<h2>Current stock</h2>";
	$items = $db->query("select item_name,descr,timestamp,going_price,bidder_id,item_id from [game]_bilkos where item_type = $view AND active=1 AND timestamp + '$bilkos_seconds' > ".time()." order by timestamp asc, item_name asc");

	if ($db->numRows($items) > 0) {
		$text .= <<<END
<p>Click the item name to place a bid.</p>
<table class="simple">
	<tr>
		<th>Name</th>
		<th>Description</th>
		<th>Open until</th>
		<th>Price</th>
		<th>Bidder</th>
	</tr>
	
END;

		while ($i = $db->fetchRow($items)) {
			$i['going_price'] = number_format($i['going_price']);
			if ($i['bidder_id'] > 0) {
				$i['bidder_id'] = print_name(array('login_id' => $i['bidder_id']));
				$i['timestamp'] = date("Y-m-d H:i:s", $i['timestamp'] + $bilkos_seconds);
			} else {
				$i['bidder_id'] = "<em>none</em>";
				$i['timestamp'] = date("Y-m-d H:i:s", $i['timestamp'] + $bilkos_seconds * 2);
			}
			$text .= <<<END
	<tr>
		<td><a href="$self?bid=$i[item_id]">$i[item_name]</a></td>
		<td>$i[descr]</td>
		<td>$i[timestamp]</td>
		<td>$i[going_price]</td>
		<td>$i[bidder_id]</td>
	</tr>

END;
		}
		$text .= "</table>\n";
	} else {
		$text .= "<p>No items</p>";
	}

} elseif (isset($show_won)) { #Show items user has won.
	db2("select item_name,item_type,item_id from [game]_bilkos where active=0 AND bidder_id='$user[login_id]'");
	$collect=dbr2(1);
	if($collect){
		$text .= "<br />You have at least one item to collect, which you have already paid for. Click the link next to the item to collect it";

		$text .= make_table(array("Item Name","Item Type"));
		while($collect) {
			if($collect['item_type'] == 1){
				$collect['item_type'] = "Ship";
			} elseif($collect['item_type'] == 2){
				$collect['item_type'] = "Equipment";
			} elseif($collect['item_type'] == 3){
				$collect['item_type'] = "Upgrade";
			} elseif($collect['item_type'] == 4){
				$collect['item_type'] = "Misc";
			} elseif($collect['item_type'] == 5){
				$collect['item_type'] = "Planetary";
			}
			$collect['item_id'] = " - <a href=$self?collect=$collect[item_id]>Collect</a>";
			$text .= make_row($collect);
			$collect = dbr2(1);
		}
		$text .= "</table>";
	}
} else {
	$typeInfo = $db->query('SELECT COUNT(item_id), item_type FROM ' .
	 '[game]_bilkos WHERE active = 1 AND timestamp > %u GROUP BY item_type',
	 array(time() - $bilkos_seconds));

	for ($i = 1; $i <= 5; ++$i) {
		$count = $db->fetchRow($typeInfo, ROW_NUMERIC);
		if ($count[1]) {
			$out[$count[1]] = $count[0];
		}
	}

	for ($i = 1; $i <= 5; ++$i) {
		if (!isset($out[$i])) {
			$out[$i] = 0;
		}
	}


	$text .= <<<END
<h2><a href="$self?show_won=1">Won lots</a></h2>
<h2>Lot categories</h2>
<table class="simple">
	<tr>
		<th>Item Type</th>
		<th>No. Lots</th>
	</tr>
	<tr>
		<td><a href="$self?view=1">Ships</a></td>
		<td>$out[1]</td>
	</tr>
	<tr>
		<td><a href="$self?view=2">Equipment</a></td>
		<td>$out[2]</td>
	</tr>
	<tr>
		<td><a href="$self?view=3">Upgrades</a></td>
		<td>$out[3]</td>
	</tr>
	<tr>
		<td><a href="$self?view=4">Miscellaneous</a></td>
		<td>$out[4]</td>
	</tr>
	<tr>
		<td><a href="$self?view=5">Planetary</a></td>
		<td>$out[5]</td>
	</tr>
</table>
END;
	print_page("Auction house", $text);
}

$text .= "<p>Return to the <a href=\"$self\">auction house</a></p>";

print_page("Auction house",$text);

?>
