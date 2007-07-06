<?php
class_exists('Savant2') || exit;

$this->pageName = 'Game listing';
$this->title = 'Listing of games running on this server';

$showLogout = true;
include($this->loadTemplate('inc/headermember.tpl.php'));

?><h1>Game listing<?php
if (isset($this->accountName)) {
	$this->eprint('for ' . $this->accountName);
}
?></h1>
<?php
if (!empty($this->gameList)) {
?><dl id="gameList">
<?php
	foreach ($this->gameList as $gameArr) {
?>	<dt><a href="<?php
		$this->eprint($this->url['self'] . ($gameArr['joined'] ?
		 '/gameenter.php' : '/gamejoin.php') . '?gameid=' . $gameArr['id']);
?>"><?php $this->eprint($gameArr['name']); ?></a></dt>
	<dd><?php $this->eprint($gameArr['summary']); ?></dd>
<?php
	}
?></dl>
<?php
}

if (isset($this->tip)) {
?><h2>Random tip</h2>
<p><?php echo $this->tip; ?></p>
<?php
}

if (isset($this->serverNews)) {
?><h2>Recent news</h2>
<div class="longText"><?php
echo $this->serverNews;
?></div>
<?php
}
?>
<h2>Options</h2>
<ul>
	<li><a href="<?php $this->eprint($this->url['base'] . '/logout.php');
?>">Logout from server</a></li>
	<li><a href="<?php $this->eprint($this->url['base'] . '/credits.php');
?>">Credits</a></li>
</ul>

<h2>External places</h2>
<ul>
	<li><a href="http://www.syswars.com/">System Wars Home</a></li>
	<li><a href="http://forum.syswars.com/">System Wars Forum</a></li>
	<li><a href="http://www.solarempire.com/">Solar Empire Home</a></li>
	<li><a href="http://sourceforge.net/projects/solar-empire/">SourceForge Project</a></li>
</ul>
<?php

include($this->loadTemplate('inc/footermember.tpl.php'));

?>
