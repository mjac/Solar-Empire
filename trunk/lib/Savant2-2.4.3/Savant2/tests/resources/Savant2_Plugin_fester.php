<?php

/**
*
* Example plugin for unit testing.
* 
* @version $Id: Savant2_Plugin_fester.php,v 1.2 2004/11/05 16:00:38 pmjones Exp $
*
*/

require_once 'Savant2/Plugin.php';

class Savant2_Plugin_fester extends Savant2_Plugin {
	
	var $message = "Fester";
	var $count = 0;
	
	function Savant2_Plugin_fester()
	{
		// do some other constructor stuff
		$this->message .= " is printing this: ";
	}
	
	function plugin(&$text)
	{
		$output = $this->message . $text . " ({$this->count})";
		$this->count++;
		return $output;
	}
}
?>