<?php

require_once('inc/user.inc.php');
require_once('inc/attack.inc.php');

if (!isset($target)) {
	header('Location: system.php');
	exit;
}

$out = "<h1>Attack result</h1>\n";

if (!($tShip = getShip($target)) ||
     $tShip['ship_id'] == $userShip['ship_id'] ||
     $tShip['location'] != $userShip['location']) {
	header('Location: system.php');
	exit;
}

if (!canAttackShip($tShip)) {
	$reasons = array();
	if ($user['clan_id'] === NULL || $user['clan'] != $ship['clan_id']) {
		$reasons[] = 'the ship is owned by one of your fellow clan members';
	}
	if ($ship['turns_run'] > $gameOpt['turns_safe']) {
		$reasons[] = 'you are still in safe-turns';
	}
	if (shipHas($tShip, 'hs') && !shipHas($userShip, 'sc')) {
		$reasons[] = 'you cannot see this ship';
	}
	if (!giveTurnsPlayer(-$gameOpt['attack_turn_cost_ship'])) {
		$reasons[] = 'you do not have enough turns';
	}

	$out .= "<h2>You may not attack this ship</h2>\n";
	if (!empty($reasons)) {
	    $out .= "<ul>\n\t<li>" . implode("</li>\n\t<li>", $reasons) .
		 "</li>\n</ul>\n";
	}

	print_page('Attack attempt failed', $out);
}

$uMsg = sprintf("<p>Your %s attacked %s&#8217;s %s in system %u</p>", 
 esc($userShip['ship_name']), esc($tShip['login_name']), 
 esc($tShip['ship_name']), $userShip['location']);

$tMsg = sprintf("<p>%s&#8217;s %s attacked your %s in system %u</p>", 
 esc($tShip['login_name']), esc($tShip['ship_name']),
 esc($userShip['ship_name']), $userShip['location']);


// Chance of defending is n / (n + 1) - 0 ships 0 chance - 2 ships 2/3 chance
if ($other = fleetDefender($tShip)) {
    $tShip = $other;

    $extra .= "<p>$tShip[ship_name] has <strong>flown in to defend</strong> " .
	 esc($tShip['login_name']) . "&#8217;s fleet</p>\n";

	$out .= $extra;
	$uMsg .= $extra;
	$tMsg .= $extra;
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

$uResult = atkShipResult($uShipOrg, $tShipOrg, $tShip);
$tResult = atkShipResult($tShip, $uShipOrg, $userShip);
$out .= $uResult . $tResult;

$uStrResult = atkShipOverview($rAttack, $rDefend, $tShip);
$out .= $uStrResult;

$uMsg .= $uResult . $uStrResult;
$tMsg .= $tResult . atkShipOverview($rDefend, $rAttack, $userShip);

msgSendSys($userShip['login_id'], $uMsg, 'Ship attack report');
msgSendSys($tShip['login_id'], $tMsg, 'Ship defence report');

checkPlayer($user['login_id']);
checkShip();

$out .= "<h3>View <a href=\"system.php\">other ships</a> " .
 ($rDefend & SHIP_DEAD ? '' :
 " or <a href=\"attack_ship.php?target=$target\">attack again</a>") . "</h3>\n";

print_page('Attack result', $out);

?>
