<?php

/** A vehicle that travels through space */
class Ship extends Point
{
	protected $weapons;
	protected $contents;
	protected $engine = NULL;
	protected $dockedShips;

	/** We are at this significant feature in space, Local object */
	protected $local;

	/** Called when this ship moves from one system to another */
	public function moveSystem(System $target)
	{
		return DataShip::moveSystem($this, $target);
	}

	/**
	 * Cost of using simple impulse drive to move a distance in space
	 *
	 * Fighters, with a tiny mass will make great system scouts!
	 * @param $distance Distance in metres
	 */
	public function impulseEnergy(Local $target)
	{
		$effic = $this->getEngine()->getImpulseEffic();
		if ($effic === 0.0) {
			return false;
		}

		return Universe::getVar('movement_impulse_const') *
			(1.0 - 1.0 / $effic) *
			$this->distance($target) * $this->mass;
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
		$effic = $this->getEngine()->getHyperEffic();
		if ($effic === 0.0) {
			return false;
		}

		return Universe::getVar('movement_hyper_const') *
			(1.0 - 1.0 / $effic) * $this->mass;
	}

	/**
	 * Cost of creating using a quantum entanglement to transfer data to 
	 * communicate data to recreate the ship across vast distances in space
	 */
	public function quantumEnergy()
	{
		$effic = $this->getEngine()->getImpulseEffic();
		if ($effic === 0.0) {
			return false;
		}

		return Universe::getVar('movement_quantum_const') *
			(1.0 - 1.0 / $effic) * $this->mass;
	}

	/** Gets the associated engine or returns NULL */
	public getEngine()
	{
		if ($this->engine instanceof ShipEngine) {
			return $this->engine;
		}

		// load engine info from data store
		$newEngine = DataShip::loadEngine($this);
		if ($newEngine instanceof ShipEngine) {
			$this->engine = $newEngine;
		}

		return $this->engine;
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

/** Collection of weapons to use on ships */
class ShipWeapons
{
	protected $weapons;
}

?>