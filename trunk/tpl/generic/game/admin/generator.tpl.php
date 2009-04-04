<?php
class_exists('Savant3') || exit;

$this->pageName = 'Universe generator';
$this->title = 'Create a new universe for this game';
$this->description = '';

include($this->template('game/inc/header_game.tpl.php'));

?><h1>Generate universe</h1>

<p><label for="actPreview">Preview the universe</label>, changing the universe variables, until you find a good combination before <label for="actCreate">generating the universe</label> to create and store all the entries and graphics for the current game.</p>
<form action="<?php $this->eprint($this->url['self']); ?>" method="get">
	<p><input type="submit" id="actPreview" name="action" value="Preview" class="button" /> then 
	<input type="submit" id="actCreate" name="action" value="Create"
	 class="button" onclick="return confirm(&quot;Are you sure?&quot;);" /></p>
</form>
<p>You can also <a href="<?php
$this->eprint($this->url['self'] . '?action=maps'); 
?>">recreate the maps</a> using the current univerese schema without resetting the entire universe.</p>
<?php

if (!isset($this->action)) {
	include($this->template('game/inc/footer_game.tpl.php'));
	return;
}

if ($this->action === 'create') {
?><h2>Creating the game universe</h2>
<p>The universe has been created successfully in <?php
	$this->eprint(number_format($this->genPeriod, 4));
?>&nbsp;s.</p>
<h3>Execution profile</h3>
<table class="simple">
	<thead>
		<tr>
		    <th>Activity</th>
		    <th>Period /s</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
		    <td>Total</td>
		    <td><?php
	$this->eprint(number_format($this->genPeriod, 4));
?></td>
		</tr>
	</tfoot>
	<tbody>
		<tr>
		    <td>Positioning</td>
		    <td><?php
	$this->eprint(number_format($this->genPeriodPos, 4));
?></td>
		</tr>
		<tr>
		    <td>Linking</td>
		    <td><?php
	$this->eprint(number_format($this->genPeriodLink, 4));
?></td>
		</tr>
		<tr>
		    <td>Rendering map</td>
		    <td><?php
	$this->eprint(number_format($this->genPeriodRender, 4));
?></td>
		</tr>
		<tr>
		    <td>Saving data</td>
		    <td><?php
	$this->eprint(number_format($this->genPeriodSave, 4));
?></td>
		</tr>
	</tbody>
</table>
<?php
} elseif ($this->action === 'preview') {
?><h2>Preview of current settings</h2>
<p>Please wait while the image loads: the actual generation is conducted when the browser requests the image below, not when this page is opened.  Previewing a universe has <strong>no effect</strong> on the current game.</p>
<p><img src="<?php
	$this->eprint($this->url['self'] . '?action=makepreview');
?>" alt="Preview of new universe" /></p>
<p>Try <a href="<?php
	$this->eprint($this->url['self'] . '?action=preview');
?>">previewing another universe</a> to see more random configurations.</p>
<?php
} elseif ($this->action === 'maps') {
?><h2>Creating universe maps</h2>
<p>Completed successfully in <?php
	$this->eprint(number_format($this->mapGenPeriod, 4));
?>&nbsp;s.  All the maps have been created using the current universe schema from the database &mdash; the data and graphics are now synchronised.</p>
<?php
}

include($this->template('game/inc/footer_game.tpl.php'));

?>
