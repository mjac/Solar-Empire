<?php

/**
* 
* Tests filters and plugins
*
* @version $Id: 5_filters.php,v 1.1 2004/10/04 01:52:24 pmjones Exp $
* 
*/


error_reporting(E_ALL);

require_once 'Savant2.php';

$conf = array(
	'template_path' => 'templates',
	'resource_path' => 'resources'
);

$savant =& new Savant2($conf);

// set up filters
$savant->loadFilter('colorizeCode');
$savant->loadFilter('trimwhitespace');
$savant->loadFilter('fester', null, true);

// run through the template
$savant->display('filters.tpl.php');

// do it again to test object persistence
$savant->display('filters.tpl.php');

// do it again to test object persistence
$savant->display('filters.tpl.php');

echo "<hr />\n";
echo "<pre>";
print_r($savant);
echo "</pre>";

?>