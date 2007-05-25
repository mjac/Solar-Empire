<?php
class_exists('Savant2') || exit;

$insDbProbs = array(
	'dbType' => 'That database type is not supported',
	'dbDetails' => 'These additional details are required' .
	 implode(', ', $this->dbRequires),
	'dbConnect' => 'Could not connect to the database using the details you provided' .
	 (isset($this->dbConnectErr) ? ('; the error reported was ' . 
	 $this->dbConnectErr) : ll),
	'dbPrefix' => 'That database prefix is invalid, use only alphanumeric characters and underscores'
);

include($this->loadTemplate('header.tpl.php'));

?><h2 id="insDbForm">Database configuration</h2>
<?php
if (isset($this->instProbs) && !empty($this->instProbs)) {
?><h3>Problems with your submission</h3>
<ul>
<?php
	foreach ($this->instProbs as $problem) {
		if (isset($insDbProbs[$problem])) {
?>
	<li><?php $this->eprint($insDbProbs[$problem]); ?></li>
<?php
		}
	}
?></ul>
<h3>Try again</h3>
<?php
}
?>
<form action="<?php $this->eprint(URL_SELF); ?>#insDbForm" method="post">
	<dl>
		<dt><label for="dbType">Type</label></dt>
		<dd><select name="dbType">
			<option value="mysql">MySQL</option>
			<option value="postgresql">PostgreSQL</option>
		</select></dd>
	
		<dt><label for="dbHostname">Hostname or file</label></dt>
		<dd><input name="dbHostname"<?php currentValue('dbHostname', 'localhost'); ?> class="text" /></dd>
	
		<dt><label for="dbName">Name</label></dt>
		<dd><input name="dbName"<?php currentValue('dbName', 'solaremp'); ?> class="text" /></dd>
	
		<dt><label for="dbUsername">Username</label></dt>
		<dd><input name="dbUsername"<?php currentValue('dbUsername'); ?> class="text" /></dd>
	
		<dt><label for="dbPassword">Password</label></dt>
		<dd><input name="dbPassword"<?php currentValue('dbPassword'); ?> class="text" /></dd>

		<dt><input type="submit" value="Try configuration" class="button" /></dt>
	</dl>
</form>
<?php
include($this->loadTemplate('footer.tpl.php'));
?>
