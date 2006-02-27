<?php

require_once('inc/user.inc.php');

function imageError($str, $width, $height)
{
	$im = imagecreate($width, $height);
	$white = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
	$black = imagecolorallocate($im, 0, 0, 0);
	imagefill($im, 0, 0, $white);

	imagestring($im, 2, 5, 5, $str, $black);

	header("Content-type: image/png");
	imagepng($im);

	imagedestroy($im);

	exit;
}

if (!$allow_search_map) {
	imageError('You are not allowed to search the map!', $uv_universe_size,
	 $uv_universe_size);
}

if(!(isset($from) && isset($to))) {
	imageError('Please set $from and $to.', $uv_universe_size, $uv_universe_size);
}

$fQuery = $db->query('SELECT x, y FROM [game]_stars WHERE star_id = %u', array($from));
$tQuery = $db->query('SELECT x, y from [game]_stars WHERE star_id = %u', array($to));

if ($db->numRows($fQuery) < 1) {
	imageError('$from is an invalid star', $uv_universe_size, $uv_universe_size);
}
if ($db->numRows($tQuery) < 1) {
	imageError('$to is an invalid star', $uv_universe_size, $uv_universe_size);
}

$starFrom = $db->fetchRow($fQuery);
$starTo = $db->fetchRow($tQuery);

$size = $uv_universe_size + 50;

$im = imagecreatefrompng('img/' . $db_name . '_maps/sm_full.png');

$text = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
$colFrom = imagecolorallocate($im, 0xFF, 50, 50);
$colTo = imagecolorallocate($im, 50, 0xFF, 50);

imagestring($im, 5, $starFrom['x'], $starFrom['y'] - 10, "From #$from", $text);
imagearc($im, $starFrom['x'] + 30, $starFrom['y'] + 25, 30, 30, 0, 360, $colFrom);

if ($from != $to) {
	imagestring($im, 5, $starTo['x'], $starTo['y'] - 10, "To #$to", $text);
	imagearc($im, $starTo['x'] + 29, $starTo['y'] + 25, 35, 35, 0, 360, $colTo);
}

header("Content-type: image/png");

imagepng($im);
imagedestroy($im);

?>
