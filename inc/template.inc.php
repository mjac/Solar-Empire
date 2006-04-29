<?php

defined('PATH_SAVANT') or exit('Create constant PATH_SAVANT!');

if (isset($user) && isset($user['style']) && 
     $user['style'] !== NULL && is_dir(PATH_STYLES . '/' . $user['style'])) {
	$style = $user['style'];
} elseif (isset($p_user) && isset($p_user['style']) && 
     $p_user['style'] !== NULL && 
     is_dir(PATH_STYLES . '/' . $p_user['style'])) {
	$style = $p_user['style'];
} elseif (is_dir(PATH_STYLES . '/' . DEFAULT_STYLE)) {
	$style = DEFAULT_STYLE;
} else {
	trigger_error('No available styles.', E_USER_ERROR);
	exit();
}

require_once(PATH_SAVANT);

$tpl =& new Savant2();
$tpl->addPath('template', PATH_STYLES . '/' . $style);

?>
