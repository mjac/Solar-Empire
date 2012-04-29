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
<tr><td bgcolor=#555555 align=right><br>Choose a default colour scheme<br>:</td>
<td bgcolor=#333333>
<input type=radio name=style value=1 checked> <b class=b1><font color='red'>Classic</font></b><br>
<input type=radio name=style value=2><b class=b1><font color='#0091ff'>Blue & Black</font></b> - aphreak<br>
<input type=radio name=style value=5><b><font color='#007900'>Green & Black</font></b> - Pinkus<br>
<input type=radio name=style value=4><b><font color='#99FFAA'>Light Green & Black</font></b> - Moriarty<br>
<input type=radio name=style value=6><b><font color='#FFFF00'>Yellow & Black</font></b> - Moriarty<br>
<input type=radio name=style value=3><b><font color='#808080'>Grey</font></b>/<b><font color='#004080'>Blue</font></b>/<b><font color='#00FF00'>Lime</b></font> - Pinkus<br>
<input type=radio name=style value=7><b><font color='#808080'>Ru</font></b><b><font color='#1f677e'>st</font></b><b><font color='#a58421'>ic</font></b> - TheMadWeaz<br>
<input type=radio name=style value=8><b><font color='#808080'>Al</font></b><font color='#008e8e'><b>ie</b></font><b><font color='#9cbf75'>n</b></font> - TheMadWeaz<br>
<input type=radio name=style value=9><b><font color='#ffffff'>Ice</font></b> <b><font color='#c0c5fe'>Age</b></font> - TheMadWeaz<br>
<input type=radio name=style value=10><b><font color='#DDDDF5'>Ch</font></b><b><font color='#C5C5CC'>ro</font></b><b><font color='#CCCCCC'>me</b></font> - KilerCris<br>
<input type=radio name=style value=11><b><font color='#9c9d8a'>The</b></font> <b><font color='#a8a800'>Golden</b></font> <b><font color='#fbcf04'>Age</b></font> - TheMadWeaz<br>
<input type=radio name=style value=12><b><font color='#77888a'>The</b></font> <b><font color='#b8b8b8'>Silver</b></font> <b><font color='#9b9493'>Age</b></font> - TheMadWeaz<br>
<input type=radio name=style value=13><b><font color='#b70000'>Mo</b></font><b><font color='#ff8000'>lt</b></font><b><font color='#f7b324'>en</b></font> - TheMadWeaz<br>
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
