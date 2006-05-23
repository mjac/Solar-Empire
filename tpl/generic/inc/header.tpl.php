<?php
if (!defined('PATH_SAVANT')) exit();

ob_start('ob_gzhandler');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php

$this->eprint(isset($this->title) ? $this->title :
 (isset($title) ? $title : 'Untitled'));

?> &#8212; Solar Empire</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link type="text/css" rel="stylesheet" href="<?php 
$this->eprint($this->url['tpl']); ?>/css/generic.css" />
<script type="text/javascript" src="<?php 
$this->eprint($this->url['tpl']); ?>/js/common.js"></script>
</head>
<body>
