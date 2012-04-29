<?php

require_once('inc/common.inc.php');

//Connect to the database
db_connect();


$login_name = trim(mysql_escape_string((string)$_POST['l_name']));

// check non-optionals
if(empty($login_name)) {
	print_header("New Account Creation");
	echo "You need to enter a Login Name.";
	echo "<p><a href=javascript:history.back()>Back to Sign-up Form</a>";
	print_footer();
	exit();
}

if((strcmp($login_name,htmlspecialchars($login_name))) || strlen($login_name) < 3 || (eregi("[^a-z0-9~@$%&*_+-=��������׀�� ]",$login_name))) {
	print_header("New Account Creation");
	echo "Invalid login name. No slashes, no spaces, no HTML permitted in name, and a minimum of three characters.";
	echo "<p><a href=javascript:history.back()>Back to Sign-up Form</a>";
	print_footer();
	exit();
}

if(empty($_POST['real_name'])) {
	print_header("New Account Creation");
	echo "You need to enter a first name.";
	echo "<p><a href=javascript:history.back()>Back to Sign-up Form</a>";
	print_footer();
	exit();
}

if($_POST['passwd'] == $login_name) {
	print_header("New Account Creation");
	echo "It's generally regarded as bad practise to use your login name as your password.<br>You should use a different password.";
	echo "<p><a href=javascript:history.back()>Back to Sign-up Form</a>";
	print_footer();
	exit();
}

$email_address = (string)mysql_escape_string($_POST['email_address']);
if($email_address != $_POST['email_address_verify']) {
	print_header("New Account Creation");
	echo "The email addresses you entered did not match.";
	echo "<p><a href=javascript:history.back()>Back to Sign-up Form</a>";
	print_footer();
	exit();
}

#Pattern used to determine if e-mail addy is valid
	$pattern = "^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$";

if(empty($email_address)) {
	print_header("New Account Creation");
	echo "You need to Enter an Email Address.";
	echo "<p><a href=javascript:history.back()>Back to Sign-up Form</a>";
	print_footer();
	exit();

}elseif(!eregi($pattern, $email_address)){
	print_header("New Account Creation");
	echo "Please Enter a Valid Email Address";
	echo "<p><a href=javascript:history.back()>Back to Sign-up Form</a>";
	print_footer();
	exit();
}

// check passwd
if($_POST['passwd'] != $_POST['passwd_verify']) {
	print_header("New Account Creation");
	echo "The passwords you entered did not match.<br>They are case-sensitive.";
	echo "<p><a href=javascript:history.back()>Back to Sign-up Form</a>";
	print_footer();
	exit();
}

// check for existing username
db("select login_id from user_accounts where login_name = '$login_name'");
$user = dbr(1);
if(!empty($user['login_id'])) {
	print_header("New Account Creation");
	echo "Login name already taken";
	echo "<p><a href=javascript:history.back()>Back to Sign-up Form</a>";
	print_footer();
	exit();
}

$email_address = strtok($email_address," ,");

// check for existing email_address
db("select login_id from user_accounts where email_address = '$email_address'");
$user = dbr(1);
if(!empty($user['login_id'])) {
	print_header("New Account Creation");
	echo "There is already an account with that email address";
	echo "<p><a href=javascript:history.back()>Back to Sign-up Form</a>";
	print_footer();
	exit();
}


// generate auth number
$auth = abs(mt_rand(0, getrandmax()));

dbn("insert into user_accounts (login_name, passwd, auth, signed_up, real_name, email_address) VALUES('$login_name', '".md5($_POST['passwd'])."', '$auth', '".time()."', '".mysql_escape_string($_POST['real_name'])."', '$email_address')");
$login_id = mysql_insert_id();

if(SENDMail == 1) {

	$message = "A new Solar Empire account has been created on " . $_SERVER['HTTP_HOST'] . " for you.\r\n
	Once you have logged into the account you will be able to join any game on the server.\r\n
	Your login name for the server is $login_name.\r\n
	Your Authorisation code is $auth.\r\n
	You will need your authorisation code the first time you log in.\r\n
	Welcome to the Server. We hope you enjoy the games.";

	if(send_mail(SERVER_NAME, $_SERVER['SERVER_ADMIN'], $_POST['real_name'], $email_address, SERVER_NAME." Authorisation Code", $message)){
		echo "Authorisation mail sent successfully. You will need it the first time you try to sign in.<p>";
		echo "You have <b class=b1>one week</b> to activate the account with the given authorisation code.<br>If it is not authorised within a week, the account will be deleted and you will have to signup again.<p>";
	} else {
		echo "ERROR! - Unable to send Authorisation mail for some reason.<p>";
		echo "The account will be deleted automatically in about 1 week.<br>You can then try again.<p>";
	}
}

insert_history($login_id,"Created Account");

print_header("New Account Created");
echo "Congratulations, your account has been set up.";
echo "<br><a href=\"login_form.php\">Click Here</a> to return to the login page.";

print_footer();

?>
