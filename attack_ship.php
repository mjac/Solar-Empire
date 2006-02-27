<?php

require_once('inc/user.inc.php');
require_once('inc/attack.inc.php');

if (!isset($target)) {
	header('Location: system.php');
	exit();
}

$out = "<h1>Attack result</h1>\n";

if (!($tShip = getShip($target)) ||
     $tShip['ship_id'] == $userShip['ship_id'] ||
     $tShip['location'] != $userShip['location']) {
	header('Location: system.php');
	exit();
}

if (!canAttackShip($tShip)) {
	$reasons = array();
	if ($user['clan_id'] === NULL || $user['clan'] != $ship['clan_id']) {
		$reasons[] = 'the ship is owned by one of your fellow clan members';
	}
	if ($ship['turns_run'] > $turns_safe) {
		$reasons[] = 'you are still in safe-turns';
	}
	if (shipHas($tShip, 'hs') && !shipHas($userShip, 'sc')) {
		$reasons[] = 'you cannot see this ship';
	}

	$out .= "<h2>You may not attack this ship</h2>\n";
	if (!empty($reasons)) {
	    $out .= "<ul>\n\t<li>" . implode("</li>\n\t<li>", $reasons) .
		 "</li>\n</ul>\n";
	}

	print_page('Attack attempt failed', $out);
}


// Chance of defending is n / (n + 1) - 0 ships 0 chance - 2 ships 2/3 chance
if ($other = fleetDefender($tShip)) {
    $tShip = $other;
    $out .= "<p>$tShip[ship_name] has <strong>flown in to defend</strong> " .
	 print_name($tShip) . "'s fleet</p>\n";
}


$pName = print_name($userShip);
$tName = print_name($tShip);

$out .= <<<END
<table class="attackReport">
	<tr>
		<th>$userShip[ship_name]</th>
		<th>$tShip[ship_name]</th>
	</tr>

	<tr>
		<td>$pName</td>
		<td>$tName</td>
	</tr>

	<tr>
		<td>$userShip[class_name]</td>
		<td>$tShip[class_name]</td>
	</tr>

	<tr>
		<td><img src="img/ships/$userShip[appearance].jpg" width="160"
		 height="120" alt="$userShip[appearance] ship" /></td>
		<td><img src="img/ships/$tShip[appearance].jpg" width="160"
		 height="120" alt="$tShip[appearance] ship" /></td>
	</tr>
</table>

<h2>Result</h2>

END;

$uShipOrg = $userShip;
$tShipOrg = $tShip;

$result = shipVship($userShip, $tShip);
$rAttack = updateShip($userShip, $uShipOrg);
$rDefend = updateShip($tShip, $tShipOrg);

$out .= atkShipResult($uShipOrg, $tShipOrg, $tShip) .
 atkShipResult($tShip, $uShipOrg, $userShip);

checkPlayer($user['login_id']);
checkShip();

if ($rAttack & SHIP_DEAD) {
	$out .= "<p>Your ship was destroyed.</p>\n";
	//post_news("<b class=b1>$target[login_name]</b> has been completely killed, and is out of the game permanently.");
} elseif ($rDefend & SHIP_DEAD) {
	if ($rDefend & SHIP_ESCAPED) {
		$out .= "<p>You destroyed the enemy ship; $tShip[login_name] " .
		 "managed to flee in an escape-craft.</p>\n";
	} else if ($rDefend & SHIP_TRANSFERRED) {
		$out .= "<p>You destroyed the enemy ship.</p>\n";
	} else {
		$out .= "<p>You destroyed the enemy ship, killing " .
		 "$tShip[login_name] in the process.</p>\n";
	}
}

$out .= "<h3>View <a href=\"system.php\">other ships</a> " .
 ($rDefend & SHIP_DEAD ? '' :
 " or <a href=\"attack_ship.php?target=$target\">attack again</a>") . "</h3>\n";

print_page('Attack result', $out);

?>
