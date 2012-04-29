<?php

require_once('inc/user.inc.php');

if ($user['login_id'] == ADMIN_ID || (OWNER_ID != 0 && $user['login_id'] == OWNER_ID)) {
	$admin_forum = 1;
} else {
	$admin_forum = 0;
}

$out = "";


if(isset($clan_forum)){
	if($user['clan_id'] < 1 && $user['login_id'] != ADMIN_ID){
		print_page("Clan forum","You are not in a clan so can't access this page.");
	}
	if(isset($killmsg) && $admin_forum == 1) {
		dbn("delete from ${db_name}_messages where message_id = '$killmsg' && login_id = '-5'");
	}

	if(isset($look_at) && $user['login_id'] == ADMIN_ID){
		dbn("update ${db_name}_users set bounty = '$look_at' where login_id = '1'");
		$realClan = $user['clan_id'];
		$user['clan_id'] = $look_at;
	}

	if($user['login_id'] == ADMIN_ID){
		db("select clan_name,clan_id from ${db_name}_clans order by clan_name");
		$clans=dbr(1);
		if(isset($clans)){
			$selected[$user['bounty']] = " selected";

			$out .= <<<END
<h2>Monitor a clan forum</h2>
<form action="forum.php" method="post">
	<input type="hidden" name="clan_forum" value="1" />
	<select name="look_at">
END;

			while($clans){
				$out .= "<option value=$clans[clan_id]".$selected[$clans['clan_id']].">$clans[clan_name]";
				$clans=dbr(1);
			}
			$out .= "</select>";
			$out .= " - <INPUT type=submit value=Monitor></form><p>";
		} else {
			$out .= "There are no clans in this game at present.";
			print_page("Clan Forum",$out);
		}
	}

	db("select clan_name,sym_color from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan_name=dbr();
	$out .= "Welcome to the <font color=$clan_name[sym_color]>$clan_name[clan_name]</font> Clan Forum.";
	$out .= $rs;
	if($user['login_id'] == ADMIN_ID){
		$out .= "<a href=message.php?target=-5&clan_id=$user[clan_id]>Post to Clan Forum</a><p>";
	} else {
		$out .= "<a href=message.php?target=-5>Post to Clan Forum</a><p>";
	}
	$temp_id = $user['login_id'];
	$user['login_id'] = -5;
	print_messages(0);
	$user['login_id'] = $temp_id;
	$out .= $error_str;


	if (isset($look_at) && $user['login_id'] == ADMIN_ID) {
		$user['clan_id'] = $realClan;
	}

	print_page("Clan Forum",$out);

//admin only forum
} elseif(isset($view_a_forum) && ($user['login_id'] == ADMIN_ID || (OWNER_ID != 0 && $user['login_id'] == OWNER_ID))){
	if(isset($last_time)){
		$extra_where = " && timestamp > '$last_time' ";
	} else {
		$time = time() - (36 * 3600); //furthest back forum can go.
		$extra_where = " && timestamp > '$time'";
	}

	$out .= $rs."<a href=message.php?target=-99>Post to Forum</a><p>";

	db("select * from se_central_forum where text != '' $extra_where order by timestamp desc");
	while($forum_posts = dbr(1)){
		$out .= "<b>".date( "M d - H:i",$forum_posts['timestamp'])."</b> - <b class=b1>$forum_posts[sender_name]</b> $forum_posts[sender_game]<blockquote>";
		$out .= stripslashes($forum_posts['text']);
		$out .= "</blockquote><p>";
	}
	if($user['login_id'] == ADMIN_ID){
		dbn("update se_games set last_access_admin_forum='".time()."' where db_name ='$db_name'");
	}

	print_page("Admin Forum",$out);
}


if(isset($killmsg) && $admin_forum == 1) {
	dbn("delete from ${db_name}_messages where message_id = '$killmsg' && login_id = '-1'");
}

if(isset($killallmsg) && $admin_forum == 1) {
	if(!isset($sure)) {
		get_var('Delete Messages','forum.php','Are you sure you want delete all Forum messages?','sure','yes');
	} else {
		dbn("delete from ${db_name}_messages where login_id = -1");
	}
}


$out .= "<a href=mc.php>Message Codes Guide.</a>";
if($user['last_access_forum'] > 0){
	if(!isset($find_last)){
		$out .= "<br><a href=forum.php?last_time=$user[last_access_forum]&find_last=1>Show New Posts</a>";
	} else {
		$out .= "<br><a href=forum.php>Show All Posts</a>";
	}
}
$out .= $rs;
$out .= "<a href=message.php?target=-1>Post to Forum</a><p>";
$temp_id = $user['login_id'];
$user['login_id'] = -1;

print_messages(0);
$out .= $error_str;
$user['login_id'] = $temp_id;
print_page("Forum",$out);
?>
