<?php
defined('PATH_SAVANT') || exit();

$title = 'Game variables';

include($this->loadTemplate('inc/header.tpl.php'));

if (!$this->gameExists) {
?><p>This game does not exist.</p>
<?php
} elseif (!$this->viewVars) {
?><p>The game variables are hidden.</p>
<?php
} else {
?><h1><?php $this->eprint($this->gameName); ?> game variables</h1>

<table class="simple">
	<tr>
	    <th>Name</th>
	    <th>Value</th>
	    <th>Description</th>
	</tr>
<?php
	foreach ($this->gameVars as $var) {
?>    <tr>
		<td><?php $this->eprint($var['name']); ?></td>
		<td><?php $this->eprint($var['value']); ?></td>
		<td><?php $this->eprint($var['descript']); ?></td>
	</tr>
<?php
	}
?></table>
<?php
}

include($this->loadTemplate('inc/footer.tpl.php'));

?>
