<?php

require_once('inc/user.inc.php');

$filename = 'bm_ships.php';

sudden_death_check($user);
$rs = "";
$error_str = "";

if(isset($from_0)){
 	$rs = "<p><a href=black_market.php>Return to Blackmarket</a>";
}
$rs .= "<p><a href=location.php>Close Contact</a>";


#Blackmarket Controls

db("select * from ${db_name}_bmrkt where location = '$user[location]' && (bmrkt_type = 1 || bmrkt_type = 0)");
$bmrkt = dbr();

if (!isset($bmrkt)) {
	print_page("Blackmarket","You may not contact a blackmarket that is not in the same system as you are in. Stop playing with the URL's'","?research=1");
} elseif($flag_research != 1) {
	print_page("Error","Admin in his/her near infinite wisdom has disabled the Blackmarket","?research=1");
}




#
#Beginning of Blackmarket ship purchase
#

if (isset($ship_type)){
	$ship_types = load_ship_types();
	$ship_stats = $ship_types[$ship_type];
	$take_flag = 1;

	if(!isset($ship_stats) && $user['game_login_count'] != '0'){
		print_page("Error","That ships is not available for purchase at this time.","?research=1");
	}

	if(!isset($ship_stats['config'])) {
		$ship_stats['config'] = "";
	}

	//begin ship checks

	if($user['one_brob'] && $new_ship_config['oo']) {
		db("select ship_id from ${db_name}_ships where login_id = '$user[login_id]' && config REGEXP 'oo' && (class_name REGEXP 'Brob' || class_name REGEXP 'Battlestar')");
		$results = dbr();

		if($results){
			print_page("Flagship","You may only own one <b>Flagship</b> class ship at a time.");
		} else {
			$ship_stats['cost'] = $ship_stats['cost'] * $user['one_brob'];
			$ship_stats['tcost'] = $ship_stats['tcost'] * $user['one_brob'];
		}
	}

	if ($numships[0] >= $max_ships) {
		$error_str = "You already own <b>$numships[0]</b> ship(s). The admin has set the max number of ships players may have. The limit is <b>$max_ships</b>.";
	}elseif($ship_type == 1 || $ship_type == 0) {
		$error_str = "You may not buy this ship type.";
	}elseif($user['cash'] < $ship_stats['cost']) {
		$error_str = "You can't afford a <b class=b1>$ship_stats[name]</b>.";
	}elseif($user['tech'] < $ship_stats['tcost']) {
		$error_str = "You don't have enough Tech. Support Units for a <b class=b1>$ship_stats[name]</b>.";
	} elseif(!isset($ship_name)) {
		$rs = "<p><a href=bm_ships.php>Return to Blackmarket Ships</a>";
		get_var('Name your new ship','bm_ships.php',"Your fleet presently consists of <b>$num_ships[total_ships]</b> ships.<br>Please enter a name for your new <b class=b1>$ship_stats[name]</b>:(20 Char Max)",'ship_name','');
	} else {
		take_cash($ship_stats['cost']);
		take_tech($ship_stats['tcost']);

		// remove old escape pods
		dbn("delete from ${db_name}_ships where login_id = '$user[login_id]' && class_name REGEXP 'Escape'");

		$ship_name = correct_name($ship_name);

	// build the new ship
		$q_string = "insert into ${db_name}_ships (";
		$q_string = $q_string . "ship_name,login_id,login_name,location,clan_id,shipclass,class_name,class_name_abbr,fighters,max_fighters,max_shields,cargo_bays,mine_rate_metal,mine_rate_fuel,config,size,upgrades,move_turn_cost,point_value,num_pc,num_ot,num_sa,num_dt,num_ew";
		$q_string = $q_string . ") values(";
		$q_string = $q_string . "'$ship_name','$login_id','$user[login_name]',$user[location],'$user[clan_id]','$ship_stats[type_id]','$ship_stats[name]','$ship_stats[class_abbr]','$ship_stats[fighters]','$ship_stats[max_fighters]','$ship_stats[max_shields]','$ship_stats[cargo_bays]','$ship_stats[mine_rate_metal]','$ship_stats[mine_rate_fuel]','$ship_stats[config]','$ship_stats[size]','$ship_stats[upgrades]','$ship_stats[move_turn_cost]',$ship_stats[point_value],$ship_stats[num_pc],$ship_stats[num_ot],$ship_stats[num_sa],$ship_stats[num_dt],$ship_stats[num_ew])";
		dbn($q_string);

		$new_ship_id = mysql_insert_id();

		#the game goes all screwy if a player get's hold of ship_id 1.
		if($new_ship_id == 1){
			$new_ship_id = 2;
			dbn("update ${db_name}_ships set ship_id = '2' where ship_id = '1'");
		}

		dbn("update ${db_name}_users set ship_id = '$new_ship_id' where login_id = '".$user['login_id']."'");
		$user['ship_id'] = $new_ship_id;
		db("select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
		$user_ship = dbr();
		empty_bays($user_ship);

		$oo_str = "";
		if(ereg("oo",$ship_stats['config'])) {
			if($user['one_brob']){
				dbn("update ${db_name}_users set one_brob = one_brob + one_brob where login_id = '$user[login_id]'");
			} else {
				dbn("update ${db_name}_users set one_brob = 2 where login_id = '$user[login_id]'");
			}
			$oo_str .= "<p>A word of warning: You may only own one Flagship class ship at a time.<br>Also, each Flagship class ship you buy will be twice as expensive as the last time.<br>This is a galactic consensus to help keep them out of the hands of reckless types.";
		}

		$error_str .= "<br><p>Your payment of <b>$ship_stats[cost]</b> Credits and <b>$ship_stats[tcost]</b> Tech. Support Units has been accepted, and your <b>$ship_stats[name]</b> has been delivered.<br>";
		$error_str .= "<p>May your new <b>$ship_stats[name]</b> bring you strength and your enemies' ruin!".$oo_str;
	}

	print_page("Blackmarket Ship Purchased",$error_str);

}#end isset


#
#End of bm ship purchase
#

print_header("Blackmarket Ships");
print_status();
echo $error_str;


echo "<p>Welcome to <b>Reilly's Shipbuilders</b>, a division of <b class=b1>$bmrkt[bm_name]'s</b> Blackmarket.";
echo "<br><p>If it's cutting edge ships you want, you've found the right place.<br>";

#list all alient ships.
$ship_types = load_ship_types();
echo "<p>Available Blackmarket Ships:";
#echo make_table(array("Ship Name","Abbrv.","Cash Cost","Tech Cost","Type"));

foreach($ship_types as $num => $ship_stats){
	if($ship_stats['tcost'] == 0){ //skip non-tech ships.
		continue;
	} else {
		if(ereg("oo",$ship_stats['config'])) {
			if($user['one_brob']) {
				$ship_stats['cost'] = $ship_stats['cost'] * $user['one_brob'];
				$ship_stats['tcost'] = $ship_stats['tcost'] * $user['one_brob'];
			}
			$ab_text = "";
		} else {
			$ab_text = "<a href=bm_ships.php?mass=$ship_stats[type_id]>Buy Many</a>";
		}

		$ship_stats['cost'] = number_format($ship_stats['cost']);

		if(!isset($ships_for_sale[$ship_stats['type']])){
			$ships_for_sale[$ship_stats['type']] = "";
		}

		$txt = make_row(array("<a href=bm_ships.php?ship_type=$ship_stats[type_id]>$ship_stats[name]</a>", "$ship_stats[class_abbr]", "<b>$ship_stats[cost]</b>", "<b>$ship_stats[tcost]</b>", "<a href=bm_ships.php?ship_type=$ship_stats[type_id]>Buy One</a>", $ab_text, popup_help("ship_info=1&shipno=$ship_stats[type_id]",300,400)));
		$ships_for_sale[$ship_stats['type']] .= $txt;
	}
}

if(empty($ships_for_sale)){
	echo "<p>There are no ships avaiable from this blackmarket at this time.";

} else {

	foreach($ships_for_sale as $class => $str){

		echo "<p>{$class}s available:";
		if(empty($str)){
			echo "<br><b>None</b>";
		} else {
			echo make_table(array("Ship Name","Abbrv.","Credit Cost","Tech Unit Cost"));
			echo stripslashes($str);
			echo "</table>";
		}
	}

}

#Blackmarket Ships

echo "<p><a href=help.php?ship_info=-1&shipno=-2 target=_blank>List all information for all Blackmarket ships.</a>";

print_footer();

?>
