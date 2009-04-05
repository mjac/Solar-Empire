<?php

/** Weapons cause damage to all physical objects */
class Weapon
{
	/** Total energy of blast */
	protected $energy;

	/** Radius of blast */
	protected $radius;

	/**
	 * Tangential maximum accuracy error
	 *
	 * Think spherically. Line to target. Actual position hit is the target 
	 * point + (a normal unit vector (with random angle) to the vector from here 
	 * to target with magnitude random * accuracyError)
	 */
	protected $accuracyError;

	/** Energy to use */
	protected $energyUsage;
}

?>