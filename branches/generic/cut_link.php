<?php

require_once('inc/user.inc.php');

if($user[login_id] != 1) {
	print_page("Admin","Admin access only.");
	exit();
}

$rs = "<p><a href=admin.php>Back to Admin Page</a>";

if(isset($fixcols)) {
	dbn("update ${db_name}_planets set colon = '100000' where colon = '2147483647'");
}

if(isset($cut)) {
	dbn("update ${db_name}_stars set link_1 = 0 where star_id = $s1 && link_1 = $s2");
	dbn("update ${db_name}_stars set link_2 = 0 where star_id = $s1 && link_2 = $s2");
	dbn("update ${db_name}_stars set link_3 = 0 where star_id = $s1 && link_3 = $s2");
	dbn("update ${db_name}_stars set link_4 = 0 where star_id = $s1 && link_4 = $s2");
	dbn("update ${db_name}_stars set link_5 = 0 where star_id = $s1 && link_5 = $s2");
	dbn("update ${db_name}_stars set link_6 = 0 where star_id = $s1 && link_6 = $s2");

	dbn("update ${db_name}_stars set link_1 = 0 where star_id = $s2 && link_1 = $s1");
	dbn("update ${db_name}_stars set link_2 = 0 where star_id = $s2 && link_2 = $s1");
	dbn("update ${db_name}_stars set link_3 = 0 where star_id = $s2 && link_3 = $s1");
	dbn("update ${db_name}_stars set link_4 = 0 where star_id = $s2 && link_4 = $s1");
	dbn("update ${db_name}_stars set link_5 = 0 where star_id = $s2 && link_5 = $s1");
	dbn("update ${db_name}_stars set link_6 = 0 where star_id = $s2 && link_6 = $s1");
}

if(isset($add)) {
	dbn("update ${db_name}_stars set link_1 = $s2 where star_id = $s1 && link_1 = 0");
	dbn("update ${db_name}_stars set link_2 = $s2 where star_id = $s1 && link_2 = 0 && link_1 != $s2");
	dbn("update ${db_name}_stars set link_3 = $s2 where star_id = $s1 && link_3 = 0 && link_1 != $s2 && link_2 != $s2");
	dbn("update ${db_name}_stars set link_4 = $s2 where star_id = $s1 && link_4 = 0 && link_1 != $s2 && link_2 != $s2 && link_3 != $s2");
	dbn("update ${db_name}_stars set link_5 = $s2 where star_id = $s1 && link_5 = 0 && link_1 != $s2 && link_2 != $s2 && link_3 != $s2 && link_4 != $s2");
	dbn("update ${db_name}_stars set link_6 = $s2 where star_id = $s1 && link_6 = 0 && link_1 != $s2 && link_2 != $s2 && link_3 != $s2 && link_4 != $s2 && link_5 != $s2");

	dbn("update ${db_name}_stars set link_1 = $s1 where star_id = $s2 && link_1 = 0");
	dbn("update ${db_name}_stars set link_2 = $s1 where star_id = $s2 && link_2 = 0 && link_1 != $s1");
	dbn("update ${db_name}_stars set link_3 = $s1 where star_id = $s2 && link_3 = 0 && link_1 != $s1 && link_2 != $s1");
	dbn("update ${db_name}_stars set link_4 = $s1 where star_id = $s2 && link_4 = 0 && link_1 != $s1 && link_2 != $s1 && link_3 != $s1");
	dbn("update ${db_name}_stars set link_5 = $s1 where star_id = $s2 && link_5 = 0 && link_1 != $s1 && link_2 != $s1 && link_3 != $s1 && link_4 != $s1");
	dbn("update ${db_name}_stars set link_6 = $s1 where star_id = $s2 && link_6 = 0 && link_1 != $s1 && link_2 != $s1 && link_3 != $s1 && link_4 != $s1 && link_5 != $s1");
}


$error_str .= make_table(array("",""));
$error_str .= "<form action=cut_link.php method=POST>";
$error_str .= "<input type='hidden' name='cut' value='1'>";
$error_str .= "<tr><td colspan=2>Cut Star Link</td></tr>";
$error_str .= quick_row("Star 1:","<input name=s1 value=$s1>");
$error_str .= quick_row("Star 2:","<input name=s2 value=$s2>");
$error_str .= "<tr><td colspan=2><input type=submit></form></table>";

$error_str .= "<br><br><br>";
$error_str .= make_table(array("",""));
$error_str .= "<form action=cut_link.php method=POST>";
$error_str .= "<input type='hidden' name='add' value='1'>";
$error_str .= "<tr><td colspan=2>Add Star Link</td></tr>";
$error_str .= quick_row("Star 1:","<input name=s1 value=$s1>");
$error_str .= quick_row("Star 2:","<input name=s2 value=$s2>");
$error_str .= "<tr><td colspan=2><input type=submit></form></table>";

print_page("Cut-link",$error_str);
echo $rs;
?>