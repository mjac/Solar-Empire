<?php

require_once('inc/user.inc.php');
require_once('inc/attack.inc.php');
require_once('inc/planet.inc.php');

if (!(isset($target) && ($planet = getPlanet($target)) && 
     canAttackPlanet($planet))) {
	header('Location: system.php');
	exit;
}

$out = "<h1>Planet assault</h1>\n";

if (!giveTurnsPlayer(-$gameOpt['attack_turn_cost_planet'])) {
	$out .= "<p>You do not have enough turns to attack this planet.</p>\n";
	print_page('Planet assault', $out);
	exit;
}

$pName = print_name($userShip);
$tName = print_name($planet);

$out .= <<<END
<table class="attackReport">
	<tr>
		<th>$userShip[ship_name]</th>
		<th>$planet[planet_name]</th>
	</tr>

	<tr>
		<td>$pName</td>
		<td>$tName</td>
	</tr>

	<tr>
		<td>$userShip[class_name]</td>
		<td>standard planet</td>
	</tr>

	<tr>
		<td><img src="img/ships/$userShip[appearance].jpg" width="160"
		 height="120" alt="$userShip[appearance] ship" /></td>
		<td><img src="img/planets/$planet[planet_img].jpg" width="200"
		 height="200" alt="planet $planet[planet_img]" /></td>
	</tr>
</table>

<h2>Result</h2>

END;

$planet['ship_name'] = $planet['planet_name']; // quick hack for now

$uShipOrg = $userShip;
$planetOrg = $planet;

$result = shipVplanet($userShip, $planet);
$rAttack = updateShip($userShip, $uShipOrg);
$rDefend = updatePlanet($planet, $planetOrg);

$out .= atkPlanetResult($uShipOrg, $planetOrg, $planet) .
 atkShipResult($planet, $uShipOrg, $userShip);

checkPlayer($user['login_id']);
checkShip();


print_page('Planet assault', $out);

?>
