<?php

require_once('inc/user.inc.php');

pageStart();

if(!isset($try)) {
?>
<h1>Message Code Guide</h1>
<p><a href="mc.php?try=1">Practice</a> your new skills!</p>
<h2>Links</h2>
<p>[link 'http://www.solarempire.com']Solar Empire[/link] =
<a href="http://www.solarempire.com" target="new">Solar Empire</a></p>
<h2>Colours</h2>
<p>[color 'lime']Default Colours[/color] or
[color '#FFFFFF']Custom Colour - Hex RGB[/color]<br />
<strong>Default colours:</strong><?php

foreach ($msgColours as $colour => $hex) {
	print " <span style=\"color: #$hex;\">$colour</span>";
}

?></p>
<p>[hr] produces a horizontal line accross the screen:<br />
<hr /></p>
<h2>Smilies</h2>
<?php
$smSetAmount = count($smileSets);
$smTypeAmount = count($smileTypes);

$table = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"0\">\n";
for ($i = -1; $i < $smSetAmount; ++$i) {
	$table .= "\t<tr>\n";
	for ($j = -1; $j < $smTypeAmount; ++$j) {
		$table .= "\t\t<td>";
		if ($j === -1 && $i === -1) {
			$table .= '&nbsp;';
		} else {
			$table .= $i === -1 ? (empty($smileTypes[$j]) ? 'default' : $smileTypes[$j]) : ($j === -1 ? (empty($smileSets[$i]) ? 'default' : $smileSets[$i]) :
			 "<img src=\"img/smiles/{$smileSets[$i]}{$smileTypes[$j]}.gif\">");
		}
		$table .= "</td>\n";
	}
	$table .= "\t</tr>\n";
}
$table .= '</table>';

print $table;

?>

<h2>Basic Formatting</h2>
<p>[b]BOLD TEXT[/b] = <b>BOLD TEXT</b></p>
<p>[i]ITALIC TEXT[/i] = <i>ITALIC TEXT</i></p>
<p>If you want to put a message code without it being interpretted use [[] and []] for [ and ]</p>
<p>Newlines are converted to their HTML equilivant, so the returns you do while typing your message will show on the forum.  You do not need to type the HTML!</p>
<?php
} else {
?>
<p><a href="mc.php">Return</a> to guide.</p>

<?php
	$text = isset($text) ? stripslashes($text) : '';
	if (!empty($text)) {
		print "<h2>Your Test:</h2>\n<div style=\"padding: 5px; border: 1px solid #CCCCCC;\">" .
		 stripslashes(mcit($text)) . "</div>\n";
	}
?>
<h1>Test Code Form</h1>
<form name="get_var_form" action="mc.php" method="post">
	<input type="hidden" name="try" value="1" />
	<textarea name="text" cols=30 rows=10 wrap="soft">
<?php

	print htmlentities($text);

?>
</textarea>
	<input type="hidden" name="rs" value='&lt;p&gt;&lt;a href=location.php&gt;Back to the Star System&lt;/a&gt;&lt;br&gt;'><br />
	<input type="submit" value="submit">
</form>
  <?php
}

pageStop('Message code');

?>
