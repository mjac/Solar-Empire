<?php
class_exists('Savant2') || exit;

$insTableProbs = array(
	'tableSchemaOpen' => 'Could not open database schema file',
	'tableSchemaInsert' => 'Schema was not created successfully',
	'tableData' => 'General data could not be found for insertation',
	'tableStarNames' => 'Star names could not be inserted successfully',
	'tableTip' => 'Table tips were not inserted successfully',
	'tableGameOption' => 'Table game options were not inserted successfully',
	'tableAdmin' => 'Administrator account was not created'
);

include($this->loadTemplate('header.tpl.php'));

?><h2 id="insTableForm">Table installation</h2>
<?php
if (isset($this->instProbs) && !empty($this->instProbs)) {
?><h3>Problems with your submission</h3>
<ul>
<?php
	foreach ($this->instProbs as $problem) {
		if (isset($insTableProbs[$problem])) {
?>
	<li><?php $this->eprint($insTableProbs[$problem]); ?></li>
<?php
		}
	}
?></ul>
<h3>Try again</h3>
<?php
}
?>
<form action="<?php $this->eprint(URL_SELF); ?>#insTableForm" method="post">
	<dl>
		<dt><label for="adminPassword">Administrator password</label></dt>
		<dd><input type="password" name="adminPassword" id="adminPassword" class="text" value="" /></dd>

		<dt><input type="submit" value="Add table structure" class="button" /></dt>
	</dl>
</form>
<?php
include($this->loadTemplate('footer.tpl.php'));
?>
