<?php

require_once('inc/user.inc.php');

$text = "<h1>Player Diary</h1>";

if (IS_ADMIN || IS_OWNER) {
	$max = 200;
} else {
	$max = 50;
}

$dCount = $db->query('SELECT COUNT(*) FROM [game]_diary WHERE login_id = %u',
 $user['login_id']);
$entryAmount = (int)current($db->fetchRow($dCount));

// Adds
if (isset($add)) {
	if($entryAmount > $max) {
		print_page("Error","<p>Your diary is full");
	}

	$text .= "<form method=POST action=diary.php>";
	$text .= "<h2>Add entry</h2>";
	$text .= "<p><textarea name=add_ent value=\"\" cols=50 rows=20 wrap=soft></textarea></p>";
	$text .= "<p><INPUT type=submit value=Submit class=\"button\" /></p></form>";

	print_page("Add Diary Entry",$text);
}

//adding an entry into the DB.
if (isset($add_ent) || isset($log_ent)){
	if($entryAmount > $max) {
		print_page("Error","<p>Your diary is full");
	}

	if(isset($log_ent)){ // entry coming in from a log.
		db("select text,sender_name from [game]_messages where message_id = '$log_ent' AND (login_id = '$user[login_id]' OR login_id < 0)");
		$message_text = dbr(1);
		$add_ent = "$message_text[sender_name]\n$message_text[text]";
	}

	$newId = newId('[game]_diary', 'entry_id');
	$db->query('INSERT INTO [game]_diary (entry_id, timestamp, login_id, ' . 
	 'entry) VALUES (%u, %u, %u, \'%s\')', array($newId, time(), 
	 $user['login_id'], $db->escape($add_ent)));
	$text .= "<p>Message successfully copied to diary.</p>\n";
}


//Deletes
if(isset($delete)) { //delete single
	dbn("delete from [game]_diary where entry_id='$delete' AND login_id = '$user[login_id]'");
} elseif(isset($delete_all)){ //delete all
	if(!isset($sure)) {
		get_var('Delete all','diary.php',"Are you sure you want to delete all entries in your diary?",'sure','yes');
		$rs = "<a href=$self>Back to Diary</a>";
	} else{
		dbn("delete from [game]_diary where login_id = '$user[login_id]'");
		$text .= "Diary Successfully Emptied.<p>";
		$rs = "<a href=$self>Back to Diary</a>";
	}
}elseif(isset($del_select)){ //delete selected
	if(empty($del_ent)){
		$text .= "No entries selected for deletion.<p>";
	} else {
		$opt = array();
		foreach($del_ent as $value){
			$opt[] = "entry_id = '$value'";
		}

		$del = $db->query("DELETE FROM [game]_diary WHERE login_id = '$user[login_id]' AND (" . implode(' OR ', $opt) . ')');
		$text .= "<p><strong>" . $db->affectedRows($del) . "</strong> Entries(s) Deleted.</p>";
	}
}


//Edits
if(isset($edit)){//edit screen
	db("select * from [game]_diary where entry_id = '$edit' AND login_id = '$user[login_id]'");
	$entry = dbr(1);
	$entry_txt = $entry['entry'];
	$text .= "<form method=post action=diary.php>";
	$text .= "<input type=hidden name=edit2 value='$entry[entry_id]'>";
	$text .= "<p>Change Text here:<br />";
	$text .= "<textarea name=edit_ent cols=50 rows=20 wrap=soft>$entry_txt</textarea>";
	$text .= "<p><INPUT type=submit value=Submit class=\"button\" /></form><p>";
	print_page("Add Diary Entry",$text);

} elseif(isset($edit2)){//saving edited entry
	$edit_ent = $db->escape($edit_ent);
	dbn("update [game]_diary set entry = '$edit_ent' where entry_id='$edit2' AND login_id = '$user[login_id]'");
	$text .= "Entry Successfully Changed.<p>";
}



//Lists

$dCount = $db->query('SELECT COUNT(*) FROM [game]_diary WHERE login_id = %u',
 $user['login_id']);
$entryAmount = (int)current($db->fetchRow($dCount));

$text .= "<p>You may store up to <b>$max</b> entries in this Diary.</p>\n";

if(!$entryAmount){//no entries in diary
	if($entryAmount < $max) {
		$text .= "<p><a href=\"$self?add=1\">Add entry</a></p>\n";
	} else {
		$text .= "<p>Your diary is full.</p>\n";
	}
	$text .= "<p>There are no entries in your diary.</p>\n";
} else {
	$text .= "<h2>Entries</h2>\n<p>There are <em>$entryAmount entries</em> " .
	 "in your diary.</p>\n";
	$text .= $entryAmount < $max ? 
	 "<p><a href=$self?add=1>Add entry</a></p>\n" :
	 "<p>Your diary is full</p>\n";

	$text .= make_table(array('Date entered', 'Entry', 'Options'));
	if($entryAmount > 1){
		$text .= "<form method=post action=diary.php id=quick_del><input type=hidden name=del_select value=1>";
	}

	db2("select * from [game]_diary where login_id = '$user[login_id]' order by timestamp desc");

	while($entry = dbr2(1)) {//list entries
		$entry['entry'] = msgToHTML($entry['entry']);
		$text .= make_row(array(date("M d - H:i", $entry['timestamp']), 
		 $entry['entry'], "<a href=\"$self?edit=$entry[entry_id]\">Edit</a> " .
		 " - <a href=\"$self?delete=$entry[entry_id]\">Delete</a>"));
	}
	$text .= "</table><br />";
}

if ($entryAmount > 1){//show the big delete options
	$text .= "<br /><input type=submit value=\"Delete selected\" class=\"button\">  - <a href=\"#\" onclick=\"tickInvert(&quot;quick_del&quot;)\">Invert entry selection</a></form><br />";
	$text .= "<br /><a href=$self?delete_all=1>Delete all</a> entries in diary.";
}
$rs = "<p><a href=\"system.php\">Back to Star System</a>";
// print page
print_page("Diary of the Fleet",$text);

?>
