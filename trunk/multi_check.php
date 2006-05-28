<?php

require_once('inc/user.inc.php');

if (!(IS_ADMIN || IS_OWNER)) {
	print_page('Access Denied','Admin Only!!!!');
}

$text = <<<END
<h1>Multi-Scanner</h1>
<p>This Multi Scanner does not tell you whether or not these players are guilty.
The purpose of this scanner is simply to present the facts.  The scanner will
find players with the same IP address.</p>
<h2>IP address</h2>

END;

$ipList = $db->query("SELECT DISTINCT last_ip FROM [game]_users ORDER BY last_ip");
while (list($ip) = $db->fetchRow($ipList, ROW_NUMERIC)) {
	$associated = $db->query("SELECT login_id FROM [game]_users WHERE last_ip='$ip' ORDER BY login_name");
	if ($db->numRows($associated) <= 1) {
		continue;
	}

	$badGuy = array();
	while (list($id) = $db->fetchRow($associated, ROW_NUMERIC)) {
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
