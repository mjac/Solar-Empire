<?php

require_once('inc/user.inc.php');
sudden_death_check($user);

if(!isset($dest_sector)) {
	get_var("AutoWarp", "autowarp.php", "Note: Autowarp will not necassarily find the shortest route, but normally it does.<p>Find route to system:", "dest_sector","");
	exit;
}

$stars = $visit = $pred = $dist = array();

$starList = mysql_query("SELECT `star_id`, `link_1`, `link_2`, `link_3`, `link_4`, `link_5`, `link_6` FROM `{$db_name}_stars`");
while ($each = mysql_fetch_row($starList)) {
	$stars[(int)$each[0]] = array(
		(int)$each[1],
		(int)$each[2],
		(int)$each[3],
		(int)$each[4],
		(int)$each[5],
		(int)$each[6]
	);
}

$starAmount  = count($stars);

$dest_sector = (int)$dest_sector;
if ($dest_sector < 1 || $dest_sector > $starAmount || $user['location'] == $dest_sector) {
	print_page("AutoWarp","That is an invalid destination.");
}

$start_sector = (int)$user['location'];
$ouptut_str   = '';

for($i = 1; $i < $starAmount; ++$i) {
	$visit[$i] = 0;
	$pred[$i]  = 0;
	$dist[$i]  = 150;
}
$dist[$dest_sector] = 0;

$queue = array($dest_sector);

while ($search_sector = array_pop($queue)) {
	$starLinks =& $stars[$search_sector];

	foreach ($starLinks as $vertex) {
		if ($vertex == $start_sector) {
			$path = array();
			$text = array("Distance is <b>$j</b> warps.<br />\nPath is <b>$vertex");

			for ($linkback = $search_sector, $j = $dist[$search_sector] + 1; $linkback != $dest_sector && $j; --$j) {
				$text[] = $path[] = $linkback;
				$linkback = $pred[$linkback];
			}
			$text[] = $path[] = $dest_sector;
			print_page('AutoWarp', implode(' =&gt; ', $text) . '</b><br /><br />
<a href="location.php?autowarp=' . implode('+', $path) . "\">Set Course For System $dest_sector</a>");
		}

		if($vertex > 0 && $visit[$vertex] == 0) {
			$visit[$vertex] = 1;
			$dist[$vertex]  = $dist[$search_sector] + 1;
			$pred[$vertex]  = $search_sector;
			array_unshift($queue, $vertex);
		}
	}
}

print_page('AutoWarp', 'No path Found');

?>
