<?php
defined('PATH_SAVANT') || exit;

if (!class_exists('Savant2')) {
	require(PATH_SAVANT);
}

$styleDir = '';

$pStyles = array();
if (isset($user) && isset($user['style']) && $user['style'] !== NULL) {
	$pStyles[] = $user['style'];
}
if (isset($account) && isset($account['style']) && 
     $account['style'] !== NULL) {
	$pStyles[] = $account['style'];
}
$pStyles[] = DEFAULT_STYLE;

foreach ($pStyles as $try) {
	$xml = URL_TPL . '/' . $try . '.xml';
	if (!(is_file($xml) && is_readable($xml))) {
		continue;
	}

	$data = file_get_contents($xml);

	$p = xml_parser_create();
	xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($p, $data, $vals, $index);
	xml_parser_free($p);

	foreach ($vals as $arr) {
		if ($arr['type'] === 'complete' && $arr['tag'] === 'directory') {
			$styleDir = $arr['value'];
			break;
		}
	}

	if (!empty($styleDir) && is_dir(URL_TPL . '/' . $styleDir)) {
		break;
	}
}

if (empty($styleDir)) {
	trigger_error('There are no styles available.', E_USER_ERROR);
	exit;
}

$tpl =& new Savant2();
$tpl->addPath('template', PATH_TPL . '/' . $styleDir);
$tpl->assign('url', array('full' => URL_FULL, 'self' => URL_SELF,
 'base' => URL_BASE, 'tpl' => URL_TPL . '/' . $styleDir));

?>
