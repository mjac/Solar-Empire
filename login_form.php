<?php

require_once('inc/common.inc.php');

print_header('Welcome to Solar Empire');

?>
<div id="logo"><img src="img/se_logo.jpg" alt="Solar Empire" /></div>

<form id="login" action="game_listing.php" method="post">
	<h2>Login or sign-up</h2>
	<p><label for="l_name">Login Name:</label><br />
	<input type="text" name="l_name" id="l_name" value="<?php
print esc($login_name); ?>" class="text" /></p>
	<p><label for="passwd">Password:</label><br />
	<input type="password" name="passwd" id="passwd" class="text" /></p>
	<p><input type="submit" value="Login" /></p>
	<p>New User? <a href="signup_form.php">Signup Here</a></p>
</form>

<div id="loginContent">

<h1>Welcome to Solar Empire</h1>

<h2>Introduction</h2>
<p>Solar Empire is a highly competitive, web based, space combat game.</p>
<p>A quote from a player: <q>I sure wish work would quit cutting into my
gaming time</q>.</p>

<h2>Operational Servers</h2>
<ul>
	<li><a href="http://www.solar-empire.net/">Endless War</a></li>
	<li><a href="http://se.purely.info/">Purely SE</a></li>
	<li><a href="http://qse.tradelair.com/">Tradelair</a></li>
	<li><a href="http://www.gegn.net/">Galactic Empire</a></li>
</ul>

<p><a href="http://sourceforge.net/projects/solar-empire/">Solar Empire</a> is
an open source project -
<a href="http://sourceforge.net/news/?group_id=16534">Development News</a></p>
</div>
<?php

print_footer();

?>