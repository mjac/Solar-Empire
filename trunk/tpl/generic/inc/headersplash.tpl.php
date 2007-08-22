<?php
class_exists('Savant2') || exit;

include($this->loadTemplate('inc/header.tpl.php'));

?><div id="logo"><img src="<?php
$this->eprint($this->url['base'] . '/img/systemwars.jpg');
?>" alt="Solar Empire: System Wars" /></div>
<?php
include($this->loadTemplate('inc/sidebarsplash.tpl.php'));
?>
<div id="splashContent">

