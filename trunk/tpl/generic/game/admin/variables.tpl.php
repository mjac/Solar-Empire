<?php
class_exists('Savant2') || exit;

$this->pageName = 'Game variables';
$this->title = 'Change variables that directly affect this game';
$this->description = '';

include($this->loadTemplate('game/inc/header_game.tpl.php'));

?><h1>Edit game variables</h1>
<?php

$updated = array();
foreach ($this->gameVars as $name => $data) {
	if ($data['newValue'] !== false) {
	    $updated = array($name, $data['value'], $data['newValue']);
	}
}

if (!empty($updated)) {
?><h2>Updated variables</h2>
<ul>
<?php

	foreach ($updated as $var) {
?>  <li><em><?php $this->eprint($var[0]); ?></em> updated from <?php
		$this->eprint(number_format($var[1]));
?> to <?php
		$this->eprint(number_format($var[2]));
?></li>
<?php
	}

?></ul>

<h2>Continue editing</h2>
<?php
}


?><form action="<?php $this->eprint($this->url['self']); ?>" method="post">
	<p><input type="submit" value="Submit changes" class="button" /></p>
	<p>Only variables that are within range will be saved.</p>
	<table class="simple">
		<tr>
			<th>Variable</th>
			<th>Description</th>
			<th>Min</th>
			<th>Max</th>
			<th>Value</th>
		</tr>
<?php

foreach ($this->gameVars as $name => $data) {
?>
		<tr>
		    <td><label for="var<?php $this->eprint($name); ?>"><?php
	$this->eprint($name);
?></label></td>
		    <td><?php echo $data['description']; ?></td>
		    <td><?php $this->eprint($data['min']); ?></td>
		    <td><?php $this->eprint($data['max']); ?></td>
		    <td><input type="text" name="change[<?php
	$this->eprint($name);
?>]" id="var<?php $this->eprint($name); ?>" value="<?php
	$this->eprint($data['newValue'] === false ? $data['value'] :
	 $data['newValue']);
?>" size="8" class="text" /></td>
		</tr>
<?php
	 "</td>\n\t\t\t<td></td>\n\t\t</tr>\n";
}

?>
	</table>
	<p><input type="submit" value="Submit changes" class="button" /></p>
</form>
<?php

include($this->loadTemplate('game/inc/footer_game.tpl.php'));

?>
