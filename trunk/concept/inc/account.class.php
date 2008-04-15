<?php

/*
Example database schema for permissions system

Perm_objects
1 file admin/account.php

Perms
id type id_from id_to object allow/deny

Load all at once and cache

Global, strong ip bans

game/ matches game/....

account->permission->add(user, file, 'game/', false) // part of the ip ban script
*/

class account
{
	private $id;

	public id()
	{
	    return $this->id;
	}

	public __construct($account_id = false)
	{
	    if ($account_id !== false) {
	        // user
	        $account_id = 1;//valid;
	    }

	    $this->id = $account_id;
	}

	private access_load()
	{
	    // load from db
	}

	public access($page_name, $cache = true)
	{
	    // database query that looks up user group
	}
};

class account_mutable extends account
{
	public __construct($account_id = false)
	{
	    parent::construct($account_id);
	}

	public create()
	{

	}
}

?>
