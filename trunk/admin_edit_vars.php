<?php

require('inc/admin.inc.php');
require('inc/template.inc.php');

$vars = array();

$currentVars = $db->query('SELECT name, min, max, value, descript FROM [game]_db_vars ORDER BY name');
while ($var = $db->fetchRow($currentVars, ROW_NUMERIC)) {
	$vars[$var[0]] = array(
		'min' => (int)$var[1],
		'max' => (int)$var[2],
		'value' => (int)$var[3],
		'description' => $var[4],
		'newValue' => false
	);
}

if (isset($_REQUEST['change'])) {
	$savedVars = array();
	foreach ($_REQUEST['change'] as $name => $value) {
		$value = (int)$value;

		if (!isset($vars[$name]) || $vars[$name]['value'] === $value) {
			continue;
		}

		$update = $db->query('UPDATE [game]_db_vars SET value = %[1] WHERE name = \'%[2]\' AND max >= %[1] AND min <= %[1]', $value, $name);

		if ($db->affectedRows($update) > 0) {
			$savedVars[] = $name;
			$vars[$name]['newValue'] = $value;
		}
	}

	if (!empty($savedVars)) {
		insert_history($user['login_id'], 'Updated game variables: ' .
		 implode(', ', $savedVars));
	}
}

$tpl->assign('gameVars', $vars);

assignCommon($tpl);
$tpl->display('game/admin/variables.tpl.php');

?>
