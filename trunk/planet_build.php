<?php

require_once('inc/user.inc.php');

deathCheck($user);

$planet_img = mt_rand(1, 15);

$error_str = "";

getStar();

if ($user['genesis'] < 1) {
	$error_str .= "You don't have a genesis device.";
} elseif ($gameOpt['uv_planet_slots_use'] && $star['planetary_slots'] < 1) {
	$error_str .= "This system has no available planetary slots.";
} elseif ($userShip['location'] == 1 && !IS_ADMIN) {
	$error_str = "Players cannot build planets in Sol.";
} elseif ($user['ship_id'] === NULL && !IS_ADMIN) {
	$error_str = "You are not in command of a ship. How do you expect to force the alliegence of snivelling planet dwellers if you have no ship with which to suppress them?<p>You may not create a planet without a ship.";
} elseif ($user['turns_run'] < $gameOpt['turns_before_planet_attack'] && 
     !isset($letme) && !IS_ADMIN) {
	print_page("No landing","Cannot land, create or attack a planet within the first <b class=b1>$gameOpt[turns_before_planet_attack] turns</b> of your account. This is to stop cheating.");
} elseif($user['turns'] < 5) {
	$error_str = "You need 5 turns to create a planet.";
} elseif(empty($planet_name)) {
	get_var('Name your new planet','planet_build.php',"Please enter a name for your new planet:",'planet_name','');
} elseif(strlen($planet_name) < 3) {
	$rs = "<p><a href=javascript:history.back()>Try Again</a>";
	print_page("Invalid Name","That is not a valid name. Must have more than three characters.");
} else {
	$planet_name = correct_name($planet_name);
	if(!$planet_name || $planet_name == " " || $planet_name == "") {
		$rs = "<p><a href=javascript:history.back()>Try Again</a>";
		print_page("Invalid Name","That is not a valid name.");
	}

	// remove gen device, but not from admin.
	if (!IS_ADMIN){
		$db->query("update [game]_users set genesis = genesis - 1 where login_id = $user[login_id]");
	}
	giveTurnsPlayer(-5);

	if($user['clan_id']) {
		$clan_id = $user['clan_id'];
	} else {
		$clan_id = -1;
	}


	// build the new planet
	$last_planet = newId('[game]_planets', 'planet_id');
	$db->query('INSERT INTO [game]_planets (planet_id, planet_name, location, ' .
	 'planet_type, login_id, planet_img) values (%u, \'%s\', %u, 0, %u, %u)',
	 array($last_planet, $db->escape($planet_name), $userShip['location'],
	 $user['login_id'], $planet_img));
	

	if ($gameOpt['uv_planet_slots_use']) {
		$db->query('UPDATE [game]_stars SET planetary_slots = ' .
		 'planetary_slots - 1 WHERE star_id = %u', array($star['star_id']));
	}

	post_news("$user[login_name] created the planet $planet_name");
	$error_str .= "Your new planet is ready for you.<p>You may now <a href=planet.php?planet_id=$last_planet>Land</a> on your new planet.<p><br />";
}

print_page("Planet Built",$error_str);

?>
