<?php

define('SYS_EVENT_NONE',      0x00);
define('SYS_EVENT_BACKHOLE',  0x01);
define('SYS_EVENT_SUPERNOVA', 0x02);

class system
{
	var $id;
	var $name = '';

	var $x = NULL;
	var $y = NULL;

	var $links = array();
	var $wormhole = array();

	var $metal = 0;
	var $fuel  = 0;

	var $event = SYS_EVENT_NONE;
	var $area = 0;

	function system($id)
	{
		$this->id = $id;
	}

	function distance($star)
	{
		return sqrt(pow($this->x - $star->x, 2) + pow($this->y - $star->y, 2));
	}

	function eventAdd($event)
	{
		$this->event |= $event;
	}

	function eventRemove($event)
	{
		$this->event &= ~$event;
	}

	function event($event)
	{
		return $this->event & $event ? true : false;
	}
}

?>
