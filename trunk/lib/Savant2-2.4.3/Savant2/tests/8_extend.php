<?php

/**
* 
* Tests default plugins
*
* @version $Id: 8_extend.php,v 1.1 2004/10/04 01:52:24 pmjones Exp $
* 
*/

error_reporting(E_ALL);

require_once 'Savant2.php';

$conf = array(
	'template_path' => 'templates',
	'resource_path' => 'resources'
);

$savant =& new Savant2($conf);

$savant->display('extend.tpl.php');

?>