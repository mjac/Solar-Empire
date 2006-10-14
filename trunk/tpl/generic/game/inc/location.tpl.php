<?php
class_exists('Savant2') || exit;

if (!function_exists('formatName')) {
	require($this->loadTemplate('game/inc/formatNames.inc.php'));
}

?><div id="locBar">
	<h2><a href="system_map.php">Map of galaxy</a></h2>
	<p><img id="miniMap" src="<?php $this->eprint($this->star['map']); 
?>" width="200" height="200"
	 alt="Map of systems around <?php $this->eprint($this->star['id']); 
?>" usemap="#systemMap" /></p>
<?php

	if (!empty($this->star['links'])) {
?>	<map name="systemMap" id="#systemMap">
<?php

		foreach ($this->star['links'] as $s) {
			$starX = $s['x'] - $star['x'] + (200 / 2);
			$starY = $s['y'] - $star['y'] + (200 / 2);

?>		<area shape="rect" coords="<?php
			$this->eprint(($starX - 10) . ',' . ($starY - 10) . ',' .
			 ($starX + 10) . ',' . ($starY + 10);
?>" href="<?php $this->eprint($this->url['self'] . '?toloc=' . $s['id']); 
?>" alt="System $id" />
<?php
		}

?>	</map>
<?php
	}

	if (isset($this->ship) && 
	     $this->ship['empty_bays'] != $this->ship['cargo_bays']) {
?>	<p><a href="<?php $this->eprint($this->url['self'] . '?jettison=1'); 
?>">Jettison Cargo</a></p>
<?php
	}
	
if (isset($this->equip)) {
?>
<h2>Equipment</h2>
<?php

	if($this->equip['genesis'] > 0) {
?>	<p><a href="<?php $this->eprint($this->url['base'] . '/planet_build.php?location=' . $this->star['id']); 
?>">Genesis Device</a> (<?php $this->eprint(number_format($this->equip['genesis'])); 
?>)</p>
<?php
	}

	if($this->equip['alpha'] > 0) {
?>	<p><a href="<?php $this->eprint($this->url['base'] . '/bombs.php?alpha=1'); 
?>">Alpha Bomb</a> (<?php $this->eprint(number_format($this->equip['alpha'])); 
?>)</p>
<?php
	}
	if($this->equip['gamma'] > 0) {
?>	<p><a href="<?php $this->eprint($this->url['base'] . '/bombs.php?bomb_type=1'); 
?>">Gamma Bomb</a> (<?php $this->eprint(number_format($this->equip['gamma'])); 
?>)</p>
<?php
	}
	if($this->equip['delta'] > 0) {
?>	<p><a href="<?php $this->eprint($this->url['base'] . '/bombs.php?bomb_type=2'); 
?>">Delta Bomb</a> (<?php $this->eprint(number_format($this->equip['delta'])); 
?>)</p>
<?php
	}
}

if (isset($this->ship)) {
?>
<h2>Ship</h2>
<?php
	if ($this->ship['transwarp']) {

?>	<form id="transwarp_form" action="<?php $this->eprint($this->url['self']); 
?>" method="post">
		<h3 title="Travel a short-distance instantly">Transwarp Jump</h3>
		<h4><a href="<?php $this->eprint($this->url['self'] . '?transburst=1'); 
?>">Burst</a> to a random location</h4>
		<h4 title="Maximum 15 light years at 5+ turns">Travel to a certain destination</h4>
		<p>Destination: <input type="text" size="3" maxlength="3" name="transwarp"  class="text" /></p>
		<p><input type="submit" value="Engage" class="button" /></p>
	</form>
<?php
	}

	if ($this->ship['subspace']) {

?>	<form id="subspace_form" action="<?php $this->eprint($this->url['self']); 
?>" method="post">
		<h3 title="Travel quickly to anywhere in the Galaxy">SubSpace Jump</h3>
		<p>Destination: <input type="text" size="3" maxlength="3" name="subspace"  class="text" /></p>
		<p><input type="submit" value="Engage" class="button" /></p>
	</form>
<?php

	}
}

?>
</div>

