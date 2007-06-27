<?php
class_exists('Savant2') || exit;

header('Cache-control: no-cache');

echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<title>System Wars installation: <?php $this->eprint($this->stage); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" media="screen" href="<?php
$this->eprint(URL_INSTALL . '/clear.css');
?>" />
</head>
<body>
<h1>System Wars installation</h1>
<?php

$stages = array(
	'licence' => URL_SELF . '?licence=reject',
	'database' => URL_SELF . '?db[reset]=true',
	'config' => URL_SELF . '?configReset=true',
	'tables' => URL_SELF . '?tableReset=true',
	'complete' => ''
);

?><ul id="stage"
	><?php
foreach ($stages as $stageName => $stageUrl) {
	if ($stageName === $this->stage) {
?><li class="active"><?php
		$this->eprint(ucfirst($stageName));
?></li
><?php
		break;
	} else {
?><li><a href="<?php
		$this->eprint($stageUrl);
?>"><?php
		$this->eprint(ucfirst($stageName));
?></a></li
	><?php
	}
}
?></ul>
