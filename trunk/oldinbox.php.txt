<?php

require_once('inc/user.inc.php');

#Message stuff
$out = "<h1>Message inbox for " . print_name($user) . "</h1>";

// Delete all messages
if (isset($removeAll)) {
	$del = $db->query('DELETE FROM [game]_messages WHERE login_id = %u', 
	 array($user['login_id']));
	$out .= "<p>" . $db->affectedRows($del) . 
	 " message(s) have been deleted!</p>\n";
}

// Delete n messages
if (isset($remove) && is_array($remove)) {
	$args = $remove;
	$args[] = $user['login_id'];

	$del = $db->query('DELETE FROM [game]_messages WHERE (message_id = %u' . 
	 str_repeat(' OR message_id = %u', count($remove) - 1) . ') AND ' .
	 'login_id = %u', $args);

	$out .= "<p>" . $db->affectedRows($del) . " message(s) deleted.</p>";
}

$cMessages = $db->query('SELECT COUNT(*) FROM [game]_messages ' .
 'WHERE login_id = %u', array($user['login_id']));

$counted = (int)current($db->fetchRow($cMessages));

if ($counted == 0) {
  	$out .= "<p>You have no messages.</p>";
} else {
	$out .= print_messages($user['login_id'], true);
}

print_page("Messages", $out);

?>
