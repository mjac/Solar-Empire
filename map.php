<?php

require_once('inc/user.inc.php');

pageStart();

print "<h1>Star Map</h1>\n";

if(isset($print)) {
	$map_url = 'img/' . $db_name . '_maps/psm_full.png';
} elseif(isset($find)) {
	db("select count(star_id) from ${db_name}_stars");
	$max_sect = dbr();
	if($find < 1 || $find > $max_sect[0] || !$find) {
		$rs = "<p><a href=\"javascript: history.back()\">Back</a>";
		print_header("Error");
		echo "That system does not exist. Try searching for systems between <b>1</b> and <b>$max_sect[0]</b>.";
		print_footer();
		exit;
	} elseif($allow_search_map == 0) {
		print_page('Error', "Admin has turned off search facility.");
	} else {
		$map_url = "star_find.php?sys1=$user[location]&sys2=" . (int)$find;
	}
} else {
	$map_url = 'img/' . $db_name . '_maps/sm_full.png';
}

if(isset($print)) {
	$link_url = "<a href='map.php'>Normal Map</a> - ";
} else {
	$link_url = "<a href='map.php?print=1'>Printable Map</a> - ";
}


if($allow_search_map == 1){
	echo "<form action=\"map.php\" method=\"get\"><b>Find system: </b><input type='text' size=4 name='find'> <input type='submit' value='Search'></form><br>";
}

print <<<END
<p><img src="$map_url" alt="Complete map of the known universe" /></p>

END;

if($wormholes == 1){
	echo "Key:<br>Wormholes:<br><font color=#FFFF44>Yellow Lines</font> represent One-Way Wormholes.<br><font color=#00FF00>Green Lines</font> Show Two Way Wormholes.<p>";
}

echo $key_text;
echo $link_url;

pageStop('Map of the known universe');

?>
