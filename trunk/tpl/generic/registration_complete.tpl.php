<?php
if (!defined('PATH_SAVANT')) exit();

$title = 'Registration complete';

include($this->loadTemplate('inc/header_splash.tpl.php'));

?><h1>Register an account</h1>
<h2>Your account has been created</h2>
<p>Congratulations, your account has been set up.</p>

<h2><a href="index.php">Start playing!</a></p>
<?php

include($this->loadTemplate('inc/footer_splash.tpl.php'));

?>
