<?php

require_once('inc/admin.inc.php');

$savedVars = array();

if(isset($save_vars)) {
	foreach ($_REQUEST as $var => $value) {
		$update = $db->query('UPDATE [game]_db_vars SET value = %d WHERE ' .
		 'name = \'%s\' AND %d <= max AND %d >= min', array($value,
		 $db->escape($var), $value, $value));
		if ($db->affectedRows($update) > 0) {
			$savedVars[] = $var;
		}
	}

	if (!empty($savedVars)) {
		insert_history($user['login_id'], "Updated Game Vars: " .
		 implode(', ', $savedVars));
	}
}

$out = <<<END
<h1>Edit game variables</h1>
<form action="admin_edit_vars.php" method="post">
	<p><input type="hidden" name="save_vars" value="1" />
	<input type="hidden" name="game_vars" value="1" />
	<input type="submit" value="Submit changes" class="button" /></p>
	<p>Only variables that are within range will be saved.</p>
	<table class="simple">
		<tr>
			<th>Variable</th>
			<th>Description</th>
			<th>Min</th>
			<th>Max</th>
			<th>Value</th>
		</tr>

END;

$vars = $db->query('SELECT name, min, max, value, descript ' .
 'FROM [game]_db_vars ORDER BY name');
while ($adminVar = $db->fetchRow($vars)) {
	$out .= "\t\t<tr>\n\t\t\t<td><label for=\"" . $adminVar['name'] . "\">" .
	 $adminVar['name'] . "</label>" . (in_array($adminVar['name'], $savedVars) ?
	 " <strong>Updated</strong>" : '') . "</td>\n\t\t\t<td>" .
	 $adminVar['descript'] . "</td>\n\t\t\t<td>" . $adminVar['min'] .
	 "</td>\n\t\t\t<td>" . $adminVar['max'] .
	 "</td>\n\t\t\t<td><input type=\"text\" name=\"" .
	 $adminVar['name'] . "\" id=\"" . $adminVar['name'] . "\" value=\"" .
	 $adminVar['value'] . "\" size=\"8\" class=\"text\" /></td>\n\t\t</tr>\n";
}

$out .= <<<END
	</table>
	<p><input type="submit" value="Submit changes" class="button" /></p>
</form>

END;

print_page("Edit game variables", $out);

?>
