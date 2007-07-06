<?php

require_once('inc/common.inc.php');
require_once('inc/db.inc.php');

if (!(isset($_POST['handle']) && isset($_POST['name']) &&
	 isset($_POST['email']) && isset($_POST['email2']))) {
	print_header("New Account");

?>
<div id="logo"><img src="img/se_logo.jpg" alt="Solar Empire" /></div>

<h1>Register an account</h1>

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

		<dt>Colour scheme</dt>
		<dd><input type="radio" name="style" value="1"
		 checked="checked" /> <label for="style1">Classic</label></dd>

		<dt><input type="submit" value="Submit" class="button" /></dt>
	</dl>
</form>
<?php

	include_once('inc/rules.inc.html');

	print_footer();
	exit();
}

// check non-optionals
if (empty($_POST['handle'])) {
	print_header("New Account Creation");
	echo "You need to enter a Login Name.";
	echo "<p><a href=javascript:history.back()>Back to Sign-up Form</a>";
	print_footer();
	exit();
}

if (!valid_name($_POST['handle'])) {
	print_header("New Account Creation");
?>
<p>Invalid login name: 4-32 ASCII numeric, letters, or punctuation characters.</p>
<p><a href="register.php" onclick="history.back(); return false;">Try again</a></p>
<?php
	print_footer();
	exit();
}

if ($_POST['email'] !== $_POST['email2']) {
	print_header('New account creation');
?>
<p>The email addresses you entered did not match.</p>
<p><a href="register.php" onclick="history.back(); return false;">Try again</a></p>
<?php
	print_footer();
	exit();
}

if (!(strlen($_POST['email']) < 65 && isEmailAddr($_POST['email']))) {
	print_header("New Account Creation");
?>
<p>Please Enter a Valid Email Address</p>
<p><a href="register.php" onclick="history.back(); return false;">Try again</a></p>
<?php
	print_footer();
	exit();
}

// check for existing username
$nameTaken = $db->query('SELECT COUNT(*) FROM user_accounts WHERE ' .
 'login_name = \'%s\'', array($db->escape($_POST['handle'])));

if (current($db->fetchRow($nameTaken)) > 0) {
	print_header("New Account Creation");
?>
<p>Login name already taken</p>
<p><a href="register.php" onclick="history.back(); return false;">Try again</a></p>
<?php
	print_footer();
	exit();
}

// check for existing email_address
$emailUsage = $db->query('SELECT COUNT(*) FROM user_accounts WHERE ' .
 'email_address = \'%s\'', array($db->escape($_POST['email'])));

if (current($db->fetchRow($emailUsage)) > 0) {
	print_header("New Account Creation");
?>
<p>There is already an account with that email address</p>
<p><a href="register.php" onclick="history.back(); return false;">Try again</a></p>
<?php
	print_footer();
	exit();
}

require_once('inc/external/sha256/sha256.class.php');
$password = create_rand_string(6);

$newId = newId('user_accounts', 'login_id');
$db->query('INSERT INTO user_accounts (login_id, login_name, passwd, ' .
 'signed_up, real_name, email_address) VALUES (%u, \'%s\', \'%s\', %u, ' .
 '\'%s\', \'%s\')', array($newId, $db->escape($_POST['handle']),
 $db->escape(sha256::hash($password)), time(), $db->escape($_POST['name']),
 $db->escape($_POST['email'])));

$location = URL_FULL;
$message = <<<END
SYSTEM WARS REGISTRATION
$location

You created a new account, here are the details.

Account name
	$_POST[handle]
Random password (change this)
	$password
Your name
	$_POST[name]

Welcome to the community; you can begin your adventure straight away.

You must sign-in once to stop your account being deleted in a few hours.
Do this now!
END;

mail("$_POST[name] <$_POST[email]>", "New account at $location", $message,
 "From: System Wars Mailer <game@$_SERVER[HTTP_HOST]>");

print_header("New Account Created");

?>
<p>Congratulations, your account has been set up.</p>
<p><a href="index.php">Return</a> to the login page.</p>

<?php

print_footer();

?>
