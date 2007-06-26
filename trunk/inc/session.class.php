<?php
defined('PATH_INC') || exit;

class session
{
	var $db;

	function session(&$db)
	{	
		$this->db =& $db;
		session_start();
	}

	function create($accountId)
	{
		$_SESSION['expires'] = time() + SESSION_TIME_LIMIT;
		$_SESSION['account'] = $accountId;

		$this->updateAccount();
	}

	function destroy()
	{
		$_SESSION = array();
	
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time() - 86400, '/');
		}
	
		session_destroy();
	}

	function authenticated()
	{
		global $account, $gameInfo;
	
		if (!(isset($_SESSION['account']) && isset($_SESSION['expires']))) {
			return false;
		}

		if ($_SESSION['expires'] <= time()) { // session is expired
			$this->destroy();
			return false;
		}
	
		$accQuery = $this->db->query('SELECT COUNT(*) FROM [server]account WHERE acc_id = %[1]',
		 $_SESSION['account']);
		if ($this->db->numRows($accQuery) < 1) { // user does not exist
			return false;
		}
		$account = $this->db->fetchRow($accQuery);

		$this->updateAccount();

		define('IS_OWNER', $_SESSION['account'] == OWNER_ID);

		// Extend the PHP session
		$_SESSION['expires'] = time() + SESSION_TIME_LIMIT;

		return true;
	}

	function updateAccount()
	{
		$this->db->query('UPDATE [server]account SET acc_requests = acc_requests + 1, acc_accessed = FROM_UNIXTIME(%[1]), acc_ip = %[2] WHERE acc_id = %[3]',
		 time(), $this->ipToUlong($this->ipAddress()), $_SESSION['account']);
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
/*
	function inGame()
	{
		if (!$gameInfo = selectGame($account['in_game'])) {
			$this->db->query('UPDATE [global]account SET in_game = NULL WHERE login_id = %[1]', 
			 $login_id);
			$account['in_game'] = NULL;
		    return false;
		}

		define('IS_ADMIN', $account['login_id'] == $gameInfo['admin']);

		$account['session_exp'] = $next_exp;
	}
*/
};

?>
