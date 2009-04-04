<?php
/**
* 
* Template for testing token assignment.
* 
* @version $Id: extend.tpl.php,v 1.1 2004/10/04 01:52:24 pmjones Exp $
*
*/
?>
<p><?php $result = $this->plugin('example'); var_dump($result); ?></p>
<p><?php $result = $this->plugin('example_extend'); var_dump($result); ?></p>
