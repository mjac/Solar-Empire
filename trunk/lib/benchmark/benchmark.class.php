<?php

/** Measures periods of time */
class benchmark
{
	var $startMS = 0.0;
	var $startS = 0;

	var $stopMS = 0.0;
	var $stopS = 0;

	function start()
	{
	    $start = explode(' ', microtime());
	    $this->startMS = (float)$start[0];
	    $this->startS = (int)$start[1];
	}

	function stop()
	{
	    $stop = explode(' ', microtime());
	    $this->stopMS = (float)$stop[0];
	    $this->stopS = (int)$stop[1];
	}

	function period()
	{
	    return (float)($this->stopS - $this->startS) +
		 ($this->stopMS - $this->startMS);
	}
};


/** Faster interface for benchmark */
class autoBenchmark extends benchmark
{
	function autoBenchmark()
	{
		$this->start();
	}

	function finish()
	{
	    $this->stop();
	    return $this->period();
	}
};

?>
