<?php
if (!defined('PATH_SAVANT')) exit();

function resolveDifficulty($diff)
{
	static $difficulties = array(
		'Training',
		'Beginner',
		'Intermediate',
		'Challenge',
		'Advanced',
		'All Levels'
	);

	return $difficulties[($diff - 1) % count($difficulties)];
}

?>
