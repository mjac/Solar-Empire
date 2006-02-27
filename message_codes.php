<?php

require_once('inc/user.inc.php');

pageStart('Message code guide');

if(!isset($try)) {
?>
<h1>Message Code Guide</h1>
<p><a href="message_codes.php?try=1">Practice</a> your new skills!</p>

<h2>Colours</h2>
<p>[color=lime]Default Colours[/color] or
[color=#FFFFFF]Custom Colour - Hex RGB[/color]<br />
<strong>Default colours:</strong><?php

foreach ($msgColours as $colour => $hex) {
	print " <span style=\"color: #$hex;\">$colour</span>";
}

?></p>
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
<p>[hr] produces a horizontal line accross the screen:<br />
<hr /></p>
<?php
} else {
	$text = isset($text) ? stripslashes($text) : '';

?>
<p><a href="message_codes.php">Return</a> to guide.</p>

<h1>Message-code tester</h1>
<form name="get_var_form" action="message_codes.php" method="post">
	<p><textarea name="text" cols=30 rows=10 wrap="soft">
<?php

	echo htmlentities($text);

?>
</textarea></p>
	<p><input type="hidden" name="try" value="1" />
	<input type="submit" value="Submit" class="button" /></p>
</form>
  <?php

	if (!empty($text)) {
		echo "<h2>Message preview</h2>\n<div style=\"padding: 5px; border: 1px solid #CCCCCC;\">" .
		 msgToHTML($text) . "</div>\n";
	}
}

pageStop();

?>
