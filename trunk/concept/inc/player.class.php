<?php

require('account.class.php');

class player extends account
{
	public __construct($player_id = false)
	{
	    parent::construct($player_id);
	}
};

class player_mutable extends player
{
	public __construct($player_id = false)
	{
	    parent::construct($player_id);
	}
};

?>
