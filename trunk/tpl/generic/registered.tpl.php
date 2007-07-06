<?php
class_exists('Savant2') || exit;

$this->pageName = 'Register';
$this->title = 'Registration complete, account created';

include($this->loadTemplate('inc/headersplash.tpl.php'));

?><h1>Register an account</h1>
<h2>Your account has been created</h2>
<p>Congratulations, your account has been created successfully. Access your e-mail account to receive your new password before returning to the <a href="<?php
$this->eprint($this->url['base'] . '/index.php'); ?>">sign-in screen</a> to begin your adventure.</p>
<?php

include($this->loadTemplate('inc/footersplash.tpl.php'));

?>
