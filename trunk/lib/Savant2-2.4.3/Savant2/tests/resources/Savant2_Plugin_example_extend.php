<?php

/**
* 
* Example plugin for unit testing.
*
* @version $Id: Savant2_Plugin_example_extend.php,v 1.1 2004/10/04 01:52:24 pmjones Exp $
*
*/

$this->loadPlugin('example');

class Savant2_Plugin_example_extend extends Savant2_Plugin_example {
	
	var $msg = "Extended Example! ";
	
}
?>