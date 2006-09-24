<?php
defined('PATH_SAVANT') || exit();

$title = 'Game administration panel';

include($this->loadTemplate('game/inc/header_game.tpl.php'));

?><h1>Administration</h1>

<h2>Game Functions</h2>
<ul>
	<li><a href="admin_edit_vars.php">Edit variables</a></li>
	<li>Set status to <a href="admin.php?status=hidden">hidden</a>, 
	<a href="admin.php?status=paused">paused</a> or 
	<a href="admin.php?status=running">running</a></li>
	<li><a href="admin.php?reset=1">Reset game</a></li>
	<li><a href="admin.php?difficulty=1">Change stated difficulty</a></li>
	<li><form method="post" action="admin.php">
		<p><input type="text" name="finishes" value="YYYY-MM-DD HH:MM:SS"
		 class="text" />
		<input type="submit" value="Change finish date" class="button" /></p>
	</form></li>
</ul>

<h2>Godlike Abilities</h2>
<ul>
	<li><a href="admin_build_universe.php?build_universe=1&amp;process=1">Create
	the universe</a></li>
	<li><a href="admin.php?preview=1">Preview a universe</a></li>
	<li><a href="admin_build_universe.php?gen_new_maps=1&amp;process=1">Generate
	maps</a></li>
	<li><a href="admin_edit_links.php">Edit star links</a></li>
	<li><a href="admin_unlink_scan.php">Link star islands</a></li>
</ul>

<h2>Communications</h2>
<ul>
	<li><a href="message.php?target=-4">Message everyone</a></li>
	<li><a href="admin.php?post_game_news=1">Post news</a></li>
</ul>

<h2>Players</h2>
<ul>
	<li><a href="admin_ban_player.php">Ban player</a></li>
	<li><a href="admin.php?show_active=1">View online players</a></li>
	<li><a href="admin.php?more_money=1">Give money</a></li>
</ul>

<h2>General</h2>
<ul>
	<li><a href="admin.php?descr=1">Change the game description</a></li>
	<li><a href="admin.php?messag=1">Change the introduction message</a></li>
</ul>
<?php

include($this->loadTemplate('game/inc/footer_game.tpl.php'));

?>
