<?php
class_exists('Savant3') || exit;

include($this->template('header.tpl.php'));

?><h2>Installation complete</h2>
<p>The structure and data have been inserted into the database. Now delete the install directory and return to the login screen.</p>
<?php
include($this->template('footer.tpl.php'));
?>
