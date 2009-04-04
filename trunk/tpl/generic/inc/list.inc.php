<?php
class_exists('Savant3') || exit;

function makeList(&$tpl, $title, $data, $ordered = false)
{
	$type = $ordered ? 'ol' : 'ul';

	$list = "<h2>" . $tpl->escape($title) . "</h2>\n<$type>\n";
	foreach ($data as $item) {
		$list .= "\t<li>" . $tpl->escape($item) . "</li>\n";
	}

	$list .= "</$type>\n";

	return $list;
}

?>
