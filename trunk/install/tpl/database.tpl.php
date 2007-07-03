<?php
class_exists('Savant2') || exit;

$insDbProbs = array(
	'dbType' => 'That database type is not supported',
	'dbDetails' => 'Additional details are required' .
	 (isset($this->dbRequires) ? (': ' .
	 $this->escape(implode(', ', $this->dbRequires))) : ''),
	'dbConnect' => 'Could not connect to the database using the details you provided' .
	 (isset($this->dbConnectErr) ? ('; the error reported was <em>' . 
	 $this->escape($this->dbConnectErr) . '</em>') : ''),
	'dbPrefixServer' => 'That database server prefix is invalid, use only alphanumeric characters and underscores',
	'dbPrefixGame' => 'That database game prefix is invalid, use only alphanumeric characters and underscores: a value must be present to prevent collisions with the server tables'
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
	<li><?php echo $insDbProbs[$problem]; ?></li>
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
		<dd><select name="db[type]" id="dbType">
			<option value="mysql" selected="selected">MySQL</option>
			<option value="postgresql">PostgreSQL</option>
		</select></dd>
	
		<dt><label for="dbHostname">Hostname or file</label></dt>
		<dd><input type="text" name="db[hostname]" id="dbHostname" class="text" value="localhost" /></dd>
	
		<dt><label for="dbDatabase">Database</label></dt>
		<dd><input type="text" name="db[database]" id="dbDatabase" class="text" /></dd>
	
		<dt><label for="dbUsername">Username</label></dt>
		<dd><input type="text" name="db[username]" id="dbUsername" class="text" /></dd>
	
		<dt><label for="dbPassword">Password</label></dt>
		<dd><input type="password" name="db[password]" id="dbPassword" class="text" /></dd>
	
		<dt><label for="dbPrefixServer">Server table prefix</label></dt>
		<dd><input type="text" name="db[prefixServer]" id="dbPrefixServer" class="text" value="sw_" /></dd>

		<dt><label for="dbPrefixGame">Game table prefix</label></dt>
		<dd><input type="text" name="db[prefixGame]" id="dbPrefixGame" class="text" value="game_" /></dd>

		<dt><input type="submit" value="Connect to database" class="button" /></dt>
	</dl>
</form>
<?php
include($this->loadTemplate('footer.tpl.php'));
?>
