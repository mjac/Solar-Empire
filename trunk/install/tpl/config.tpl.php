<?php
isset($this) || exit();

$insConfProbs = array(
);

?><h2>Documents</h2>

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

<h2 id="insconfForm">Database configuration</h2>
<?php
if (isset($this->problems) && !empty($this->problems)) {
?><h3>Problems with your submission</h3>
<ul>
<?php
	foreach ($this->problems as $problem) {
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
<form action="<?php $this->eprint(URL_SELF); ?>#insconfForm" method="post">
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

		<dt><label for="email">Your e-mail address</label></dt>
		<dd><input type="text" id="email" name="email" size="40" class="text<?php
if (isset($this->problems) && in_array('invalidEmail', $this->problems)) {
	echo ' invalid';
}
?>"<?php
if (isset($this->email)) {
	echo ' value="' . $this->escape($this->email) . '"';
}
?> /></dd>

		<dt><input type="submit" value="Try configuration" class="button" /></dt>
	</dl>
</form>

