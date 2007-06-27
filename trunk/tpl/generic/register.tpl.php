<?php
class_exists('Savant2') || exit;

$this->pageName = 'Register';
$this->title = 'Create an account and start playing';

include($this->loadTemplate('inc/header_splash.tpl.php'));

?><h1>Register an account</h1>

<h2>Account details</h2>
<?php

if (isset($this->regProblem) && !empty($this->regProblem)) {
	$registerProb = array(
		'queryHandle' => 'Unable to verify whether handle exists',
		'queryEmail' => 'Unable to verify whether e-mail address exists',
		'queryId' => 'Unable to find new ID',

		'invalidHandle' => 'Your account name must be composed of 4&ndash;16 alpha-numeric (a&ndash;z 0&ndash;9) characters',
		'invalidEmail' => 'That e-mail address is invalid',
		'confirmEmail' => 'The e-mail addresses do not match',
		'handleDuplicate' => 'The account name that you chose is already being used',
		'emailDuplicate' => 'That e-mail address is already associated with an existing account',

		'accountMail' => 'Unable to e-mail new account information before inserting it into the database',
		'accountCreate' => 'Failed adding the new account to the database'
	);
?><h3>Problems</h3>
<ul>
<?php
	foreach ($this->regProblem as $problem) {
		if (isset($registerProb[$problem])) {
?>
	<li><?php echo $registerProb[$problem]; ?></li>
<?php
		}
	}
?></ul>
<h3>Try again</h3>
<?php
}

?><p>By submitting this form, you agree to all the server-rules below.  Once
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

<textarea class="longText"><?php
$this->eprint($this->rules);
?></textarea>

<h2>Return to <a href="index.php">sign-in screen</a></h2>
<?php

include($this->loadTemplate('inc/footer_splash.tpl.php'));

?>
