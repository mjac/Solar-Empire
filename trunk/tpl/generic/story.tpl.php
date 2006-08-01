<?php
if (!defined('PATH_SAVANT')) exit();

$title = 'The Solar Empire Story';

include($this->loadTemplate('inc/header_splash.tpl.php'));

?><h1>The Solar Empire Story</h1>

<p><a href="game_listing.php">Game listing</a></p>
<?php

include('inc/story.inc.php');
echo $story['The_Solar_Empire_Story'];

?><p><a href="game_listing.php">Game listing</a></p>

<?php

include($this->loadTemplate('inc/footer_splash.tpl.php'));

?>
