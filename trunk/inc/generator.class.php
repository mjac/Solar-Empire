<?php

if (!class_exists('genUniverse')) {
	require(PATH_LIB . '/genUniverse/genUniverse.class.php');
}

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
	}

	/** Fill arrays from the database*/
	function loadData()
	{
	    global $db;
	}
};

?>
