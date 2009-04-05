<?php

/** Generic object in space, think point mass (hence abstract) */
abstract class Point
{
	/** Mass constant */
	protected $mass = 0.0;

	/** Position in 3d Euclidean space */
	protected $position = array(0.0, 0.0, 0.0);

	/** Return mass of this point mass */
	public function getMass()
	{
		return $this->mass;
	}

	/** Distance between two points (three dimensional space */
	public function distance($to)
	{
		$xDisp = $position[0] - $to[0];
		$yDisp = $position[1] - $to[1];
		$zDisp = $position[2] - $to[2];

		return sqrt($xDisp * $xDisp + $yDisp * $yDisp + $zDisp * $zDisp);
	}
}

?>