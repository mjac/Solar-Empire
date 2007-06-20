<?

require_once('inc/user.inc.php');
$filename = 'retire.php';

if($user['login_id'] != ADMIN_ID) {
  print_page("Admin","Admin access only.");
} elseif ($target == ADMIN_ID) {
  print_page("Admin", "The admin cannot retire.");
}

#get users clan info and player info.
db("select login_id,clan_id,login_name from ${db_name}_users where login_id = '$target'");
$target_info = dbr();

if($sure != 'yes') {
  get_var('Retire',$filename,'Are you sure you want to retire this account?','sure','yes');
} elseif(!$give_reason) {
	$new_page .= "<form action=retire.php method=POST name=reason>";
	foreach ($_POST as $var => $value) {
		$new_page .= "<input type=hidden name=$var value='$value'>";
	}
	$new_page .= "<input type=hidden name=give_reason value=1>";
	$new_page .= "You are about to retire <b clan=b1>$target_info[login_name]</b> for:";
	$new_page .= "<input type=text name=reason value=><p><input type=submit value=Submit></form>";
	print_page("Give a Reason",$new_page);

#if user is a clan leader, give option to disband clan or assign new leader
} elseif($target_info['clan_id'] > 0) {
	db("select u.login_id,u.clan_id,u.login_name,c.leader_id,c.symbol,c.sym_color,c.clan_name from ${db_name}_clans c, ${db_name}_users u where u.login_id = $target && u.clan_id = c.clan_id");
	$clan = dbr(1);
	db("select count(distinct login_id) from ${db_name}_users where clan_id = '$clan[clan_id]' && login_id > 5");
	$temp_2 = dbr();
	$clan['members'] = $temp_2[0];
	if($clan['login_id'] == $clan['leader_id']){
		if($clan['members'] > 1 && !$what_to_do){
				$new_page = "Before you retire this person, you must first select whether you want their clan to be disbanded, or assign a new leader to it:";
				$new_page .= "<form action=retire.php method=POST name=retiring>";
				foreach ($_POST as $var => $value) {
					$new_page .= "<input type=hidden name=$var value='$value'>";
				}
				$new_page .= "<p>Disband Clan <INPUT type=radio name=what_to_do value=1 CHECKED> / Assign New Clan Leader<INPUT type=radio name=what_to_do value=2><p><INPUT type=submit value='Submit'></form>";
				print_page("Retiring",$new_page);

		#removing the clan
		} elseif($clan['members'] < 2 || $what_to_do == 1){
			dbn("update ${db_name}_users set clan_id = 0 where clan_id = $clan[clan_id]");
			dbn("update ${db_name}_planets set clan_id = -1 where clan_id = $clan[clan_id]");
			dbn("delete from ${db_name}_clans where clan_id = $clan[clan_id]");
			dbn("delete from ${db_name}_messages where clan_id = $clan[clan_id]");
			post_news("The $clan[clan_name] (<font color=$clan[sym_color]>$clan[symbol]</font>) Clan Disbanded.");
		} elseif($what_to_do == 2 && !$leader_id){
			$new_page = "Please select which of the below you would like to be the new clan leader:";
			$new_page .= "<form action=retire.php method=POST name=retiring2>";
			#$new_page .= "<input type=hidden name=what_to_do value='$what_to_do'>";
			db2("select login_id,login_name from ${db_name}_users where clan_id = '$clan[clan_id]' && login_id != '$clan[login_id]'");
			$new_page .= "<select name=leader_id>";
			while ($member_name = dbr2(1)) {
				$new_page .= "<option value=$member_name[login_id]>$member_name[login_name]</option>";
			}
			$new_page .= "</select>";
			foreach ($_POST as $var => $value) {
				$new_page .= "<input type=hidden name=$var value='$value'>";
			}
			$new_page .= "<p><INPUT type=submit value='Submit'></form>";
			print_page("Assign New Clan Leader",$new_page);
		} else {
			dbn("update ${db_name}_clans set leader_id = $leader_id where clan_id = $clan[clan_id]");
		}
	}
}

if(empty($reason)){
	$reason = "No Reason";
}

#  db("select login_name from ${db_name}_users where login_id = $target");
#  $target_user = dbr();
	retire_user($target);
	post_news("Admin Retired <b class=b1>$target_info[login_name]</b> for <b>$reason.</b>");
	insert_history($user['login_id'],"Retired $target_info[login_name] From the Game");
	insert_history($target_info['login_id'],"Was Retired By The Admin");
	print_page("Retired","<b class=b1>$target_info[login_name]</b> has been removed from the game.");
?>