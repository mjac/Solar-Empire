<?php
if (!defined('PATH_SAVANT')) exit();

ob_start('ob_gzhandler');

echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<title><?php

$this->eprint((isset($this->title) ? $this->title :
 (isset($title) ? $title : 'Untitled')) . ' (Solar Empire' . 
 (isset($this->pageName) ? ", $this->pageName" : '') . ')');

?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link type="text/css" rel="stylesheet" href="<?php 
$this->eprint($this->url['tpl'] . '/css/generic.css'); ?>" />
<script type="text/javascript" src="<?php 
$this->eprint($this->url['tpl'] . '/js/common.js'); ?>"></script>
</head>
<body>
