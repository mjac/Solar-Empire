<?php

if (!class_exists('genUniverse')) {
	require(PATH_LIB . '/genUniverse/genUniverse.class.php');
}

/** Generates a Solar Empire universe based on the general genUniverse class */
class generator extends genUniverse
{
	/** Apply game options to universe generator */
	function generator()
	{
		global $gameOpt;

		$this->options['width'] = $this->options['height'] =
		 $gameOpt['uv_universe_size'];
		$this->options['starAmount'] = $gameOpt['uv_num_stars'];

		if (!$gameOpt['wormholes']) {
			$this->options['wormholeChance'] = 0.0;
		}

		$mapTypes = array_keys($this->mapTypes);
		switch ($gameOpt['uv_map_layout']) {
			case 0:
			case 1:
			case 2:
			case 3:
				$this->options['mapType'] =
				 $mapTypes[$gameOpt['uv_map_layout']];
				break;
			default:
				$this->options['mapType'] = 0;
		}

		if ($gameOpt['uv_map_graphics']) {
			$this->appearance['graphics']['earth'] =
			 PATH_BASE . '/img/map/earth.png';
			$this->appearance['graphics']['star'] =
			 PATH_BASE . '/img/map/star.png';
		}

		$this->options['localWidth'] = $this->options['localHeight'] = 200;

		$this->options['starMinDist'] = $gameOpt['uv_min_star_dist'];
		$this->options['linkMaxDist'] = $gameOpt['uv_max_link_dist'];
	}

	/** Replace the most central star with Sol */
	function centreSol()
	{
		$xCentre = floor($this->options['width'] / 2);
		$yCentre = floor($this->options['height'] / 2);

		$solIndex = 0;

		$lowIndex = 0;
		$lowQuad = -1;

		$amount = count($this->stars);
		foreach ($this->stars as $index => $star) {
			$currentQuad = ($star->x - $xCentre) * ($star->x - $xCentre) +
			 ($star->y - $yCentre) * ($star->y - $yCentre);
			if ($lowQuad === -1 || $lowQuad > $currentQuad) {
				$lowQuad = $currentQuad;
				$lowIndex = $index;
			}

			if ($star->id === 1) {
				$solIndex = $index;
			}
		}

		$pivot = $this->stars[$solIndex]->id;

		$this->stars[$solIndex]->id = $this->stars[$lowIndex]->id;
		$this->stars[$lowIndex]->id = $pivot;

		$this->stars[$solIndex]->name = $this->stars[$lowIndex]->name;
		$this->stars[$lowIndex]->name = 'Sol';
	}

	/** Output map to browser (for preview) */
	function displayMap()
	{
		header('Content-Type: image/png');
		imagepng($this->starMap);
	}

	/** Save current arrays to the database */
	function saveData()
	{
		global $db;

		// Delete old stars
		$db->query('DELETE FROM [game]_stars');

		// Add each new star
		foreach ($this->stars as $star) {
			$links = array(0, 0, 0, 0, 0, 0);

			$linkIndex = 0;
			foreach ($star->links as $link) {
				if ($link !== NULL) { // When link allowed but never done
					$links[$linkIndex] = $link->id;
					++$linkIndex;
				}
			}

			$wormhole = $star->wormhole ? $star->wormhole->id : 0;

			$db->query('INSERT INTO [game]_stars (star_id, star_name, x, y, link_1, link_2, link_3, link_4, link_5, link_6, wormhole) VALUES (%[1], \'%[2]\', %[3], %[4], %[5], %[6], %[7], %[8], %[9], %[10], %[11])',
			 $star->id, $star->name, $star->x, $star->y, $links[0], $links[1],
			 $links[2], $links[3], $links[4], $links[5], $wormhole);
		}

		// Order by star id so Sol is at the top
		$db->query('ALTER TABLE [game]_stars ORDER BY star_id ASC');
	}

	/** Fill arrays from the database*/
	function loadData()
	{
		global $db;

		$this->stars = array();

		// Get all stars from the database and put into a simply array
		$allStars = $db->query('SELECT star_id, star_name, x, y, link_1, link_2, link_3, link_4, link_5, link_6, wormhole FROM [game]_stars ORDER BY star_id ASC');

		while ($star = $db->fetchRow($allStars, ROW_NUMERIC)) {
			$newStar =& $this->stars[];

			$newStar = new genStarLoad;
			$newStar->id = (int)$star[0];
			$newStar->name = $star[1];
			$newStar->x = (int)$star[2];
			$newStar->y = (int)$star[3];

			for ($linkId = 1; $linkId <= 6; ++$linkId) {
				$link = (int)$star[$linkId + 3];
				if ($link !== 0) {
					$newStar->linksId[] = $link;
					++$newStar->linkAmount;
				}
				$newStar->linksLeft = $newStar->linkAmount;
			}
			
			$newStar->wormholeId = (int)$star[10];
		}

		// Make more complicated links between the stars (class references)
		foreach ($this->stars as $starIndex => $star) {
			foreach ($star->linksId as $linkId) {
				$this->stars[$starIndex]->linkTo($this->stars[$linkId - 1]);
			}

			if ($star->wormholeId) {
			    $star->wormhole =& $this->stars[$star->wormholeId - 1];
			}
		}
	}

	/** Load names from database and set them */
	function setNames()
	{
	    global $db;

		$nameList = array();

		$names = $db->query('SELECT name FROM se_star_names');
		while ($name = $db->fetchRow($names, ROW_NUMERIC)) {
		    $nameList[] = $name[0];
		}

		$this->names($nameList);
	}
};

class genStarLoad extends genStar
{
	var $linksId = array();
	var $wormholeId;
};

?>
