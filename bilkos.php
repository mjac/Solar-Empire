<?php

require_once('inc/user.inc.php');

sudden_death_check($user);

db("select port_id from ${db_name}_ports where location = '$user[location]'");
$ports = dbr(1);
if($user['location'] != 1 && !$ports){
	print_page("Error","Bilkos Auction House does not exist at this location.");
}

if($user['location'] == 1){
	$rs = "<p><a href=earth.php>Return to Earth</a>";
} else {
	$rs = "<p><a href=port.php>Back to the Port</a>";
}
#percentage new bids must increase over old ones.
$rate = 5;

#work out the number of seconds an item is to remain in bilkos for.
$bilkos_seconds = $bilkos_time * 3600;

$text .= "<br>Select a catogory to see lots presently available. New lots arrive hourly.<br>";
db("select count(item_id),item_type from ${db_name}_bilkos where active = 1 && timestamp + $bilkos_seconds > ".time()." group by item_type");

for($i=1;$i<=5;$i++){
	$count=dbr();
	if($count['item_type']){
		$out[$count['item_type']] = $count[0];
	}
}

for($i=1;$i<=5;$i++){
	if(!$out[$i]){
		$out[$i] = 0;
	}
}

$text .= "<br><a href=bilkos.php?view=1>Ships</a> - (<b>$out[1]</b>)";
$text .= "<br><a href=bilkos.php?view=2>Equipment</a> - (<b>$out[2]</b>)";
$text .= "<br><a href=bilkos.php?view=3>Upgrades</a> - (<b>$out[3]</b>)";
$text .= "<br><a href=bilkos.php?view=4>Misc</a> - (<b>$out[4]</b>)";
$text .= "<br><a href=bilkos.php?view=5>Planetary</a> - (<b>$out[5]</b>)";


db("select count(item_id) from ${db_name}_bilkos where active=0 && bidder_id='$user[login_id]'");
$do_now=dbr();

if($do_now[0] > 0 && !$show_won && !$collect){
	$text .= "<p><a href=bilkos.php?show_won=1>Collect Item(s)</a> - (<b>$do_now[0]</b>)";
}

$text .= "<p>";

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
	db("select item_type,bidder_id,item_code,active from ${db_name}_bilkos where item_id = $collect");
	$item=dbr(1);
	$all_done = 0;
	if($user['login_id'] != $item['bidder_id']){
		$text .= "You did not win this item.";
	} elseif($item['active'] == 1) {
		$text .= "You have not won this item yet";
	} elseif($user['location'] != 1) {
		$text .= "You may only collect this item the bilkos store at Earth, as that is where the central repository if goods is stored.";
	} else {
		if($item[item_type] == 1){ #ships
			if($numships[0] >= $max_ships){
				$text .= "You may not collect this item as you are already at the ship limit.";
			} else {
				$item[item_code] = eregi_replace("ship","",$item[item_code]);

				#delete old EP's
				dbn("delete from ${db_name}_ships where login_id = '$user[login_id]' && class_name REGEXP 'Escape'");

				db("select * from ${db_name}_ship_types where type_id = '$item[item_code]'");
				$ship_stats=dbr();
				$q_string = "insert into ${db_name}_ships (";
				$q_string = $q_string . "ship_name,login_id,login_name,clan_id,shipclass,class_name,fighters,max_fighters,max_shields,cargo_bays,mine_rate_metal,mine_rate_fuel,config,size,upgrades,num_sa,num_pc,num_ew";
				$q_string = $q_string . ") values(";
				$q_string = $q_string . "'$ship_stats[name]','$user[login_id]','$user[login_name]','$user[clan_id]','$item[type_id]','$ship_stats[type]','$ship_stats[fighters]','$ship_stats[max_fighters]','$ship_stats[max_shields]','$ship_stats[cargo_bays]','$ship_stats[mine_rate_metal]','$ship_stats[mine_rate_fuel]','$ship_stats[config]','$ship_stats[size]','$ship_stats[upgrades]','$ship_stats[num_sa]','$ship_stats[num_pc]','$ship_stats[num_ew]')";
				dbn($q_string);
				$user[ship_id] = mysql_insert_id();

				db("select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
				$user_ship = dbr(1);
				dbn("update ${db_name}_users set ship_id = '$user[ship_id]' where login_id = '$user[login_id]'");
				$text .= "<p>Collection of the <b class=b1>$ship_stats[name]</b> complete.<br>You are now in command of your new ship.<p>Have a nice day.<br><br>";
				$all_done = 1;
			}
		} elseif($item['item_type'] == 3){ #upgrades
			if($item['item_code'] == "up2"){ #terra maelstrom
				if(eregi("sw",$user_ship['config'])) {
					$text .= "This ship has already had its Super Weapon upgraded.";
				} elseif(eregi("sv",$user_ship['config'])) {
					$text .= "Your Quark Disrupter has now been upgraded to a Terra Maelstrom... Have Fun!";
					$user_ship['config'] = eregi_replace("sv","sw",$user_ship['config']);
					dbn("update ${db_name}_ships set config = '$user_ship[config]' where ship_id = '$user_ship[ship_id]'");
					$all_done = 1;
				} else {
					$text .= "You must be commanding a ship with a Quark Disrupter on it to be able to collect this item.";
				}
			} elseif($user_ship['upgrades'] < 1) { #Ensure enough free slots.
				$text .= "You must be commanding a ship with at least one upgrade pod free to collect this upgrade.";
			} elseif($item[item_code] == "upbs"){
				if(eregi("bs",$user_ship[config])){
					$text .= "This ship already has the battleship upgrade. Please command a different ship and try again.";
				} else {
					$text .= "This ship has now been upgraded to a <b class=b1>Battleship</b> class ship. This means more damage will be done when attacking, more shields generated per hour, and the maximum fighter capacity can go above $max_non_warship_fighters.";
					$user_ship['config'] = $user_ship['config'].":bs";
					dbn("update ${db_name}_ships set config = '$user_ship[config]', upgrades = upgrades -1 where ship_id = '$user_ship[ship_id]'");
					$all_done = 1;
				}
			} elseif($item[item_code] == "fig600"){
				$text .= "Here's 600 more Fighter Capacity.";
				$user_ship[max_fighters] = $user_ship[max_fighters] + 600;
				dbn("update ${db_name}_ships set max_fighters = '$user_ship[max_fighters]', upgrades = upgrades -1 where ship_id = '$user_ship[ship_id]'");
				$all_done = 1;
			} elseif($item[item_code] == "fig1500"){
				$text .= "Here's 1500 more Fighter Capacity.";
				$user_ship[max_fighters] = $user_ship[max_fighters] + 1500;
				dbn("update ${db_name}_ships set max_fighters = '$user_ship[max_fighters]', upgrades = upgrades -1 where ship_id = '$user_ship[ship_id]'");
				$all_done = 1;
			} elseif($item[item_code] == "attack_pack"){
				if(eregi("sj",$user_ship[config])){
					$text .= "Ships with SubSpace Jump Drives cannot have shields on them.";
				} else {
					$text .= "Your ship has been upgraded with a further 200 shield capacity, and 600 fighter capacity.";
					$user_ship[max_fighters] = $user_ship[max_fighters] + 600;
					$user_ship[max_shields] = $user_ship[max_shields] + 200;
					dbn("update ${db_name}_ships set max_fighters = '$user_ship[max_fighters]',max_shields = '$user_ship[max_shields]', upgrades = upgrades -1 where ship_id = '$user_ship[ship_id]'");
					$all_done = 1;
				}
			}

		} elseif($item[item_type] == 2){ #equipment
			if($item[item_code] == "warpack"){
				$text .= "Here's your Warpack. Enjoy.";
				dbn("update ${db_name}_users set gamma = gamma+'4', alpha=alpha+2 where login_id = '$item[bidder_id]'");
				$all_done = 1;
			}elseif($item[item_code] == "deltabomb"){ #delta bomb
				if($user[delta] == 1){
					$text .= "Sorry. You may only have one Delta bomb at a time. These things <b>are</b> Contraband you know!";
				} else {
					$text .= "Here's your Delta Bomb. Enjoy.";
					dbn("update ${db_name}_users set delta = 1 where login_id = '$item[bidder_id]'");
					$all_done = 1;
				}
			}
		} elseif($item[item_type] == 4){ #misc
			if($item[item_code] >9 && $item[item_code] < 101){
				if($user['turns'] + $item['item_code'] > $max_turns){
					$text .= "You cannot collect your turns because adding these to your present turns would take you over the turn limit.";
				} else {
					$text .= "Here are your <b>$item[item_code]</b> turns.";
					$user[turns] += $item[item_code];
					dbn("update ${db_name}_users set turns = turns+'$item[item_code]' where login_id = '$item[bidder_id]'");
					$all_done = 1;
				}
			}
		} elseif($item[item_type] == 5){ #Planetary
			if($destination){
				db("select login_id, shield_gen, planet_name, planet_id, launch_pad from ${db_name}_planets where planet_id = '$destination'");
				$planets=dbr(1);
				if($planets[login_id] != $user[login_id]){
					$text .= "That Planet does not belong to you.";
				} elseif($item[item_code] >3 && $item[item_code] <10) {
					if($planets[shield_gen] > 3) {
						$text .= "This planet has already had its shield generater upgraded.";
					}
					$charge_cap = $item[item_code] * 1000;
					$text .= "Shield Generater on <b class=b1>$planets[planet_name]</b> is now lvl <b>$item[item_code]</b>.<br>This means Shield Capacity of <b>$charge_cap</b> and <b>$item[item_code]</b>* Shield Generation Rate.";
					dbn("update ${db_name}_planets set shield_gen = '$item[item_code]' where planet_id = '$planets[planet_id]'");
					$all_done = 1;
				} elseif($item[item_code] == "MLPad") {
					if($planets[launch_pad] != 0) {
						$text .= "This planet already has a <b class=b1>Missile Launch Pad</b>";
					}
					$text .= "<b class=b1>Missile Launch Pad</b> fitted on <b class=b1>$planets[planet_name]</b>.";
					dbn("update ${db_name}_planets set launch_pad = '1' where planet_id = '$planets[planet_id]'");
					$all_done = 1;
				}
			} else {
				db("select planet_name,planet_id from ${db_name}_planets where planet_id != 1 && login_id = '$user[login_id]'");
				$planets=dbr(1);
				if(!$planets){
					$text .= "You have no planets and so cannot collect this lot.";
				} else {
					$text .= "Select Planet to install this Lot on.";
					$text .= "<form method=post action=bilkos.php name=despatch_form>";
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

		if($all_done==1){ #remove lot from auction
			dbn("delete from ${db_name}_bilkos where item_id = $collect");
		}
	}

} elseif(isset($bid)){
	db("select * from ${db_name}_bilkos where item_id = $bid");
	$item=dbr(1);
	if($item[active] = 0){
		$text .= "This item is not for sale.";
	} elseif($item[timestamp] + $bilkos_seconds < time()){
		$text .= "This item has been removed from sale. Hard luck.";
	} elseif($new_bid){
		settype($new_bid, "integer");
		$new_price = round(($item[going_price] /100) * $rate) + $item[going_price];
		if($new_bid > $user[cash]){
			$text .= "Sorry, you do not have enough money";
		} elseif($new_price > $new_bid) {
			$text .= "That bid is not large enough. Minimum bid increases of <b>$rate%</b> are accepted.<br>Minimum bid on this item is <b>$new_price</b>";
		} elseif($new_bid < 1) {
			$text .= "Thats a ridiculous bid.";
#		} elseif($numships[0] > $max_ships && $item[item_type] == 1) {
#			$text .= "You may not bid for a ship as you are already at the Ship Limit.";
		} else {
			if($item['bidder_id'] > 0){
				dbn("update ${db_name}_users set cash= cash + '$item[going_price]' where login_id='$item[bidder_id]'");
				dbn("insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text) values(".time().",'Bilkos','$user[login_id]','$item[bidder_id]','Your bid on the <b class=b1>$item[item_name]</b> has been beaten by <b class=b1>$user[login_name]</b> who has put a new bid of <b>$new_bid</b> Credits on the item.<p>The lot will remain open for a further <b>$bilkos_time hrs</b>. If there are no new bidders, then <b class=b1>$user[login_name]</b> will take the lot.<p>You have been refunded the money you deposited on the lot.')");
			}
			dbn("update ${db_name}_bilkos set timestamp=".time().", bidder_id=$user[login_id],going_price=$new_bid where item_id = $bid;");
			take_cash($new_bid);
			$text .= "<br>Bid successful. <br>Provided no-one out bids you within the next <b>$bilkos_time hrs</b>, you will soon be the proud new owner of a <b class=b1>$item[item_name]</b>.";
		}
	} else {
		$new_price = round(($item[going_price] /100) * $rate) + $item[going_price];
		$text .= "Please place a bid for this <b class=b1>$item[item_name]</b>.<br>Present bid stands at: <b>$item[going_price]</b>;<br>New bid must be at least: <b>$new_price</b>";
		$text .= "<form method=post action=bilkos.php name=bid_form>";
		$text .= "<input type=hidden name=bid value=$bid>";
		$text .= "<input type=text name=new_bid size=10>";
		$text .= " - <INPUT type=submit value=Bid></form><p>";
		$rs = "<a href=bilkos.php>Back to Bilkos</a>";
		print_page("Make a Bid",$text);
	}
} elseif($view){ #Show all items in a particular catagory.
	$text .= "Current stock:<p>";
	db2("select item_name,descr,timestamp,going_price,bidder_id,item_id from ${db_name}_bilkos where item_type = $view && active=1 && timestamp + '$bilkos_seconds' > ".time()." order by timestamp asc, item_name asc");
	$items=dbr2(1);

	if(!$items){
		$text .= "None - Sorry.";
	} else {
		$text .= make_table(array("Item Name","Description","Open Till","Present Price","Present Bidder"));
		while($items) {
			$items['going_price'] = number_format($items['going_price']);
			if($items['bidder_id'] > 0){
				db("select login_name,login_id,clan_sym,clan_sym_color from ${db_name}_users where login_id = $items[bidder_id]");
				$bidder=dbr(1);
				$items['bidder_id'] = print_name($bidder);
				$items['timestamp'] = date( "M d - H:i",$items['timestamp']+$bilkos_seconds);
			} else {
				$items['bidder_id'] = "None Yet";
				$items['timestamp'] = date( "M d - H:i",$items['timestamp'] + ($bilkos_seconds * 2) );
			}
			$items['item_id'] = " - <a href=bilkos.php?bid=$items[item_id]>Bid</a>";
			$text .= make_row($items);
			$items = dbr2(1);
		}
		$text .= "</table>";
	}

} elseif($show_won) { #Show items user has won.
	db2("select item_name,item_type,item_id from ${db_name}_bilkos where active=0 && bidder_id='$user[login_id]'");
	$collect=dbr2(1);
	if($collect){
		$text .= "<br>You have at least one item to collect, which you have already paid for. Click the link next to the item to collect it";

		$text .= make_table(array("Item Name","Item Type"));
		while($collect) {
			if($collect[item_type] == 1){
				$collect[item_type] = "Ship";
			} elseif($collect[item_type] == 2){
				$collect[item_type] = "Equipment";
			} elseif($collect[item_type] == 3){
				$collect[item_type] = "Upgrade";
			} elseif($collect[item_type] == 4){
				$collect[item_type] = "Misc";
			} elseif($collect[item_type] == 5){
				$collect[item_type] = "Planetary";
			}
			$collect[item_id] = " - <a href=bilkos.php?collect=$collect[item_id]>Collect</a>";
			$text .= make_row($collect);
			$collect = dbr2(1);
		}
		$text .= "</table>";
	}

}

print_page("Bilkos Auction House",$text);

?>