<?php

require_once('inc/user.inc.php');

function imageError($str)
{
	global $gameOpt;

	$im = imagecreate($gameOpt['uv_universe_size'],
	 $gameOpt['uv_universe_size']);
	$white = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
	$black = imagecolorallocate($im, 0, 0, 0);
	imagefill($im, 0, 0, $white);

	imagestring($im, 2, 5, 5, $str, $black);

	header("Content-type: image/png");
	imagepng($im);

	imagedestroy($im);

	exit;
}

if (!$gameOpt['allow_search_map']) {
	imageError('You are not allowed to search the map.');
}

if (!isset($from)) {
	$from = $userShip['location'];
}

if(!isset($to)) {
	imageError('From and to where?');
}

$from = (int)$from;
$to = (int)$to;

$sQuery = $db->query('SELECT star_id, x, y FROM [game]_stars WHERE star_id = %[1] OR star_id = %[2]',
 $from, $to);

$sNum = $db->numRows($sQuery);
if ($sNum < 2 && !($sNum == 1 && $from === $to)) {
	imageError('Supply valid stars.');
}

$starFrom = $db->fetchRow($sQuery, ROW_ASSOC);

if ($from !== $to) {
	$starTo = $db->fetchRow($sQuery, ROW_ASSOC);

	if ($starFrom['star_id'] != $from) {
		$starSwap = $starFrom;
		$starFrom = $starTo;
		$starTo = $starSwap;
	}
}

$findMap = imagecreatefrompng('img/maps/' . $gameInfo['db_name'] .
 '/screen.png');

if (!$findMap) {
	imageError('Star-map does not exist.');
}

$colText = imagecolorallocate($findMap, 0xFF, 0xFF, 0xFF);
$colFrom = imagecolorallocate($findMap, 0xFF, 0x33, 0x33);
$colTo = imagecolorallocate($findMap, 0x33, 0xFF, 0x33);

imagestring($findMap, 5, $starFrom['x'] + 25, $starFrom['y'] + 20,
 "From #$from",
 $colText);
imagearc($findMap, $starFrom['x'] + 50, $starFrom['y'] + 50, 30, 30, 0, 360,
 $colFrom);

if ($from !== $to) {
	imagestring($findMap, 5, $starTo['x'] + 25, $starTo['y'] + 20, "To #$to",
	 $colText);
	imagearc($findMap, $starTo['x'] + 50, $starTo['y'] + 50, 30, 30, 0, 360,
	 $colTo);
}

header('Content-Type: image/png');

imagepng($findMap);
imagedestroy($findMap);

?>
