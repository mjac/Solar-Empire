<?php

require_once('inc/user.inc.php');

if($user['ship_id'] !== NULL && $userShip['location'] != 1) {
	 print_page("Access Denied","You are not in system #1.");
	 exit();
}

$commission = 7;

$text = <<<END
<h1>The Charity Shop</h1>
<p>Welcome to our humble abode, we offer a variety of services including
benevolence funds (bounties).</p>
<p>We charge a measly <strong>$commission% commission</strong>, but I am sure
that you will find that our service is fairly <em>efficient</em>.</p>

END;

if (isset($amount) && isset($target)) {
	if ($amount < 25) {
		$initial = $amount;
		$amount = round(($amount /100) * $commission) + $amount + 1;
	} else {
		$initial = $amount;
		$amount = round(($amount /100) * $commission) + $amount;
	}

	db("select clan_id from [game]_users where login_id = '$target'");
	$todo = dbr();

	if ($target == $user['login_id']) {
		print_page("Bounty","You may not place a bounty on yourself.");
	} elseif ($todo['clan_id'] == $user['clan_id'] && $user['clan_id'] !== NULL) {
		print_page("Bounty","You may not place a bounty on a clan-mate.");
	} elseif ($target == $gameInfo['admin']) {
		print_page("Bounty","You may not place a bounty on the Admin.");
	} elseif ($user['turns_run'] < $turns_before_attack && !IS_ADMIN) {
		print_page("Bounty","You may not place a bounty during the first <b>$turns_before_attack</b> turns of your account's existence. This is because placing a bounty is a form of attack.");
	} elseif ($amount < 0) {
		print_page("Bounty","Negative sums can not be placed for bounties.");
	} elseif (!$initial) {
		print_page("Bounty","You didn't state an amount to place on $targets head.");
	} elseif (!giveMoneyPlayer(-$amount)) {
		print_page("Bounty","You do not have enough money to place that bounty.");
	} else {
		dbn("update [game]_users set bounty = bounty + '$initial' where login_id = '$target'");
		db("select bounty, login_name from [game]_users where login_id = '$target'");
		$returned = dbr();
		msgSendSys($target, "<p>Someone has put $initial on your head, " .
		 "making your bounty <em>$returned[bounty] credits</em>.</p>");
		if ($returned['bounty'] > $amount) {
			$text .= "You have added <b>$initial</b> Credits to <b class=b1>$returned[login_name]'s</b> bounty, making the present bounty <b>$returned[bounty]</b>. You were charged <b>$amount</b> Credit(s) for the transaction.<p>";
		} else {
			$text .= "You have placed <b>$initial</b> Credits on <b class=b1>$returned[login_name]'s</b> head. You were charged <b>$amount</b> Credit(s) for the transaction.<p>";
		}
		if($bount_mess) {
			if (!giveTurnsPlayer(-1)) {
				$text .= "You do not have enough turns to add the message, but rest assured, the bounty has been added non-the-less.";
			} else {
				msgSendSys($target, "<p>The bounty also came with a message: " .
				 "$bount_mess</p>");
				$text .= "You have been charged one turn for the additional message.";
			}
		}
	}
}


//allow user to pay off bounty.
if(isset($payoff) && $payoff < 0) {
	if($user['cash'] == 0) {
		print_page("Bounty","You have no money. how do you expect to pay off a bounty?");
	} else {

	db("select login_name,login_id,bounty from [game]_users where login_id = $user[login_id] OR (clan_id = $user[clan_id] AND clan_id IS NOT NULL) AND bounty > '0' order by login_name");
	$list_em = dbr();

	while($list_em) {
	$bount = round(($list_em[bounty] / 100) * $commission);
	$bount += $list_em[bounty];
		if($bount > 0) {
			$o_text_t .= "<option value=$list_em[login_id]>$list_em[login_name] - $bount</option>";
		}
		$list_em = dbr();
	}

	if(empty($o_text_t)){
		$text .= "There is no-one you may pay a bounty off for.<br />It is only possible to pay off a clan mates bounty.";
	} else {
		$text .= "<FORM action=bounty.php>";
		$text .= "Select Player whose bounty you wish to pay off.<br />You may only pay off a fellow clan mates bounty.<br />You will pay an extra <b>$commission</b>% on top of the original bounty if you pay it off.<p>";
		$text .= "You <b class=b1>must</b> pay off the whole bounty at once.<p>";
		$text .= "<select name=payoff>";
		$text .= $o_text_t;
		$text .= "</select>";
		$text .= "<p><INPUT type=submit value=Submit></form><p>";
	}
	print_page("Bounty Payoff",$text);
	}
} elseif(isset($payoff) && $payoff >0) {
	db("select bounty,login_name,clan_id from [game]_users where login_id = '$payoff'");
	$topay = dbr();
	$bount = round(($topay[bounty] / 100) * $commission);
	$bount += $topay[bounty];
	if($user[cash] < $bount) {
		print_page("Bounty","You do not have enough money. You require <b>$bount</b> to pay this bounty off.");
	}elseif($user[login_id] ==1) {
		print_page("Bounty","The admin may have nothing to do with bounties.");
	} elseif($sure != 'yes') {
	  get_var('Bounty Payoff','bounty.php',"Are you sure you want to spend <b>$bount</b> credits to get rid of the bounty on <b class=b1>$topay[login_name]</b>?",'sure','yes');
	}elseif($payoff == $user[login_id] || ($user[clan_id] == $topay[clan_id] && $user[clan_id] >= 1)) {
		giveMoneyPlayer(-$bount);
		dbn("update [game]_users set bounty = 0 where login_id = '$payoff'");
		$text .= "You have paid off the bounty on <b class=b1>$topay[login_name]</b>, at a cost of <b>$bount</b> Credits.<p>";
	} else {
		print_page("Bounty","You may not pay-off a bounty on anyone, other than yourself, or a clan-mate.");
	}

}

if(!isset($place)) {
	if(!isset($amount)) {
		$text .= <<<END
<ul>
	<li><a href="bounty.php?place=1">Place a bounty</a></li>
	<li><a href="bounty.php?payoff=-1">Payoff a bounty</a></li>
</ul>

END;
	}
} elseif ($user['turns_run'] < $turns_before_attack && !IS_ADMIN) {
	print_page("Bounty","You may not place a bounty during the first <b>$turns_before_attack</b> turns of your accounts' existence. This is because placing a bounty is a form of attack.");
} else {
	if ($user['clan_id'] !== NULL) {
		db("select login_name, login_id from [game]_users where login_id != $gameInfo[admin] AND login_id != $user[login_id] AND clan_id != $user[clan_id] AND joined_game = 1 order by login_name");
	} else {
		db("select login_name, login_id from [game]_users where login_id != $gameInfo[admin] AND login_id != $user[login_id] order by login_name");
	}

	$text .= <<<END
<h2>Place a bounty</h2>
<form action="bounty.php" method="post" name="bounty_form">
	<p><select name="target">

END;
	while ($list_em = dbr()) {
		$text .= "\t\t<option value=\"{$list_em['login_id']}\">{$list_em['login_name']}</option>\n";
	}

	$text .= <<<END
	</select> Who you want to <em>donate</em> to</p>
	<p><input type="text" name="amount" size="10" class="text" /> Amount of credits</p>
	<p><input type="submit" value="Submit" class="button" /></p>
	<h3>Anonymous message</h3>
	<p>If you like you can also attach a anonymous message with the bounty.
	This service costs just one turn.</p>
	<p><textarea name="bount_mess" cols="50" rows="20"></textarea></p>
</form>

END;

}

db('SELECT login_name, fighters_killed, ships_killed, bounty, ' .
 'login_id FROM ' . $db_name . '_users WHERE ship_id IS NOT NULL AND ' .
 'bounty > 0 ORDER BY bounty DESC, login_name ASC');

$player = dbr(1);
if($player) {
	$text .= make_table(array("Name", "Fighter kills", "Ship kills", "Bounty"));
	do {
	  $dis_name = print_name($player);
	  $player['login_name'] = $dis_name;
	  unset($player['login_id']);
	  $text .= make_row($player);
	} while ($player = dbr(1));
	$text .= "</table>";
}

print_page('Bounties',$text);

?>
