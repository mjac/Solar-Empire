<?php

/**
* 
* Tests default plugins
*
* @version $Id: a_classExists.php,v 1.1 2006/01/01 17:40:11 pmjones Exp $
* 
*/

error_reporting(E_ALL);

function __autoload($class) {
    echo "(trying autoload) ";
    return false;
}


require_once 'Savant2.php';

$savant =& new Savant2();

echo "<pre>";

echo "PHP " . PHP_VERSION . " Savant2: ";
var_dump($savant->_classExists('Savant2'));
echo "\n\n";

echo "PHP " . PHP_VERSION . " SavantX: ";
var_dump($savant->_classExists('SavantX'));
echo "\n\n";

$savant->setAutoload(true);

echo "PHP " . PHP_VERSION . " Savant2 with __autoload(): ";
var_dump($savant->_classExists('Savant2'));
echo "\n\n";

echo "PHP " . PHP_VERSION . " SavantX with __autoload(): ";
var_dump($savant->_classExists('SavantX'));
echo "\n\n";


echo "</pre>";


?>