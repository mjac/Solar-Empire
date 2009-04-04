<?php
class_exists('Savant3') || exit;

include($this->template('inc/header.tpl.php'));

?><div id="logo"><img src="<?php
$this->eprint($this->url['base'] . '/img/systemwars.jpg');
?>" alt="Solar Empire: System Wars" /></div>
<?php
include($this->template('inc/sidebarsplash.tpl.php'));
?>
<div id="splashContent">

