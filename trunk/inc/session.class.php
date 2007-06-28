<?php
defined('PATH_INC') || exit;

/** General session class for authentication */
class session
{
	/** Database class */
	var $db;

	/** Input class */
	var $input;

	/** Session variables */
	var $data = array();

	/** Start the session and associate vsriables */
	function session(&$db, &$input)
	{
		$this->db =& $db;
		$this->input =& $input;

		session_start();
		$this->data =& $_SESSION;
	}

	function create($accountId)
	{
		$this->data['expires'] = time() + SESSION_TIME_LIMIT;
		$this->data['account'] = $accountId;

		$this->updateAccount();
	}

	function destroy()
	{
		$_SESSION = array();
	
		if ($this->input->exists(session_name())) {
			setcookie(session_name(), '', time() - 86400, '/');
		}
	
		session_destroy();
	}

	function authenticated()
	{
		global $account, $gameInfo;
	
		if (!(isset($this->data['account']) && isset($this->data['expires']))) {
			return false;
		}

		if ($this->data['expires'] <= time()) { // session is expired
			$this->destroy();
			return false;
		}
	
		$accQuery = $this->db->query('SELECT COUNT(*) FROM [server]account WHERE acc_id = %[1]',
		 $this->data['account']);
		if ($this->db->numRows($accQuery) < 1) { // user does not exist
			return false;
		}
		$account = $this->db->fetchRow($accQuery);

		$this->updateAccount();

		define('IS_OWNER', $this->data['account'] == OWNER_ID);

		// Extend the PHP session
		$this->data['expires'] = time() + SESSION_TIME_LIMIT;

		return true;
	}

	function updateAccount()
	{
		$this->db->query('UPDATE [server]account SET acc_requests = acc_requests + 1, acc_accessed = FROM_UNIXTIME(%[1]), acc_ip = %[2] WHERE acc_id = %[3]',
		 time(), $this->ipToUlong($this->ipAddress()), $this->data['account']);
	}

	function ipAddress()
	{
		return $_SERVER['REMOTE_ADDR'];
	}

	function ipToUlong($ipStr)
	{
		return (double)sprintf('%u', ip2long($ipStr));
	}

	function ipFromUlong($ipUlong)
	{
		return long2ip((int)$ipUlong);
	}
};

?>
