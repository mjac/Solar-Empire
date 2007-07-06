<?php

require_once('inc/admin.inc.php');

$map_array = array();
map_systems($map_array,1); //Crawl through systems from sol and record all systems found.

$number_of_islands = 0;

//Find what stars aren't connected to sol that can be linked
$sql_blank_link = "( (link_1 = 0) OR (link_2 = 0) OR (link_3 = 0) OR (link_4 = 0) OR (link_5 = 0) OR (link_6 = 0) )";

db("select * from [game]_stars where $sql_blank_link");

while($star = dbr()) {
	if(in_array($star['star_id'],$map_array)) { //Skip it if it was found in the crawl
		continue;
	}

	$island_systems = array();
	map_systems($island_systems,$star['star_id']);	//Crawl through the island
	++$number_of_islands;


	//Go through each star of the island
	foreach ($island_systems as $island_star) {
		$sInfo = $db->query("select * from [game]_stars where star_id = $island_star");
		$star = $db->fetchRow($sInfo);
		//Find the nearest system that has been mapped to sol and link the two
		$sql_star_distance = "SQRT(POWER(x - $star[x], 2) + POWER(y - $star[y], 2))";

		db2("select *,$sql_star_distance AS distance from [game]_stars where $sql_blank_link order by distance");

		while($nearby_star = dbr2()) {
			if(!in_array($nearby_star['star_id'],$map_array)) { //Skip it if it isn't connected to Sol
				continue;
			}

			$star1 = $star;
			$star2 = $nearby_star;

			//Find the closest star in the island to this star
			$sql_star_distance = "SQRT(POWER(x - $star2[x], 2) + POWER(y - $star2[y], 2))";

			db2("select * from [game]_stars where ($sql_star_distance < $star2[distance]) AND $sql_blank_link order by $sql_star_distance");

			while ($closer_star = dbr2()) {
				if (!in_array($closer_star['star_id'], $island_systems)) { //Skip it if it isn't in the island
					continue;
				}

				$star1 = $closer_star;
				break;
			}

			add_link($star1, $star2['star_id']);
			add_link($star2, $star1['star_id']);
			break;
		}
	}

	$map_array = array_merge($map_array,$island_systems); //Add the island systems into the list
}


print_page("Unlinked Scan", "$number_of_islands islands found and linked.");



function map_systems(&$map_array,$current_system)
{
	global $db_name;


	array_push($map_array,$current_system);

	db2("select * from [game]_stars where star_id = $current_system"); //Get the star info
	$star = dbr2();
	$linknum = 1;

	while($linknum <= 6) { //Loop through each linked system
		if($star["link_$linknum"] && !in_array($star["link_$linknum"],$map_array)) {
			map_systems($map_array,$star["link_$linknum"]);
		}

		$linknum++;
	}

}

function add_link($star,$link_to)
{
	global $db_name;

	$linknum = 1;

	while($linknum <= 6) { //Look for an empty link slot
		if($star["link_$linknum"] == $link_to) {
			return false;
		}
		if(empty($star["link_$linknum"])) {
			break;
		}
		$linknum++;
	}

	if($linknum > 6) { //No available links
		return false;
	} else {
		$star["link_$linknum"] = $link_to;
		dbn("update [game]_stars set link_$linknum = $link_to where star_id = $star[star_id]");
	}
}

?>
