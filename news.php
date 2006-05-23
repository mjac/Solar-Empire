<?php

require_once('inc/user.inc.php');
require_once('inc/template.inc.php');

$sStr = array();
$sParams = array();

if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
	$sStr[] = 'headline LIKE \'%%%s%%\'';
	$sParams[] = $_REQUEST['search'];
	$tpl->assign('search', $_REQUEST['search']);
}

if (isset($_REQUEST['from'])) {
	$sStr[] = 'timestamp > %u';
	$sParams[] = (int)$_REQUEST['from'];
	$tpl->assign('from', (int)$_REQUEST['from']);
}

$offset = isset($_REQUEST['offset']) ? (int)$_REQUEST['offset'] : 0;
if ($offset > 100000 || $offset < 0) {
	$offset = 0;
}
$sParams[] = $offset;
$tpl->assign('offset', $offset);

$amount = isset($_REQUEST['amount']) ? (int)$_REQUEST['amount'] : 10;
if ($amount > 100 || $amount < 1) {
	$amount = 10;
}
$sParams[] = $amount;
$tpl->assign('amount', $amount);

$newsFind = $db->query('SELECT timestamp, headline FROM [game]_news' . 
 (empty($sStr) ? '' : (' WHERE ' . implode(' AND ', $sStr))) . 
 ' ORDER BY timestamp DESC LIMIT %u, %u', $sParams);

$count = (int)$db->numRows($newsFind);
$tpl->assign('count', $count);

$articles = array();
while ($article = $db->fetchRow($newsFind, ROW_ASSOC)) {
	$articles[] = $article;
}
$tpl->assign('articles', $articles);

assignCommon($tpl);

$tpl->display('news.tpl.php');
exit();

?>
