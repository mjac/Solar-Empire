<?php
defined('PATH_SAVANT') || exit();

$title = 'Credits, a list of contributors to System Wars';

include($this->loadTemplate('inc/header_splash.tpl.php'));

?><h1>Credits, contributors to System Wars</h1>

<p>People who have contributed to the development of System Wars, both directly 
and indirectly.  Return to the <a href="<?php 
$this->eprint($this->url['base'] . '/index.php');
?>">sign-in form</a> when you 
have finished reading.</p>

<h2>System Wars</h2>
<p>Developed by <a href="http://www.mjac.co.uk/">Michael J.A. Clark</a> 
(<a href="http://www.mjac.co.uk/">Mjac</a>) from the public domain Generic 
2.2.2 release.</p>
<h3>Other contributors</h3>
<dl>
	<dt>Ship images and free hosting</dt>
	<dd><a href="http://www.imod.ca/">Joshua Eaton</a> (Lex Luthor)</dd>
</dl>

<h2>Generic 2.2.2</h2>
<dl>
	<dt>Original designer</dt>
	<dd><a href="http://www.bryanlivingston.com/">Bryan Livingston</a>
	with inspiration from Eric Hamilton</dd>

	<dt>Closed-source programming</dt>
	<dd>Rob Hardy and Randee Shirts</dd>

	<dt>Open-source programming</dt>
	<dd>Moriarty, KilerCris, TheRumour,
	<a href="http://www.quantum-star.com/">Maugrim_The_Reaper</a></dd>

	<dt>Renders</dt>
	<dd>Admiral V'Pier (meshes from
	<a href="http://www.3dtotal.com/">www.3dtotal.com</a>) and
	<a href="http://www.imod.ca/">Joshua Eaton</a></dd>

	<dt>Opening story</dt>
	<dd><a href="http://www.quantum-star.com/">Maugrim_The_Reaper</a></dd>
</dl>
<?php

include($this->loadTemplate('inc/footer_splash.tpl.php'));

?>
