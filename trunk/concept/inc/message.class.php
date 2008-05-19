<?php

class message
{
	protected $db;
	protected $user;
	
	/**
	 * @param $from Sender user, anonymous if false
	 */
	public function __construct($db, $user = false)
	{
	    $this->db = $db;
	    $this->user = $user;
	}
	
	public function insert()
	{
	
	}
	
	public function edit()
	{
	
	}
	
	public function delete($messageId)
	{
		$delQuery = $this->db->prepare('UPDATE ? SET state = deleted WHERE message_id = ?');
		
		$delQuery->execute(array($this->db->table['message'], (int)$messageId));
		
	}
	
	/**
	 * User is allowed to edit or delete this entry
	 */
	public function allowed($messageId)
	{
	
	}
}

?>
