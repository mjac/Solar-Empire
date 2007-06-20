<?php

require_once('inc/user.inc.php');
$filename = "politics.php";

/*
Military Contract Senator Support
Requirements: Must have most Warships.

Trader Senator Support
Requirements: Must have most Freighters

Secret Service Senator Support
Requirements: Must have the most Kestrals.
*/


#Main Senator page.
$text .= "Listed below are the Senators. These are players who are the most powerful in a certain field.<p>";
$text .= "<b><font color=lime>The politics area of the game is <b>FAR</b> from finished</font></b><br>At present Senators don't do anything, other than have a nice title.<p>";
db("select timestamp,position_id,login_name,login_id from ${db_name}_politics where login_id > '0' order by login_id");
$polit = dbr(1);
if ($polit) {
  $text .= make_table(array("Since","Position","Holder"));
	while($polit) {
	  if($polit[position_id] != 1){
		$polit[position_id] = "Senator";
	  } else {
		$polit[position_id] = "Monarch";
	  }
	  $polit[login_name] = print_name($polit);
	  $polit[login_id] = '';
	  $polit[timestamp] = "<b>".date("M d - H:i",$polit[timestamp]);
	  $text .= make_row($polit);
	  $polit = dbr(1);
	}
	$text .= "</table><br>";
} else {
	$text .= "No players have got a political position at present.";
}
$text .= "<p><a href=help.php?senators=1>Information about Senators</a>";
print_page("Politics","$text");

?>