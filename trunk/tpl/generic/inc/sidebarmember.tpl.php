<?php
class_exists('Savant3') || exit;
?><ul id="splashSidebar">
	<li><a href="<?php
$this->eprint($this->url['base'] . '/credits.php');
?>">Game credits</a></li>
	<li><a href="<?php
$this->eprint($this->url['base'] . '/logout.php');
?>">Logout</a></li>
	<li><a href="http://forum.syswars.com/">Global forum</a></li>
	<li><a href="http://sourceforge.net/projects/solar-empire/">SourceForge</a></li>
</ul>
