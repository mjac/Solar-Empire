<?php

require_once('inc/user.inc.php');

#Message stuff
$error_str = "";
#Deleted single message
if(isset($killmsg)) {
    dbn("delete from ${db_name}_messages where message_id = '$killmsg' && login_id = '$user[login_id]'");
	$error_str .= "Message Deleted.<p>";
}

#Delete all messages
if(isset($killallmsg)) {
  if(!isset($sure)) {
	db("select count(message_id) from ${db_name}_messages where login_id = '$user[login_id]'");
	$count_mess = dbr();
    get_var('Delete Messages','mpage.php','Are you sure you want delete all <b>'.$count_mess[0].'</b> messages?','sure','yes');
  } else {
	dbn("delete from ${db_name}_messages where login_id = $user[login_id]");
	$error_str .= "All Messages Deleted.<p>";
  }
}

//Delete selected messages
if(isset($clear_messages)) {
	if(!$del_mess){
	    $error_str .= "No messages selected to be deleted.<p>";
	} else {
		$q_m = 0;
		$temp656 = $del_mess;
		while ($var = each($temp656)) {
		  dbn("delete from ${db_name}_messages where message_id = '$var[value]' && login_id = '$user[login_id]'");
		  $q_m++;
		}
		$error_str .= "<b>$q_m</b> Message(s) Deleted.<p>";
	}
}

db("select count(m.message_id) from ${db_name}_messages m, ${db_name}_users u where m.login_id = '$user[login_id]' && m.sender_id = u.login_id");
$counted = dbr();

if($counted[0] > 5){
	$error_str .= $rs;
}

if($counted[0] == 0){
  	$error_str .= "<p>You have no messages.</p>";
  }else{
	print_messages(1);
}
print_page("Messages",$error_str);
?>
