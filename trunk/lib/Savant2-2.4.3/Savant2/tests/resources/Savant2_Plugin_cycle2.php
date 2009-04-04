<?php

/**
* 
* Example plugin for unit testing.
*
* @version $Id: Savant2_Plugin_cycle2.php,v 1.1 2004/10/04 01:52:24 pmjones Exp $
*
*/

require_once 'Savant2/Plugin.php';

class Savant2_Plugin_cycle extends Savant2_Plugin {
	function plugin()
	{
		return "REPLACES DEFAULT CYCLE";
	}
}
?>