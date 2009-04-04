<?php
class_exists('Savant3') || exit;

$this->pageName = 'System';
$this->title = 'The system does not exist';
$this->description = '';

$locAlerts = array(
	'travelMissingSystem' => 'That system does not exist',
	'travelPointless' => 'You are already in that system',
	'travelMissingLink' => 'There is no link to that system',
	'travelLinkTurns' => 'You do not have enough turns to travel to that system'
);

include($this->template('game/inc/header_game.tpl.php'));

include($this->template('game/inc/location.tpl.php'));

?><div id="locInfo">
<h1>Star system <?php $this->eprint($this->star['id']); ?></h1>

<?php
if (isset($this->locAlerts) && !empty($this->locAlerts)) {
?><h2>Alerts</h2>
<ul>
<?php
	foreach ($this->locAlerts as $alert) {
		if (isset($locAlerts[$alert])) {
?>
	<li><?php echo $locAlerts[$alert]; ?></li>
<?php
		}
	}
?></ul>
<?php
}
?>

<h2>System information</h2>

<h2>Ships found</h2>

<h2>Planets located</h2>
</div>
<?php

include($this->template('game/inc/footer_game.tpl.php'));

?>
