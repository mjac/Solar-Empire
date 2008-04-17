<?php

if (!class_exists('account')) {
	require('account.class.php');
}

class player extends account
{
	public function __construct(PDO $db, $playerId = false)
	{
	    parent::construct($db, $playerId);
	}
};

class playerMutable extends player
{
	public function __construct(PDO $db, $playerId = false)
	{
	    parent::construct($db, $playerId);
	}
};

?>
