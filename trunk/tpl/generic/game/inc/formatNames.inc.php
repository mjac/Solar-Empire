<?php
class_exists('Savant3') || exit;

function formatName($id, $name, $clanId, $clanSym, $clanCol)
{
	static $cache = array();

	if ($name === NULL) {
		return '<em>Galactic control</em>';
	}

	if ($id === NULL) {
		return esc($name);
	}

	if (!isset($cache[$name])) {
		$cache[$name] = '<a href="' . esc('player_info.php?target=' . $id) .
		 '">' . esc($name) . '</a>';

		if ($clanId !== NULL) {
			$cache[$name] = clanSymbol($clanSym, $clanCol) . " $cache[$name]";
		}
	}

	return $cache[$name];
}

function clanSymbol($symbol, $colour)
{
	return "<span style=\"color: #" . str_pad(dechex($colour), 6, '0',
	 STR_PAD_LEFT) . ";\">" . esc($symbol) . "</span>";
}

function clanName($name, $symbol, $colour)
{
	return esc($name) . ' (' . clanSymbol($symbol, $colour) . ')';
}

?>
