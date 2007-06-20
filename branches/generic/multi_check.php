<?php

require_once('inc/user.inc.php');

/*if($user['login_id'] != ADMIN_ID) {
	print_page('Access Denied','Admin Only!!!!');
}*/

$text = <<<END
<h1>Multi-Scanner</h1>
<p>This Multi Scanner does not tell you whether or not these players are guilty.
The purpose of this scanner is simply to present the facts.  The scanner will
find players with the same IP address.</p>
<h2>IP address</h2>

END;

$ipList = mysql_query("SELECT DISTINCT `last_ip` FROM `{$db_name}_users` ORDER BY `last_ip`");
while(list($ip) = mysql_fetch_row($ipList)) {
	$associated = mysql_query("SELECT `login_id` FROM `{$db_name}_users` WHERE `last_ip`='$ip' ORDER BY `login_name`");
	if (mysql_num_rows($associated) <= 1) {
		continue;
	}

	$badGuy = array();
	while (list($id) = mysql_fetch_row($associated)) {
		$badGuy[] = array('login_id' => $id);
	}

	$host = gethostbyaddr($ip);

	$text .= "\n<h3><a href=\"ip_search.php?ip=$ip\" title=\"$ip\">" .
	 ($host === $ip ? $ip : $host) . "</a> (" . count($badGuy) .
	 " players)</h3>\n<ul>";

	foreach ($badGuy as $info) {
		$text .= "\n\t<li><a href=\"player_info.php?target={$info['login_id']}\">" .
		 print_name($info) . "</a></li>";
	}
	$text .= "\n</ul>";
}

print_page('Multi Scanner',$text);

?>
