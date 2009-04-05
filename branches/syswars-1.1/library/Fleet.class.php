<?php

/**
 * A collection of ships that move through space
 *
 * Composed of Ship objects but with many more properties
 */
class Fleet extends Point
{
	public function getShips()
	{
	
	}

	/** Batch move a lot of ships */
	public function moveSystem(System $target)
	{
		return DataFleet::move($this, $target);
	}

	/** Sum of Ship::$impulseEnergy, false if not possible */
	public function impulseEnergy(Local $target)
	{
		$sum = 0.0;

		$ships = $this->getShips();
		foreach ($ships as $ship) {
			$energy = $ship->impulseEnergy($target);
			if ($energy === false) {
				return false;
			}
			$sum += $energy;
		}

		return $energy;
	}

	/** Sum of Ship::$hyperEnergy, false if not possible */
	public function hyperEnergy()
	{
		$sum = 0.0;

		$ships = $this->getShips();
		foreach ($ships as $ship) {
			$energy = $ship->hyperEnergy();
			if ($energy === false) {
				return false;
			}
			$sum += $energy;
		}

		return $energy;
	}

	/** Sum of Ship::$quantumEnergy, false if not possible */
	public function quantumEnergy()
	{
		$sum = 0.0;

		$ships = $this->getShips();
		foreach ($ships as $ship) {
			$energy = $ship->quantumEnergy();
			if ($energy === false) {
				return false;
			}
			$sum += $energy;
		}

		return $energy;
	}
}

?>