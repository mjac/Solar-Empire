<?php

require_once('inc/user.inc.php');

function list_specials($config_breakdown = 1)
{
	$ret_str = "";

	// big array contains smaller arrays with details of items within it.
	$specials = array(
		"bs" => array(
			"short_for" => "Battleship",
			"type" => "Warfare",
			"description" => "This dictates that a ship is registered as a Battleship. This shows the ship will do more damage in combat.<br>It also allows a ship's fighter capacity to go above 4,999."),

		"sh" => array(
			"short_for" => "Shield Charger",
			"type" => "Warfare",
			"description" => "A unit that can be fitted to a ship to increase the shield charge rate by a further 25%!"),

		"hs" => array(
			"short_for" => "High Stealth",
			"type" => "Stealth",
			"description" => "Nothing about the ship can be discerned. Only that it is there, and it's size. It is not possible to attack a ship with High Stealth, unless the attacking ship has a scanner. Ships with High stealth also take much less damage when attacked."),

		"ls" => array(
			"short_for" => "Low Stealth",
			"type" => "Stealth",
			"description" => "The type of ship will be shown, but other players will not be able to see the stat's for it. Everything about it can be seen if the enemy has a ship with a scanner on it. Ships with Low Stealth do increased damage when attacking, as they have an element of surprise."),

		"na" => array(
			"short_for" => "No Attack",
			"type" => "Limiter",
			"description" => "This ship type cannot attack anything."),

		"oo" => array(
			"short_for" => "Only One",
			"type" => "Limiter",
			"description" => "You can only own one ship with this special at a time."),

		"po" => array(
			"short_for" => "Planets Only",
			"type" => "Limiter",
			"description" => "This ship type can only attack planets and is unable to attack other ships."),

		"so" => array(
			"short_for" => "Ships Only",
			"type" => "Limiter",
			"description" => "This ship type can only attack other ships, and may not attack planets."),

		"sv" => array(
			"short_for" => "SuperWeapon Mark 1 - Quark Disrupter",
			"type" => "SuperWeapon",
			"description" => "This ship has a super weapon on it. The superweapon in question being a Quark Displacer, which can only be fired at planets, but does a serious amount of damage per shot, at a cost of a number of turns. Can attack Hostile planets as well as passive ones."),

		"sw" => array(
			"short_for" => "SuperWeapon Mark 2 - Terra Maelstrom",
			"type" => "SuperWeapon",
			"description" => "This ship has a Mark 2 super weapon on it, a Terra Maelstrom.  This can only be used against planets. It can be a very major threat to a players planet provided there are enough turns to use it. Can attack Hostile planets as well as passive ones."),

		"sj" => array(
			"short_for" => "Sub-Space Jump Drive",
			"type" => "Propulsion",
			"description" => "Allows the ship to make subspace jumps to anywhere in the galaxy without using warp-links. The jumping ship may only be accompanied by 10 other ships during a jump (this limit goes away with the wormhole stabiliser). The turn cost is based solely upon the direct distance to the destination, irrespective of the number of ships following it."),

		"tw" => array(
			"short_for" => "Transwarp Drive",
			"type" => "Propulsion",
			"description" => "The ship has a built in transwarp drive. This means it can jump limited distances without needing to use warp links. It can tow any number of ships, however each ship towed adds one turn to the turn cost of the jump."),

		"ws" => array(
			"short_for" => "Wormhole Stabiliser",
			"type" => "Propulsion",
			"description" => "An upgrade that is strictly for ships that have a SubSpace Jump Drive (sj). This will allow the selected ship to participate in the <b class=b1>AutoShifting</b> of colonists and materials from Sol to a players planet, and between a players planet. Without one in the system, Autoshifting is not possible.<br>This upgrade also allows any number of ships to follow a ship that is making a sub-space jump."),

		"e1" => array(
			"short_for" => "Engine Upgrade",
			"type" => "Propulsion",
			"description" => "This upgrade increases the combat movement of the ship by 1.<br>It will also reduce the turn cost to move the ship by warp links by 1, <b>if</b> warp costs are based upon ship-size (rather than being fixed)."),

		"e2" => array(
			"short_for" => "Advanced Engine Upgrade",
			"type" => "Propulsion",
			"description" => "This upgrade increases the combat movement of the ship by 2.<br>It will also reduce the turn cost to move the ship by warp links by 2, <b>if</b> warp costs are based upon ship-size (rather than being fixed)"),

		"fr" => array(
			"short_for" => "Freighter",
			"type" => "Misc",
			"description" => "This ship is a freighter. This means that it will generally do more counter-damage when attacked, than a normal ship."),

		"sc" => array(
			"short_for" => "Scanner",
			"type" => "Misc",
			"description" => "A scanner enables the ship fitted with it to see cloaked ships, and also to attack them. To use the scanner succesfully you must be commanding the ship with it in.<br>Scanners also allow for a slight increase in damage during battle."),

			); #end of specials array declaration.

	#print out all the specials
	if ($config_breakdown == 1) {
		foreach ($specials as $key => $value) {
			$ret_str .= "<table class=\"simple\">\n\t<tr>\n" .
			 "\t\t<th>Abbreviation</th>\n\t\t<td>$key</td>\n\t</tr>\n" .
			 "\t<tr>\n\t\t<th>Short for</th>\n\t\t<td>" .
			 $value['short_for'] . "</td>\n\t</tr>\n" .
			 "\t<tr>\n\t\t<th>Type</th>\n\t\t<td>" .
			 $value['type'] . "</td>\n\t</tr>\n" .
			 "\t<tr>\n\t\t<th>Description</th>\n\t\t<td>" .
			 $value['description'] . "</td>\n\t</tr>\n" .
			 "</table>\n";
		}
		#print out only selected specials
	} elseif(!empty($config_breakdown)) {
		$config_array = preg_split("/:/",$config_breakdown);
		foreach ($config_array as $value) {
			#if the user is playing with a config that isn't on the list.
			if (empty($specials[$value])) {
				$ret_str .= "<p>No entry for <b>$value</b></p>";
			} else { #print out the details for the upgrade
				$ret_str .= "<table class=\"simple\">\n\t<tr>\n" .
				 "\t\t<th>Abbreviation</th>\n\t\t<td>$value</td>\n\t</tr>\n" .
				 "\t<tr>\n\t\t<th>Short for</th>\n\t\t<td>" .
				 $specials[$value]['short_for'] . "</td>\n\t</tr>\n" .
				 "\t<tr>\n\t\t<th>Type</th>\n\t\t<td>" .
				 $specials[$value]['type'] . "</td>\n\t</tr>\n" .
				 "\t<tr>\n\t\t<th>Description</th>\n\t\t<td>" .
				 $specials[$value]['description'] . "</td>\n\t</tr>\n" .
				 "</table>\n";
			}
		}
	}

	return $ret_str;
}

$rs = "<p><a href=\"#top\">Top</a></p>";
$error_str = "";


//pop-specials info.
if (isset($special_info)) {
	$error_str .= "";
} elseif (isset($game_vars)) {
	if(!isset($admin_var_show)) {
		$error_str .= "You may not see the vars for a game when you are not in said game.";
	} elseif($admin_var_show == 0) {
		$error_str .= "The Admin of this game, doesn't want the game variables displayed.";
	} else {
		db("select * from ${db_name}_db_vars order by name");
		$error_str .= "<h3><b>Game Variables</b></h3>Shown below are all the variables for the game, as set by the Admin.";
		$error_str .= "<table border=0 cellspacing=1 width=350>";
		while($var = dbr()) {
			$error_str .= "<tr bgcolor=#333333><td width=220>$var[name] = ${var['value']}</td>";
			$error_str .= "<tr bgcolor=#555555><td><blockquote>${var['descript']}<br></td>";
			$error_str .= "<tr bgcolor=#000000><td colspan=2>&nbsp;</td></tr>";
		}
		$error_str .= "</table>";
	}

	$help_type = "Game Variables";
} elseif (isset($story)) {
	$stories = include('inc/story.inc.php');
	if (empty($stories)) {
		exit('Unable to find the stories.');
	}

	$error_str .= "<h1>Solar Empire Stories</h1>\n<h2>List of Stories:</h2>\n";

	$counter = 0;
	$error_str .= "<ul>\n";
	foreach ($stories as $title => $content) {
		$error_str .= "\t<li><a href=\"#$title\">" .
		 str_replace('_', ' ', $title) . "</a></li>\n";
		++$counter;
	}
	$error_str .= "</ul>\n";

	$counter = 0;
	foreach ($stories as $title => $content) {
		$error_str .= "<h2 id=\"$title\">" . str_replace('_', ' ', $title) . "</h2>\n$content\n";
		++$counter;
	}

	$help_type = "Game Stories";
	$rs = "";
} elseif(isset($ship_info)) {
	$ship_types = load_ship_types();
	if($shipno < 0) {//list stats of all ships:
	if($shipno == -1){
		$error_str .= "<h3>Ship Listing</h3><p>Listed below is information for all ships that can be brought from the Shipyards at Earth.</p>";
	} elseif($shipno==-2){
		$error_str .= "<h3>Ship Listing</h3><p>Listed below is information for all ships that can be brought from the blackmarkets.</p>";
	}
		$ship_types_2 = $ship_types;
		while($ship_array_1 = each($ship_types_2)) {
			$ship_stats_1 = $ship_array_1[0];
			$ship_stats = $ship_types[$ship_stats_1];
			if(($ship_stats['type_id'] >= 300 && $shipno==-2) || ($ship_stats['type_id'] < 300 && $shipno==-1 && $ship_stats['type_id'] != 1)){
				$ship_stats['cost'] = number_format($ship_stats['cost']);
				$ship_stats['tcost'] = number_format($ship_stats['tcost']);
				$error_str .= make_table(array(),"WIDTH=75%");
				$img_txt = "<tr><td colspan=2><center><a href=\"img/ships/ship_{$ship_stats['type_id']}.jpg\"><img height=\"120\" width=\"160\" src=\"img/ships/ship_${ship_stats['type_id']}_tn.jpg\" /></a></center></td></tr>";
				$error_str .= "<tr><td colspan=2 align='center' bgcolor='#555555'><b>$ship_stats[name]</b> ($ship_stats[class_abbr]) </td></tr>$img_txt";
				$error_str .= quick_row("<b>Size</b>",discern_size($ship_stats['size']));
				$error_str .= quick_row("<b>Type</b>","$ship_stats[type]");
				$error_str .= quick_row("<b>Fighters</b>","$ship_stats[fighters]/$ship_stats[max_fighters]");
				$error_str .= quick_row("<b>Max Shields</b>","$ship_stats[max_shields]");
				$error_str .= quick_row("<b>Cargo Bays</b>","$ship_stats[cargo_bays]");
				if($alternate_play_1 == 1){
					$error_str .= quick_row("<b>Mining Rate: Metal</b>","$ship_stats[mine_rate_metal]");
					$error_str .= quick_row("<b>Mining Rate: Fuel</b>","$ship_stats[mine_rate_fuel]");
				} else {
					$quick_maths = $ship_stats['mine_rate_metal'] + $ship_stats['mine_rate_fuel'];
					$error_str .= quick_row("<b>Mining Rate</b>","$quick_maths");
				}
				if($ship_warp_cost < 0){
					$error_str .= quick_row("<b>Move Cost (</b>turns<b>)</b>","$ship_stats[move_turn_cost]");
				}
				if (!$ship_stats['config']) {
					$error_str .= quick_row("<b>Specials</b>","None");
				} else {
					$error_str .= quick_row("<b>Specials</b>","$ship_stats[config]");
				}
				$error_str .= quick_row("<b>Upgrade Pods</b>","$ship_stats[upgrades]");
				$error_str .= quick_row("<b>Description</b>","$ship_stats[descr]");
				$error_str .= quick_row("<b>Cost</b>","$ship_stats[cost]");
				if($ship_stats['type_id'] >= 300){
					$error_str .= quick_row("<b>Tech. Support Cost</b>","$ship_stats[tcost]");
				}
				$error_str .= "</table><br>";
			}
#			$ship_counter++;
		}
		$help_type = "Complete Ship Listing";

	//list stats for specific ship.
	} else {

		$ship_counter=$shipno;
		$ship_stats = $ship_types[$ship_counter];
		$ship_stats['cost'] = number_format($ship_stats['cost']);
		$ship_stats['tcost'] = number_format($ship_stats['tcost']);

		if(isset($popup)){
			$error_str .= "<br><center><table width=250 height=250 cellspacing=0 cellpadding=3 border=1>";
		} else {
			$error_str .= "<p>".make_table(array("",""),"WIDTH=75%");
		}

		$img_txt = "<tr><td colspan=2><center><a href=img/ships/ship_${ship_stats['type_id']}.jpg target=_blank><img border=0 height=120 width=160 src='img/ships/ship_${ship_stats['type_id']}_tn.jpg'></a></center></td></tr>";
		$error_str .= "<tr><td colspan=2 align='center' bgcolor='#555555'><b>$ship_stats[name]</b> ($ship_stats[class_abbr]) </td></tr>$img_txt";
		$error_str .= quick_row("<b>Size</b>",discern_size($ship_stats['size']));
		$error_str .= quick_row("<b>Type</b>","$ship_stats[type]");
		$error_str .= quick_row("<b>Fighters</b>","$ship_stats[fighters]/$ship_stats[max_fighters]");
		$error_str .= quick_row("<b>Max Shields</b>","$ship_stats[max_shields]");
		$error_str .= quick_row("<b>Cargo Bays</b>","$ship_stats[cargo_bays]");
		if($alternate_play_1 == 1){
			$error_str .= quick_row("<b>Mining Rate: Metal</b>","$ship_stats[mine_rate_metal]");
			$error_str .= quick_row("<b>Mining Rate: Fuel</b>","$ship_stats[mine_rate_fuel]");
		} else {
			$quick_maths = $ship_stats['mine_rate_metal'] + $ship_stats['mine_rate_fuel'];
			$error_str .= quick_row("<b>Mining Rate</b>","$quick_maths");
		}
		if($ship_warp_cost < 0){
			$error_str .= quick_row("<b>Move Cost (</b>turns<b>)</b>","$ship_stats[move_turn_cost]");
		}
		if (!$ship_stats['config']) {
			$error_str .= quick_row("<b>Specials</b>","None");
		} else {
			$error_str .= quick_row("<b>Specials</b>","$ship_stats[config]");
		}
		$error_str .= quick_row("<b>Upgrade Pods</b>","$ship_stats[upgrades]");
		$error_str .= quick_row("<b>Description</b>","$ship_stats[descr]");
		$error_str .= quick_row("<b>Cost</b>","$ship_stats[cost]");
		if($ship_stats['type_id'] >= 300){
			$error_str .= quick_row("<b>Tech. Support Cost</b>","$ship_stats[tcost]");
		}
		$error_str .= "</table>";

		$error_str .= "<h2>Specials Meanings</h2>" . list_specials($ship_stats['config']);

		$help_type = "$ship_stats[name] Ship Info";
	}

	#don't show the link if in a pop-up window.
	if(!isset($popup)){
		if(!isset($specials)) {
			$error_str .= "<p><a href=help.php?ship_info=1&shipno=$shipno&specials=1>Add information about Specials.</a>";
		} else {
			$error_str .= $rs."<br><b>Specials</b>".list_specials();
		}
	}
} elseif(isset($random)) {

	$error_str .= "<h3><b>Random Events</b></h3>";
	$error_str .= "Shown below are all the random events that can occur in Solar Empire.";
	$error_str .= "The first table shows a general key to what the information means.";
	$error_str .= make_table(array("",""),"WIDTH=75%");
	$error_str .= quick_row("<b class=b1>Name</b>","This is the name the Event comes under.");
	$error_str .= quick_row("<b class=b1>Type</b>","Whether the event activly does things, or is stationary etc.");
	$error_str .= quick_row("<b class=b1>When</b>","When does it occur? Hourly, Daily, Initiated (when a player starts it). A <b>?</b> (Question mark) shows that it may be random.");
	$error_str .= quick_row("<b class=b1>Level</b>","At what level does it occur. From 1 to 3. The higher the level the more potent it will most likely be.");
	$error_str .= quick_row("<b class=b1>Information</b>","Helpful information about the event. Usually what it does.");
	$error_str .= quick_row("<b class=b1>Description</b>","A general, and completly useless description.");
	$error_str .= quick_row("<b class=b1>Notes</b>","Any Notes. Generally a brief list of what the event does.");
#	$error_str .= "<tr bgcolor=#000000><td colspan=2>&nbsp;</td></tr>";
	$error_str .= "</table><br>";

	//Nebula
	$error_str .= make_table(array("",""),"WIDTH=75%");
	$error_str .= quick_row("Name","<b class=b1>Nebula</b>");
	$error_str .= quick_row("Type","Stationary/Active");
	$error_str .= quick_row("When","Hourly");
	$error_str .= quick_row("Level","2+");
	$error_str .= quick_row("Information","<b class=b1>Nebula </b>offer a get rich quick system that can also decimate a fleet. Mining of Fuel in a <b class=b1>Nebula </b>is tripled due to their gaseous nature. And due to the size of the <b class=b1>Nebula</b>, the fuel is infinite. <br>However the density of material in <b class=b1>Nebula </b>means that there can be no shields on any ship that is in one, even if the ship is just passing through. A ship will also take anything up to 10 damage per hour it is left in a <b class=b1>Nebula</b>, also due to the density.<br>Due to the quantity Hydrogen in a <b class=b1>Nebula</b>, a bomb let loose in one will do <b>triple</b> damage!!!");
	$error_str .= quick_row("Description","A Huge cloud of gases that are responsible for star creation.");
	$error_str .= quick_row("Notes","One <b class=b1>Nebula</b> will cover many systems.<br>Mining of Fuel in a <b class=b1>Nebula</b> is tripled.<br>Fuel is infinite in a <b class=b1>Nebula</b>.<br>Gamma bombs do triple damage in a <b class=b1>Nebula</b>.<br>There are <b>No</b> shields on any ships in an <b class=b1>Nebula</b>.<br>Ships in a <b class=b1>Nebula</b> take anything up to 10 damage per hour.");
	$error_str .= "</table><br>";
	//Mining rush
	$error_str .= make_table(array("",""),"WIDTH=75%");
	$error_str .= quick_row("Name","<b class=b1>Mining Rush</b>");
	$error_str .= quick_row("Type","Stationary");
	$error_str .= quick_row("When","Daily - Randomised");
	$error_str .= quick_row("Level","2+");
	$error_str .= quick_row("Information","Prospectors have found a very large ammount of metal in a system. This metal can be mined at a rate of 4 times normal (quadrupled). The metal is also infinite until the rush ends, which could be any hour. <br>There are <b>no</b> bad effects.");
	$error_str .= quick_row("Description","'Gold rush o' 49' was nothing compared to this. A very large quantity of metal has been found in a system and lots of people are going to get there fast.");
	$error_str .= quick_row("Notes","Regularity of a <b class=b1>rush </b>based on the number of stars.<br>Mining rate of any ship in the system is quadrupled.<br>Metal is infinite for the duration of the <b class=b1>rush</b>.<br>The rush could end at any time, and the length of a <b class=b1>rush </b>is based on the random_event admin var, as well as the number of stars.");
	$error_str .= "</table><br>";
	//Solar Storm
	$error_str .= make_table(array("",""),"WIDTH=75%");
	$error_str .= quick_row("Name","<b class=b1>Solar Storm</b>");
	$error_str .= quick_row("Type","Active");
	$error_str .= quick_row("When","Hourly");
	$error_str .= quick_row("Level","2+");
	$error_str .= quick_row("Information","Increased activity within a Star results in a <b class=b1>Solar Storm</b>. This means huge amounts of charged particles are thrown all around the system which in turn interfere with the shields, thus reducing them to zero.");
	$error_str .= quick_row("Description","Activity within a Star varys depending on where it is in its <b class=b1>Solar Cycle</b>. When a Star's cycle reaches its peak, it can easily cause major disruption to many things in the system, shields being one of them. <b class=b1>Storms</b> don't generally last long though.");
	$error_str .= quick_row("Notes","Short lived <b class=b1>Storms</b>, that are common.<br>Will cause a ships shields to go to, and stay at zero, for the duration of the <b class=b1>Storm</b>.<br>Can <b>even</b> effect the <b class=b1>Sol</b> system");
	$error_str .= "</table><br>";
	//SuperNova
	$error_str .= make_table(array("",""),"WIDTH=75%");
	$error_str .= quick_row("Name","<b class=b1>SuperNova</b>");
	$error_str .= quick_row("Type","Active");
	$error_str .= quick_row("When","Daily");
	$error_str .= quick_row("Level","3");
	$error_str .= quick_row("Information","When the Science Institute of Sol find out a star is near death, they report it to the news. As of that time, it could go <b class=b1>SuperNova</b> in <b>24-72</b> hours. Maybe longer on rare occasions, but <b>24</b> hours is the absolute mininmum.<br>When the star does go <b class=b1>SuperNova</b> it will destroy absolutly everything in the system.<br>The star will then become a <b class=b1>SuperNova Remnant</b>.");
	$error_str .= quick_row("Description","A star that has gone bang in a very big way. This is a rare event.");
	$error_str .= quick_row("Notes","Happens rarely. Based on size of universe.<br>Once reported to the news, players have a minimum of <b>24 </b>hrs to get clear of the system, though it could take longer to go. But <b>24-72</b> is the norm.<br>When the star does blow, everything in the system will be destroyed. Planets and all.<br>After it has blown, the star will turn into a <b class=b1>SuperNova Remnant</b>.");
	$error_str .= "</table><br>";
	//SuperNova Remnant
	$error_str .= make_table(array("",""),"WIDTH=75%");
	$error_str .= quick_row("Name","<b class=b1>SuperNova Remnant</b>");
	$error_str .= quick_row("Type","Active");
	$error_str .= quick_row("When","Daily<br>Created by a <b class=b1>SuperNova</b>.");
	$error_str .= quick_row("Level","3");
	$error_str .= quick_row("Information","Once a system has gone <b class=b1>SuperNova</b>, a huge shockwave sends out large quantities of minerals, which then end up in the adjoining systems. Also due to the shear size of a star, when it has gone <b class=b1>SuperNova</b> theres lots of minerals left in the system too. This can be mined at higher rates than normal because there is just so much.");
	$error_str .= quick_row("Description","When a star goes <b class=b1>SuperNova</b> some of what it was made of gets thrown outward. This is a <b class=b1>SuperNova Remnant</b>. It takes a while for the scientist to work out if the <b class=b1>Remnant</b> will remain <b class=b1>Safe</b>, or turn into a <b class=b1>BlackHole</b>");
	$error_str .= quick_row("Notes","When a star has gone <b class=b1>SuperNova</b> it will throw lots of materials to the adjoining systems.<br>There will be huge quantities of materials within the system that has just had the star explode in it. Though not infinite, it can be mined quicker than normal.<br>Mining of Fuel in a <b class=b1>Remnant</b>system is tripled.<br>Mining of Metal is quintupled.<br>Could turn into <b>either</b> a <b class=b1>BlackHole</b> or (more likely) a <b class=b1>Safe SuperNova Remnant</b>.");
	$error_str .= "</table><br>";
	#Safe SuperNova Remnant
	$error_str .= make_table(array("",""),"WIDTH=75%");
	$error_str .= quick_row("Name","<b class=b1>Safe SuperNova Remnant</b>");
	$error_str .= quick_row("Type","Inactive");
	$error_str .= quick_row("When","~");
	$error_str .= quick_row("Level","3");
	$error_str .= quick_row("Information","Once a system has gone <b class=b1>SuperNova</b>, it could well form a Blackhole, or the former star could have been too small, meaning it will remain as a <b class=b1>Safe SuperNova Remnant.</b> If it stays as a <b class=b1>Remnant</b>, then it will be the same as any 'normal' system, but with lots more materials (which are <b class=b1>NOT</b> mined faster in this type of <b class=b1>Remnant</b>).");
	$error_str .= quick_row("Description","Once a Star has gone <b class=b1>SuperNova</b>, it has to be determined by the Boffs at the Observatory if the star will turn into a BlackHole, or if it will just stay as a Remnant.");
	$error_str .= quick_row("Notes","Is same as normal system.<br>However it will most probably have more minerals in.");
	$error_str .= "</table><br>";
	//Black Hole
	$error_str .= make_table(array("",""),"WIDTH=75%");
	$error_str .= quick_row("Name","<b class=b1>Black Hole</b>");
	$error_str .= quick_row("Type","Stationary");
	$error_str .= quick_row("When","Intiated (Player runs into it)<br>Created with game/or SuperNova");
	$error_str .= quick_row("Level","3");
	$error_str .= quick_row("Information","These are created with either the universe, or a Super-Nova of a massive star. They don't go away.<br>Due to the wonders of Gravity, anything that goes into a system with one of these in will have a tough time escaping, but escape they will, though taking <b>5</b>% damage per ship in the process.<br>Whilst escaping, your whole fleet will be thrown around, with each ship most likely ending up in a different system, so be sure to read the messages you get from it.");
	$error_str .= quick_row("Description","These are massive stars that have exploded(SuperNova), and formed into a <b class=b1>Black Hole</b> which is now causing havoc in its neighbourhood.");
	$error_str .= quick_row("Notes","Created with universe, or by a SuperNova.<br>Will damage each ship you are towing (as well as the one you are commanding) by <b>5%</b>.<br>Will scatter all ships in your convoy all over the galaxy. Even onto star Islands.");
	$error_str .= "</table><br>";
	$help_type = "Random Event Info";
} elseif(isset($equip)) {

		$error_str .= "<h3><b>Equipment</b></h3>Equipment is the general stuff you use to do things, and that doesn't fall into some other category.<br>";
		$error_str .= "<br><a href=#fighters>Fighters & Shields</a>";
		$error_str .= "<br><a href=#genesis>Genesis Device</a>";
		$error_str .= "<br><a href=#bombs>Bombs</a>";

		$error_str .= "<br><br><br><a name=fighters><b>Fighters & Shields</b></a><br><br>All ships have a specified maximum number of fighters that they can be equipped with. <br>Fighters are the units that do all of the work in a battle situation. They deal damage to enemy ships/fighters and when attacked, they deal counter damage to the attacker. <br><br>A ship that has only shields will only be able to take damage. Shields absorb the first impacts of enemy fighters on your ship. Once they have run-out the fighters get to work in defending you ship. Shields automatically replenish over each hour until they reach the maximum a ship can hold. Fighters cannot and do not.<br><br>Fighters can be switched from ship to planet, as can shields (provided the target planet has a shield generator). $rs";
		$error_str .= "<br><br><br><a name=genesis><b>The Genesis Device</b></a><br><br>Genesis Devices are used to create a planet. Simply buy a genesis device, go to a system other than system #1, and click the <b class=b1>Use Genesis Device</b> link in the right column of the star system.<br><br>This will create a thriving planet, which will have 1,000 colonists on it, which you can then practice your despotic ways upon.";
		$error_str .= "<br><br><br><a name=bombs><b>Bombs</b></a><br><br>'Big things that go Bang' : Definition of <b class=b1>Bombs</b> from the 'Idiots guide to destroying the universe'. <br><br>An <b class=b1>Alpha bomb</b> will Eliminate ALL shields from ALL ships in a system (yours included), no matter how many shields the ships may have. It will not actually do any damage to the fighters, or the ships.<br><br>A <b class=b1>Gamma bomb</b> can be used to do 200 damage to each ship in a star system (yours included). It will take this damage from shields first, and when all shields are gone, then from the fighter count. If there are no fighters, then it will simply destroy the ship. Best used in conjuction with an Alpha Bomb.<br><br>A <b class=b1>SuperNova Effector</b> can responsible for destroying an entire star system. It will get the Sun to go 'bang in rather a big way' (Idiots guide again), in the process eliminating everything that happens to be in the system when it goes bang. Planets and all. It takes between 24 and 48 hours for a sun to go bang after one of these things has been released.";
} elseif(isset($upgrades)) {
	if(isset($specials)){
		$error_str .= "<a href=\"javascript:history.back()\">Back to Upgrades & Accessories</a>";
		$error_str .= list_specials();
		$error_str .= "<br><a href=\"javascript:history.back()\">Back to Upgrades & Accessories</a>";
	} else {

		$error_str .= "<h3><b>Upgrades & Accessories</b></h3>There are numerous other upgrades not mentioned here, however where they do appear they in the game they come with a suitable explanation.<br>Upgrades can be purchased from either:<b class=b1>Vladimirs Accessories & Upgrades Store</b> or <b class=b1>Bilkos Auction House</b>.<br><br>";
		$error_str .= "<br><a href=#about>About Upgrades</a>";
		if($flag_research == 1){
			$error_str .= "<br><a href=#aboutadv>About Advanced Upgrades</a>";
		}
		$error_str .= "<br><a href=#basic>3 Basic Upgrades</a>";
		$error_str .= "<br><a href=#ot>Offensive Turret</a>";
		$error_str .= "<br><a href=#dt>Defensive Turret</a>";
		$error_str .= "<br><a href=#transwarp>Transwarp Drive</a>";
		$error_str .= "<br><a href=#shroud>Shrouding Unit</a>";
		$error_str .= "<br><a href=#scanner>Scanner</a>";
		$error_str .= "<br><a href=#shield_charge>Shield Charging Unit</a>";
		$error_str .= "<br><a href=#wormhole>Wormhole Stabiliser</a>";
		$error_str .= "<br><a href=#terra>Terra Maelstrom</a>";
		$error_str .= "<br><a href=help.php?upgrades=1&specials=1>List of config meanings</a>";

		$error_str .= "<br><br><br><a name=about><b>About Upgrades</b></a><br><br>Upgrades are used to improve your current ship in one way or another. You may simply want to put some more shield capacity onto it, or you may want to give it a new Super Weapon, the point remains that you are aiming to improve it.<br><br>Each upgrade requires one upgrade pod (unless otherwise stated), and will allow your ship to do something it couldn't do before. However once a upgrade pod has been used, it cannot be reclaimed.<br>Use upgrade pods wisely on ships that you intend to keep in your service for a long time. $rs";

		if($flag_research == 1){
			$error_str .= "<br><br><br><a name=aboutadv><b>About Advanced Upgrades</b></a><br><br>Advanced Upgrades are similar to normal Upgrades on most respects. The main difference is that they are based on Alien Technologies and can only be purchased from Blackmarkets. Normally these Upgrades are reverse engineered from originals discovered on Alien Derelict Ships found after battles or lost in Star Systems.<br><br>To install Advanced Upgrades requires that you research what are called Tech. Support Units. These are required to use most Alien Technologies. You generate Support Units by building Research Facilities on your planets.<br><br>Note that these Advanced Upgrades rather than increasing fighters/shields etc. actually convey certain Attack/Defence Bonuses which are used during battle.";

			$error_str .= "<br><br>The Tachyon Communication Array was developed over two years ago in secret by the Sol Authorities. Its purpose was to allow supra-light communications through the use of a stealthed Tachyon emission. Used almost exclusively by the much feared Stealth Probes, Pirates stumbled across the design after salvaging a malfuntioning unit. Some slight redesigning has resulted in a much smaller efficient unit capable of being installed on Leeches and other forms of Stealthed ship to enable undetectable transmissions.";

			$error_str .= "$rs";
		}

		$error_str .= "<br><br><br><a name=basic><b>3 Basic Upgrades</b></a><br><br>The 3 basic upgrades will each upgrade certain aspects of the ship you are commanding for a small price. $rs";

		$error_str .= "<br><br><a name=ot><b>Offensive Turret</b></a><br><br>The Offensive Turret is an AI controlled Level 1 weapons array dedicated to clearing local space of enemy fighters. The AI ensures the turrets hit each and every time to the tune of 225 damage points.<br>No battleship should be without it! $rs";

		$error_str .= "<br><br><a name=dt><b>Defensive Turret</b></a><br><br>The Defensive Turret is a Level 1 defence system controlled by an AI. Its special design enables it to intercept and destroy incoming munitions. <br>Excellent combat defence system that will block up to 275 attack points before allowing a ship to take damage. The first defence system of its kind! $rs";

		$error_str .= "<br><br><a name=transwarp><b>Transwarp Drive</b></a><br><br>This upgrade will allow a ship to jump between systems whilst skipping the systems in-between. It does cost a couple more turns, however its invaluable for getting to star islands, and can tow an unlimited number of ships. It may not be installed onto ships that have the sub-space jump capability. $rs";

		$error_str .= "<br><br><a name=shroud><b>Shrouding Unit</b></a><br><br>This upgrade gives a ship the <b class=b1>ls</b> configeration. This means that enemy players will not be able to determine any information about the ship, unless they have a ship with a scanner on it. $rs";

		$error_str .= "<br><br><a name=scanner><b>Scanner</b></a><br><br>Allows a user to see all information about a ship that would otherwise be an 'unknown', if the ship is lightly stealthed.<br>However, if the ship is Highly Stealthed, then it will give limited information about a ships fighter count, and such like, however it will not be able to determine the ships owner. $rs";

		$error_str .= "<br><br><a name=shield_charge><b>Shield Charging Unit</b></a><br><br>This upgrade will allow a ship's shields to regenerate 25% faster. $rs";

		$error_str .= "<br><br><a name=wormhole><b>Wormhole Stabiliser</b></a><br><br>An absolutely essential upgrade if you have a Transverser, or plan on building a planet, or both.<br>This upgrade allows for <b class=b1>Autoshifting</b> to take place, whereby a fleet of ships will shift from your planet, to Sol, nab as many colonists as it can carry and you can afford, and then come back and dump them onto the planet. Saves vast amounts of time, and generally costs the same number of turns as doing it manually.<br>This upgrade also allows for an infinite number of ships to be towed with it when it makes a jump (from 10 ships max if there is no stabiliser installed).<br>May only be used on ships with a sub-space jump drive. $rs";

		$error_str .= "<br><br><a name=terra><b>Terra Maelstrom</b></a><br><br>This upgrade is here because of its complexity. It does not require an upgrade pod, instead the ship must already have the Quark Disrupter built in.<br><br>It can only be fired at a planet, and uses either a lot of turns, or ALL of a players turns, depending on the size of the planet, and the turn count of the user. In general, it is best used against big planets when the player has a very high turn count.<br>It also charges the ships shields at 3 times the normal rate. $rs<br>";
		$rs = "";
	}
} elseif(isset($research)) {
	$error_str .= "<h3><b>Research and Tech Support Units</b></h3>Research is the term used to describe the generation of Tech. Support Units from a planets Research Facility. It is these Tech. Support Units that will enable you to purchase all the advanced Alien items at your nearest Blackmarket. Research and Blackmarkets is an area of Solar Empire that may be added or removed by the games Admin. The basic system is described in the following sections below.<br><br>";
	$error_str .= "<br><a href=#advitems>About Advanced and Alien Items</a>";
	$error_str .= "<br><a href=#research>Research, Facilities & Support Units</a>";
	$error_str .= "<br><a href=#blackmarket>Illegal Blackmarkets</a>";

	$error_str .= "<br><br><br><a name=advitems><b>Advanced and Alien Items</b></a><br><br>So what are Advanced and Alien Items? To put it simply, they are the collection of Ships, Upgrades ad Bombs that are based either on reverse-engineered Alien artifacts or on advanced human designs considered illegal and dangerous by the Sol Authorities. They include all manner of exotic ships, equipment, upgrades and a range of exprimental bombs, mines and missiles. For a full list of these items consult the relevant areas of this help file. $rs";
	$error_str .= "<br><br><br><a name=research><b>Research Facilities and Tech. Support Units</b></a><br><br>So how do you get hold of all these Blackmarket items? To purchase these items from any Blackmarket you will require two things, cash and a source of Tech. Support Units. Research begins very simply by building a Research Facility on your planet. Each player is limited to two such Facilities, one to a planet. (<b>Important Note</b>: Claiming a planet that has a research facility on it will result in the facility being destroyed. No matter who presently owns the planet).<br><br>These Research Facilities generate Tech. Support Units each hour. The number generated depends primarily on the population of the planet in question. Typically your planet's maximum research output is 5 times the basic hourly rate. The maximum rate is reached by attaining successive research rate increases, each of which requires approximately 1.7 times the number of colonists as the previous one. Thus each new level is just that much more difficult to reach.<br><br>Tech Support Units are those things which enable you to make use of Blackmarket goods. They are basically a measure of the level of Knowledge, Support Staff, Power Sources and Computer Skills you can bring to bear when using non-standard items. These units are used up as you purchase more Blackmarket items. $rs";
	$error_str .= "<br><br><br><a name=blackmarket><b>The Blackmarket</b></a><br><br>In Solar Empire certain groups of ex-pirates have set up an extensive blackmarket network. In-game any number of blackmarkets can exist, each normally located in a random Star System. Due to their illegal nature, modern blackmarkets take the form of immense Trader or Cargo Ships which move at random between Star Systems, always one step ahead of the Sol Authorities. Once you actually find a Blackmarket and contact it you will find that three areas exist within it. The first area deals with all manner of Advanced Ships and/or Alien Spacecraft. The second area you will see handles the fitting of equipment and upgrades to ships. The third is a more unusual realm since here you will find many bombs, mines, or missiles which often have extremely odd purposes. Most of these have been reverse engineered from Alien originals, even though few could even guess at just how they work. It should be noted that Blackmarkets change location each day. It is also planned for the future that prices at Blackmarkets will vary from place to place, so exercise your comsumer right to shop around!.";
} elseif(isset($planet)) {
	$error_str .= "<h3><b>Planets</b></h3>Planets are created by using a <a href=help.php?equip=1#genesis>Genesis</a> device, which you must have purchased at Earths Equipment shop. Once you have a Genesis device, simply goto a system where you want to build a planet (you may not build a planet in system #1), and click the <b class=b1>Use Genesis Device</b> link which appears in the right column of the star system view.<br><br>";
	$error_str .= "<br><a href=#basics>Planet Basics</a>";
	$error_str .= "<br><a href=#colonists>Colonists</a>";
	$error_str .= "<br><a href=#missiles>Missiles</a>";
	$error_str .= "<br><a href=#shields>Shield Generators</a>";

	$error_str .= "<br><br><br><a name=basics><b>Planet Basics</b></a><br><br>The basic functions of a planet are to produce fighters, defend a fleet, and/or make money. Any player may make a planet assuming (s)he is out of safe turns, and can afford a Genesis Device from the Equipment shop at Earth. <br>Planets may be renamed at any time, and a password may be set on a planet to keep clan members from taking your money/minerals/fighters/colonists. Passwords are usually not recommended unless you are uneasy about another clan member, because they tend to restrict your clan members from free movement of those commodities.<br>Once in the planet view, you have the ability to ï¿½claimï¿½ a planet.<br><br>Each planet has the ability to house virtually limitless numbers of fighters. Fighters are the actual units that defend your planet/fleet from oncoming attack by enemy forces. <br>Fighters are set at either one of two modes, Passive and Hostile.<br><b>Hostile</b> fighters will defend your system from any ship not belonging to you, or your clan (should you be in one), while <b>Passive</b> fighters will only defend the planet itself from direct attack. Setting fighters to Hostile also defends every ship within the system. $rs";

	$error_str .= "<br><br><br><a name=colonists><b>Colonists & Taxing & Production</b></a><br><br>Colonists are the workforce of the planets. <br>500 <b class=b1>assinged</b> Colonists can take 10 units of fuel and 10 units of metal and produce an admin specified amount of electronics.<br>100 <b class=b1>assinged</b> Colonists can use one unit of the following: Fuel, Metal, Electronics, and produce an admin specified number of fighters which will be used to defend the planet with until you find other things for them to do.<br>An admin specified number of colonists can be used to create one organic unit if you assign them to that task.<br>See the <a href=help.php?game_vars=1>Game Variables</a> section of this help for what the vars are for this game)<br><br>Colonists that are not doing anything - Idle Colonists - will pay taxes and reproduce. Taxes set at a level higher than 11% will cause negative reproduction (death). Taxes set at a lower level will cause higher reproduction. At a zero percent tax rate, idle colonists will reproduce at a rate of 30% per night. <br>Colonists can also be <b class=b1>assigned</b> to farm organic material that may be sold at ports. $rs";

	$error_str .= "<br><br><br><a name=missiles><b>Missiles</b></a><br><br>Missile Launch Pads can be constructed on any planet. The cost is given to you on the planet menu screen. These usually take a considerable amount of time, money and materials to build, and they give you the ability to construct <b class=b1>Omega Missiles</b>.<br>Omega Missiles also require a large amount of materials to construct. However, once constructed you may launch the Omega Missile at the planet of your choice.<br>When launching, you must also have fuel and turns available, from which the missile generates the energy necassary to make the long distance trek from one solar system to another. <br><br>Missile Launch Pads may also be purchased through auction at <b class=b1>Bilkoï¿½s Auction House</b>. These may run for as little as 100,000 credits with no cost in minerals. $rs";
	$error_str .= "<br><br><br><a name=shields><b>Shield Generators</b></a><br><br>A shield generator may also be built on any planet. A typical shield generator can generate and store up to 3000 units of shields at a time, which may then be used to augment your fleets shields. Shield generators in <b>NO</b> way increase the defensive capabilities of a planet itself.<br><br>Larger generators can be purchased at a <b class=b1>Bilkoï¿½s Auction House</b>. These store significantly more shields, and can also regenerate much faster.";
} elseif(isset($clans)) {
	$error_str .= "This Page contains basic information on clans. How to join, what they're good for, and that sort of thing.";

	$error_str .= "<br><br><a href=#basic_clan>Basic Clan Information</a>";
	$error_str .= "<br><br><a href=#loyal_clan>Loyalty in a Clan</a>";

	$error_str .= "<br><br><br><b><a name=basic_clan>Basic Clan Information</a></b><br>";
	$error_str .= "It is not required to join a clan. Some players play solo with great success (sometimes), however if you are new at this game then it should be a high priority to find a clan. You may search through different organised clans by selecting the Clan Control option. You may also post onto the Forum. <br>If you are in a newbie game, just post that you are a newbie and are looking for some aid in the game. You may even find someone advertising a clan for newbie assistance. These are usually experienced members who are willing to give a helping hand to anyone new to the game.<br>After you post a message to the forum, check back often to see if someone else has invited you. You will also want to check your Messages to see if anyone has sent you a personal reply. To go through the process of joining a clan, click the Clan Control link again. Now find the name of the clan you have been recruited to and select the Join link. Then enter the password of the clan you are entering, usually given to you by the clan leader. $rs";

	$error_str .= "<br><br><br><b><a name=loyal_clan>Loyalty in a Clan</a></b><br>";
	$error_str .= "Clan loyalty is one of the more important aspects of the game; if you show strong loyalty to help your clan without many complaints to the clan leader then others may ask you to be part of their clan at a later date. If you are unloyal to your clan, you will be most likely be kicked out of the clan, and then probably destroyed by that clan's members to boot. After that offense, information about you will be posted on the forum and you will have a very hard time finding clans to join in the future.";
} elseif(isset($misc)) {
	$error_str .= "<h3><b>Miscellaneous Information</b></h3>This page contains much Misc Info that is really quite important, and is a must read for any newbie to the game.";

	$error_str .= "<br><a href=#misc_turns>Turns</a>";
	$error_str .= "<br><a href=#misc_attack>Attacking</a>";
	$error_str .= "<br><a href=#misc_mining>Mining</a>";
	$error_str .= "<br><a href=#misc_moving>Moving & Autowarp</a>";



	$error_str .= "<br><br><br><b><a name=misc_turns>Turns</a></b><br>";
	$error_str .= "Turns are used for pretty much anything. Given below is a small table which lists some of the things that use turns and their costs. A players turns are augmented each hour by some more turns. These turns are given out by the game, and vary from game to game. For this game they are at <b>$hourly_turns</b> turns/hour.<br><br>";
	$error_str .= make_table(array("Action","Turn Cost"));
	if($ship_warp_cost < 0){
		$error_str .= quick_row("Moving","Depends on the Ship Size");
	} else {
		$error_str .= quick_row("Moving","$ship_warp_cost");
	}
	$error_str .= quick_row("Attack Ship","$space_attack_turn_cost");
	$error_str .= quick_row("Attack Planet","$planet_attack_turn_cost");
	$error_str .= quick_row("Self Destruct a Ship","1");
	$error_str .= quick_row("Buy Colonists","1 Turn/Ship");
	$error_str .= quick_row("Selling At Ports","0,1,5 (Depends on Method Used)");
	$error_str .= "</table><br>Note About Table: Many of the variables listed in the table above may change from game to game. The variables shown are correct for this game. Always check the <a href=help.php?game_vars=1>Game Variables</a> section for a complete and up-to-date variable list for the game. $rs";


	$error_str .= "<br><br><br><b><a name=misc_attack>Attacking</a></b><br>";
	$error_str .= "To be able to attack a ship requires that some conditions are met.<br><br>You must be out of the <b class=b1>Turns Safe</b> Period.<br>The Enemy must also be out of the <b class=b1>Turns Safe</b> Period.<br>Admin must have attacking enabled.<br>You need to have enough turns to be able to attack.<br><br>Those are the common reasons players are un-able to attack each other. When you are able to attack a player a link will appear next each of their ships saying <b class=b1>attack</b>. $rs";


	$error_str .= "<br><br><br><b><a name=misc_mining>Mining</a></b><br>Different ships mine at different rates, and this game be seen in the Ship listing section of the help.<br><br>Material comes into a ships cargo bays from mining every hour on the hour.<br>There are no conditions attached, anyone with a mining capable ship, or fleet of them, can mine. Random events can effect mining rates, but more about those is discussed elsewhere in the help. $rs";

	$error_str .= "<br><br><br><b><a name=misc_moving>Moving & Autowarp</a></b><br>There are several different types of ways to move between systems. <br>They are all listed here:<br><br>";

	$error_str .= make_table(array("",""),"WIDTH=75%");
	$error_str .= quick_row("Name","Warp-Links");
	$error_str .= quick_row("Special Equipment <br>Required","None");
	$error_str .= quick_row("Description","The normal way to get around. All stars are connected to other stars via these links. It is possible to get so called <b class=b1>star islands</b> which is where a cluster of islands is segregated from the main group.");
	$error_str .= quick_row("Turn Cost","Low; Avg: 1");
	$error_str .= "</table><br>";

	$error_str .= make_table(array("",""),"WIDTH=75%");
	$error_str .= quick_row("Name","Wormholes");
	$error_str .= quick_row("Special Equipment <br>Required","None");
	$error_str .= quick_row("Description","These are links between two random systems. They can go either one-way, or two-way. Using one is just like using a normal warp-link, however they are random in their locations.");
	$error_str .= quick_row("Turn Cost","Low; Avg: 1");
	$error_str .= "</table><br>";

	$error_str .= make_table(array("",""),"WIDTH=75%");
	$error_str .= quick_row("Name","Transwarp");
	$error_str .= quick_row("Special Equipment <br>Required","<a href=help.php?upgrades=1#transwarp>Transwarp Drive</a>");
	$error_str .= quick_row("Description","This is best used for getting to star islands, and skipping around peninsulas. Otherwise its turn cost gets expensive in comparison to normal travel. Has a limited range of about 15 Light-years (2-3 systems), and can have any number of ships in tow (however, each extra ship in tow costs 1 turn more to move).");
	$error_str .= quick_row("Turn Cost","Medium-Low; Depends on distance Jumped & Num ships in tow..");
	$error_str .= "</table><br>";

	$error_str .= make_table(array("",""),"WIDTH=75%");
	$error_str .= quick_row("Name","Sub-Space-Jump");
	$error_str .= quick_row("Special Equipment <br>Required","(Adv.) Transverser");
	$error_str .= quick_row("Description","Can jump from any one location in the game to another, skipping everything in-between. It may only tow 10 ships at a time, though, unless the wormhole stabiliser upgrade is installed. Best for <b class=b1>Autoshifting colonists</b> using the <a href=help.php?upgrades=1#wormhole>Wormhole Stabiliser</a> upgrade.");
	$error_str .= quick_row("Turn Cost","Medium-High; Depends on distance Jumped.");

	$error_str .= "</table><br>The cost for moving between systems can vary due the admin being able to control it.<br><br>There is a function called <b class=b1>Autowarp</b> which can be used to plot, and follow a course to a different system from the system you are in. This will not always find the shortest route to your destination, it will not use wormholes, and it will cost one turn to implement. However it will also save you considerable time in getting to your destination.<br>The link for <b class=b1>Autowarp</b> can be seen in the right column in the star system view.";
} elseif(isset($started)) {
	$error_str .= "<h3><b>Getting Started</b></h3>This guide should be open in a different window from the one in which you are playing the game in. The idea being that as you read the instructions in this window, you can then switch over to the game and see just what we're talking about.<br>So without any further ado, lets get started:<br><br>";

	$error_str .= "<b>First</b> - Check and see how many credits you are given at signup. (This is visible in the column on the left, directly below your <b class=b1>user name</b>). <br>Why? Because you need to buy more ships. Moneymaking is exponential in this game. That means the more ships you have, the faster you can make money, and the faster you can buy more ships.";

	$error_str .= "<br><br><b>Buying your first ship(s)</b> - You'll be in a Star System(SS) called <b class=b1>Sol</b>. This system is number <b>1</b> and is the heart of the universe. This information is in the top centre of your screen.<br> Land on the planet (<b class=1>Earth</b>) and take a visit to <b class=b1>Seatogu's Spacecraft Emporium</b>. Here you will find numerous different kinds of ships, with the ones that you want to look at at this stage in the game being the <b class=b1>Freighters</b> at the top of the page.<br>The <b class=b1>Merchant Freighter</b>(MF) is the least costly mining ship. Its mining rate is about 6 units per hour for at a cost of <b>10,000</b> Credits (To see information about a specific ship, click the 'info' link next to it.<br>The <b class=b1>Harvester Mammoths</b>(HM) may look very tempting. For fifty grand you can get a ship that mines about 17 units/hour. This means that for five times the price you can mine 3 times as fast. It's not worth it in the beginning of the game.<br><br>Now go ahead and buy yourself some MF's. If you have lots of money, you can click the <b class=b1>Mass Buy</b> link which will allow you to buy many Freighters at once. But if you only want to buy one ship, just click the ships name.";

	$error_str .= "<br><br><b>Once the ship has been brought and named</b> - You will want to collate your ships into a fleet, whereby one ship will tow the others. You can command different ships by clicking the <b class=b1>command</b> link that is next to them, or you can tow them by selecting that instead. When a ship is being towed the tow link will change to <b class=b1>Stop Towing</b>. Also the <b class=b1>Tow All</b> link will save you time and effort by towing all ships in the fleet behind the ship you are currently commanding.";

	$error_str .= "<br><br><b>When you have all your ships in tow</b> - Leave the Sol system and go to an adjacent system. To do this simply click one of the numbers at the top of the screen which look like so: <b class=b1>Warp:</b> <X>, <X>,<X>. Each number is a different system. The starmap on the right of your screen helps you navigate.";

	$error_str .= "<br><br><b>In the New system</b> - You will quite likely find either some Fuel, or Metal, or usually both (it will be shown in the top centre of the screen, below the warp links). If there is none, simply warp around until you find some. but don't go too far because you only have a limited number of turns (shown in the left column below your cash). You are better off finding a system adjacent to Sol that has Fuel in it. Once in a system with Fuel (fuel generally gets more money than Metal) click the <b class>Mine All</b> button next to the fuel and all of your mining ships in the system will now start mining fuel. Do this right now.";

	$error_str .= "<br><br><b>Congratulations</b> - You have just got started. There is much more information in these help pages, but thats all you need to get started.<br>You will get more turns per hour that passed. In this game your turns will increase by <b>$hourly_turns</b>/hour.<br> Your ships will mine the fuel/metal when the game gets to the next hour. You may then sell the fuel/metal at a port and go back to Earth (System #<b>1</b>) to buy more ships.";

	$error_str .= "<br><br><b>Other help sections</b> - Recommended for immediate reading for a Newbie:<br> - Clans<br> - -Misc Info.<br><br>The other areas of this help are concerned with aspects of the game you won't be needing to get to know for a short while yet. But you may start reading them whenever you like. Feel free to click links randomly. You won't be able to do anything you're not allowed to do, and the best way to learn is by exploring.<br><br>So off you go to start having fun, and we hope to see you perusing these help files later in the game.";

	$help_type = "Getting Started";


/************
* Politics Help
*************/

} elseif(isset($politics)){
	$help_type = "Politics";
	$error_str .= "<h3><b>Politics</b></h3>At present politics does nothing other than appoint Senators (people who have the most of something).<br>Being a senator doesn't bestow any priviledges at present, apart from a nice title next to that players name.<br><br>When politics are completed, so will this help information be.";



/************
* Technical Documents
*************/

} elseif(isset($tech_info)){
	$help_type = "Technical Information";
	$error_str .= "<h3><b>Technical Information</b></h3>This page contains technical information about the game, and as such can get pretty technical.<br>The idea behind the page is to give details about how certain aspects of the game work, so as to allow players to better exploit those areas.<p>The present collection of information is listed below:";
	$error_str .= "<p><a href=#tech_attack_sys>The Attack System</a>";
	$error_str .= "<br><a href=#tech_terras>Terra Maelstroms</a>";
	$error_str .= "<br><a href=#tech_omega>Omega Missiles</a>";
	#New (as of June 02) attacking system
	$error_str .= "<br><br><br><a name=tech_attack_sys><b>The Attack System</b></a> - Moriarty (11/June/02)<br><br>The ship to ship as well as ship to planet attacking system can get quite complicated and so it was deemed worthy of a mention.<br>First up, a couple of things should be clarified: The attacker is called the <b>user</b> and the person being attacked is called the <b>target</b>. Ships are the same but with the word <b>ship</b> on the end funnily enough. ;-)";
	$error_str .= "<p>Electronic Warfare Pods (EWP's)<br>The first thing that is done is the number of EWPs are counted. If both sides have the same number, then they cancel each other out. If eithe side has more, then the other side's are nullified, and the winning ship gets to use its EWP's to do some damage.<p>Noting that EWP's have attack, and defense values, these are calculated seperatly. The system then uses the defensive amount of the EWP to try and stop the other ships defensive turrets. Whichever does the more damage wins, and whatever damage is done is taken from the winner, whilst the loosing upgrade doesn't take any more part within the battle as it's been nullified.<br>If there is any defensive EWP charge left, it is then directed at the opposing ships offensive turrets. The same happens here as with the defensive turrets.<br>The remaining defensive EWP charge (if any) is then merged with the offensive EWP charge. This charge is then used to destroy enemy fighters.<p>Defensive Turrets<br>Provided that the defensive turrets on the ship, and they survived the EWP onslought (if there was one), they will now come into play.<br>What these do is destroy enemy fighters. However they destroy enemy fighters <b class=b1>BEFORE</b> they can do damage to your ship. All other components destroy fighters after they have hit your ship.";
	$error_str .= "<P>Offensive Turrets<br>It is now time for the offensive turrets to come into play. These are both the Pea Shooters, and the plasma cannons, and their damage is total is added together to give the offensive turret damage capacity.<br>This damage is then flung at the enemy Silicon armour if there is any. If it is stopped, then the Offensive turrets are out for the round, and the silicon armour takes that much damage.<br>Otherwise they kill the silicon armour and work their way towards the shields, which they proceed to try and destroy.<br>If any shots get through the shields too, they then set about destroying fighters.<br>Should all the fighters be destroyed, then they will destroy the ship.<p>Fighter damage<br>Assuming the ship wasn't destroyed and has some fighters on it, then its time to figure out how much damage they should do. Below is a complete listing of how the fighters damage is calculated.<p>Whatever fighters survived the defensive turrets are used.<br>The damage done is 65% of the fighters on the attacking ship, and 85% on the defending ship.<br>This number then gets randomised by up to 6% plus or minus.<p>The resulting attack/counter attack number is then modified in the following ways once the total percentage modifiers have been worked out:";
	$error_str .= "<p>The damage done by the opponenet goes up in % by whatever your ships move_turn_cost (ship speed. Bigger is slower, and thus worse) + ship size (bigger is worse) total to. So a ship with a speed of 7 (slowest) and size of 8 (biggest) would take an extra 15% damage, whilst a small ship (2) that is quick (2) would only take 4% extra damage.<p>The ships speed is then also converted direcretly into a %, which will be removed from the damage your ship takes.<br>The amount of damage each ship does then goes up by a percentage value based upon the number and size of previous ships killed by that ship. This can be up to 20%! (but that would require killing about 130 HM's with that ship).<p>It is then time to take in the configuration of the ship:<br>battleship = +10% dam done<br>hs = +3.5% dam done<br>ls = +1% dam done<br>sc = +5% dam done<br>fr = +4% dam done<br>po = +25% dam taken!<br>fr = -7.5% dam taken<br>hs = -4.5% dam taken<br>ls = -3% dam taken<p>At this point the damage done and damage taken modifying percentages are then used on the total damages to figure out the final numbers.<br>This damage is then thrown at the silicon armour. If it gets through that it takes on the shields, and then the fighters.<br><br>This system is also used for ship to planet assualts with a few minor differences.<br>EWP's server no purpose against planets, as they just don't work.<br>The defending planet does not do like a defending ship and have only 85% of its fighters do damage. It does 100% damage!.<br>Scanners and freighter configs also serve no purpose against planets.<br>And of course planets don't have armour or turrets to defend themselves these days.<P>And that is the attacking system. I hope I made it all clear. :-)";
	#terra maelstrom := How it works.
	$error_str .= "<br><br><br><a name=tech_terras><b>Terra Maelstroms</b></a> - Moriarty (11/June/02)<br><br>These are vicious little anti-planet weapons that are an upgrade for the quark disrupter.<br>At present, the quark disrupter does 600 to 1400 damage per shot (randomised) for 30 turns.<p>The Terra takes 50 turns as an absolute minimum to fire. What happens is this:<br>Those 50 turns can generate between 4000 and 6000 damage (randomised). <br>Now, if the planet has more than that number of fighters, and the user has more than 50 turns, the game will first find out how many turns is the maximum a player can have. If the player has the maximum number of turns, they will kill between 65% and 75% of the fighters on the planet (randomised). If they don't have that many turns, the gun will use all of the turns a player has and work out what amount up to that 65-75% the user is capable of doing.<br.Whatever number is a result of working out the damage done is then randomised by 5%.<p>Once that is all done, the game will see if the damage done by using all of a players turns is actually greater than the fixed damage done for 50 turns. Whichever does the most damage wins, and is used.";

	#omega missile information
	$error_str .= "<br><br><br><a name=tech_omega><b>Omega Missiles</b></a> - Moriarty (11/June/02)<br><br>Like the Terra's, these things do damage based on percentages.<br>The missile takes at least 5 turns to launch, but this number can go up, the further away the target planet is. The amount of fuel required is 20 times greater than the turns required.<br>If there are less than 100 fighters on the planet, the missile will destroy the planet.<br>If there are more than 100, but less than 1000, the missile will destroy all fighters.<br>If the fighter count is greater than 1000, the missile will kill 4% of the fighters on the planet (the 4% is randomised by 15%).<p>Once the fighters have been figured, its the turn of the colonists to perish (provided there are some). Any less than 3000, and they are all killed off instantly. More than 3000, and the missile will kill 4% of them (the 4% is also randomised by 15%).";


/************
* Index
*************/

} else {
	$help_type = "Index";
	$error_str .= "<h3><b>Welcome to the Solar Empire Help Pages</b></h3>The aim of these pages is to provide you with all the information you'll need to become a successfull SE player. As you may have noted, this help page comes up in its own window, so you can flick between it and the game to see whats being talked about. <br><br>If you're new to Solar Empire, then lets <a href=help.php?started=1>get you started</a>.<br><br>If you're more of a Vet, then you may want to peruse the other sections of the site to try and find out more information to try and improve your play.<br><br>Note: There are no strategies in these pages, they can be found in the game forums, and on the strategy sites listed in the <a href=help.php?ext=1>External Links</a> Section.";
}


print_header("Help - $help_type");


#prints help left column
echo "<a name=top> </a>";
if(!isset($popup)){

	echo '<table border=0 cellspacing=0 cellpadding=0>';
	echo '<tr><td valign=top width=150>';
	echo "<h2>Help topics</h2>\n" .
	 "<ul style=\"list-style: none; padding-left: 0px;\">\n";
	echo "\t<li><a href=help.php?started=1>Getting Started</a></li>\n";
	echo "\t<li><a href=help.php?misc=1>Misc Info</a></li>\n";

	if(!isset($clan_member_limit) || ($clan_member_limit > 0 && $max_clans > 0)){
		echo "\t<li><a href=help.php?clans=1>Clans</a></li>\n";
	}

	if($enable_politics == 1){
		echo "\t<li><a href=help.php?politics=1>Politics</a></li>\n";
	}

	echo "\t<li><a href=help.php?equip=1>Equipment</a></li>\n";
	echo "\t<li><a href=help.php?upgrades=1>Upgrades & Accessories</a></li>\n";
	echo "\t<li><a href=help.php?planet=1>Planets</a></li>\n";
	echo "\t<li><a href=help.php?story=1>The Stories</a></li>\n";

	if(!isset($random_events) || $random_events > 0){
		echo "\t<li><a href=help.php?random=1>Random Events</a></li>\n";
	}

	if(!isset($flag_research) || $flag_research == 1){
		echo "\t<li><a href=help.php?research=1>Research & Blackmarkets</a></li>\n";
	}

	if(!empty($db_name)){
		echo "\t<li><a href=help.php?ship_info=1&shipno=-1>Ship Listings</a></li>\n";
	}
	echo "\t<li><a href=help.php?tech_info=1>Technical Information</a></li>\n";

	if($admin_var_show == 1) {
		echo "\t<li><a href=help.php?game_vars=1>Game Variables</a></li>\n";
	}

	echo '</ul></td><td valign=top>';
} else {
	$rs = "<center><a href=\"javascript:window.close()\">Close Window</a></center>";
}

echo $error_str;

print_footer();

?>
