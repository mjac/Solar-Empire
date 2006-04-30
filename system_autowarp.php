<?php

require_once('inc/user.inc.php');
deathCheck($user);

if(!isset($dest_sector)) {
	get_var("AutoWarp", $self, "<h1>Find route to system</h1>", "dest_sector","");
	exit;
}

$stars = $visit = $pred = $dist = array();

// Fetch all stars for speed
$starList = $db->query('SELECT star_id, link_1, link_2, link_3, link_4, ' .
 'link_5, link_6 FROM [game]_stars');
while ($each = $db->fetchRow($starList, ROW_NUMERIC)) {
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
if ($dest_sector < 1 || $dest_sector > $starAmount || $userShip['location'] == $dest_sector) {
	print_page("AutoWarp","That is an invalid destination.");
}

$start_sector = (int)$userShip['location'];
$ouptut_str   = '';

for ($i = 1; $i < $starAmount; ++$i) {
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

			for ($linkback = $search_sector, $j = $dist[$search_sector] + 1; $linkback != $dest_sector && $j; --$j) {
				$path[] = $linkback;
				$linkback = $pred[$linkback];
			}
			$path[] = $dest_sector;
			print_page('AutoWarp', "<h1>" . count($path) . " warp(s) to #$dest_sector</h1><p>Path is: " . implode(' &raquo; ', $path) . '</p>
<p><a href="system.php?autowarp=' . implode(',', $path) . "\">Set Course For System $dest_sector</a></p>");
		}

		if ($vertex > 0 && isset($visit[$vertex]) && $visit[$vertex] == 0) {
			$visit[$vertex] = 1;
			$dist[$vertex]  = $dist[$search_sector] + 1;
			$pred[$vertex]  = $search_sector;
			array_unshift($queue, $vertex);
		}
	}
}

print_page('AutoWarp', 'No path Found');

?>
