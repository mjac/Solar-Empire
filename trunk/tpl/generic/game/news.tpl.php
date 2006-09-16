<?php
defined('PATH_SAVANT') || exit();

$title = 'News and events';

include($this->loadTemplate('game/inc/header_game.tpl.php'));

?><h1>News and events</h1>

<h2>Search</h2>
<form method="get" action="news.php" id="newsSearch">
	<dl>
		<dt>For</dt>
		<dd><input type="text" name="search" size="16" class="text"<?php
if (isset($this->search)) {
?>
		 value="<?php $this->eprint($this->search); ?>"<?php 
}
?> /></dd>
		
		<dt>Offset</dt>
		<dd><input type="text" name="offset" value="<?php 
$this->eprint($this->offset); ?>" size="4" class="text" /></dd>
		
		<dt>Amount</dt>
		<dd><input type="text" name="amount" value="<?php 
$this->eprint($this->amount); ?>" size="4" class="text" /></dd>

		<dt><input type="submit" value="Search" class="button" /></dt>
	</dl>
</form>

<h2><?php $this->eprint($this->count); ?> news article(s)</h2>
<table class="simple" id="news">
	<tr>
		<th>Date</th>
		<th>Headline</th>
	</tr>
<?php

foreach ($this->articles as $article) {
?>	<tr>
		<td><?php $this->eprint(date("M d - H:i", $article['timestamp'])); 
?></td>
		<td><?php
	if (isset($this->search) && !empty($this->search)) {
		echo preg_replace('/(' . preg_quote($this->escape($this->search)) . 
		')/i', '<em>\1</em>', $this->escape($article['headline']));
	} else {
		$this->eprint($article['headline']);
	}
?></td>
	</tr>
<?php
}

?></table>
<?php

include($this->loadTemplate('game/inc/footer_game.tpl.php'));

?>
