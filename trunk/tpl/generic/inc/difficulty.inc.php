<?php
class_exists('Savant2') || exit;

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
