<?php

/** Generic object in space */
abstract class SpaceObject
{
	protected $mass = 0.0;
	protected $position = array(0.0, 0.0, 0.0);

	public function getMass()
	{
		return $this->mass;
	}

	public function distance($to)
	{
		$xDisp = $position[0] - $to[0];
		$yDisp = $position[1] - $to[1];
		$zDisp = $position[2] - $to[2];

		return sqrt($xDisp * $xDisp + $yDisp * $yDisp + $zDisp * $zDisp);
	}
}

?>