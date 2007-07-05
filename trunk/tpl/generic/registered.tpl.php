<?php
class_exists('Savant2') || exit;

$this->pageName = 'Register';
$this->title = 'Registration complete, account created';

include($this->loadTemplate('inc/headersplash.tpl.php'));

?><h1>Register an account</h1>
<h2>Your account has been created</h2>
<p>Congratulations, your account has been set up.</p>

<h2><a href="index.php">Start playing!</a></p>
<?php

include($this->loadTemplate('inc/footersplash.tpl.php'));

?>
