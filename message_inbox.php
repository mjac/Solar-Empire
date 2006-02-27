<?php

require_once('inc/user.inc.php');

#Message stuff
$out = "<h1>Message inbox for " . print_name($user) . "</h1>";

#Deleted single message
if(isset($killmsg)) {
    $gone = $db->query('DELETE FROM [game]_messages WHERE message_id = ' .
	 '%u AND login_id = %u', array($killmsg, $user['login_id']));
	if ($db->affectedRows($gone) > 0) {
		$out .= "<p>Message Deleted.</p>\n";
	}
}

#Delete all messages
if(isset($killallmsg)) {
  if(!isset($sure)) {
	db("select count(message_id) from [game]_messages where login_id = '$user[login_id]'");
	$count_mess = dbr();
    get_var('Delete Messages','message_inbox.php','Are you sure you want delete all <b>'.$count_mess[0].'</b> messages?','sure','yes');
  } else {
	dbn("delete from [game]_messages where login_id = $user[login_id]");
	$out .= "<p>All Messages Deleted!</p>";
  }
}

//Delete selected messages
if(isset($clear_messages)) {
	if(!$del_mess){
	    $out .= "No messages selected to be deleted.<p>";
	} else {
		$q_m = 0;
		$temp656 = $del_mess;
		while ($var = each($temp656)) {
		  dbn("delete from [game]_messages where message_id = '$var[value]' AND login_id = '$user[login_id]'");
		  $q_m++;
		}
		$out .= "<b>$q_m</b> Message(s) Deleted.<p>";
	}
}

$cMessages = $db->query('SELECT COUNT(*) FROM [game]_messages AS m, ' .
 '[game]_users AS u where m.login_id = %u AND ' .
 'm.sender_id = u.login_id', array($user['login_id']));
$counted = (int)current($db->fetchRow($cMessages));

if($counted == 0){
  	$out .= "<p>You have no messages.</p>";
} else {
	$out .= print_messages($user['login_id'], true);
}

print_page("Messages", $out);

?>
