<?php
class_exists('Savant2') || exit;

$this->pageName = 'Register';
$this->title = 'Create an account and start playing';

include($this->loadTemplate('inc/header_splash.tpl.php'));

?><h1>Register an account</h1>

<h2>Account details</h2>
<p>By submitting this form, you agree to all the server-rules below.  Once
you receive your password by e-mail, sign-in to prevent your account being
deleted.</p>
<form method="post" action="register.php">
	<dl>
		<dt><label for="handle">Handle</label></dt>
		<dd><input type="text" name="handle" class="text" /></dd>

		<dt><label for="name">Real-name</label></dt>
		<dd><input type="text" name="name" class="text" /></dd>

		<dt><label for="email">Email address</label></dt>
		<dd>Write it twice for verification; a random account password will be 
		sent to this address.</dd>
		<dd><input type="text" name="email" class="text" /></dd>
		<dd><input type="text" name="email2" class="text" /></dd>

		<dt><input type="submit" value="Submit" class="button" /></dt>
	</dl>
</form>

<div><?php

echo $this->rules;

?>
</div>

<h2>Return to <a href="index.php">sign-in screen</a></h2>
<?php

include($this->loadTemplate('inc/footer_splash.tpl.php'));

?>
