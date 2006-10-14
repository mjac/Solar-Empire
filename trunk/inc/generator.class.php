<?php

/**
 * Generates a universe similar to a mathematical graph
 * 
 * Provides a wealth of customisation options.   
 */ 

class genUniverse
{
	var $options = array(
		'starAmount' => 300,
		'width' => 800,
		'height' => 800,
		'mapType' => 'random',
	
		'localWidth' => 200,
		'localHeight' => 200,

		'wormholeChance' => 0.025,
		'wormholeChanceBi' => 0.25,
	
		'linkMin' => 2,
		'linkMax' => 6,
	
		'starMinDist' => 25,
	
		'linkMaxDist' => 100,

		'centralStar' => 1
	);

	var $appearance = array(
		'mapPadding' => 50,
		'label' => true,
		'print' => array(
			'background' => array(0xFF, 0xFF, 0xFF),
			'label' => array(0x00, 0x00, 0x00),
			'link' => array(0xCC, 0xCC, 0xCC),
			'star' => array(0xCC, 0xCC, 0xCC),
			'wormhole' => array(0xEF, 0xEF, 0xEF),
			'wormholeBi' => array(0xEF, 0xEF, 0xEF)
		),
		'screen' => array(
			'background' => array(0x00, 0x00, 0x00),
			'label' => array(0x99, 0xCC, 0xFF),
			'link' => array(0x66, 0x66, 0x66),
			'star' => array(0xCC, 0xCC, 0xCC),
			'wormhole' => array(0xE6, 0xE6, 0x40),
			'wormholeBi' => array(0x00, 0xE6, 0x00),
			'current' => array(0xFF, 0xFF, 0xFF)
		),
		'graphics' => array(
			'earth' => '',
			'star' => ''
		)
	);

	var $mapTypes = array(
		'random' => 'genMapsetRandom',
		'core' => 'genMapsetCore',
		'clusters' => 'genMapsetClusters',
		'ellipse' => 'genMapsetEllipse'
	);

	var $stars;

	function createStars()
	{
		$this->stars = array();
		for ($createId = 0; $createId < $this->options['starAmount']; 
		     ++$createId) {
		    $this->stars[] =& new genStar;
			$this->stars[$createId]->id = $createId + 1;
			$this->stars[$createId]->setLinkAmount($this->options['linkMin'], 
			 $this->options['linkMax']);
		}
	}

	function positions()
	{
		$class = $this->mapTypes[$this->options['mapType']];
		$map = new $class($this->options);
		$map->generate($this->stars);
	}

	function names(&$names)
	{
		$count = count($names);

		for ($nameStar = 0; $nameStar < $this->options['starAmount']; 
		     ++$nameStar) {
			$nameId = $nameStar % $count;
			if ($nameId == 0) {
				$this->shuffle($names);
			}

			$this->stars[$nameStar]->name = $names[$nameId] . 
			 ($nameStar > $count ? (' ' . ceil($nameStar / $count)) : '');
		}
	}

	function link()
	{
		$maxQuad = $this->options['linkMaxDist'] * 
		 $this->options['linkMaxDist'];
	
		for ($toLink = 0; $toLink < $this->options['starAmount']; ++$toLink) {
			$fromStar = &$this->stars[$toLink];

			$starComp = array();
			for ($quadElement = 0; $quadElement < $this->options['starAmount']; 
			     ++$quadElement) {
				$starComp[$quadElement] = 
				 ($this->stars[$quadElement]->x - $fromStar->x) * 
				 ($this->stars[$quadElement]->x - $fromStar->x) + 
				 ($this->stars[$quadElement]->y - $fromStar->y) * 
				 ($this->stars[$quadElement]->y - $fromStar->y);
			}
			asort($starComp);

			foreach ($starComp as $starId => $quadrance) {
				if ($fromStar->linksLeft < 1 || $quadrance > $maxQuad) {
					break;
				}

				$fromStar->linkTo($this->stars[$starId]);
			}
		}
	}

	function wormholes()
	{
		if ($this->options['wormholeChance'] == 0) {
			return;
		}

		$randMax = (float)mt_getrandmax();
		$maxStar = $this->options['starAmount'] - 1;

		for ($starIndex = 0; $starIndex < $this->options['starAmount']; 
		     ++$starIndex) {
			$thisStar =& $this->stars[$starIndex];
	
			if (((float)mt_rand() / $randMax) > 
			     $this->options['wormholeChance'] ||
			     !empty($thisStar->wormhole)) {
				continue;
			}
	
			for ($attempt = 0; $attempt < $this->options['starAmount'] &&
			     empty($thisStar->wormhole); ++$attempt) {
				$linkStar =& $this->stars[mt_rand(0, $maxStar)];
				if (empty($linkStar->wormhole) &&
				     ((float)mt_rand() / $randMax) <= 
				     $this->options['wormholeChanceBi']) {
					$linkStar->wormhole =& $thisStar;
				}
	
				$thisStar->wormhole =& $linkStar;
			}
		}
	}

	function renderGlobal($standard, $print)
	{
		$graphics = $this->usingGraphics();

		$totalWidth = $this->options['width'] + 2 * 
		 $this->appearance['mapPadding'];
		$totalHeight = $this->options['height'] + 2 * 
		 $this->appearance['mapPadding'];

		$starMap = imagecreate($totalWidth, $totalHeight);

		if ($graphics) {
			$earthIm = 
			 imagecreatefrompng($this->appearance['graphics']['earth']);
			$earthDim = array(imagesx($earthIm), imagesy($earthIm));
			$earthPos = array(-$earthDim[0] / 2, -$earthDim[1] / 2);

			$starIm = imagecreatefrompng($this->appearance['graphics']['star']);
			$starDim = array(imagesx($starIm), imagesy($starIm));
			$starPos = array(-$starDim[0] / 2, -$starDim[1] / 2);
		}

		$sCol =& $this->appearance['screen'];
		$colBack = imagecolorallocate($starMap, $sCol['background'][0],
		 $sCol['background'][1], $sCol['background'][2]);
		$colLabel = imagecolorallocate($starMap, $sCol['label'][0], 
		 $sCol['label'][1], $sCol['label'][2]);
		$colStar = imagecolorallocate($starMap, $sCol['star'][0], 
		 $sCol['star'][1], $sCol['star'][2]);
		$colLink = imagecolorallocate($starMap, $sCol['link'][0], 
		 $sCol['link'][1], $sCol['link'][2]);
		$colWorm = imagecolorallocate($starMap, $sCol['wormhole'][0],
		 $sCol['wormhole'][1], $sCol['wormhole'][2]);
		$colWormBi = imagecolorallocate($starMap,$sCol['wormholeBi'][0],
		 $sCol['wormholeBi'][1], $sCol['wormholeBi'][2]);

		foreach ($this->stars as $star) {
			for ($link = 0; $link < $star->linkAmount; ++$link) {
				if (empty($star->links[$link]) ||
				     $star->links[$link]->id <= $star->id) {
					continue;
				}

				imageline($starMap, $star->x + 
				 $this->appearance['mapPadding'], $star->y + 
				 $this->appearance['mapPadding'], $star->links[$link]->x + 
				 $this->appearance['mapPadding'], $star->links[$link]->y + 
				 $this->appearance['mapPadding'], $colLink);
			}
		}

		// Wormholes
		if ($this->options['wormholeChance'] != 0) {
			foreach ($this->stars as $star) {
				if (empty($star->wormhole)) {
					continue;
				}
	
				$twoWay = $star->wormhole->wormhole;
	
				imageline($starMap, $star->x + $this->appearance['mapPadding'], 
				 $star->y + $this->appearance['mapPadding'], 
				 $star->wormhole->x + $this->appearance['mapPadding'], 
				 $star->wormhole->y + $this->appearance['mapPadding'],
				 empty($twoWay) || $twoWay->id !== $star->id ? $colWorm : 
				 $colWormBi);
			}
		}

		if ($graphics) {
			foreach ($this->stars as $star) {
				if ($star->id == $this->options['centralStar']) {
					$im =& $earthIm;
					$pos =& $earthPos;
					$dim =& $earthDim;
				} else {
					$im =& $starIm;
					$pos =& $starPos;
					$dim =& $starDim;
				}

				imagecopy($starMap, $im, $star->x + 
				 $this->appearance['mapPadding'] + $pos[0], $star->y + 
				 $this->appearance['mapPadding'] + $pos[1], 0, 0, $dim[0], 
				 $dim[1]);
			}
		} else {
			foreach ($this->stars as $star) {
				imagesetpixel($starMap, $star->x + 
				 $this->appearance['mapPadding'], $star->y + 
				 $this->appearance['mapPadding'], $colStar);
			}
		}

		if ($this->appearance['label']) {
			$off = $graphics ? array(6, -4) : array(3, -4);

			foreach ($this->stars as $star) {
				imagestring($starMap, 
				 $star->id == $this->options['centralStar'] ? 3 : 1,
				 $star->x + $this->appearance['mapPadding'] + $off[0],
				 $star->y + $this->appearance['mapPadding'] + $off[1],
				 $star->id, $colLabel);
			}
		}


		// Create printable map by changing colours
		$printMap = imagecreate($totalWidth, $totalHeight);

		$pCol =& $this->appearance['print'];
		imagecolorallocate($starMap, $pCol['background'][0],
		 $pCol['background'][1], $pCol['background'][2]);
		imagecopy($printMap, $starMap, 0, 0, 0, 0, $totalWidth, $totalHeight);

		$index = imagecolorexact($printMap, $sCol['background'][0], 
		 $sCol['background'][1], $sCol['background'][2]);
		imagecolorset($printMap, $index, $pCol['background'][0], 
		 $pCol['background'][1], $pCol['background'][2]);

		$index = imagecolorexact($printMap, $sCol['link'][0], 
		 $sCol['link'][1], $sCol['link'][2]);
		imagecolorset($printMap, $index, $pCol['link'][0], 
		 $pCol['link'][1], $pCol['link'][2]);

		$index = imagecolorexact($printMap, $sCol['wormhole'][0], 
		 $sCol['wormhole'][1], $sCol['wormhole'][2]);
		imagecolorset($printMap, $index, $pCol['wormhole'][0], 
		 $pCol['wormhole'][1], $pCol['wormhole'][2]);

		$index = imagecolorexact($printMap, $sCol['wormholeBi'][0], 
		 $sCol['wormholeBi'][1], $sCol['wormholeBi'][2]);
		imagecolorset($printMap, $index, $pCol['wormholeBi'][0], 
		 $pCol['wormholeBi'][1], $pCol['wormholeBi'][2]);

		$index = imagecolorexact($printMap, $sCol['label'][0], 
		 $sCol['label'][1], $sCol['label'][2]);
		imagecolorset($printMap, $index, $pCol['label'][0], 
		 $pCol['label'][1], $pCol['label'][2]);
	
		if (!$graphics) {
			$index = imagecolorexact($printMap, $sCol['star'][0], 
			 $sCol['star'][1], $sCol['star'][2]);
			imagecolorset($printMap, $index, $pCol['star'][0], 
			 $pCol['star'][1], $pCol['star'][2]);
		}

		// Output and destroy images
		imagepng($starMap, $standard);
		imagedestroy($starMap);

		imagepng($printMap, $print);	
		imagedestroy($printMap);
	}

	function renderLocal($global, $directory)
	{
		if (!is_dir($directory)) {
			return;
		}
		$graphics = $this->usingGraphics();
	
		$starMap = imagecreatefrompng($global);
	
		foreach ($this->stars as $star) {
			$sCol =& $this->appearance['screen'];

			$localMap = imagecreate($this->options['localWidth'], 
			 $this->options['localHeight']);
	
			imagecolorallocate($localMap, $sCol['background'][0], 
			 $sCol['background'][1], $sCol['background'][2]);

			imagecopy($localMap, $starMap, 0, 0, $star->x - 
			 $this->options['localWidth'] / 2 + $this->appearance['mapPadding'],
			 $star->y - $this->options['localHeight'] / 2 + 
			 $this->appearance['mapPadding'], $this->options['localWidth'], 
			 $this->options['localHeight']);
	
			$colCurrent = imagecolorallocate($localMap, $sCol['current'][0], 
			 $sCol['current'][1], $sCol['current'][2]);
	
			if ($this->appearance['label']) {
				$off = $graphics ? array(6, -4) : array(3, -4);
	
				imagestring($localMap, 
				 $star->id == $this->options['centralStar'] ? 3 : 1,
				 $this->options['localWidth'] / 2 + $off[0],
				 $this->options['localHeight'] / 2 + $off[1],
				 $star->id, $colCurrent);
			}
	
			imagepng($localMap, $directory . '/' . $star->id . '.png');
			imagedestroy($localMap);
		}
	
		imagedestroy($starMap);
	}

	function usingGraphics()
	{
		return $this->appearance['graphics']['earth'] && 
		 $this->appearance['graphics']['star'] &&
		 is_file($this->appearance['graphics']['earth']) && 
		 is_file($this->appearance['graphics']['star']);
	}

	function shuffle(&$items)
	{
		for ($i = count($items) - 1; $i > 0; --$i) {
			$j = mt_rand(0, $i);
			$tmp = $items[$i];
			$items[$i] = $items[$j];
			$items[$j] = $tmp;
		}
	}
};





class star
{
	var $name = '';
	var $id = 0;
	var $x = 0;
	var $y = 0;
	var $linkAmount = 0;
	var $linksLeft = 0;
	var $links = array();
	var $wormhole;
};

class genStar extends star
{
	function linkTo(&$to)
	{
		if ($this->linksLeft < 1 || $to->linksLeft < 1 || 
		     $to->id == $this->id) {
			return false;
		}
	
		--$this->linksLeft;
		--$to->linksLeft;
	
		$this->links[$this->linksLeft] =& $to;
		$to->links[$to->linksLeft] =& $this;
	
		return true;
	}
	
	function setLinkAmount($min, $max)
	{
		$this->linkAmount = mt_rand($min, $max);
		$this->linksLeft = $this->linkAmount;
		$this->links = array();
	
		for ($eachLink = 0; $eachLink < $this->linkAmount; ++$eachLink) {
			$this->links[$eachLink] = NULL;
		}
	}
};

class genMapset
{
	var $options;

	function genMapset(&$options)
	{
		$this->options = &$options;
	}

	function spaceCheck(&$thisStar, &$stars)
	{
		$maxQuad = $this->options['starMinDist'] * 
		 $this->options['starMinDist'];
		for ($starIndex = 0; $starIndex < $this->options['starAmount']; 
		     ++$starIndex) {
			$checkStar = &$stars[$starIndex];
	
			if ($checkStar->id == $thisStar->id) {
				continue;
			}
	
			// Assuming stars are created consecutively
			if ($checkStar->id >= $thisStar->id) {
				break;
			}
	
			if ((($checkStar->x - $thisStar->x) * 
			     ($checkStar->x - $thisStar->x) +
			     ($checkStar->y - $thisStar->y) * 
			     ($checkStar->y - $thisStar->y)) < $maxQuad) {
				return true;
			}
		}
	
		return false;
	}

	function generate(&$stars)
	{
	}
};

class genMapsetRandom extends genMapset
{
	function genMapsetRandom(&$options)
	{
		$this->genMapset($options);
	}

	function generate(&$stars)
	{
		for ($starIndex = 0; $starIndex < $this->options['starAmount'];
		     ++$starIndex) {
			$thisStar = &$stars[$starIndex];

			$thisStar->x = mt_rand(0, $this->options['width'] - 1);
			$thisStar->y = mt_rand(0, $this->options['height'] - 1);
	
			if ($this->spaceCheck($thisStar, $stars)) {
				--$starIndex;
				continue;
			}
		}
	}
};

class genMapsetCore extends genMapset
{
	function genMapsetCore(&$options)
	{
		$this->genMapset($options);
	}

	function generate(&$stars)
	{
		$centreX = $this->options['width'] / 2;
		$centreY = $this->options['height'] / 2;
	
		for ($starIndex = 0; $starIndex < $this->options['starAmount']; 
		     ++$starIndex) {
			$thisStar = &$stars[$starIndex];
	
			$divideBy = mt_rand(1, 4) * 2;
	
			$xTmp = $this->options['width'] / $divideBy;
			$yTmp = $this->options['height'] / $divideBy;
	
			$thisStar->x = mt_rand(-$xTmp, $xTmp) + $centreX;
			$thisStar->y = mt_rand(-$yTmp, $yTmp) + $centreY;
	
			if ($this->spaceCheck($thisStar, $stars)) {
				--$starIndex;
				continue;
			}
		}
	}
};

class genMapsetClusters extends genMapset
{
	function genMapsetClusters(&$options)
	{
		$this->genMapset($options);
	}

	function generate(&$stars)
	{
		$numberClusters = ceil(sqrt($this->options['starAmount'])) - 1.0;
		$starsPerCluster = ceil($this->options['starAmount'] / $numberClusters);
		$clusterDivision = ceil($numberClusters * 0.275);
	
		$clusterXSize = floor($this->options['width'] / $clusterDivision);
		$clusterYSize = floor($this->options['height'] / $clusterDivision);
	
		$basisX = $this->options['width'] / 2;
		$basisY = $this->options['height'] / 2;
		$sectorCount = 0;
	
		for ($starIndex = 0; $starIndex < $this->options['starAmount']; 
		     ++$starIndex) {
			$thisStar = &$stars[$starIndex];
	
			if ($sectorCount == $starsPerCluster) {
				$sectorCount = 0;
				$basisX = mt_rand($clusterXSize, $this->options['width'] - 
				 $clusterXSize);
				$basisY = mt_rand($clusterYSize, $this->options['height'] - 
				 $clusterYSize);
			}
	
			$thisStar->x = mt_rand(0, $clusterXSize - 1) << 1 - $clusterXSize + 
			 $basisX;
			$thisStar->y = mt_rand(0, $clusterYSize - 1) << 1 - $clusterYSize + 
			 $basisY;
	
			if ($this->spaceCheck($thisStar, $stars)) {
				--$starIndex;
				continue;
			}
	
			++$sectorCount;
		}
	}
};

class genMapsetEllipse extends genMapset
{
	function genMapsetEllipse(&$options)
	{
		$this->genMapset($options);
	}

	function generate(&$stars)
	{
		$middleX = round($this->options['width'] / 2);
		$middleY = round($this->options['height'] / 2);
		
		$xRatioSq = ($middleY * $middleY) / ($middleX * $middleX);
		
		$maxQuadrance = $middleX * $middleX * $xRatioSq;
		
		for ($starIndex = 0; $starIndex < $this->options['starAmount'];
		     ++$starIndex) {
			$thisStar = &$stars[$starIndex];

			$thisStar->x = mt_rand(0, $this->options['width'] - 1);
			$thisStar->y = mt_rand(0, $this->options['height'] - 1);

			$diffX = $thisStar->x - $middleX;
			$diffY = $thisStar->y - $middleY;

			if (($diffX * $diffX * $xRatioSq  + $diffY * $diffY) > 
			     $maxQuadrance || $this->spaceCheck($thisStar, $stars)) {
				--$starIndex;
				continue;
			}
		}
	}
};








// to test
$a = new genUniverse;
$a->options['width'] = 500;
$a->options['height'] = 1000;
//$a->appearance['graphics']['star'] = 'star.png';
//$a->appearance['graphics']['earth'] = 'earth.png';
$a->createStars();
$a->positions();
$a->wormholes();
$a->link();
$a->names(explode("\n\n", file_get_contents('se_star_names.csv')));
$a->renderGlobal('test.png', 'test2.png');
$a->renderLocal('test.png', 'D:/www/test');

?>