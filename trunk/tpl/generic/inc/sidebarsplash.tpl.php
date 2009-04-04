<?php
class_exists('Savant3') || exit;
?><ul id="splashSidebar">
	<li><a href="<?php
$this->eprint($this->url['base'] . '/index.php');
?>">Introduction</a></li>
<?php
if (isset($showRegister) && $showRegister) {
?>	<li><a href="<?php
$this->eprint($this->url['base'] . '/register.php');
?>">Create account</a></li>
<?php
	}
?>	<li><a href="<?php
$this->eprint($this->url['base'] . '/credits.php');
?>">Game credits</a></li>
	<li><a href="<?php
$this->eprint($this->url['base'] . '/servers.php');
?>">Other servers</a></li>
<?php
if (isset($showLogin) && $showLogin) {
?>
	<li id="loginSection"><h2>Back for more?</h2>
	<form action="<?php
$this->eprint($this->url['base'] . '/login.php');
?>" method="post"><dl>
		<dt><label for="handle">Name</label></dt>
		<dd><input type="text" name="handle" id="handle" class="text" /></dd>

		<dt><label for="password">Password</label></dt>
		<dd><input type="password" name="password" id="password" class="text" /></dd>

		<dt><input type="submit" value="Enter game" class="button" /></dt>
	</dl><?php
	if (isset($this->authProblem) && !empty($this->authProblem)) {
		$authProblem = array(
			'existQuery' => 'A problem occurred while verifying account information',
			'accountMissing' => 'An account is not associated with the details provided'
		);
?>
	<h3>Problems</h3>
	<ul>
<?php
		foreach ($this->authProblem as $probId) {
			if (isset($authProblem[$probId])) {
?>		<li><?php $this->eprint($authProblem[$probId]); ?></li>
<?php
			}
		}
?>	</ul>
<?php
	}
?></form></li>
<?php
}
?>
</ul>
