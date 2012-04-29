<?php

require_once('inc/common.inc.php');

$out_text = "";

print_header("Game Variables");
$rs = "<p><a href=javascript:history.back()>Back</a></b></blockquote>";
echo $rs."<BR><BR>";

//Connect to the database
db_connect();

$db_name = $_GET['db_name'];

db("select name, value, descript from ${db_name}_db_vars order by name");

echo "<table border=0 cellspacing=1 width=350>";
$delta = 0;

while ($var = dbr()) {
	if($var['name'] == "admin_var_show" && $var['value']==0){
		echo "Admin has disabled public viewing of game vars";
		$delta=1;
		break;
	}
	$out_text .= "<tr bgcolor=#333333><td width=220>$var[name] = ${var['value']}</td>";
	$out_text .="<tr bgcolor=#555555><td><blockquote>${var['descript']}<br></td>";
	$out_text .= "<tr bgcolor=#000000><td colspan=2>&nbsp;</td></tr>";
}

if($delta==0){
	echo $out_text;
}

echo "</table>";
print_footer();

?>
