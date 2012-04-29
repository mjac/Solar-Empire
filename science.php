<?php

require_once('inc/user.inc.php');

$filename = 'science.php';
$homeworld = 'Earth';

$rs = "<p><a href=science.php>Return to Observatory</a>";

if ($user[location] != 1) {
	$rs = "<p><a href=location.php>Back to the Star System</a>";
	print_page("Observatory","The <b class=b1>Observatory </b>is only accessable from system <b>#1</b>.");
}

print_header("Earth");
print_status();

if ($news) {

	echo make_table(array("",""));
	echo "Last <b>$user_options[news_back]</b> news posts relating to random events:<br>";

	db("select headline,timestamp from ${db_name}_news where login_id = -10 || login_id = -11 || login_id = 2 || login_id = 3 order by timestamp desc LIMIT $user_options[news_back]");
	while($news = dbr()) {
		echo quick_row("<b>".date("M d - H:i",$news[timestamp]),$news[headline]);
	}
	echo "</table><br>";
	print_footer();
	exit();
}

if($list){
	db("select count(star_id) from ${db_name}_stars where event_random != '0'");
	$events = dbr();
 	if($events[0]){
		echo "<p>There are a total of <b>$events[0]</b> systems with random events in them.";
		echo "<br>These include:<br>";
		db("select count(event_random),event_random from ${db_name}_stars where event_random > 0 GROUP by event_random");
		while($ran_ev = dbr()){
			if($ran_ev[event_random] == 4){
				#Mining Rushes
				$t_s .= "<br><b>$ran_ev[0]</b> <b class=b1>Mining Rush(es)</b>.";
			} elseif($ran_ev[event_random] == 10){
				#Artifical SuperNova
				$t_s .= "<br><b>$ran_ev[0]</b> <b class=b1>Artificial SuperNova(s)</b> set to explode.";
			} elseif($ran_ev[event_random] == 5){
				#SuperNova
				$t_s .= "<br><b>$ran_ev[0]</b> <b class=b1>Star(s) Set to go SuperNova</b>(Explode).";
			} elseif($ran_ev[event_random] == 6){
				#SuperNova Remnant - Unknown
				$t_s .= "<br><b>$ran_ev[0]</b> Unknown <b class=b1>SuperNova Remnant(s)</b>.";
			} elseif($ran_ev[event_random] == 14){
				#SuperNova Remnant - Safe
				$t_s .= "<br><b>$ran_ev[0]</b> Safe<b class=b1> SuperNova Remnant(s)</b>.";
			} elseif($ran_ev[event_random] == 1){
				#Black holes
				$t_s .= "<br><b>$ran_ev[0]</b> <b class=b1>Black Hole(s)</b>.";
			} elseif($ran_ev[event_random] == 12){
				#Solar Storm
				$t_s .= "<br><b>$ran_ev[0]</b> <b class=b1>Solar Storm(s)</b> in Progress.";
			} elseif($ran_ev[event_random] == 2){
				#Nebula
				$count = round($events[0]/4);
				$t_s .= "<br>About <b>$count</b> <b class=b1>Nebula</b>, covering <b>$ran_ev[0]</b> systems.";
			}
		}
	} else {
		echo "<p>There are no random events in this game.";
	}

	echo $t_s;

	#WormHoles
	$owayworm = 0;
	$twayworm = 0;
	db("select star_id,wormhole from ${db_name}_stars where wormhole > '0'");
	while($events = dbr()) {
		db2("select star_id,wormhole from ${db_name}_stars where star_id = '$events[wormhole]' && wormhole = $events[star_id]");
		$tway = dbr2();
		if ($tway[wormhole]) {
				$twayworm++;
		} else {
			$owayworm++;
		}
	}
		echo "<p>WormHole Count:";
	if($owayworm){
		echo "<br><b>$owayworm</b> <b class=b1>One-Way</b> wormhole(s).";
		if($twayworm){
			$twayworm = $twayworm /2;
			echo "<br><b>$twayworm</b> <b class=b1>Two-Way</b> wormhole(s).";
		} else {
			echo "<br>There are no <b class=b1>Two-Way</b> wormholes.";
		}
	} elseif($twayworm){
			$twayworm = $twayworm /2;
		echo "<br>There are no <b class=b1>One-Way</b> wormholes.";
		echo "<br><b>$twayworm</b> <b class=b1>Two-Way</b> wormhole(s).";
	} else{
		echo "<br>There are no wormholes of any type.";
	}

	print_footer();
	exit();
}


echo "The <b class=b1>Observatory of Sol</b> is home of the <b class=b1>Science Institute of Sol</b>.";
echo "<br>The random event level for this game is <b>$random_events</b> (max is <b>3</b>).";

echo "<p><a href=$filename?list=1>Listing of Random Events/WormHoles</a>";
echo "<br><a href=$filename?news=1>News of Random Events</a>";

$rs = "<p><a href=location.php>Back to the Star System</a>";
print_footer();
?>