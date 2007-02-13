<?php
class_exists('Savant2') || exit;

$this->pageName = 'Administration';
$this->title = 'Game administration panel';
$this->description = '';

include($this->loadTemplate('game/inc/header_game.tpl.php'));

?><h1>Administration</h1>

<h2>Game Functions</h2>
<ul>
	<li><a href="<?php
$this->eprint($this->url['base'] . '/admin_edit_vars.php'); 
?>">Edit variables</a></li>
	<li><a href="<?php
$this->eprint($this->url['base'] . '/admin_settings.php');
?>">Edit settings</a></li>
	<li><a href="<?php
$this->eprint($this->url['base'] . '/admin.php?reset=1'); 
?>">Reset game</a></li>
</ul>

<h2>Godlike Abilities</h2>
<ul>
	<li><a href="<?php
$this->eprint($this->url['base'] . '/admin_build_universe.php'); 
?>">Universe generator</a></li>
	<li><a href="<?php
$this->eprint($this->url['base'] . '/admin_edit_links.php'); 
?>">Edit star links</a></li>
	<li><a href="<?php
$this->eprint($this->url['base'] . '/admin_unlink_scan.php'); 
?>">Link star islands</a></li>
</ul>

<h2>Communications</h2>
<ul>
	<li><a href="<?php
$this->eprint($this->url['base'] . '/message.php?target=-4'); 
?>">Message everyone</a></li>
	<li><a href="<?php
$this->eprint($this->url['base'] . '/admin.php?post_game_news=1'); 
?>">Post news</a></li>
</ul>

<h2>Players</h2>
<ul>
	<li><a href="<?php 
$this->eprint($this->url['base'] . '/admin_ban_player.php'); 
?>">Ban player</a></li>
	<li><a href="<?php
$this->eprint($this->url['base'] . '/admin_active_users.php');
?>">View online players</a></li>
	<li><a href="<?php
$this->eprint($this->url['base'] . '/admin.php?more_money=1'); 
?>">Give money</a></li>
</ul>
<?php

include($this->loadTemplate('game/inc/footer_game.tpl.php'));

?>
