<?php

require_once('inc/user.inc.php');

$error_str = "";

// change player options
if(isset($player_op) && $player_op == 1){
	$error_str .= <<<END
<h1>Change player information</h1>
<form method="post" action="options.php">
	<dl>
		<dt>Signature (100 characters)</dt>
		<dd>$user[sig]</dd>
		<dd><textarea name="sig" cols="25" rows="10"></textarea></dd>

		<dt><input type="submit" name="Submit" />
		<input type="hidden" name="player_op" value="2" /></dt>
	</dl>
</form>

END;

	print_page("Change Player Information", $error_str);
} elseif (isset($player_op) && $player_op == 2) {
	$db->query('UPDATE [game]_users SET sig = \'%s\' WHERE login_id = %u',
	 array($db->escape(msgToHTML($sig)), $user['login_id']));
	$error_str .= "<p>User information updated</p>\n";
}

#save changes to vars
if (isset($save_vars)) {
	foreach ($_POST as $var => $value) {
		$option_check = "";
		if ($var == 'save_vars' || !(isset($userOpt[$var]) &&
		     $value != $userOpt[$var])) {
			continue;
		}

		#ensure option is in range
		db("SELECT option_min, option_max FROM option_list WHERE " .
		 "option_name = '$var'");
		$option_check=dbr();
		#option out of range
		if($value < $option_check['option_min'] || $value > $option_check['option_max']){
			$error_str .= "<br /><b class=b1>$var</b> out of range.";
		} else { #option in range
			dbn("update [game]_user_options set $var = '$value' where login_id = '$user[login_id]'");
			$userOpt[$var] = $value;
			$error_str .= "<br /><b class=b1>$var</b> updated to <b>$value</b>";
		}
	}
}

// retire
if (isset($retire)) {
    if ($user['clan_id'] !== NULL) {
        print_page('Cannot retire', 'You must disband or leave your clan before retiring.');
    } elseif(!isset($sure)) {
		get_var("Retire","options.php","<p><b class=b1>Warning!</b> This will permanently remove your account from this game. <br />Are you sure you want to retire?", "sure", "yes");
	} else {
		retire_user($user['login_id']);
		insert_history($user['login_id'], 'Retired From Game');
		print_header('Account Removed');
		echo <<<END
<p>You have been removed from the Game.</p>
<p><a href=game_listing.php>Go to Game List</a></p>
END;
		print_footer();
		exit();
	}
}


// change password
if(isset($changepass)) {
	$back = <<<END
<p><a href="options.php?changepass=change" onclick="history.back();">Try 
again</a></p>

END;

	require_once('inc/external/sha256/sha256.class.php');
	if ($changepass == 'change' || !(isset($newpass) && isset($oldpass))) {
		$temp_str = <<<END
<h1>Enter a new password</h1>
<form action="options.php" method="post">
<dl>
	<dt>Old password</dt>
	<dd><input type="password" name="oldpass" class="text" /></dd>

	<dt>New password</dt>
	<dd><input type="password" name="newpass" class="text" /></dd>
	
	<dt><input type="submit" value="Change password" class="button" />
	<input type="hidden" name="changepass" value="changed" /></dt>
</dl>
</form>

END;
		print_page("Change Password", $temp_str);
	} elseif ($changepass == 'changed') {
		$oldPass = sha256::hash($oldpass);
		$newPass = sha256::hash($newpass);

		if ($oldPass !== $p_user['passwd']) {
			$temp_str = "<p>The old password is wrong.</p>\n$back";
		} elseif ($newPass === $p_user['passwd']) {
		   $temp_str = <<<END
<p>Really. You want your new pass to be the same as your old one? Are you 
just wasting my bandwith?</p>
$back

END;
		} else {
			$db->query('UPDATE user_accounts SET passwd = \'%s\' WHERE ' .
			 'login_id = %u', array($db->escape($newPass), $user['login_id']));
			$p_user['passwd'] = $newPass;
			$temp_str = "<p>Password changed successfully.</p>\n";
			insert_history($user['login_id'], "Password Changed");
		}
	}

	print_page("Change Password", $temp_str);
}



#print main page
$error_str .= <<<END
<h1>Player options</h1>
<ul>
	<li><a href="options.php?changepass=change">Change your Password</a></li>
	<li><a href="options.php?player_op=1">Change your player information</a> (signature)</li>
	<li><a href="options.php?retire=1">Retire from Game</a></li>
</ul>
<form method="post" name="get_var_form" action="options.php">
	<h2>Edit variables</h2>
	<p><input type="submit" value="Submit Vars" class="button" /></p>

END;

#select and output all the user options
db("select * from option_list order by option_name asc");
while ($gen_options = dbr()) {
	#radio boxes.
	if($gen_options['option_type'] == 1){
		$ct = 0;
		$desc_vars = explode(' &&& ', $gen_options['option_desc']);
		$error_str.= "<h3>$gen_options[option_name]</h3>\n<p>$desc_vars[0]</p>\n";
		$checked = array();
		$checked = array_pad($checked, 5, '');
		$checked[$userOpt[$gen_options['option_name']]] = ' checked="checked"';
		$sec_count = 1;

		for ($ct = $gen_options['option_min']; $ct <= $gen_options['option_max']; ++$ct) {
			$error_str .= <<<END
	<p><input type="radio" name="$gen_options[option_name]" value="$ct"$checked[$ct] />
	$desc_vars[$sec_count]</p>\n

END;
			++$sec_count;
		}

	#numerical interface
	} elseif($gen_options['option_type'] == 2){
		$error_str .= <<<END
	<h3>$gen_options[option_name]</h3>
	<p><input type="text" name="$gen_options[option_name]" size="4" value="{$userOpt[$gen_options['option_name']]}" class="text" /> 
	Min: <b>$gen_options[option_min]</b>, Max: <b>$gen_options[option_max]</b></p>
	<p>$gen_options[option_desc]</p>

END;
	}
}

$error_str .= <<<END
	<p><input type="hidden" name="save_vars" value="1" />
	<input type="submit" value="Submit Vars" class="button" /></p>
</form>

END;

print_page("Account Options", $error_str);

?>
