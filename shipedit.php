<?php
#edit ship_types table for current game
require_once('inc/user.inc.php');
$out = "";

if($user['login_id'] == ADMIN_ID || $user['login_id'] == OWNER_ID) {
	if($editshiptype == -1) {
		$out .= "<p>Ship listing:<br>";
		$cur_ship = null;
		$info = null;

		db("SELECT * FROM ${db_name}_ship_types WHERE type_id != 1 ORDER BY type_id");

		$out .= make_table(array("Name","Cost","Tech Cost","Fighters","Shields","Cargo Cap","Mine Rate","Configuration","Upgrade Pods","Base Move Cost","Point Value","From","Action"));

		while($cur_ship = dbr(1))		{
			$info['Name'] = $cur_ship['name'];
			$info['Cost'] = $cur_ship['cost'];
			$info['Tech'] = $cur_ship['tcost'];
			$info['Fighters'] = "$cur_ship[fighters] / $cur_ship[max_fighters]";
			$info['Shields'] = $cur_ship['max_shields'];
			$info['Cargo'] = $cur_ship['cargo_bays'];
			$info['Mining'] = $cur_ship['mine_rate_metal'] + $cur_ship['mine_rate_fuel'];
			$info['Configuration'] = $cur_ship['config'];
			$info['Pods'] = $cur_ship['upgrades'];
			$info['move_cost'] = $cur_ship['move_turn_cost'];
			$info['Point_value'] = $cur_ship['point_value'];
			if($cur_ship['auction'] == 1){
				$info['auction_only'] = "Auction";
			} else {
				$info['auction_only'] = "Sol";
			}

			if($cur_ship['type_id'] != 1 && $cur_ship['type_id'] != 2) {
				$info['Action'] = "<a href=$PHP_SELF?editshiptype=$cur_ship[type_id]>Edit</a> - <a href=$PHP_SELF?editshiptype=-$cur_ship[type_id]>Remove</a>";
			} else {
				$info['Action'] = "<a href=$PHP_SELF?editshiptype=$cur_ship[type_id]>Edit</a> - Remove</a>";
			}
			$out .= make_row($info);
		}
		$out .= "</table></p>";
		$out .= "<a href=$PHP_SELF?editshiptype=-2>Add New Ship</a>";
		print_page("Ship Editor",$out);

	//Add ship to db and redirect
	} elseif($editshiptype == -2 && isset($submit)) {
		if($auction == 'on') {
			$auction = 1;
		} else {
			$auction = 0;
		}
		dbn("INSERT INTO ${db_name}_ship_types VALUES ('', '$name', '$type', '$class_abbr', '$cost', '$tcost', '$fighters', '$max_fighters', '$max_shields', '$cargo_bays', '0', '$mining_rate', '$descr', '$size', '$config', '$upgrades', '$auction', '$move_turn_cost', '$point_value', '$num_pc', '$num_ot', '$num_dt', '$num_sa', '$num_ew')");
		$out = "<script>window.location=(\"$PHP_SELF?editshiptype=-1\");</script>";
		print_page("Adding Ship",$out);

	} elseif($editshiptype == -2) {
		//Create form to add ship
		$out = "
		<form method=\"post\">
		<input type=hidden name=submit value=true>
		FILL IN <b>ALL</b> TEXT FIELDS!!!!<br><br>
		Name: <input type=text name=name size=30><br><br>
		Type: (Usually Freighter, Battleship, or Carrier) <input type=text name=type size=30><br><br>
		Class Abbr: <input type=text name=class_abbr size=10><br><br>
		Cost: <input type=text name=cost size=10><br><br>
		Tech Unit Cost: <input type=text name=tcost size=10><br><br>
		Fighters: <input type=text name=fighters size=6> / Max Fighters: <input type=text name=max_fighters size=16><br><br>
		Shields: <input type=text name=max_shields size=6><br><br>
		Cargo Bays: <input type=text name=cargo_bays size=6><br><br>
		Mining Rate: <input type=text name=mining_rate size=5><br><br>
		Description: <textarea rows=5 cols=30 name=descr></textarea><br><br>
		Size (1 being smallest, 6 being gigantic): <input type=text name=size size=1><br><br>
		Config (Seperate with colon aka tw:na:oo): <input type=text name=config size=20><br>
		<a href=help.php?ship_info=1&shipno=-1&specials=1 target=_blank>Configuration Help</a> (Scroll down to Specials Index)<br><br>
		Upgrade Pods: <input type=text name=upgrades size=5><br><br>
		Auction only? <input type=checkbox name=auction><br><br>
		Move Turn Cost: <input type=text name=move_turn_cost size=2 maxlength=2><br><br>
		Point Value (For scoring purposes): <input type=text name=point_value size=3><br><br>
		Plasma Cannons: <input type=text name=num_pc size=2><br><br>
		Offensive Turrets: <input type=text name=num_ot size=2><br><br>
		Defensive Turrets: <input type=text name=num_dt size=2><br><br>
		Silicon Armor Modules: <input type=text name=num_sa size=2><br><br>
		Electronic Warfare Modules: <input type=text name=num_ew size=2><br><br>
		<input type=submit name=NewShip value=\"Create\"><br><br>
		</form>
		";
		$out .= "<a href=$PHP_SELF?editshiptype=-1>Return to Ship Editor</a>";
		print_page("Add New Ship", $out);

	//Code to purge ship from database and redirect
	} elseif($editshiptype < -2) {
		$editshiptype = abs($editshiptype);

		dbn("DELETE FROM ${db_name}_ship_types WHERE type_id = $editshiptype");
		$out = "<script>window.location=(\"$PHP_SELF?editshiptype=-1\");</script>";

		print_page("Removing Ship", $out);

	//Code to modify ship in database and redirect
	} elseif($editshiptype > 0 && isset($submit)) {
		dbn("DELETE FROM ${db_name}_ship_types WHERE type_id = ".$editshiptype);
		if(isset($auction)) {
			$auction = 1;
		} else {
			$auction = 0;
		}
		dbn("INSERT INTO ${db_name}_ship_types VALUES ('', '$name', '$type', '$class_abbr', '$cost', '$tcost', '$fighters', '$max_fighters', '$max_shields', '$cargo_bays', '0', '$mining_rate', 'addslashes($descr)', '$size', '$config', '$upgrades', '$auction', '$move_turn_cost', '$point_value', '$num_pc', '$num_ot', '$num_dt', '$num_sa', '$num_ew')");
		$out = "<script>window.location=(\"$PHP_SELF?editshiptype=-1\");</script>";
		print_page("Adding Ship",$out);

	//Code to create form to modify ship
	} elseif($editshiptype > 0) {
		db("SELECT * FROM ${db_name}_ship_types WHERE type_id = ".$editshiptype);
		$defaults=dbr(1);

		$out = "
		<form method=\"post\">
		<input type=hidden name=submit value=true>
		DO NOT LEAVE A TEXT FIELD BLANK!!!!<br>
		Name: <input type=text name=name size=30 value='$defaults[name]'><br><br>
		Type: (Usually Freighter, Battleship, or Carrier) <input type=text name=type size=30 value='$defaults[type]'><br><br>
		Class Abbr: <input type=text name=class_abbr size=10 value='$defaults[class_abbr]'><br><br>
		Cost: <input type=text name=cost size=10 value='$defaults[cost]'><br><br>
		Tech Unit Cost: <input type=text name=tcost size=10 value='$defaults[tcost]'><br><br>
		Fighters: <input type=text name=fighters size=6 value='$defaults[fighters]'> / Max Fighters: <input type=text name=max_fighters size=16 value='$defaults[max_fighters]'><br><br>
		Shields: <input type=text name=max_shields size=6 value='$defaults[max_shields]'><br><br>
		Cargo Bays: <input type=text name=cargo_bays size=6 value='$defaults[cargo_bays]'><br><br>
		Mining Rate: <input type=text name=mining_rate size=5 value='".($defaults['mine_rate_metal'] + $defaults['mine_rate_fuel'])."'><br><br>
		Description: <textarea rows=5 cols=30 name=descr>".htmlentities($defaults['descr'])."</textarea><br><br>
		Size (1 being smallest, 6 being gigantic): <input type=text name=size size=1 value='$defaults[size]'><br><br>
		Config (Seperate with colon aka tw:na:oo): <input type=text name=config size=20 value='$defaults[config]'><br>
		<a href=help.php?ship_info=1&shipno=-1&specials=1 target=_blank>Configuration Help</a> (Scroll down to Specials Index)<br><br>
		Upgrade Pods: <input type=text name=upgrades size=5 value='$defaults[upgrades]'><br><br>
		";
		if($defaults['auction']==0) {
			$out .= "Auctionable? <input type=checkbox name=auction><br><br>";
		} else {
			$out .= "Auctionable? <input type=checkbox name=auction checked><br><br>";
		}
		$out .= "
		Base Move Cost: <input type=text name=move_turn_cost size=2 maxlength=2 value='$defaults[move_turn_cost]'><br><br>
		Point Value (For scoring purposes): <input type=text name=point_value size=3 value='$defaults[point_value]'><br><br>
		Plasma Cannons: <input type=text name=num_pc size=2 value='$defaults[num_pc]'><br><br>
		Offensive Turrets: <input type=text name=num_ot size=2 value='$defaults[num_ot]'><br><br>
		Defensive Turrets: <input type=text name=num_dt size=2 value='$defaults[num_dt]'><br><br>
		Silicon Armor Modules: <input type=text name=num_sa size=2 value='$defaults[num_sa]'><br><br>
		Electronic Warfare Modules: <input type=text name=num_ew size=2 value='$defaults[num_ew]'><br><br>
		<input type=submit name=ModShip value=\"Modify Ship\"><br><br>
		</form>
		";
		$out .= "<a href=$PHP_SELF?editshiptype=-1>Return to Ship Editor</a>";
		$rs = "<a href=admin.php>Back to admin page</a>";
		print_page("Modify Ship", $out);
	}
} else {
	die ("go away");
}
?>