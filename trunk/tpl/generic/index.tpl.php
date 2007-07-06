<?php
class_exists('Savant2') || exit;

$this->pageName = 'Welcome';
$this->title = 'Competitive, web based, space combat game';

$showLogin = $showRegister = true;
include($this->loadTemplate('inc/headersplash.tpl.php'));

?><h1>Welcome to System Wars</h1>

<h2>Introduction</h2>
<p><em>System Wars</em> is a highly competitive, web based, <strong>space combat game</strong> based on the <a href="http://www.solarempire.com/">Solar Empire</a> universe.  Master the arts of colonisation and exploration while playing a noble but precarious game of warfare to ensure survival.</p>

<h2>Project information</h2>
<p><a href="http://sourceforge.net/projects/solar-empire/"><img src="http://sourceforge.net/sflogo.php?group_id=16534&amp;type=3" width="125" height="37" alt="Solar Empire sourceforge project" style="float: right; padding: 1em;" /></a></p>
<p><a href="http://sourceforge.net/projects/solar-empire/">Solar Empire</a> is an open source project hosted on SourceForge; look at the <a href="http://sourceforge.net/news/?group_id=16534">news section</a> for the latest updates.  The <a href="<?php $this->eprint($this->url['base'] . '/credits.php'); ?>">credits</a> page contains a list of all the contributors to the game.</p>
<p>Visit the <a href="http://forum.syswars.com/">global forums</a> to learn more and help develop this open-source game.</p>
<?php

include($this->loadTemplate('inc/footersplash.tpl.php'));

?>
