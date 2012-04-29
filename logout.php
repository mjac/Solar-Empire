<?php

require_once('inc/common.inc.php');

//Connect to the database
db_connect();

check_auth();

//admin logout
if ($login_id == ADMIN_ID) {
	db("update se_games set session_id = 0, session_exp=0 where db_name = '$db_name'");
	insert_history(1, "Logged Out");

//logout FROM GAME. to either gamelisting or index
} elseif(isset($logout_single_game) || isset($comp_logout)){

	dbn("update ${db_name}_users set on_planet = 0 where login_id = '$login_id'");
	dbn("update user_accounts set in_game = '' where login_id = '$login_id'");
	SetCookie("p_pass","",0);

	//Update score, and last_request
	score_func($login_id,0);
	$time_to_set = time() - 1800; //30 mins ago
	dbn("update ${db_name}_users set last_request = '$time_to_set' where login_id = '$login_id'");


	//only logging out to gamelisting
	if(isset($logout_single_game)){
		insert_history($login_id,"Logged Out of $db_name");
		header('Location: game_listing.php');
		exit;
	}
}

//totally leaving the game
if(!empty($db_name) && $login_id != 1){
	if(isset($comp_logout)) {//logging out directly from game to index
		score_func($login_id, 0);
	}
	insert_history($login_id,"Logged Out Completely");

	SetCookie("p_pass","",0);
}

//unset session details.
dbn("update user_accounts set session_id = '', session_exp = 0 where login_id = '$login_id'");

SetCookie("session_id",0,0);
SetCookie("login_id",0,0);

header('Location: login_form.php');

?>
