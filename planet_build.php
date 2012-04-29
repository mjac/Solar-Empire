<?php

require_once('inc/user.inc.php');

sudden_death_check($user);

$planet_img = mt_rand(1,15);

$error_str = "";
// checks
get_star();
if($user['genesis'] < 1) {
	$error_str .= "You don't have a genesis device.";
} elseif($uv_planet_slots_use && $star['planetary_slots'] < 1) {
	$error_str .= "This system has no available planetary slots.";
} elseif($user['location'] == 1) {
	$error_str = "Cannot build planets in Sol.";
} elseif($user['ship_id'] == NULL && $user['login_id'] != ADMIN_ID) {
	$error_str = "You are not in command of a ship. How do you expect to force the alliegence of snivelling planet dwellers if you have no ship with which to suppress them?<p>You may not create a planet without a ship.";
} elseif($star['event_random'] > 0 && $user['login_id'] != ADMIN_ID) {
	$error_str = "You may not build a planet in system with a random event in.";
} elseif ($user['turns_run'] < $turns_before_planet_attack && !isset($letme) && $user['login_id'] != ADMIN_ID) {
	print_page("No landing","Cannot land, create or attack a planet within the first <b class=b1>$turns_before_planet_attack turns</b> of your account. This is to stop cheating.");
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
	if($user['login_id'] != ADMIN_ID){
		dbn("update ${db_name}_users set genesis = genesis - 1 where login_id = $user[login_id]");
	}
	charge_turns(5);

	if($user['clan_id']) {
		$clan_id = $user['clan_id'];
	} else {
		$clan_id = -1;
	}


	// build the new planet
	dbn("insert into ${db_name}_planets (planet_name,location,planet_type,login_id,login_name,clan_id,planet_img) values ('$planet_name', '$user[location]', '0', '$user[login_id]', '$user[login_name]', '$clan_id', '$planet_img')");
	$last_planet = mysql_insert_id();

	if ($uv_planet_slots_use) {
		dbn("UPDATE `{$db_name}_stars` SET `planetary_slots` = `planetary_slots` - 1 where `star_id` = $star[star_id]");
	}

	post_news("<b class=b1>$user[login_name]</b> created the planet <b class=b1>$planet_name</b>");
	$error_str .= "Your new planet is ready for you.<p>You may now <a href=planet.php?planet_id=$last_planet>Land</a> on your new planet.<p><br>";
}

print_page("Planet Built",$error_str);
?>
