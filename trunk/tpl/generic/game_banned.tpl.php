<?php
class_exists('Savant2') || exit;

$this->pageName = 'Banned';
$this->title = 'You have been banned from playing this game';

include($this->loadTemplate('inc/headersplash.tpl.php'));

?><h1>You are banned from this game</h1>

<p>The Admin of this game has banned you from it, <strong>until <?php
$this->eprint($this->bannedUntil > 0 ? 
 date('l jS F H:i', $this->bannedUntil) : 'it resets');
?></strong> 
or until the admin releases the ban. During this period your fleets/planets are
susceptible to the usual woes of the game.</p>
<p>The reason given by the <cite>admin</cite> was:</p>
<blockquote>
	<p><?php $this->eprint($this->banReason); ?></p>
</blockquote>
<?php

include($this->loadTemplate('inc/footersplash.tpl.php'));

?>
