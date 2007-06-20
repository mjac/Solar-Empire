<?php

require_once('inc/owner.inc.php');

#only the server admin may use this page!
if ($user['login_id'] != OWNER_ID) {
	print_page('Error', 'Error');
}


pageStart();

if ($sender != "" && $message != "") {
	if ($file_contents = file ("server_news.inc.htm", r)){
		$date = date("h:i - M d, Y",time());
		$output_stuff = "\n\n<P><TABLE BORDER='1' BORDERCOLOR='#000000' CELLSPACING='0' CELLPADDING='5'><TR><TD BGCOLOR='#333333'><B CLASS='b1'>$sender</B> - $date</TD></TR><TR><TD BGCOLOR='#333333'>".stripslashes($message)."</TD></TR></TABLE>\n";
		while ($line = each($file_contents)){
			$output_stuff .= $line[1];
		}

		$file_stream = fopen("server_news.inc.htm", "w+") or die("Unable to open file for writing");
		fwrite($file_stream, $output_stuff) or die("unable to write to file");

		header('Location: owner.php');
		exit();
	} else {
		echo "Unable to open File";
		exit;
	}
}

print <<<END
<form action="post_server_news.php" method="post">
	<input type="hidden" name="pass" value="$pass" />
	<input type="hidden" name="new_post" value="1" />
<TABLE BORDER='0' CELLSPACING='0' CELLPADDING='5'>
<TR><TD>Your Name:</TD><TD><INPUT TYPE='text' NAME='sender'></TD></TR>
<TR><TD>Message:</TD><TD><TEXTAREA NAME='message' COLS='60' ROWS='10'></TEXTAREA></TD></TR>
<TR><TD COLSPAN='2' ALIGN='center'><INPUT TYPE='submit' VALUE='Post'></TD></TR>
</table></FORM>
</body></html>
END;

pageStop('Post server news');

?>
