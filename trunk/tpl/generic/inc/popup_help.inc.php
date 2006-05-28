<?php
if (!defined('PATH_SAVANT')) exit();

// Function that will create a help-link
function popupHelp($topic, $height, $width, $string, &$tpl)
{
	return '<a href="' . $tpl->escape($topic) . '" onclick="popup(\'' . 
	 $tpl->escape($topic) . '\', ' . (int)$height . ',' . (int)$width . 
	 '); return false;">' . $tpl->escape($string) . '</a>';
}

?>
