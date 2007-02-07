<?php
class_exists('Savant2') || exit;

$this->pageName = 'Game variables';
$this->title = 'Change variables that directly affect this game';
$this->description = '';

include($this->loadTemplate('game/inc/header_game.tpl.php'));

?><h1>Edit game variables</h1>
<form action="<?php $this->eprint($this->url['self']); ?>" method="post">
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
?>" size="8" class="text" /><?php
	if ($data['newValue'] !== false) {
?><br /><em>(<?php $this->eprint($data['value']); ?>)</em><?php
	}
?></td>
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
