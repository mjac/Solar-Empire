<?php

class config_url
{
	public $base = '';
	public $style = '';
	public $domain = '';
	
	public function initiate()
	{
		self::$url = configUrl;
	}
}

class config_path
{
	public $interface;
	public $include;
	public $style;
	
	public $Savant3;
	
	public function __construct()
	{
		$this->interface = dirname(__FILE__);
		$this->include = $this->interface . '/inc';
		$this->style = $this->interface . '/style';
		$this->template = $this->interface . '/tpl';
	}
}

class config_db
{
	public $type = 'mysql';
	public $database = 'localhost';
	public $port = false;
	public $username = 'syswars';
	public $password = 'syswars';
}


class config
{
	public static $url;
	public static $path;
	public static $db;

	private function __construct()
	{
	}
	
	public function initiate()
	{
		self::$url = new config_url;
		self::$path = new config_path;
		self::$db = new config_db;
	}
}

config::initiate();

?>
