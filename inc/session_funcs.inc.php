<?php

//Connect to the database if not already.
db_connect();

//Function that will log a user into gamelisting, or the admin into location.php
function login_to_server()
{
	global $p_user;
	$login_name = mysql_escape_string($_POST['l_name']);

	/********************** Admin Login *******************/
	if($login_name == "admin"){
		db('select * from se_games where admin_pw = \'' .
		 md5($_POST['passwd']) . '\'');
		$games_info = dbr(1);
		if (empty($games_info)) { //invalid admin login
			insert_history(1, "Bad login Attempt");
			exit('Wrong password, go away.');
		} else { //Admin successfully logged into game
			$db_name = $games_info['db_name'];
			$expire = time() + SESSION_TIME_LIMIT;

			$session = create_rand_string(32);
			setcookie("login_id", 1, $expire);
			setcookie("login_name", "Admin",$expire);
			setcookie("session_id", $session, $expire);

			insert_history(1, "Successfully logged in.");

			dbn("update ${db_name}_users set game_login_count = game_login_count + 1 where login_id = '1'");
			dbn("update se_games set session_id = '$session', session_exp = '$expire' where db_name = '$db_name'");

			header('Location: location.php');
			exit;
		}
	}

	/*************************User Login************************/
	db("select * from user_accounts where login_name = '$login_name'");
	$p_user = dbr(1);

	if(!isset($_POST['enc_pass'])){//user entered pass on login form
		$enc_pass = md5($_POST['passwd']);
		$pre_enc_pass = 0;
	} else { //pass coming from being hidden in auth. so set pre_enc to ensure auth is checked.
		$enc_pass = $_POST['enc_pass'];
		$pre_enc_pass = 1;
	}

	if (empty($p_user)) { //incorrect username
		print_header("Login Problem");
		echo "<blockquote>User <b>$login_name</b> does not exist on this Server.<br>
		Either you typed in your user name wrong, or your account no longer exists.<p>
		<p> <a href=signup_form.php>
		Sign up</a> <p> <a href=\"index.php\">Try to log in again.</a></b></blockquote>";
		print_footer();
		exit;

	} elseif($enc_pass != $p_user['passwd']) { //incorrect password
		print_header("Bad Password");
		echo "<blockquote><b>Error: The password you entered is incorrect.<br>Note: Password is case SenSitIve.
		<p><a href=\"javascript:history.back()\">Try to log in again.</a></b></blockquote><p>";
		insert_history($p_user['login_id'],"Bad Login Attempt");
		print_footer();
		exit;

	//valid username/pass combination.
	//But MUST enter a auth code to continue, as pre_enc_pass was set.
	//or no auth code yet entered, and sendmail is set
	} elseif($pre_enc_pass == 1 || ($p_user['auth'] == 0 && SENDMail == 1)) {

		//check authorisation e-mail
		if((empty($_POST['auth_code']) || $_POST['auth_code'] != $p_user['auth']) && SENDMail == 1 && $p_user['auth'] != 0) {
			print_header("Authorisation Code Required");
			$rs = "";
			if(empty($_POST['auth_code'])){
				echo "Please enter the Authorisation Code that was sent to your email address:<br><br>";
			} else {
				echo "Authorisation Code did not match.<br>";
			}
			echo "<form name=get_var_form action=login.php method=POST>";
			echo "<input type=hidden name=l_name value='$login_name'><input type=hidden name=enc_pass value='$enc_pass'>";
			echo "<input type=text name=auth_code value='' size=20> - ";
			echo "<input type=submit value=Submit></form>";
			print_footer();
			exit;
		}
	}

/*****************User successfully logged in***********************/

	$session = create_rand_string(32);

	$expire = time() + SESSION_TIME_LIMIT;

	setcookie("login_id", $p_user['login_id'], $expire);
	setcookie("login_name", $p_user['login_name'], $expire);
	setcookie("session_id", $session, $expire);

	dbn("update user_accounts set last_login = " . time() . ", session_id = '$session', session_exp = $expire, last_ip = '".$_SERVER['REMOTE_ADDR']."', login_count = login_count + 1 where login_id = $p_user[login_id]");
	insert_history($p_user['login_id'], "Logged Into GameList");

	if($p_user['last_login'] == 0) { //first login. show them the story.
		print_header("The Solar Empire Story");
		echo "<br><a href=\"game_listing.php\">Skip Story</a><br>";
		$storyText = include_once("story.inc.php");
		echo "<h1>The Solar Empire Story</h1>" . $storyText['The_Solar_Empire_Story'];
		echo "<br><br><a href=game_listing.php>Skip Story</a><br>";
		print_footer();
		exit;
	}
}

?>
