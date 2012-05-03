<?php

require_once('inc/common.inc.php');

print_header("New Account");

?>
<div id="logo"><img src="img/se_logo.jpg" alt="Solar Empire" /></div>

<?php

include_once('inc/rules.inc.html');

?>
<center><br>
<form method="post" action="signup.php" name="signup">
<blockquote>These fields are <b>Required</b>
<table cellspacing=1 cellpadding=2 border=0><tr bgcolor=#555555>
<tr>
<td bgcolor=#555555 align=right><B>Login Name:</B></td>
<td bgcolor=#333333><INPUT name='l_name' value='' size=20>
</td>
</tr>
<tr><td bgcolor=#555555 align=right><B>Password:</B></td>
<td bgcolor=#333333><INPUT type=password name='passwd' value='' size=20></td></tr>
<tr><td bgcolor=#555555 align=right><B>Password Again:</B></td>
<td bgcolor=#333333><INPUT type=password name='passwd_verify' value='' size=20></td></tr>
<tr><td bgcolor=#555555 align=right><B>Real Name:</B></td>
<td bgcolor=#333333><INPUT name='real_name' value='' size=20></td></tr>
<tr><td bgcolor=#555555 align=right><B>Email Address:</B><br>(Must be valid for authorisation)<br>(It will remain private)</td>
<td bgcolor=#333333><INPUT name='email_address' value='' size=20></td></tr>
<tr><td  bgcolor=#555555 align=right><B>Email Address Again:</B><br>(Please retype the above address)</td>
<td bgcolor=#333333><INPUT name='email_address_verify' value='' size=20></td></tr>
<tr><td bgcolor=#555555 align=right><br>Choose a default colour scheme:</td>
<td bgcolor=#333333>
<input type=radio name=style value=1 checked> <b class=b1><font color='red'>Classic</font></b><br>
</td></tr>
</table>
<p>By Clicking the below button, you agree to follow the above rules pertaining to the game.</p>
<p align="center"><INPUT type=submit value="Submit"></p>
</form>
<p>Disclaimer: The Server Admins/Operators are not responsible for the content
of the site, nor are they obliged to let you create, or keep an account. The
Server Admins/Operators are not responsible for any problems you may encounter,
or that may be caused by your use of this site. The Server Admins/Operators
have the right to delete accounts or ban IP adresses without warning or
reason.</p>
<?php

print_footer();

?>