<?php

require_once('inc/user.inc.php');
$filename = 'diary.php';

$text = "";

//max number of entries in diary.
if($user['login_id'] != ADMIN_ID && ($user['login_id'] != OWNER_ID && OWNER_ID != 1)){
	$max = 50;
} elseif($user['login_id'] == ADMIN_ID) {
	$max = 200;
} else {
	$max = 5000;
}

db("select count(entry_id) from ${db_name}_diary where login_id = $user[login_id]");
$num_ent = dbr();

$rs = "<p><a href=$PHP_SELF>Back to Diary</a><br>";

//diary search
if(isset($term)) {
	$text .= search_the_db($term,$can_do_stop,"diary",$PHP_SELF,"entry");
	print_page("Diary Search",$text);
	if(empty($ret_text)){ //no results found
		$text .= "<br>There are no entries of <b class=b1>$term</b> in your diary. Please broaden your search.<br><br>";
	} else {
		print_page("Diary Search",$ret_text);
	}
}

// Adds
if(isset($add)) {
	if($num_ent[0] > $max) {
		print_page("Error","<p>Your diary is full");
	}
	$text .= "<FORM method=POST action=diary.php>";
	$text .= "<p><br><br>Diary Entry:<br>";
	$text .= "<textarea name=add_ent value='' cols=50 rows=20 wrap=soft></textarea>";
	$text .= "<p><INPUT type=submit value=Submit></form><p>";
	print_page("Add Diary Entry",$text);
}

//adding an entry into the DB.
if(isset($add_ent) || isset($log_ent)){
	if($num_ent[0] > $max) {
		print_page("Error","<p>Your diary is full");
	}


	if(isset($log_ent)){//entry coming in from a log.
		db("select text,sender_name from ${db_name}_messages where message_id = '$log_ent' && (login_id = '$user[login_id]' || login_id < 0)");
		$message_text = dbr(1);
		$add_ent = "$message_text[sender_name]\n\n\n$message_text[text]";
	}
	$add_ent = addslashes($add_ent);
	dbn("insert into ${db_name}_diary (timestamp,login_id, entry) values(".time().",'$user[login_id]','$add_ent')");
	$text .= "Message Successfully Copied to Diary.<p>";
}


//Deletes
if(isset($delete)) { //delete single
	dbn("delete from ${db_name}_diary where entry_id='$delete' && login_id = '$user[login_id]'");
} elseif(isset($delete_all)){ //delete all
	if(!isset($sure)) {
		get_var('Delete all','diary.php',"Are you sure you want to delete all entries in your diary?",'sure','yes');
		$rs = "<a href=$PHP_SELF>Back to Diary</a>";
	} else{
		dbn("delete from ${db_name}_diary where login_id = '$user[login_id]'");
		$text .= "Diary Successfully Emptied.<p>";
		$rs = "<a href=$PHP_SELF>Back to Diary</a>";
	}
}elseif(isset($del_select)){ //delete selected
	if(empty($del_ent)){
		$text .= "No entries selected for deletion.<p>";
	} else {
		$del_str = "";
		foreach($del_ent as $value){
			$del_str .= "entry_id = '$value' || ";
		}
		$del_str = preg_replace("/\|\| $/", "", $del_str);
		dbn("delete from ${db_name}_diary where login_id = '$user[login_id]' && (".$del_str.")");
		$num_del = mysql_affected_rows();
		$text .= "<b>$num_del</b> Entries(s) Deleted.<p>";
	}
}


//Edits
if(isset($edit)){//edit screen
	db("select * from ${db_name}_diary where entry_id = '$edit' && login_id = '$user[login_id]'");
	$entry = dbr(1);
	$entry_txt = stripslashes($entry['entry']);
	$text .= "<FORM method=POST action=diary.php>";
	$text .= "<input type=hidden name=edit2 value='$entry[entry_id]'>";
	$text .= "<p>Change Text here:<br>";
	$text .= "<textarea name=edit_ent cols=50 rows=20 wrap=soft>$entry_txt</textarea>";
	$text .= "<p><INPUT type=submit value=Submit></form><p>";
	print_page("Add Diary Entry",$text);

} elseif(isset($edit2)){//saving edited entry
	$edit_ent = addslashes($edit_ent);
	dbn("update ${db_name}_diary set entry = '$edit_ent' where entry_id='$edit2' && login_id = '$user[login_id]'");
	$text .= "Entry Successfully Changed.<p>";
}



//Lists

//Top of front diary page
db("select count(entry_id) from ${db_name}_diary where login_id = '$user[login_id]'");
$num_ent = dbr();
$text .= "You may store up to <b>$max</b> entries in this Diary. <br>There are presently <b>$num_ent[0]</b> entries in your diary.";
if($user['login_id'] == ADMIN_ID){
	$text .= "<p>The (Server) Admin Diary's do <b class=b1><b>NOT</b> get wiped</b> when the game resets.";
}

if(!$num_ent[0]){//no entries in diary
	if($num_ent[0] < $max) {
		$text .= "<p><br><a href=$PHP_SELF?add=1>Add entry</a>";
	} else {
		$text .= "<br>Your diary is full";
	}
	$text .= "<p>There are no entries in your diary.";
} else {

	if($num_ent[0] < $max) {
		$text .= "<p><br><a href=$filename?add=1>Add entry</a>";
	} else {
		$text .= "<br>Your diary is full";
	}
	$text .= "<FORM method=POST action=$filename>";
	$text .= "Search for a term in the Diary:<br>";
	$text .= "<input type=text name=term size=10>";
	$text .= " - <INPUT type=submit value=Search></form><p>";
	$text .= "<p>Contents of the diary:";
	$text .= make_table(array("Date Entered","Entry"));
	if($num_ent[0] > 1){
		$text .= "<FORM method=POST action=diary.php name=quick_del><input type=hidden name=del_select value=1>";
	}

	db2("select * from ${db_name}_diary where login_id = '$user[login_id]' order by timestamp desc");

	while($entry = dbr2(1)) {//list entries
		$entry['entry'] = mcit($entry['entry']);
		$entry['entry'] = stripslashes($entry['entry']);
		$e_num = $entry['entry_id'];
		$entry['entry_id'] = "- <a href=$filename?edit=$e_num>Edit</a> - <a href=$filename?delete=$e_num>Delete</a>";
		if($num_ent[0] > 1){
			 $entry['entry_id'].= "- <input type=checkbox name=del_ent[$e_num] value=$e_num>";
		 }
		$text .= make_row(array("<b>".date("M d - H:i",$entry['timestamp'])."</b>",$entry['entry'],$entry['entry_id']));
	}
	$text .= "</table><br>";
}

if ($num_ent[0] > 1){//show the big delete options
	$text .= "<br><INPUT type=submit value=\"Delete Selected Entries\">  - <a href=javascript:TickAll(\"quick_del\")>Invert Entry Selection</a></form><br>";
	$text .= "<br><a href=$filename?delete_all=1>Delete All</a> entries in diary.";
}
$rs = "<p><a href=location.php>Back to Star System</a>";
// print page
print_page("Diary of the Fleet",$text);
?>