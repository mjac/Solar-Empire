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
	protected $id;
	protected $group;

	protected $db;

	private $detailsCached = false;
	
	public function __get($name)
	{
	    if ($name === 'id') {
	        return $this->id;
	    }
	}

	public function __construct(PDO $db, $accountId = false)
	{
	    $this->db = $db;
	
	    if ($accountId !== false) {
	        // user
	        $accountId = 1;//valid;
	    }

	    $this->id = $accountId;
	}
	
	public function load($cache = true)
	{
	    // load/cache all details
	    if ($this->detailsCached && $cached) {
			return;
	    }


		$detailQuery = $this->db->prepare("SELECT * FROM account WHERE account_id = ?");
		
		if (!($detailQuery->execute(array($this->id)) &&
		    $detailResult = $detailQuery->fetch(PDO::FETCH_ASSOC))) {
		    return;
		}
		
        $this->detailsCached = true;
    	$this->group = (int)$detailResult['group_id'];
	}

	/** */
	private $permOrder = array('ip', 'user', 'group');
	private function permissions()
	{
		$this->load();

		// table for permissions reason permission LEFT JOIN permission_reason
		
		$permQuery = $this->db->prepare("SELECT o.object_type AS otype, p.target_type AS ptype, o.name AS name, p.allow AS allow FROM permission AS p INNER JOIN object AS o ON p.object_id = o.object_id WHERE (p.target_type = 'group' AND p.id_from <= :groupid AND p.id_to >= :groupid) OR (p.target_type = 'user' AND p.id_from <= :userid AND p.id_to >= :userid) OR (p.target_type = 'ip' AND p.id_from <= :ip AND p.id_to >= :ip)");
		
		if (!$permQuery->execute(array(
			':ip' => (double)sprintf("%u", ip2long($_SERVER['REMOTE_ADDR'])),
			':userid' => $this->id,
			':groupid' => $this->group
		))) {
			return;
		}

		
		$permArray = array();
        while ($permRow = $permQuery->fetch(FETCH_ASSOC)) {
			$typeKey = array_search($permRow['ptype'], $this->permOrder, true);
			// Invalid permission type
			if ($typeKey === false) {
				continue;
			}

			$objType = $permRow['otype'];
			$permName = $permRow['name'];
			$permAllow = $permRow['allow'] == 1;
			
			// Combine allow and type into an integer
			$typeKeyInt = $typeKey * 2 + ($permAllow ? 1 : 0);

			if (!isset($permArray[$objType])) {
				$permArray[$objType] = array();
			}

			// Ignore if already overridden
			if (isset($permArray[$objType][$permName]) &&
			    $typeKeyInt >= $permArray[$objType][$permName]) {
			    continue;
			}
			
			// Create permission
			$permArray[$objType][$permName] = $typeKeyInt;
        }
        
        // Replace permission integers with booleans
        foreach ($permArray as $oType => $permInfo) {
			foreach ($permInfo as $permName => $permType) {
				$permArray[$oType][$permName] = ($typeKeyInt % 2) === 1;
			}
        }
	}


	private $permArray = false;
	
	public function can($permType, $name, $cache = true)
	{
	    if (($cache && $this->permArray === false)) {
	        $this->permArray = $this->permissions();
	    }
	    
	    return isset($this->permArray[$permType][$name]) &&
			$this->permArray[$permType][$name];
	}
};

class accountMutable extends account
{
	public function __construct(PDO $db, $accountId = false)
	{
	    parent::construct($db, $accountId);
	}

	public function create()
	{

	}
}

?>
