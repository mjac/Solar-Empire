<?php
class_exists('Savant2') || exit;

$this->pageName = 'Server list';
$this->title = 'Actively updated list of current servers';

include($this->loadTemplate('inc/headersplash.tpl.php'));

?><h1>Server list</h1>
<ul>
	<li><a href="http://www.solar-empire.net/">Endless War</a></li>
	<li><a href="http://game.quantum-star.com/">QS: Generations</a></li>
	<li><a href="http://www.imperial-empire-se.com/">Imperial Empire</a></li>
	<li><a href="http://solarempire.fuoriradio.com/">Italian Server</a></li>
</ul>
<?php

include($this->loadTemplate('inc/footersplash.tpl.php'));

?>
