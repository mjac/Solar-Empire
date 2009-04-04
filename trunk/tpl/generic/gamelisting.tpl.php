<?php
class_exists('Savant3') || exit;

$this->pageName = 'Game listing';
$this->title = 'Listing of games running on this server';

$showLogout = true;
include($this->template('inc/headermember.tpl.php'));

?><h1>Game listing<?php
if (isset($this->accountName)) {
	$this->eprint('for ' . $this->accountName);
}
?></h1>
<?php
if (!empty($this->gameList)) {
?><h2>Available games</h2>
<dl id="gameList">
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
<blockquote>
	<p><?php echo $this->tip; ?></p>
</blockquote>
<?php
}

if (isset($this->serverNews)) {
?><h2>Recent news</h2>
<div class="longText"><?php
echo $this->serverNews;
?></div>
<?php
}

include($this->template('inc/footermember.tpl.php'));

?>
