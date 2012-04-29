<?php

require_once('inc/user.inc.php');

if(!isset($sys1) || !isset($sys2)) {
	exit('Required parem\'s missing!');
}

//Connect to the database
db_connect();

db("select x_loc,y_loc from ${db_name}_stars where star_id = " . (int)$sys1);
$star_one = dbr();

db("select x_loc,y_loc from ${db_name}_stars where star_id = " . (int)$sys2);
$star_two = dbr();

db("select value from ${db_name}_db_vars where name = 'uv_universe_size '");
$size_record = dbr();
$size = $size_record[value] + 50;

$im = imagecreatefrompng('img/' . $db_name . '_maps/sm_full.png');

$red = imagecolorallocate($im, 255, 50, 50);
$red2 = imagecolorallocate($im, 255, 150, 150);
$green = imagecolorallocate($im, 50, 255, 50);
$green2 = imagecolorallocate($im, 50, 255, 150);

imagestring($im, 3, $star_one['x_loc'] - 10, $star_one['y_loc'] - 5, 'You are here', $red2);

imagearc($im, $star_one['x_loc'] + 30, $star_one['y_loc'] + 25, 30, 30, 0, 360, $red);

if($sys1 != $sys2){
	#imagestring($im,3,($star_two[x_loc]-15),$star_two[y_loc]+40,"System $sys2 here",$green2);
	imagearc($im, $star_two['x_loc'] + 29, $star_two['y_loc'] + 25, 35, 35, 0, 360, $green);
}

header("Content-type: image/png");

imagepng($im);
imagedestroy($im);

?>