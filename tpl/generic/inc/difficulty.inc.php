<?php

$difficulties = array(
	'Training',
	'Beginner',
	'Intermediate',
	'Challenge',
	'Advanced',
	'All Levels'
);

function resolve_difficulty($diff)
{
	global $difficulties;
	return $difficulties[($diff - 1) % count($txt)];
}

?>
