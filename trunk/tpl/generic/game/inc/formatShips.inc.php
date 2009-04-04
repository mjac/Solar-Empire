<?php
class_exists('Savant3') || exit;

function shipCargoReport($cargo)
{
	if (empty($cargo['cargo_bays'])) {
		return 'none';
	}

	$types = array();
	if(!empty($cargo['metal'])) {
		$types[] = $cargo['metal'] . ' metals';
	}
	if(!empty($cargo['fuel'])) {
		$types[] = $cargo['fuel'] . ' fuels';
	}
	if(!empty($cargo['organics'])) {
		$types[] = $cargo['organics'] . ' organics';
	}
	if(!empty($cargo['electronics'])) {
		$types[] = $cargo['electronics'] . ' electronics';
	}
	if(!empty($cargo['colonists'])) {
		$types[] = $cargo['colonists'] . ' colonists';
	}
	if ($cargo['free'] > 0) {
		$types[] = $cargo['free'] . ' free';
	}

	return implode("<br />\n", $types);
}

?>
