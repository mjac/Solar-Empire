<?php

require_once('inc/user.inc.php');

deathCheck($user);

$cost_per_transfer = 0;
$text = '';
$rs = "<p><br /><a href=player_info.php?target=$target>Back to Player Info</a>";

if ($user['joined_game'] > (time() - ($gameOpt['min_before_transfer'] * 86400)) && !IS_ADMIN) {
	print_page("Transfer Ship Registration", "You cannot sign over a ship in the first $gameOpt[min_before_transfer] days of having your account");
} elseif(!isset($target)) { #ensure target has been selected
	print_page("Transfer Ship Registration", "Target player required.");
}

#get information from DB about target player.
db("select login_id, login_name, clan_id from [game]_users where login_id = $target");
$target = dbr();

if($sudden_death == 1 && !IS_ADMIN) { //SD check
	#ensure target isn't dead.
	db("select count(ship_id) from [game]_ships where login_id = '$target[login_id]'");
	$count = dbr();

	if(!isset($count[0]) || (isset($count[0]) && $count[0] <= 0) ) {
		print_page("Sudden Death","This game is now in Sudden Death. As this player has died, they are not allowed ships sent to them.");
	}

}


if(isset($do_ship)) { //user has selected stuff to transfer
	$num_ships = count($do_ship);
	$estimated_cost = $num_ships * $cost_per_transfer;
	$loop_txt = '';
	$rs .= "<p><a href=ship_send.php?target=$target[login_id]>Transfer Another Ship</a><br />";

	db("select count(ship_id) from [game]_ships where login_id = '$target[login_id]'");
	$ship_count = dbr();

	if($user['cash'] < $estimated_cost) { # ensure have enough cash
		$text .= "Signing over a single ship costs $estimated_cost Credits<br />To transfer all the selected ships will cost as much as <b>$estimated_cost</b> Credits.";
	} elseif($num_ships < 1){
		$text .= "No ships Selected for Transfer!";
	} elseif($ship_count[0] >= $max_ships){
		$text .= "<b class=b1>$target[login_name]</b> already has the maximum number of ships.";
	} else {
		//can transfer ships.
		$transfer_counter = 0;

		//loops through the ships.
		foreach($do_ship as $ship_id) {
			if($ship_id == 1){ //safety check. Don't want to transfer an SD.
				continue;
			}

			db("select config REGEXP 'bs' as is_warship, ship_name, login_id, config from [game]_ships where ship_id = '$ship_id'");
			$this_ship = dbr(1);

			if(empty($this_ship)){
				$loop_txt .= "$ship_id does not exist!<br />";
			} elseif($this_ship['login_id'] != $user['login_id']){ //not users ship
				$loop_txt .= "$this_ship[ship_name] does NOT belong to you! Stop Screwing around!<br />";
				continue;
			} elseif(substr_count($this_ship['config'],"oo")){ //trying to transfer a flagship
				$loop_txt .= "Flagships may not be transfered.<br />";
				continue;
			} elseif($ship_id == $userShip['ship_id']){
				$loop_txt .= "Command ship cannot be transfered.";
			} else {
				$loop_txt .= "<b class=b1>$this_ship[ship_name]</b> transfered successfully.<br />";

				$db->query("update [game]_ships set login_id = '$target[login_id]', towed_by = NULL, metal=0, fuel=0, elect=0, organ=0, colon=0 where ship_id = '$ship_id'");

				//ensure don't go over the limit
				++$transfer_counter;
			}
		}
		$text .= "$transfer_counter ships transfered out of a selected $num_ships.<p>".$loop_txt;

		if($transfer_counter > 0){
			$total_cost = $cost_per_transfer * $transfer_counter;
			$text .= "<p>Total cost transfer cost to you: $total_cost Credits";
			giveMoneyPlayer(-$total_cost);

			post_news("$user[login_name] transferred $transfer_counter ship(s) to $target[login_name]");
			send_message($target['login_id'],"<b>$transfer_counter</b> ship(s) have been transfered to you from <b class=b1>$user[login_name]</b>.");
			insert_history($user['login_id'],"Transfered <b>$transfer_counter</b> ship(s) to <b class=b1>$target[login_name]</b>.");
		}
	}
	print_page("Transfer Ship",$text);

}

$text .= <<<END
<p>Select ships to sign over to <em>$target[login_name]</em>, then click the 
<em>Send Ships</em> button.  Alternatively, to easily send one ship, simply 
click the <em>Sign Over</em> link.</p>
<p>Note: All cargo will be jettisoned from any ships getting transfered.</p>
<form action="ship_send.php" method="post" id="transfer_ships">
<table>

END;

db("select ship_name, class_name, location, fighters, max_fighters, shields, max_shields, config, ship_id from [game]_ships where login_id = '$user[login_id]' AND ship_id != '$user[ship_id]' order by class_name");
$ships = dbr(1);

if(!isset($ships)){	#ensure there are some ships to display
	$text .= "You only appear to have your command ship which you cannot transfer.";
} else {
	$text .= make_table(array("Ship Name","Ship Class","Location","Fighters","Shields", "Config"));
	while($ships) {
		$ships['fighters'] = $ships['fighters']." / ".$ships['max_fighters'];
		$ships['shields'] = $ships['shields']." / ".$ships['max_shields'];

		#remove the un-necassaries from the array. As well as their numerical counterparts (it's a multi-indexed array).
		unset($ships['max_fighters'], $ships['max_shields']);

		$ships['ship_id'] = "<input type=checkbox name=do_ship[$ships[ship_id]] value=$ships[ship_id]> - <a href=ship_send.php?target=$target[login_id]&do_ship[$ships[ship_id]]=$ships[ship_id]>Sign Over</a>";
		$text .= make_row($ships);
		$ships = dbr(1);
	}
}

$text .= <<<END
</table>
<p><input type="hidden" name="target" value="$target[login_id]" />
<input type="submit" name="submit" value="Send Ships" class="button" /> - 
<a href="#" onclick="tickInvert(&quot;transfer_ships&quot;);">Invert 
Ship Selection</a></p>
</form>
<p><a href="$self?target=$target[login_id]">Reload ship list</a></p>
END;

print_page("Transfer Ship Registration",$text);

?>
