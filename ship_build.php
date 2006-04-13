<?php

require_once('inc/user.inc.php');

if (!deathCheck($user) && $userShip['location'] != 1) {
	print_page('Not in Sol', '<p>You are not in Star System #1</p>');
}

$cQuery = $db->query('SELECT COUNT(*) FROM [game]_ships WHERE login_id = %u',
 $user['login_id']);
$numships = (int)current($db->fetchRow($cQuery));


$rs = "<p><a href=earth.php>Back to Earth</a>";
$rs .= "<br /><a href=\"shop_ship.php\">Return to Ship Shop</a>";
$error_str = "";

$ship_types = load_ship_types();
$ship_stats = isset($mass)? $ship_types[$mass] : $ship_types[$ship_type];
$take_flag = 1;

if(empty($ship_stats) && $user['game_login_count'] != '0'){
	print_page("Error","Admin has set the game up so as that ship is not available for purchase.");
}

if (!isset($ship_stats['config'])) {
	$ship_stats['config'] = "";
}

//Bulk Purchase of ships
if (isset($mass)) {
	if ($ship_stats['type'] != "Freighter") { #check to ensure are only able to bulk buy merchants
		$error_str = "<b>Seatogu's Spacecraft Emporium</b> does not offer facilities for mass purchasing of any ship type other than Freighters.";
	} elseif ($num < 1) {	#check to allow user to enter the number of ships they want to buy.
		$maxPurchase = $gameOpt['max_ships'] - $numships;
		if($maxPurchase * $ship_stats['cost'] > $user['cash']){
			$maxPurchase = floor($user['cash'] / $ship_stats['cost']);
		}

		$error_str .= <<<END
<h1>Bulk purchase</h1>
<h2>$ship_stats[name] quantity?</h2>
<form action="$self" method="post">
	<input type="hidden" name="mass" value="$mass" />
	<input name="num" value="$maxPurchase" size="3" class="text" />
	<input type="submit" value="Submit" class="button" /></form>
</form>

END;
	} elseif ($numships + $num > $gameOpt['max_ships']) {
		$error_str = "You already own <b>$numships</b> ship(s). The Admin has set the max number of ships players may have to <b>$gameOpt[max_ships]</b>.";
	} elseif(!isset($ship_name)) {
		$rs = "<p><a href=earth.php>Back to Earth.</a>";
		$rs .= "<br /><a href=\"shop_ship.php\">Return to Ship Shop</a>";
		get_var('Name your new ships','ship_build.php',"Your fleet presently consists of <b>$numships</b> ship(s).<br />When naming your new ships they will be given a number after the name you have entered. (3-25 Characters)",'ship_name','');
	} elseif (strlen($ship_name) < 3) {
		$rs .= "<p><a href=javascript:history.back()>Try Again</a>";
		print_page("Error","Ship name must be at least three characters.");
	} else { #do the processing.
		$x1 = $num * $ship_stats['cost'];
		if (!giveMoneyPlayer(-$x1)) {
			print_page("Error", "You cannot afford that.");
		}

		$ship_name = correct_name($ship_name);

		$zeros = floor(log($num) / log(10)) + 1;
		for ($s = 1; $s <= $num; ++$s) {
			$ship_stats['ship_name'] = sprintf("%s %0{$zeros}d", $ship_name, $s);

			make_ship($ship_stats, $user);
		}

		$x2 = $ship_name . ' ' . str_repeat('0', $zeros - 1) . '1';
		$x3 = $ship_name . ' ' . $num;
		$x4 = $numships + $num;
		$error_str .= "<b>$num</b> <b class=b1>$ship_stats[name]</b>s brought for a total price of <b>$x1</b> Credits.<br /> The ships have been named: <p><b>$x2</b>...<b>$x3</b> consecutively.<p>Your fleet now consists of <b>$x4</b> ships.";
	}

	print_page('Bulk buying', $error_str);
}


//The Brob Test!!!
if($user['one_brob'] > 0 && !isset($duplicate) && !isset($mass)) {
	db("select ship_id from [game]_ships where login_id = '$user[login_id]' AND config REGEXP 'oo'");
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


if ($numships >= $gameOpt['max_ships']) {
	$error_str = "You already own <b>$numships</b> ship(s).	The admin has set the max number of ships players may have. The limit is <b>$gameOpt[max_ships]</b>.";
} elseif ($got_a_brob == 1 && shipHas($ship_stats, 'oo')) {
	$error_str .= "You are already the proud owner of a flagship.<br />Due to galactic conventions, to keep the universe fairly safe you're only allowed one at a time.<br />Also, when you do loose this present one, your next one will cost twice the amount it of the last one.";
} elseif(!isset($ship_name)) {
	$rs = "<p><a href=\"shop_ship.php\">Return to Ship Shop</a>";
	get_var('Name your new ship','ship_build.php',"Your fleet presently consists of <b>$numships</b> ships.<br />Please enter a name for your new <b class=b1>$ship_stats[name]</b>:(30 Char Max)",'ship_name','');
} elseif (strlen($ship_name) < 3) {
		$rs .= "<p><a href=javascript:history.back()>Try Again</a>";
		print_page("Error","Ship name must be at least three characters.");
} else {
	if (!giveMoneyPlayer(-$ship_stats['cost'])) {
		print_page("Error", "You cannot afford that.");
	}

	$ship_stats['ship_name'] = correct_name($ship_name);

	$newId = make_ship($ship_stats, $user);

	$oo_str = "";
	if (shipHas($ship_stats, 'oo')) {
		if($user['one_brob']){
			$db->query("update [game]_users set one_brob = one_brob + one_brob where login_id = '$user[login_id]'");
		} else {
			$db->query("update [game]_users set one_brob = 2 where login_id = '$user[login_id]'");
		}
		$oo_str = "<p>A word of warning: You may only own one Flagship class ship at a time.<br />Also: Each Flagship class ship you buy will be twice as expensive as the last time.<br />This is a galactic consensus to help keep them out of the hands of reckless types.";
	}

	if ($user['ship_id'] === NULL) {
		$user['ship_id'] = $newId;
		$db->query('UPDATE [game]_users SET ship_id = %u WHERE ' .
		 'login_id = %u', array($newId, $user['login_id']));
	}

	$error_str .= <<<END
<h1>Purchase complete</h1>
<p>Your new <strong>$ship_stats[name]</strong> has just been delivered at
cost of <strong>$ship_stats[cost] credits</strong>.</p>
<p><a href="system.php?command=$newId">Take command</a> of your new ship</p>

END;

}

$error_str .= "<p><a href=earth.php>Return to Earth</a></p>\n";

checkShip();

// print page
print_page("Ship Built",$error_str);

?>
