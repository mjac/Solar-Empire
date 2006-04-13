<?php

require_once('inc/user.inc.php');

function list_specials($config_breakdown = false)
{
	$ret_str = "";

	// big array contains smaller arrays with details of items within it.
	$specials = array(
		'bs' => array(
			'short_for' => 'Battleship',
			'type' => 'Warfare',
			'description' => 'This dictates that a ship is registered as a Battleship. This shows the ship will do more damage in combat.<br />It also allows a ship&#8217;s fighter capacity to go above 4,999.'),

		'sh' => array(
			'short_for' => 'Shield Charger',
			'type' => 'Warfare',
			'description' => 'A unit that can be fitted to a ship to increase the shield charge rate by a further 25%!'),

		'hs' => array(
			'short_for' => 'High Stealth',
			'type' => 'Stealth',
			'description' => 'Nothing about the ship can be discerned. Only that it is there, and it&#8217;s size. It is not possible to attack a ship with High Stealth, unless the attacking ship has a scanner. Ships with High stealth also take much less damage when attacked.'),

		'ls' => array(
			'short_for' => 'Low Stealth',
			'type' => 'Stealth',
			'description' => 'The type of ship will be shown, but other players will not be able to see the stat&#8217;s for it. Everything about it can be seen if the enemy has a ship with a scanner on it. Ships with Low Stealth do increased damage when attacking, as they have an element of surprise.'),

		'na' => array(
			'short_for' => 'No Attack',
			'type' => 'Limiter',
			'description' => 'This ship type cannot attack anything.'),

		'oo' => array(
			'short_for' => 'Only One',
			'type' => 'Limiter',
			'description' => 'You can only own one ship with this special at a time.'),

		'po' => array(
			'short_for' => 'Planets Only',
			'type' => 'Limiter',
			'description' => 'This ship type can only attack planets and is unable to attack other ships.'),

		'so' => array(
			'short_for' => 'Ships Only',
			'type' => 'Limiter',
			'description' => 'This ship type can only attack other ships, and may not attack planets.'),

		'sv' => array(
			'short_for' => 'SuperWeapon Mark 1 - Quark Disrupter',
			'type' => 'SuperWeapon',
			'description' => 'This ship has a super weapon on it. The superweapon in question being a Quark Displacer, which can only be fired at planets, but does a serious amount of damage per shot, at a cost of a number of turns. Can attack Hostile planets as well as passive ones.'),

		'sw' => array(
			'short_for' => 'SuperWeapon Mark 2 - Terra Maelstrom',
			'type' => 'SuperWeapon',
			'description' => 'This ship has a Mark 2 super weapon on it, a Terra Maelstrom.  This can only be used against planets. It can be a very major threat to a players planet provided there are enough turns to use it. Can attack Hostile planets as well as passive ones.'),

		'sj' => array(
			'short_for' => 'Sub-Space Jump Drive',
			'type' => 'Propulsion',
			'description' => 'Allows the ship to make subspace jumps to anywhere in the galaxy without using warp-links. The jumping ship may only be accompanied by 10 other ships during a jump (this limit goes away with the wormhole stabiliser). The turn cost is based solely upon the direct distance to the destination, irrespective of the number of ships following it.'),

		'tw' => array(
			'short_for' => 'Transwarp Drive',
			'type' => 'Propulsion',
			'description' => 'The ship has a built in transwarp drive. This means it can jump limited distances without needing to use warp links. It can tow any number of ships, however each ship towed adds one turn to the turn cost of the jump.'),

		'ws' => array(
			'short_for' => 'Wormhole Stabiliser',
			'type' => 'Propulsion',
			'description' => 'An upgrade that is strictly for ships that have a SubSpace Jump Drive (sj). This will allow the selected ship to participate in the <b class=b1>AutoShifting</b> of colonists and materials from Sol to a players planet, and between a players planet. Without one in the system, Autoshifting is not possible.<br />This upgrade also allows any number of ships to follow a ship that is making a sub-space jump.'),

		'fr' => array(
			'short_for' => 'Freighter',
			'type' => 'Misc',
			'description' => 'This ship is a freighter. This means that it will generally do more counter-damage when attacked, than a normal ship.'),

		'sc' => array(
			'short_for' => 'Scanner',
			'type' => 'Misc',
			'description' => 'A scanner enables the ship fitted with it to see cloaked ships, and also to attack them. To use the scanner succesfully you must be commanding the ship with it in.<br />Scanners also allow for a slight increase in damage during battle.'),

			); #end of specials array declaration.

	#print out all the specials
	if ($config_breakdown === false) {
		$ret_str .= <<<END
<table class="simple" style="margin-left: 1em auto;">
	<tr>
		<th>Name (abbreviation)</th>
		<th>Type</th>
		<th>Description</th>
	</tr>

END;
		foreach ($specials as $key => $value) {
			$ret_str .= <<<END
	<tr>
		<td>$value[short_for] ($key)</td>
		<td>$value[type]</td>
		<td>$value[description]</td>
	</tr>

END;
		}
		$ret_str .= <<<END
</table>
END;
		#print out only selected specials
	} elseif(!empty($config_breakdown)) {
		$config_array = explode(':', $config_breakdown);
		foreach ($config_array as $value) {
			#if the user is playing with a config that isn't on the list.
			if (empty($specials[$value])) {
				$ret_str .= "<p>No entry for <b>$value</b></p>";
			} else { #print out the details for the upgrade
				$ret_str .= "<table class=\"simple\" style=\"margin-left: auto; margin-right: auto;\">\n\t<tr>\n" .
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

$error_str = "";


//pop-specials info.
if (isset($special_info)) {
	$error_str .= "";
} elseif (isset($game_vars)) {
	if(!isset($gameOpt['admin_var_show'])) {
		$error_str .= "You may not see the vars for a game when you are not in said game.";
	} elseif($gameOpt['admin_var_show'] == 0) {
		$error_str .= "The Admin of this game, doesn't want the game variables displayed.";
	} else {
		db("select * from [game]_db_vars order by name");
		$error_str .= <<<END
<h1>Game Variables</h1>
<p>Shown below are all the variables for the game, as set by the Admin.</p>
<dl>

END;
		while($var = dbr()) {
			$error_str .= "\t<dt>$var[name] = ${var['value']}</dt>\n" .
			 "\t<dd>$var[descript]</dd>\n";
		}
		$error_str .= "</dl>\n";
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
} elseif(isset($ship_info)) {
	$ship_types = load_ship_types();
	if ($shipno < 0) {//list stats of all ships:
		if ($shipno == -1) {
			$error_str .= "<h1>Ship Listing</h1><p>Listed below is information for all ships that can be brought from the Shipyards at Earth.</p>";
		} elseif ($shipno==-2) {
			$error_str .= "<h1>Ship Listing</h1><p>Listed below is information for all ships that can be brought from the blackmarkets.</p>";
		}

		$error_str .= <<<END
<table class="simple">
	<tr>
		<th>Name</th>
		<th>Appearance</th>
		<th>Size</th>
		<th>Type</th>
		<th>Hull</th>
		<th>Fighters</th>
		<th>Shields</th>
		<th>Cargo bays</th>
		<th>Mining rate</th>
		<th>Specials</th>
		<th>Upgrades</th>
		<th>Description</th>
	</tr>

END;

		foreach ($ship_types as $ship_stats) {
			$ship_stats['cost'] = number_format($ship_stats['cost']);
			$size = discern_size($ship_stats['hull']);
			$config = empty($ship_stats['config']) ? '-' : $ship_stats['config'];

			$error_str .= <<<END
	<tr>
		<th>$ship_stats[name] ($ship_stats[abbr])</th>
		<td style="padding: 0;"><img height="120" width="160" 
		 src="img/ships/$ship_stats[appearance].jpg" 
		 alt="$ship_stats[appearance]" /></td>
		<td>$size</td>
		<td>$ship_stats[type]</td>
		<td>$ship_stats[hull] / $ship_stats[max_hull]</td>
		<td>$ship_stats[fighters] / $ship_stats[max_fighters]</td>
		<td>$ship_stats[max_shields]</td>
		<td>$ship_stats[cargo_bays]</td>
		<td>$ship_stats[mining_rate]</td>
		<td>$config</td>
		<td>$ship_stats[upgrades]</td>
		<td>$ship_stats[description]</td>
	</tr>

END;
		}

		$error_str .= <<<END
</table>

END;

		$help_type = "Complete Ship Listing";
		//list stats for specific ship.
	} else {

		$ship_counter = $shipno;
		$ship_stats = $ship_types[$ship_counter];
		$ship_stats['cost'] = number_format($ship_stats['cost']);

		$size = discern_size($ship_stats['hull']);
		$specials = empty($ship_stats['config']) ? 'None' : $ship_stats['config'];

		$error_str .= <<<END
<div style="padding: 5px; text-align: center;">
<h1>$ship_stats[name] ($ship_stats[abbr])</h1>
<p><img height="120" width="160" src="img/ships/$ship_stats[appearance].jpg" alt="$ship_stats[name]" /></p>
<blockquote><p>$ship_stats[description]</p></blockquote>
<table class="simple" style="margin-left: auto; margin-right: auto;">
	<tr>
		<th>Size</th>
		<td>$size</td>
	</tr>
	<tr>
		<th>Type</th>
		<td>$ship_stats[type]</td>
	</tr>
	<tr>
		<th>Hull</th>
		<td>$ship_stats[hull] / $ship_stats[max_hull]</td>
	</tr>
	<tr>
		<th>Fighters</th>
		<td>$ship_stats[fighters] / $ship_stats[max_fighters]</td>
	</tr>
	<tr>
		<th>Max Shields</th>
		<td>$ship_stats[max_shields]</td>
	</tr>
	<tr>
		<th>Cargo Bays</th>
		<td>$ship_stats[cargo_bays]</td>
	</tr>
	<tr>
		<th>Mining Rate</th>
		<td>$ship_stats[mining_rate]</td>
	</tr>
	<tr>
		<th>Specials</th>
		<td>$specials</td>
	</tr>
	<tr>
		<th>Upgrade Pods</th>
		<td>$ship_stats[upgrades]</td>
	</tr>
	<tr>
		<th>Cost</th>
		<td>$ship_stats[cost]</td>
	</tr>
</table>
END;

		$error_str .= "<h2>Specials Meanings</h2>" . list_specials($ship_stats['config']);

		$help_type = "$ship_stats[name] Ship Info";
	}

	#don't show the link if in a pop-up window.
	if(!isset($popup)) {
		$error_str .= "<h2>Specials</h2>". list_specials();
	}
} elseif(isset($equip)) {
		$error_str .= <<<END
<h1>Equipment</h1>
<p>Equipment is the general stuff you use to do things, and that doesn't 
fall into some other category.</p>
<ul>
	<li><a href="#fighters">Fighters and shields</a></li>
	<li><a href="#genesis">Genesis device</a></li>
	<li><a href="#bombs">Bombs</a></li>
</ul>

<h2 id="fighters">Fighters and shields</h2>
<p><img src="img/equipment/fighter.jpg" alt="Fighters" />
<img src="img/equipment/shield.jpg" alt="Shields" /></p>
<p>All ships have a specified maximum number of fighters that they can be 
equipped with.  Fighters are the units that do all of the work in a battle 
situation. They deal damage to enemy ships/fighters and when attacked, they 
deal counter damage to the attacker.</p>
<p>A ship that has only shields will only be able to take damage. Shields 
absorb the first impacts of enemy fighters on your ship. Once they have 
run-out the fighters get to work in defending you ship. Shields automatically 
replenish over each hour until they reach the maximum a ship can hold. 
Fighters cannot and do not.</p>
<p>Fighters can be switched from ship to planet, as can shields (provided the
target planet has a shield generator).</p>

<h2 id="genesis">Genesis device</h2>
<p><img src="img/equipment/genesis_device.jpg" alt="Genesis device" /></p>
<p>Genesis Devices are used to create a planet. Simply buy a genesis device, 
go to a system other than system #1, and click the <em>Use Genesis Device</em> 
link in the right column of the star system.  This will create a thriving 
planet, which will have 1,000 colonists on it, which you can then practice 
your despotic ways upon.</p>

<h2 id="bombs">Bombs</h2>
<dl>
	<dt>Alpha bomb</dt>
	<dd><img src="img/equipment/bomb_alpha.jpg" alt="Alpha bomb" /></dd>
	<dd>Eliminates all shields from every ships in a system (yours included), 
	no matter how many shields the ships may have. It will not actually do 
	any damage to the fighters, or the ships.</dd>

	<dt>Gamma bomb</dt>
	<dd><img src="img/equipment/bomb_gamma.jpg" alt="Gamma bomb" /></dd>
	<dd>Does 200 damage to each ship in a star system (yours included). 
	It will take this damage from shields first, and when all shields are gone, 
	then from the fighter count. If there are no fighters, then it will simply 
	destroy the ship.  This bomb is used most effectively when detonated after 
	an Alpha Bomb (when there are no shields); it can destroy entire 
	fleets.</dd>

	<dt>Delta bomb</dt>
	<dd><img src="img/equipment/bomb_delta.jpg" alt="Delta bomb" /></dd>
	<dd>Causes tremendous damage (over 4000 points) to every ship in the 
	system.</dd>
</dl>

END;

} elseif(isset($upgrades)) {
	if(isset($specials)){
		$error_str .= "<a href=\"javascript:history.back()\">Back to Upgrades & Accessories</a>";
		$error_str .= list_specials();
		$error_str .= "<br /><a href=\"javascript:history.back()\">Back to Upgrades & Accessories</a>";
	} else {

		$error_str .= <<<END
<h1>Upgrades and accessories</h1>
<p>There are numerous other upgrades not mentioned here, however where they 
do appear they in the game they come with a suitable explanation.</p>
<p>Upgrades can be purchased from either the <em>Accessories and Upgrades 
Store</em> or the <em>Auction House</em>.</p>
<ul>
	<li><a href="#about">About upgrades</a></li>
	<li><a href="#basic">Basic upgrades</a></li>
	<li><a href="#transwarp">Transwarp drive</a></li>
	<li><a href="#shroud">Shrouding unit</a></li>
	<li><a href="#scanner">Scanner</a></li>
	<li><a href="#shield_charge">Shield charging unit</a></li>
	<li><a href="#wormhole">Wormhole stabiliser</a></li>
	<li><a href="#terra">Terra maelstrom</a></li>
	<li><a href="help.php?upgrades=1&amp;specials=1">List of config meanings</a></li>
</ul>

<h2 id="about">About upgrades</h2>
<p>Upgrades are used to improve your current ship in one way or another. 
You may simply want to put some more shield capacity onto it, or you may want 
to give it a new Super Weapon, the point remains that you are aiming to 
improve it.</p>
<p>Each upgrade requires one upgrade pod (unless otherwise stated), and will 
allow your ship to do something it couldn't do before. However once a upgrade 
pod has been used, it cannot be reclaimed. Use upgrade pods wisely on 
ships that you intend to keep in your service for a long time.</p>

<h2 id="basic">Basic upgrades</h2>
<p>The three basic upgrades will each upgrade certain aspects of the ship you 
are commanding for a small price.  Fighters, shields and cargo can all be 
upgraded in this fashion.</p>

<h2 id="transwarp">Transwarp drive</h2>
<p>This upgrade will allow a ship to jump between systems whilst skipping the 
systems in-between. It does cost a couple more turns, however its invaluable 
for getting to star islands, and can tow an unlimited number of ships. It may 
not be installed onto ships that have the sub-space jump capability.</p>

<h2 id="shroud">Shrouding unit</h2>
<p>This upgrade gives a ship the <em>ls</em> configuration. This means that 
enemy players will not be able to determine any information about the ship, 
unless they have a ship with a scanner on it.</p>

<h2 id="scanner">Scanner</h2>
<p>Allows a user to see all information about a ship that would otherwise be 
an 'unknown', if the ship is lightly stealthed. However, if the ship is Highly 
Stealthed, then it will give limited information about a ships fighter count, 
and such like, however it will not be able to determine the ships owner.</p>

<h2 id="shield_charge">Shield charging unit</h2>
<p>This upgrade will allow a ship's shields to regenerate 25% faster.</p>

<h2 id="wormhole">Wormhole stabiliser</h2>
<p>An absolutely essential upgrade if you have a Transverser, or plan on 
building a planet, or both.  This upgrade allows for Autoshifting to take 
place, whereby a fleet of ships will shift from your planet, to Sol, nab as 
many colonists as it can carry and you can afford, and then come back and 
dump them onto the planet. Saves vast amounts of time, and generally costs 
the same number of turns as doing it manually.  This upgrade also allows for 
an infinite number of ships to be towed with it when it makes a jump (from 
10 ships max if there is no stabiliser installed). May only be used on ships 
with a sub-space jump drive.</p>

<h2 id="terra">Terra maelstrom</h2>
<p>This upgrade is here because of its complexity. It does not require an 
upgrade pod, instead the ship must already have the Quark Disrupter built 
in.</p>
<p>It can only be fired at a planet, and uses either a lot of turns, or ALL of 
a players turns, depending on the size of the planet, and the turn count of 
the user. In general, it is best used against big planets when the player has 
a very high turn count.  It also charges the ships shields at 3 times the 
normal rate.</p>

END;
	}
} elseif(isset($planet)) {
	$error_str .= <<<END
<h1>Planets</h1>

<p>Planets are created by using a <a href="help.php?equip=1#genesis">Genesis</a> 
device, which you must have purchased at Earth's Equipment shop. Once you have a 
Genesis device, simply go to a system where you want to build a planet (you may 
not build a planet in system #1), and click the <em>Use Genesis Device</em> 
link which appears in the right column of the star system view.</p>

<ul>
	<li><a href="#basics">Planet Basics</a></li>
	<li><a href="#colonists">Colonists</a></li>
	<li><a href="#missiles">Missiles</a></li>
	<li><a href="#shields">Shield Generators</a></li>
</ul>

<h2 id="basics">Planet Basics</h2>
<p>The basic functions of a planet are to produce fighters, defend a fleet,
and/or make money. Any player may make a planet assuming (s)he is out of safe 
turns, and can afford a Genesis Device from the Equipment shop at Earth.  
<p>Planets may be renamed at any time, and a password may be set on a planet to 
keep clan members from taking your money/minerals/fighters/colonists. Passwords 
are usually not recommended unless you are uneasy about another clan member, 
because they tend to restrict your clan members from free movement of those 
commodities.</p>
<p>Once in planet view, you have the ability to claim a planet.  Each planet 
has the ability to house virtually limitless numbers of fighters.  Fighters are 
the actual units that defend your planet/fleet from oncoming attack by enemy 
forces.  Fighters are set at either one of two modes, Passive and Hostile. 
<em>Hostile</em> fighters will defend your system from any ship not belonging 
to you, or your clan (should you be in one), while <em>Passive</em> fighters 
will only defend the planet itself from direct attack. Setting fighters to 
Hostile also defends every ship within the system.</p>

<h2 id="colonists">Colonists, taxing and production</h2>
<p>Colonists are the workforce of the planets. 500 <strong>assigned</strong> 
colonists can take 10 units of fuel and 10 units of metal and produce a 
specified amount of electronics determined by the game variables.  100 
assigned colonists can use one unit of fuel, metal and electronics to produce 
a specified number of fighters which will be used to defend the plane  with 
until you find other things for them to do.</p>
<p>An admin specified number of colonists can be used to create one unit of 
organics.  See the <a href="help.php?game_vars=1">game variables</a> section 
of this help for what the variables are for this game)</p>
<p>Colonists that are not doing anything - Idle Colonists - will pay taxes 
and reproduce. Taxes set at a level higher than 11% will cause negative 
reproduction (death). Taxes set at a lower level will cause higher 
reproduction. At a zero percent tax rate, idle colonists will reproduce at 
a rate of 30% per night.</p>

<h2 name="missiles">Missiles</h2>
<p>Missile Launch Pads can be constructed on any planet. The cost is given to 
you on the planet menu screen. These usually take a considerable amount of 
time, money and materials to build, and they give you the ability to 
construct Omega Missiles.  Omega Missiles also require a large amount of 
materials to construct. However, once constructed you may launch the Omega 
Missile at the planet of your choice.  When launching, you must also have 
fuel and turns available, from which the missile generates the energy 
necessary to make the long distance trek from one solar system to another.
Missile Launch Pads may also be purchased through auction at the auction 
house. These may run for as little as 100,000 credits with no cost in 
minerals.</p>

<h2 id="shields">Shield generators</h2>
<p>A shield generator may also be built on any planet. A typical shield 
generator can generate and store up to 3000 units of shields at a time, which
may then be used to augment your fleets shields. Shield generators 
<strong>in no way</strong> increase the defensive capabilities of a planet 
itself.</p>
<p>Larger generators can be purchased at an auction house. These store 
significantly more shields, and can also regenerate much faster.</p>

END;
} elseif(isset($clans)) {
	$error_str .= <<<END
<h1>Clan information</h1>
<p>This page contains basic information on clans: how to join, what they are 
good for and that sort of thing.</p>
<ul>
	<li><a href="#basic_clan">Basic clan information</a></li>
	<li><a href="#loyal_clan">Loyalty in a Clan</a></li>
</ul>

<h2>Basic clan information</h2>
<p>It is not required to join a clan. Some players play solo with great 
success (sometimes), however if you are new at this game then it should be 
a high priority to find a clan. You may search through different organised 
clans by selecting the Clan Control option. You may also post onto the Forum.</p>
<p>If you are in a newbie game, just post that you are a newbie and are looking
for some aid in the game. You may even find someone advertising a clan for 
newbie assistance. These are usually experienced members who are willing to 
give a helping hand to anyone new to the game.</p>
<p>After you post a message to the forum, check back often to see if someone
else has invited you. You will also want to check your Messages to see if 
anyone has sent you a personal reply. To go through the process of joining a 
clan, click the Clan Control link again. Now find the name of the clan you 
have been recruited to and select the Join link. Then enter the password of 
the clan you are entering, usually given to you by the clan leader.</p>

<h2>Loyalty in a clan</h2>
<p>Clan loyalty is one of the more important aspects of the game: if you show 
strong loyalty to help your clan without many complaints to the clan leader 
then others may ask you to be part of their clan at a later date. If you are 
disloyal to your clan, you will be most likely be kicked out of the clan, and 
then probably destroyed by that clan's members to boot. After that offence, 
information about you will be posted on the forum and you will have a very 
difficult time finding clans to join in the future.</p>

END;
} elseif(isset($misc)) {
	$error_str .= <<<END
<h1>Miscellaneous Information</h1>
<p>This page contains much information that is really quite important, and is a 
must read for any newbie to the game.</p>
<ul>
	<li><a href="#misc_turns">Turns</a></li>
	<li><a href="#misc_attack">Attacking</a></li>
	<li><a href="#misc_mining">Mining</a></li>
	<li><a href="#misc_moving">Moving and autowarp</a></li>
</ul>

<h2 id="misc_turns">Turns</h2>
<p>Turns are used for pretty much anything. Given below is a small table 
which lists some of the things that use turns and their costs. A players turns 
are augmented each hour by some more turns. These turns are given out by the 
game, and vary from game to game. For this game they are at 
<em>$gameOpt[increase_turns] per $gameOpt[process_turns] seconds</em>.
<table class="simple">
	<tr>
		<th>Action</th>
		<th>Turn cost</th>
	</tr>
	<tr>
		<td>Movement</td>
		<td>1</td>
	</tr>
	<tr>
		<td>Attack ship</td>
		<td>$attack_turn_cost_ship</td>
	</tr>
	<tr>
		<td>Attack planet</td>
		<td>$attack_turn_cost_planet</td>
	</tr>
	<tr>
		<td>Self destruct</td>
		<td>1</td>
	</tr>
	<tr>
		<td>Hiring colonists</td>
		<td>1 for every ship</td>
	</tr>
	<tr>
		<td>Selling at port</td>
		<td>0, 1 or 5 depending on method</td>
	</tr>
</table>
<p>Many of the variables listed in the table above may change from game to 
game. The variables shown are correct for this game. Always check the 
<a href="help.php?game_vars=1">Game Variables</a> section for a complete and 
up-to-date variable list for the game.</p>

<h2 id="misc_attack">Attacking</h2>
<p>To be able to attack a ship requires that some conditions are met.</p>
<ul>
	<li>You and the enemy must be out of safe turns</li>
	<li>Attacking must be enabled</li>
	<li>You need to have enough turns to be able to attack</li>
</ul>
<p>When you are able to attack a player, a link will appear on the target's 
ship name which you click to attack.</p>

<h2 id="misc_mining">Mining</h2>
<p>Different ships mine at different rates; the rate for each ship can be 
seen in the <em>ship listing</em> section of the help files.</p>
<p>Material comes into a ships cargo bays at a set period of time, based on
the game variables.</p>
<p>There are no conditions attached, anyone with a mining capable ship, or 
fleet of them, can mine.</p>

<h2 id="misc_moving">Moving and autowarp</h2>
<p>There are several different types of ways to move between systems.</p>
<table class="simple">
	<tr>
		<th>Name</th>
		<th>Equipment required</th>
		<th>Description</th>
		<th>Turn cost</th>
	</tr>

	<tr>
		<td>Warp-links</td>
		<td>None</td>
		<td>The normal way to get around. All stars are connected to other 
		stars via these links. It is possible to get so called star islands
		which is where a cluster of islands is segregated from the main 
		group.</td>
		<td>One turn</td>
	</tr>

	<tr>
		<td>Wormholes</td>
		<td>None</td>
		<td>These are links between two random systems. They can go either 
		one-way, or two-way. Using one is just like using a normal warp-link, 
		however they are random in their locations.</td>
		<td>One turn</td>
	</tr>

	<tr>
		<td>Transwarp</td>
		<td><a href="help.php?upgrades=1#transwarp">Transwarp Drive</a></td>
		<td>This is best used for getting to star islands, and skipping around 
		peninsulas. Otherwise its turn cost gets expensive in comparison to 
		normal travel. Has a limited range of about 15 Light-years (2 &ndash; 3 
		systems), and can have any number of ships in tow (however, each extra 
		ship in tow costs 1 turn more to move).</td>
		<td>Medium&ndash;low: depends on distance jumped and number of ships in tow.</td>
	</tr>

	<tr>
		<td>Sub-space jump</td>
		<td>Transverser</td>
		<td>Can jump from any one location in the game to another, skipping 
		everything in-between. It may only tow 10 ships at a time, though, 
		unless the wormhole stabiliser upgrade is installed. Best for 
		Autoshifting colonists</b> using the 
		<a href="help.php?upgrades=1#wormhole">Wormhole Stabiliser</a> 
		upgrade.</td>
		<td>Medium&ndash;high: depends on distance jumped.</td>
	</tr>
</table>
<p>The cost for moving between systems can vary due the admin being able to 
control it.  There is a function called Autowarp which can be used to plot, 
and follow a course to a different system from the system you are in. This 
will not always find the shortest route to your destination, it will not use 
wormholes, and it will cost one turn to implement. However it will also save 
you considerable time in getting to your destination.  The link for Autowarp 
can be seen in the right column in the star system view.</p>

END;
} elseif(isset($started)) {
	$error_str .= <<<END
<h1>Getting Started</h1>
<p>This guide should be open in a different window from the one in which you 
are playing the game in.  The idea being that as you read the instructions in 
this window, you can then switch over to the game and see just what we are 
talking about.</p>

<h2>Beginning</h2>
<p>Check and see how many credits you are given at signup &mdash visible 
in the column on the left, directly below your player-name.  Why? Because 
you need to buy more ships. Money-making is imperative in this game. That 
means the more ships you have, the faster you can make money, and the faster 
you can buy more ships.</p>

<h2>Creating a fleet</h2>
<p>Land on <em>Earth</em> in System #1 and visit the <em>Spacecraft 
Emporium</em>. Here you will find numerous different kinds of ships, 
with the ones that you want to look at at this stage in the game being 
the <em>Freighters</em> at the top of the page.</p>
<p class="figure"><img src="img/help/menu_earth_ship.png" alt="Select the ship ship at earth" /></p>
<p>Once the ship has been brought and named, you will want to collate your 
ships into a fleet: one ship will tow the others.  You can command different 
ships by clicking the <em>command</em> button or you can tow them by selecting 
that instead.</p>
<p class="figure"><img src="img/help/ship_shop.png" alt="Purchase ships on earth" /></p>

<h2>Navigating</h2>
<p>When you have all your ships in tow, leave the System #1 and go to an 
adjacent system.  To do this simply click one of the numbers at the top of 
the screen or an adjacent system on the map; each number is a different 
system.  Autowarp enables you to travel to any system without having to 
remember the link path.</p>
<p class="figure"><img src="img/help/system_title.png" alt="System title" /></p>

<h2>Mining</h2>
<p>In the new system you will quite likely find fuel, metal or both. If the
system is empty, navigate to close systems until you find some &#8212; 
don&#8217;t go too far as you have a limited number of turns. You are better 
off finding a system near Sol that has fuel in it.  Once in a system with 
minerals, assign the fleet to mine either fuel or metal.</p>
<p class="figure"><img src="img/help/menu_ship_mine.png" alt="Assigning ships to mine fuel" /></p>

<h2>Congratulations</h2>
<p>Now you should be ready to start an empire: you will gain 
$gameOpt[increase_turns] turns per $gameOpt[process_turns] seconds, spend 
them and improve your status.</p>

END;

	$help_type = "Getting Started";

/************
* Technical Documents
*************/

} elseif(isset($tech_info)) {
	$help_type = "Technical Information";
	$error_str .= <<<END
<h1>Technical Information</h1>
<p>This page contains technical information about the game, and as such can 
get pretty technical.  The idea behind the page is to give details about how 
certain aspects of the game work, so as to allow players to better exploit 
those areas.</p>

<h2 id="tech_terras">Terra Maelstroms</h2>
<p>These are vicious little anti-planet weapons that are an upgrade for the 
quark disrupter.  At present, the quark disrupter does 600 to 1400 damage per 
shot (randomised) for 30 turns.</p>
<p>The Terra takes 50 turns as an absolute minimum to fire. What happens is 
this:<br />Those 50 turns can generate between 4000 and 6000 damage
(randomised).  If the planet has more
than that number of fighters, and the user has more than 50 turns, the game
will first find out how many turns is the maximum a player can have. If the
player has the maximum number of turns, they will kill between 65% and 75% of
the fighters on the planet (randomised). If they don't have that many turns,
the gun will use all of the turns a player has and work out what amount up to
that 65-75% the user is capable of doing.<br.Whatever number is a result of
working out the damage done is then randomised by 5%.</p>
<p>Once that is all done, the game will see if the damage done by using all of
a players turns is actually greater than the fixed damage done for 50 turns. 
Whichever does the most damage wins, and is used.</p>

<h2 id="tech_omega">Omega Missiles</h2>
<p>Like the terra maelstroms, omega missiles things do damage based on 
percentages.  The missile takes at least 5 turns to launch, but this number
can go up, the further away the target planet is. The amount of fuel required 
is 20 times greater than the turns required.<br />If there are less than 100 
fighters on the planet, the missile will destroy the planet.<br />If there 
are more than 100, but less than 1000, the missile will destroy all fighters.
If the fighter count is greater than 1000, the missile will kill 4% of the 
fighters on the planet (the 4% is randomised by 15%).<p>Once the fighters have 
been figured, its the turn of the colonists to perish (provided there are 
some). Any less than 3000, and they are all killed off instantly. More than 
3000, and the missile will kill 4% of them (the 4% is also randomised by 
15%).</p>

END;


/************
* Index
*************/

} else {
	$help_type = "Index";
	$error_str .= <<<END
<h1>Solar Empire help</h1>
<p>The aim of these pages is to provide you with all the information you will 
need to become a successful player.</p>
<p>If you are new to Solar Empire, then lets <a href="help.php?started=1">get 
you started</a>.  If you are more of a Vet, you may want to peruse the other 
sections to try and find out more information to improve your play.</p>
<p>There are no strategies in these pages, they can be found in the game 
forums and should be shared through clans and community.</p>

END;
}


print_header("Help" . (isset($help_type) ? " - $help_type" : ''));


#prints help left column
if(!isset($popup)){

	echo <<<END
<table>
	<tr>
		<td valign="top" width="220">
		<h1>Help topics</h1>
		<ul>
			<li><a href="help.php?started=1">Getting started</a></li>
			<li><a href="help.php?misc=1">Miscellaneous info</a></li>

END;

	if ($gameOpt['max_clans'] > 0) {
		echo "\t<li><a href=\"help.php?clans=1\">Clans</a></li>\n";
	}

	echo <<<END
			<li><a href="help.php?equip=1">Equipment</a></li>
			<li><a href="help.php?upgrades=1">Upgrades and accessories</a></li>
			<li><a href="help.php?planet=1">Planets</a></li>
			<li><a href="help.php?story=1">Stories</a></li>
			<li><a href="help.php?ship_info=1&amp;shipno=-1">Ship listings</a></li>
			<li><a href="help.php?tech_info=1">Technical information</a></li>

END;
	if($gameOpt['admin_var_show'] == 1) {
		echo "\t<li><a href=help.php?game_vars=1>Game variables</a></li>\n";
	}

	echo <<<END
		</ul>
		<p>Return to <a href="system.php">star-system</a> overview</p></td>
		<td valign="top">

END;
}

echo $error_str;

print_footer();

?>
