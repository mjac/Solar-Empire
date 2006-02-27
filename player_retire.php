<?php

require_once('inc/user.inc.php');

if (!IS_ADMIN) {
	print_page("Admin","Admin access only.");
} elseif ($target == $gameInfo['admin']) {
	print_page("Admin", "The admin cannot retire.");
}

#get users clan info and player info.
$db->query("select login_id,clan_id,login_name from [game]_users where login_id = '$target'");
$target_info = dbr();

if($sure != 'yes') {
	get_var('Retire',$filename,'Are you sure you want to retire this account?','sure','yes');
} elseif(!$give_reason) {
	$new_page .= "<form action=$self method=post name=reason>";
	foreach ($_POST as $var => $value) {
		$new_page .= "<input type=hidden name=$var value='$value'>";
	}
	$new_page .= "<input type=hidden name=give_reason value=1>";
	$new_page .= "You are about to retire <b clan=b1>$target_info[login_name]</b> for:";
	$new_page .= "<input type=text name=reason value=><p><input type=submit value=Submit></form>";
	print_page("Give a Reason",$new_page);

#if user is a clan leader, give option to disband clan or assign new leader
} elseif($target_info['clan_id'] > 0) {
	db("select u.login_id,u.clan_id,u.login_name,c.leader_id,c.symbol,c.sym_color,c.clan_name from [game]_clans c left join [game]_users AS u ON u.clan_id = c.clan_id WHERE u.login_id = $target");
	$clan = dbr(1);
	db("select count(distinct login_id) from [game]_users where clan_id = '$clan[clan_id]'");
	$temp_2 = dbr();
	$clan['members'] = $temp_2[0];
	if($clan['login_id'] == $clan['leader_id']){
		if($clan['members'] > 1 && !$what_to_do){
				$new_page = "Before you retire this person, you must first select whether you want their clan to be disbanded, or assign a new leader to it:";
				$new_page .= "<form action=\"$self\" method=\"post\" name=\"retiring\">\n";
				foreach ($_POST as $var => $value) {
					$new_page .= "<input type=hidden name=$var value='$value'>";
				}
				$new_page .= "<p>Disband Clan <INPUT type=radio name=what_to_do value=1 CHECKED> / Assign New Clan Leader<INPUT type=radio name=what_to_do value=2><p><INPUT type=submit value='Submit'></form>";
				print_page("Retiring",$new_page);

		#removing the clan
		} elseif ($clan['members'] < 2 || $what_to_do == 1) {
			dbn("update [game]_users set clan_id = NULL where clan_id = $clan[clan_id]");
			dbn("delete from [game]_clans where clan_id = $clan[clan_id]");
			dbn("delete from [game]_messages where clan_id = $clan[clan_id]");
			$db->query('DELETE FROM [game]_clan_invites WHERE clan_id = %u',
			 array($clan['clan_id']));
			post_news("Clan $clan[clan_name] ($clan[symbol]) disbanded.");
		} elseif($what_to_do == 2 && !$leader_id){
			$new_page = "Please select which of the below you would like to be the new clan leader:";
			$new_page .= "<form action=\"$self\" method=\"post\" name=\"retiring2\">\n";
			db2("select login_id,login_name from [game]_users where clan_id = '$clan[clan_id]' AND login_id != '$clan[login_id]'");
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
			dbn("update [game]_clans set leader_id = $leader_id where clan_id = $clan[clan_id]");
		}
	}
}

if(empty($reason)){
	$reason = "No Reason";
}

retire_user($target);
post_news("Admin retired $target_info[login_name]: $reason");
insert_history($user['login_id'],"Retired $target_info[login_name] From the Game");
insert_history($target_info['login_id'],"Was Retired By The Admin");
print_page("Retired","<b class=b1>$target_info[login_name]</b> has been removed from the game.");

?>
