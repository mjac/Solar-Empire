<?php

require_once('inc/user.inc.php');
require_once('inc/template.inc.php');

$queryStr = array();
$search = '';
$from = 0;

if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
	$queryStr[] = 'headline LIKE \'%%[1]%\'';
	$search = $_REQUEST['search'];
	$tpl->assign('search', $search);
}

if (isset($_REQUEST['from'])) {
	$queryStr[] = 'timestamp > %[2]';
	$from = (int)$_REQUEST['from'];
	$tpl->assign('from', $from);
}

$offset = isset($_REQUEST['offset']) ? (int)$_REQUEST['offset'] : 0;
if ($offset > 100000 || $offset < 0) {
	$offset = 0;
}
$tpl->assign('offset', $offset);

$amount = isset($_REQUEST['amount']) ? (int)$_REQUEST['amount'] : 10;
if ($amount > 100 || $amount < 1) {
	$amount = 10;
}
$tpl->assign('amount', $amount);

$newsFind = $db->query('SELECT timestamp, headline FROM [game]_news' . 
 (empty($queryStr) ? '' : (' WHERE ' . implode(' AND ', $queryStr))) . 
 ' ORDER BY timestamp DESC LIMIT %[3], %[4]',
 $search, $from, $offset, $amount);

$count = (int)$db->numRows($newsFind);
$tpl->assign('count', $count);

$articles = array();
while ($article = $db->fetchRow($newsFind, ROW_ASSOC)) {
	$articles[] = $article;
}
$tpl->assign('articles', $articles);

assignCommon($tpl);

$tpl->display('game/news.tpl.php');
exit;

?>
