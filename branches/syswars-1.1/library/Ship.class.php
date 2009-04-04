<?php

/** A vehicle that travels through space */
class Ship extends SpaceObject
{
	protected $weapons;
	protected $contents;
	protected $engine;

	public function move()
	{
	
	}



	/**
	 * Cost of using simple impulse drive to move a distance in space
	 *
	 * Using relativistic equations of motion
	 * @param $distance Distance in metres
	 */
	public function impulseEnergy($target)
	{
		return Universe::getVar('impulseconst') * (1.0 - 1.0 / $engine->getImpulseEffic()) * $this->distance($target);
	}	

	/**
	 * Cost of going into hyperspace
	 *
	 * Theory is based on the concept that the energy expended is proportional
	 * to the mass of the ship with the constant of proportionality being 
	 * the inverse hyperspace efficiency of the engine.
	 */
	public function hyperEnergy()
	{

	}

	/**
	 * Cost of creating using a quantum entanglement to transfer data to 
	 * communicate data to recreate the ship across vast distances in space
	 */
	public function quantumEnergy()
	{

	}
}

/**
 * Every ship has an engine that governs every mechanical action it performs
 */
class ShipEngine
{
	/** Governs all local functions like fighting, mining */
	protected $powerOutput;

	/** Governs the cost of using impulse drive */
	protected $impulseEffic;

	/** Governs the cost of using hyper drive */
	protected $hyperEffic;

	/** Governs the cost of using impulse drive */
	protected $quantumEffic;
}

/** */
class ShipWeapons
{
	protected $weapons;
}

?>