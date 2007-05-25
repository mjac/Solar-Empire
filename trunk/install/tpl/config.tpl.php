<?php
class_exists('Savant2') || exit;

$insConfProbs = array(
);

include($this->loadTemplate('header.tpl.php'));

?><h2 id="insConfForm">Write configuration file</h2>
<p>A connection has been made to the database.  The configuration file must now be written.</p>
<?php
if (isset($this->instProbs) && !empty($this->instProbs)) {
?><h3>Problems with your submission</h3>
<ul>
<?php
	foreach ($this->instProbs as $problem) {
		if (isset($insConfProbs[$problem])) {
?>
	<li><?php $this->eprint($insConfProbs[$problem]); ?></li>
<?php
		}
	}
?></ul>
<h3>Try again</h3>
<?php
}
?>
<form action="<?php $this->eprint(URL_SELF); ?>#insConfForm" method="post">
	<p><input type="submit" name="configWrite" id="configWrite" value="Write configuration file" /></p>
</form>
<?php
include($this->loadTemplate('footer.tpl.php'));
?>
