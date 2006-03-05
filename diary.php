<?php

require_once('inc/user.inc.php');
$filename = 'diary.php';

$text = "<h1>Player Diary</h1>";

if (IS_ADMIN || IS_OWNER) {
	$max = 200;
} else {
	$max = 50;
}

$dCount = $db->query('SELECT COUNT(*) FROM [game]_diary WHERE login_id = %u',
 $user['login_id']);
$entryAmount = (int)current($db->fetchRow($dCount));

//diary search
if (isset($term)) {
	$text .= search_the_db($term, $can_do_stop, "diary", $self, "entry");
	print_page("Diary Search",$text);
	if(empty($ret_text)){ //no results found
		$text .= "<br />There are no entries of <b class=b1>$term</b> in your diary. Please broaden your search.<br /><br />";
	} else {
		print_page("Diary Search", $ret_text);
	}
}

// Adds
if (isset($add)) {
	if($entryAmount > $max) {
		print_page("Error","<p>Your diary is full");
	}
	$text .= "<FORM method=POST action=diary.php>";
	$text .= "<p>Diary Entry:<br />";
	$text .= "<textarea name=add_ent value='' cols=50 rows=20 wrap=soft></textarea></p>";
	$text .= "<p><INPUT type=submit value=Submit></form><p>";
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
		$rs = "<a href=$PHP_SELF>Back to Diary</a>";
	} else{
		dbn("delete from [game]_diary where login_id = '$user[login_id]'");
		$text .= "Diary Successfully Emptied.<p>";
		$rs = "<a href=$PHP_SELF>Back to Diary</a>";
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
	$text .= "<FORM method=POST action=diary.php>";
	$text .= "<input type=hidden name=edit2 value='$entry[entry_id]'>";
	$text .= "<p>Change Text here:<br />";
	$text .= "<textarea name=edit_ent cols=50 rows=20 wrap=soft>$entry_txt</textarea>";
	$text .= "<p><INPUT type=submit value=Submit></form><p>";
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

$text .= "<p>You may store up to <b>$max</b> entries in this Diary.</p><p>There are presently <b>$entryAmount</b> entries in your diary.</p>";
if (IS_ADMIN || IS_OWNER) {
	$text .= "<p>Admin and Owner's diaries are not wiped when the game resets.</p>";
}

if(!$entryAmount){//no entries in diary
	if($entryAmount < $max) {
		$text .= "<p><a href=\"{$_SERVER['SCRIPT_NAME']}?add=1\">Add entry</a></p>\n";
	} else {
		$text .= "<br />Your diary is full";
	}
	$text .= "<p>There are no entries in your diary.";
} else {
	if($entryAmount < $max) {
		$text .= "<p><a href=$filename?add=1>Add entry</a></p>";
	} else {
		$text .= "<p>Your diary is full</p>";
	}
	$text .= "<FORM method=POST action=$filename>";
	$text .= "Search for a term in the Diary:<br />";
	$text .= "<input type=text name=term size=10>";
	$text .= " - <INPUT type=submit value=Search></form><p>";
	$text .= "<p>Contents of the diary:";
	$text .= make_table(array("Date Entered","Entry"));
	if($entryAmount > 1){
		$text .= "<FORM method=POST action=diary.php id=quick_del><input type=hidden name=del_select value=1>";
	}

	db2("select * from [game]_diary where login_id = '$user[login_id]' order by timestamp desc");

	while($entry = dbr2(1)) {//list entries
		$entry['entry'] = msgToHTML($entry['entry']);
		$e_num = $entry['entry_id'];
		$entry['entry_id'] = "- <a href=$filename?edit=$e_num>Edit</a> - <a href=$filename?delete=$e_num>Delete</a>";
		$text .= make_row(array("<b>".date("M d - H:i",$entry['timestamp'])."</b>",$entry['entry'],$entry['entry_id']));
	}
	$text .= "</table><br />";
}

if ($entryAmount > 1){//show the big delete options
	$text .= "<br /><INPUT type=submit value=\"Delete Selected Entries\">  - <a href=javascript:tickInvert(\"quick_del\")>Invert Entry Selection</a></form><br />";
	$text .= "<br /><a href=$filename?delete_all=1>Delete All</a> entries in diary.";
}
$rs = "<p><a href=\"system.php\">Back to Star System</a>";
// print page
print_page("Diary of the Fleet",$text);

?>
