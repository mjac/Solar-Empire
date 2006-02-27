<?php

require_once('inc/user.inc.php');

pageStart('Map of the known universe');

echo "<h1>Star Map</h1>\n";

if(isset($print)) {
	$map_url = 'img/' . $db_name . '_maps/psm_full.png';
} elseif(isset($find) && $allow_search_map != 0) {
	$star = $db->query('SELECT COUNT(*) FROM [game]_stars WHERE ' .
	 'star_id = %u', array($find));

	if (current($db->fetchRow($star)) > 0) {
		$map_url = 'system_find.php?from=' . $userShip['location'] . '&amp;to=' . $find;
	}
}

if (!isset($map_url)) {
	$map_url = 'img/' . $db_name . '_maps/sm_full.png';
}


echo <<<END
<p><img src="$map_url" alt="Complete map of the known universe" /></p>
<ul>
	<li><a href="$self">Normal Map</a></li>
	<li><a href="$self?print=1">Printable Map</a></li>
</ul>

END;

if($allow_search_map == 1){
	echo <<<END
<h2>Find system</h2>
<form action="$self" method="get">
	<input type="text" name="find" id="find" size="4" class="text" />
	<input type="submit" value="Search for system" class="button" />
</form>

END;
}

pageStop();

?>
