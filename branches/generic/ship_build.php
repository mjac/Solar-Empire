<?php

require_once('inc/user.inc.php');


db("SELECT count(*) as ships FROM ${db_name}_ships WHERE login_id = '$login_id'");
$temp_123 = dbr(1);
$numships = $temp_123['ships'];

if($numships < 1 && $sudden_death && $user['login_id'] != ADMIN_ID && $user['last_login'] != 0) {
	print_page("Sudden Death","You have no ship, and this game is Sudden Death. As such you are out of the game.");
}

if($user['location'] != 1) {
	print_page("Error","You are unable to buy ships from here. Ships can only be brought from Earth (System #1).");
}

$rs = "<p><a href=earth.php>Back to Earth</a>";
$rs .= "<br><a href=earth.php?ship_shop=1>Return to Ship Shop</a>";
$error_str = "";

$ship_types = load_ship_types();
if(isset($mass)){
	$ship_stats = $ship_types[$mass];
	$take_flag = 1;
} elseif($user['game_login_count'] > 0) {
	$ship_stats = $ship_types[$ship_type];
	$take_flag = 1;
}

if(empty($ship_stats) && $user['game_login_count'] != '0'){
	print_page("Error","Admin has set the game up so as that ship is not available for purchase.");
}

if(!isset($ship_stats['config'])) {
	$ship_stats['config'] = "";
}

#build users first ship
if($user['ship_id'] == NULL && $user['game_login_count'] == 0) {
	if(!$start_ship) {
		$start_ship[0] = 4;
	}

	db("select * from ${db_name}_ship_types where type_id = '$start_ship'");
	$ship_stats = dbr(1);

	//SetCookie("login_id",$login_id,time()+2592000);
	//SetCookie("session_id",$session_id,0);
	//SetCookie("db_name",$db_name,0);

	$ship_name = correct_name($ship_name);

	if(empty($ship_name)){
		$ship_name = "Un-Named";
	}

	// build the new ship
	$q_string = "insert into ${db_name}_ships (";
	$q_string = $q_string . "ship_name,login_id,login_name,clan_id,shipclass,class_name,class_name_abbr,fighters,max_fighters,max_shields,cargo_bays,mine_rate_metal,mine_rate_fuel,config,size,upgrades,move_turn_cost,point_value";
	$q_string = $q_string . ") values(";
	$q_string = $q_string . "'$ship_name','$login_id','$user[login_name]','$user[clan_id]','$ship_stats[type_id]','$ship_stats[name]','$ship_stats[class_abbr]','$ship_stats[fighters]','$ship_stats[max_fighters]','$ship_stats[max_shields]','$ship_stats[cargo_bays]','$ship_stats[mine_rate_metal]','$ship_stats[mine_rate_fuel]','$ship_stats[config]','$ship_stats[size]','$ship_stats[upgrades]','$ship_stats[move_turn_cost]','$ship_stats[point_value]')";
	// echo $q_string;
	dbn($q_string);

	$new_ship_id = mysql_insert_id();

	dbn("update ${db_name}_users set ship_id = '$new_ship_id', game_login_count = game_login_count + 1 where login_id = '$user[login_id]'");
	$user['ship_id'] = userShip($new_ship_id);

	if(ereg("oo",$ship_stats['config'])) {
		if($user['one_brob'] > 0){
			dbn("update ${db_name}_users set one_brob = one_brob + one_brob where login_id = '$user[login_id]'");
		} else {
			dbn("update ${db_name}_users set one_brob = 2 where login_id = '$user[login_id]'");
		}
	}
	$rs = "<p><a href=location.php>Click To Play</a>";
	if($p_user['num_games_joined'] <= 1){ #first game played on this server
		print_page("First Ship Brought","The paperwork is completed, and the ship is now yours.<br><br>Seeing as this is your first game on this server, maybe you'll want to take a look at the <a href=help.php?started=1 target=_blank>Getting Started</a> section of the Help (link will open a new window).<br><br><b>Hint:</b> You can access that, and all other aspects of Help for the game by clicking the <b class=b1>Help</b> link thats in the left column.");
	} else {
		print_page("First Ship Brought","The paperwork is completed, and the ship is now yours.<br>Have a nice game.");
	}
}



//Bulk Purchase of ships
if(isset($mass)) {
	#ensure users don't enter equations in place of numbers.
	settype($num, "integer");

	if($ship_stats['type'] != "Freighter") { #check to ensure are only able to bulk buy merchants
		$error_str = "<b>Seatogu's Spacecraft Emporium</b> does not offer facilities for mass purchasing of any ship type other than Freighters.";
	}elseif ($num < 1) {	#check to allow user to enter the number of ships they want to buy.
		$t7676 = $max_ships - $numships;
		if($t7676*$ship_stats['cost'] > $user['cash']){
			$t7676 = floor($user['cash'] / $ship_stats['cost']);
		}
		$error_str .= "Enter Number of <b class=b1>$ship_stats[type]</b>s to purchase:";
		$error_str .= "<form name=mass_buy action=ship_build.php method=post>";
		$error_str .= "<input type=hidden name=mass value='$mass'>";
		$error_str .= "<input name=num value='$t7676' size=3>";
		$error_str .= ' <input type=submit value=Submit></form>';
	}elseif ($numships + $num > $max_ships) { # check to ensure tehy are not trying to buy too many ships
		$error_str = "You already own <b>$numships</b> ship(s). The Admin has set the max number of ships players may have to <b>$max_ships</b>.";
	}elseif($user[cash] < $ship_stats[cost]*$num) { #check to see if the user can afford them
		$error_str = "You can't afford <b>$num</b> <b class=b1>$ship_stats[name]</b>s";
	} elseif(!isset($ship_name)) { #confirm purchase.
		$rs = "<p><a href=earth.php>Back to Earth.</a>";
		$rs .= "<br><a href=earth.php?ship_shop=1>Return to Ship Shop</a>";
		get_var('Name your new ships','ship_build.php',"Your fleet presently consists of <b>$numships</b> ship(s).<br>When naming your new ships they will be given a number after the name you have entered. (3-25 Characters)",'ship_name','');
	} elseif (strlen($ship_name) < 3) {
		$rs .= "<p><a href=javascript:history.back()>Try Again</a>";
		print_page("Error","Ship name must be at least three characters.");
	} else { #do the processing.
		$ship_name = correct_name($ship_name);
		$quotes = $ship_name;
		// remove old escape pods
		dbn("delete from ${db_name}_ships where login_id = '$user[login_id]' && class_name REGEXP 'Escape'");

		for($s=1;$s<=$num;$s++){
			if ($s<10) {
				$s_name = $ship_name." 0".$s;
			} else {
				$s_name = $ship_name." ".$s;
			}
			$q_string = "insert into ${db_name}_ships (";
			$q_string = $q_string . "ship_name,login_id,login_name,clan_id,shipclass,class_name,class_name_abbr,fighters,max_fighters,max_shields,cargo_bays,mine_rate_metal,mine_rate_fuel,config,size,upgrades,move_turn_cost,point_value";
			$q_string = $q_string . ") values(";
			$q_string = $q_string . "'$s_name','$login_id','$user[login_name]','$user[clan_id]','$ship_stats[type_id]','$ship_stats[name]','$ship_stats[class_abbr]','$ship_stats[fighters]','$ship_stats[max_fighters]','$ship_stats[max_shields]','$ship_stats[cargo_bays]','$ship_stats[mine_rate_metal]','$ship_stats[mine_rate_fuel]','$ship_stats[config]','$ship_stats[size]','$ship_stats[upgrades]','$ship_stats[move_turn_cost]','$ship_stats[point_value]')";
			// echo $q_string;
			dbn($q_string);
		}

		#puts the user into the newest ship, but only if they are in a EP, or ship destroyed.
		if($user_ship['shipclass'] < 3) {
			$new_ship_id = mysql_insert_id();
			dbn("update ${db_name}_users set ship_id = '$new_ship_id' where login_id = '$user[login_id]'");
			$user['ship_id'] = $new_ship_id;
			$user_ship = userShip($new_ship_id);
		}
		$x1 = $num*$ship_stats['cost'];
		$x2 = $quotes." 01";
		$x3 = $quotes." $num";
		$x4 = $numships + $num;
		take_cash($x1);
		$error_str .= "<b>$num</b> <b class=b1>$ship_stats[name]</b>s brought for a total price of <b>$x1</b> Credits.<br> The ships have been named: <p><b>$x2</b>...<b>$x3</b> consecutively.<p>Your fleet now consists of <b>$x4</b> ships.";
	}
	$rs = "<p><a href=earth.php?ship_shop=1>Return to Ship Shop</a>";
	$rs .= "<p><a href=location.php>Back to Star System</a>";
	print_page("Bulk Buying",$error_str);
}


//The Brob Test!!!
if($user['one_brob'] > 0 && !isset($duplicate) && !isset($mass)) {
	db("select ship_id from ${db_name}_ships where login_id = '$user[login_id]' && config REGEXP 'oo'");
	$results = dbr();
	if($results){
		$got_a_brob = 1;
	} else {
		$got_a_brob = 0;
		$ship_stats['cost'] = $ship_stats['cost'] * $user['one_brob'];
	}
} else {
	$got_a_brob = 0;
}


if ($numships >= $max_ships) {
	$error_str = "You already own <b>$numships</b> ship(s).	The admin has set the max number of ships players may have. The limit is <b>$max_ships</b>.";
}elseif($got_a_brob == 1 && eregi("oo",$ship_stats['config'])) {
	$error_str .= "You are already the proud owner of a flagship.<br>Due to galactic conventions, to keep the universe fairly safe you're only allowed one at a time.<br>Also, when you do loose this present one, your next one will cost twice the amount it of the last one.";
}elseif($ship_type == 1 || $ship_type == 0) {
	$error_str = "You may not buy this ship type.";
}elseif($user['cash'] < $ship_stats['cost']) {
	$error_str = "You can't afford a <b class=b1>$ship_stats[name]</b>";
} elseif(!isset($ship_name)) {
	$rs = "<p><a href=earth.php?ship_shop=1>Return to Ship Shop</a>";
	get_var('Name your new ship','ship_build.php',"Your fleet presently consists of <b>$numships</b> ships.<br>Please enter a name for your new <b class=b1>$ship_stats[name]</b>:(30 Char Max)",'ship_name','');
	} elseif (strlen($ship_name) < 3) {
		$rs .= "<p><a href=javascript:history.back()>Try Again</a>";
		print_page("Error","Ship name must be at least three characters.");
} else {
	take_cash($ship_stats['cost']);

	// remove old escape pods
	dbn("delete from ${db_name}_ships where login_id = '$user[login_id]' && class_name REGEXP 'Escape'");

	$ship_name = correct_name($ship_name);

	// build the new ship
	$q_string = "insert into ${db_name}_ships (";
	$q_string = $q_string . "ship_name,login_id,login_name,clan_id,shipclass,class_name,class_name_abbr,fighters,max_fighters,max_shields,cargo_bays,mine_rate_metal,mine_rate_fuel,config,size,upgrades,move_turn_cost,point_value";
	$q_string = $q_string . ") values(";
	$q_string = $q_string . "'$ship_name','$login_id','$user[login_name]','$user[clan_id]','$ship_stats[type_id]','$ship_stats[name]','$ship_stats[class_abbr]','$ship_stats[fighters]','$ship_stats[max_fighters]','$ship_stats[max_shields]','$ship_stats[cargo_bays]','$ship_stats[mine_rate_metal]','$ship_stats[mine_rate_fuel]','$ship_stats[config]','$ship_stats[size]','$ship_stats[upgrades]','$ship_stats[move_turn_cost]','$ship_stats[point_value]')";
	dbn($q_string);

	$new_ship_id = mysql_insert_id();

	#the game goes all screwy if a player get's hold of ship_id 1.
	if($new_ship_id == 1){
		$new_ship_id = 2;
		dbn("update ${db_name}_ships set ship_id = '2' where ship_id = '1'");
	}

	dbn("update ${db_name}_users set ship_id = '$new_ship_id' where login_id = '".$user['login_id']."'");
	$user['ship_id'] = $new_ship_id;
	$user_ship = userShip($new_ship_id);


	$oo_str = "";
	if(ereg("oo",$ship_stats['config'])) {
		if($user['one_brob']){
			dbn("update ${db_name}_users set one_brob = one_brob + one_brob where login_id = '$user[login_id]'");
		} else {
			dbn("update ${db_name}_users set one_brob = 2 where login_id = '$user[login_id]'");
		}
		$oo_str = "<p>A word of warning: You may only own one Flagship class ship at a time.<br>Also: Each Flagship class ship you buy will be twice as expensive as the last time.<br>This is a galactic consensus to help keep them out of the hands of reckless types.";
	}

	$error_str .= "Your payment of <b>$ship_stats[cost]</b> has been accepted, and your new <b>$ship_stats[name]</b>, with complementary Escape Pod has been delivered.<br> Have a nice day.";
	$oo_str .= "";
	if($user_ship['fighters'] < $user_ship['max_fighters'] && $user_ship['max_fighters'] > 0){
		$error_str .= "<p><a href='equip_shop.php?buy=1'>Buy Some Fighters</a>";
	}
	if($user_ship['upgrades'] > 0){
		$error_str .= "<br><a href='upgrade.php'>Purchase Some Upgrades</a>";
	}

}
	$error_str .= "<p><a href=earth.php>Return to Earth</a>";

// print page
print_page("Ship Built",$error_str);
?>