<?php
class_exists('Savant2') || exit;

$insLicProbs = array(
	'licenceOpen' => 'The licence could not be opened'
);

include($this->loadTemplate('header.tpl.php'));

?><h2 id="insLicForm">Introduction, licence agreement</h2>
<p>The licence agreement must be checked before progression to the next stage of installation.</p>
<?php
if (isset($this->instProbs) && !empty($this->instProbs)) {
?><h3>Problems with your submission</h3>
<ul>
<?php
	foreach ($this->instProbs as $problem) {
		if (isset($insLicProbs[$problem])) {
?>
	<li><?php $this->eprint($insLicProbs[$problem]); ?></li>
<?php
		}
	}
?></ul>
<h3>Try again</h3>
<?php
}
?>
<form action="<?php $this->eprint(URL_SELF); ?>#insLicForm" method="post">
	<h3>Readme</h3>
	<p><?php
if (isset($this->readme)) {
?><textarea rows="10" cols="80"><?php $this->eprint($this->readme); ?></textarea><?php
} else {
?>General information is missing.<?php
}
?></p>

	<h3>Licence</h3>
	<p><?php
if (isset($this->licence)) {
?><textarea rows="10" cols="80"><?php $this->eprint($this->licence); ?></textarea><?php
} else {
?>Licence information is missing.<?php
}
?></p>
	<p><input type="checkbox" name="licence" id="licAccept" value="accept" /> I accept the licence agreement</p>
	<p><input type="submit" value="Continue to next stage" /></p>
</form>
<?php
include($this->loadTemplate('footer.tpl.php'));
?>
