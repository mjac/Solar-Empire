<?php
class_exists('Savant2') || exit;

include($this->loadTemplate('header.tpl.php'));

?><h2>Installation complete</h2>
<p>The structure and data have been inserted into the database. Now delete the install directory and return to the login screen.</p>
<?php
include($this->loadTemplate('footer.tpl.php'));
?>
