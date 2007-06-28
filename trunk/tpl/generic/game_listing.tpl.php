<?php
class_exists('Savant2') || exit;

if (!function_exists('popupHelp')) {
	require($this->loadTemplate('inc/popupHelp.inc.php'));
}

function formatGameList(&$tpl, $gList)
{
	$list = "\t<ul>\n";
	foreach ($gList as $game) {
		$list .= "\t\t<li><a href=\"" . $tpl->escape($tpl->url['self'] . 
		 '?game_selected=' . $game['db_name']) . "\">" . 
		 $tpl->escape($game['name']) . "</a> (" . 
		 $tpl->escape($game['status']) . ') - ' . 
		 popupHelp('gameinfo.php?db_name=' . $game['db_name'], 600, 450,
		 'Info', $tpl) . "</li>\n";
	}
	$list .= "\t</ul>\n";

	return $list;
}

$this->pageName = 'Game listing';
$this->title = 'Listing of games running on this server';

include($this->loadTemplate('inc/header_splash.tpl.php'));

$joined = array();
$unjoined = array();

if (isset($this->gameList)) {
	foreach ($this->gameList as $game) {
		if ($game['in']) {
			$joined[] = $game;
		} else {
			$unjoined[] = $game;
		}
	}
}

?>

<h1>Game Listing<?php
if (isset($this->accountName)) {
	$this->eprint('for ' . $this->accountName);
}
?></h1>

<div id="gameList">
<?php

if (!empty($joined)) {
?>	<h2>Joined games</h2>
<?php
	echo formatGameList($this, $joined);
}

if (!empty($unjoined)) {
?>	<h2>Unjoined games</h2>
<?php
	echo formatGameList($this, $unjoined);
}

if (empty($joined) && empty($unjoined)) {
?>	<p>There are no games running.</p>
<?php
}

?>
</div>
<?php
if (isset($this->tip)) {
?><h2>Random tip</h2>
<p><?php echo $this->tip; ?></p>
<?php
}

if (isset($this->serverNews)) {
<h2>Recent news</h2>
<div class="longText"><?php
echo $this->serverNews;
?></div>
<?php
}
?>
<h2>Options</h2>
<ul>
	<li><a href="<?php $this->eprint($this->url['base'] . '/logout.php');
?>">Logout Completely</a></li>
	<li><a href="<?php $this->eprint($this->url['base'] . '/credits.php');
?>">Credits</a></li><?php
if ($this->canCreateGame) {
?>
	<li><form action="<?php $this->eprint($this->url['self']); 
?>" method="get">
		<p><input type="text" name="newGame" class="text" />
		<input type="submit" class="button" value="Add game" /></p>
	</form></li>
<?php
}
?>
</ul>

<h2>Places to go</h2>
<ul>
	<li><a href="http://www.solarempire.com/">Solar Empire Home</a></li>
	<li><a href="http://forum.syswars.com/">Solar Empire Forum</a></li>
	<li><a href="http://sourceforge.net/projects/solar-empire/">Sourceforge Project</a></li>
</ul>
<?php

include($this->loadTemplate('inc/footer_splash.tpl.php'));

?>
