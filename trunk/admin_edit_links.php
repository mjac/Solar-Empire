<?php

require_once('inc/admin.inc.php');

if (isset($cut_from) && isset($cut_to)) {
	for ($i = 1; $i <= 6; ++$i) {
		$db->query("UPDATE [game]_stars SET link_$i = 0 WHERE (star_id = %u AND link_$i = %u) OR (star_id = %u AND link_$i = %u)", array($cut_to, $cut_from, $cut_from, $cut_to));
	}
}

if (isset($add_from) && isset($add_to)) {
	$db->query("update [game]_stars set link_1 = $add_to where star_id = $add_from AND link_1 = 0");
	$db->query("update [game]_stars set link_2 = $add_to where star_id = $add_from AND link_2 = 0 AND link_1 != $add_to");
	$db->query("update [game]_stars set link_3 = $add_to where star_id = $add_from AND link_3 = 0 AND link_1 != $add_to AND link_2 != $add_to");
	$db->query("update [game]_stars set link_4 = $add_to where star_id = $add_from AND link_4 = 0 AND link_1 != $add_to AND link_2 != $add_to AND link_3 != $add_to");
	$db->query("update [game]_stars set link_5 = $add_to where star_id = $add_from AND link_5 = 0 AND link_1 != $add_to AND link_2 != $add_to AND link_3 != $add_to AND link_4 != $add_to");
	$db->query("update [game]_stars set link_6 = $add_to where star_id = $add_from AND link_6 = 0 AND link_1 != $add_to AND link_2 != $add_to AND link_3 != $add_to AND link_4 != $add_to AND link_5 != $add_to");

	$db->query("update [game]_stars set link_1 = $add_from where star_id = $add_to AND link_1 = 0");
	$db->query("update [game]_stars set link_2 = $add_from where star_id = $add_to AND link_2 = 0 AND link_1 != $add_from");
	$db->query("update [game]_stars set link_3 = $add_from where star_id = $add_to AND link_3 = 0 AND link_1 != $add_from AND link_2 != $add_from");
	$db->query("update [game]_stars set link_4 = $add_from where star_id = $add_to AND link_4 = 0 AND link_1 != $add_from AND link_2 != $add_from AND link_3 != $add_from");
	$db->query("update [game]_stars set link_5 = $add_from where star_id = $add_to AND link_5 = 0 AND link_1 != $add_from AND link_2 != $add_from AND link_3 != $add_from AND link_4 != $add_from");
	$db->query("update [game]_stars set link_6 = $add_from where star_id = $add_to AND link_6 = 0 AND link_1 != $add_from AND link_2 != $add_from AND link_3 != $add_from AND link_4 != $add_from AND link_5 != $add_from");
}


$out = <<<END
<h1>Edit star-links</h1>

<h2>Cut links</h2>
<form action="$self" method="get">
	<p>From <input type="text" name="cut_from" class="text" />
	to <input type="text" name="cut_to" class="text" /></p>
	<p><input type="submit" value="Execute" class="button" /></p>
</form>

<h2>Add links</h2>
<form action="$self" method="get">
	<p>From <input type="text" name="add_from" class="text" />
	to <input type="text" name="add_to" class="text" /></p>
	<p><input type="submit" value="Execute" class="button" /></p>
</form>

<p>You may need to 
<a href="admin_build_universe.php?gen_new_maps=1&amp;process=1">re-create 
the maps</a></p>

END;

print_page("Cut-link", $out);

?>
