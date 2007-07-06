<?php

require_once('inc/user.inc.php');

$text = "";

#news search
if (isset($term) && !empty($term)) {
	$search = $db->query('SELECT headline, timestamp FROM [game]_news WHERE headline LIKE \'%%%s%%\' ORDER BY timestamp DESC LIMIT 20', array($db->escape($term)));

	$text .= "<h1>Search results</h1>\n<ul>\n";
	while ($post = $db->fetchRow($search)) {
		$text .= "\t<li>$post[headline]</li>\n";
	}
	$text .= "</ul>\n";

	print_page("News Search",$text);

}

$newsQuery = $db->query("SELECT COUNT(*) FROM [game]_news");
$newsCount = (int)current($db->fetchRow($newsQuery));

if (!isset($news_posts_show)) {
	$news_posts_show = $userOpt['news_back'];
}

if (!isset($prev)) {
	db("select * from [game]_news order by timestamp desc LIMIT 0, $news_posts_show");
	$extra = "<p>Showing the latest <strong>$news_posts_show articles</strong>.</p>";
	$prev2 = $news_posts_show;
	$prev = $news_posts_show;
} else {
	$prev3 = $prev - $news_posts_show;
	$prev2 = $prev + $news_posts_show;
	$extra = "<p><a href=news.php?prev=$prev3>Back to posts $prev3 to $prev</a></p><p>Posts $prev to $prev2 of the news.</p>";
	db("select * from [game]_news order by timestamp desc LIMIT $prev, $news_posts_show");
}

$text .= <<<END
<h1>Galaxy news</h1>
$extra
<h2>Search</h2>
<form method="post" action="news.php">
	<p><input type="text" name="term" size="20" class="text" />
	<input type="submit" value="Search" class="button" /></p>
</form>
<h2>There are $newsCount entries</h2>
<table class="simple">
	<tr>
		<th>Date</th>
		<th>Headline</th>
	</tr>

END;

while ($news = dbr(1)) {
	$text .= "\t<tr>\n\t\t<td>" . date("M d - H:i",$news['timestamp']) .
	 "</td>\n\t\t<td>" . $news['headline'] . "</td>\n\t</tr>\n";
}

$text .= "</table>\n";

if($newsCount > $prev2) {
	$prev3 = $prev2 + $news_posts_show;
	$text .= "<p><a href=news.php?prev=$prev2>Posts $prev2 to $prev3</a>";
} else {
	$prev3 = $prev2 - $news_posts_show;
	if($newsCount > $news_posts_show){
		$text .= "<a href=news.php?prev=0>Back to start</a><p>";
	}
}

print_page('News',$text);

?>
