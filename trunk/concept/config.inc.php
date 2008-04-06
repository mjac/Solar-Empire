<?php
error_reporting(E_ALL | E_STRICT);



class my_config_url extends config_url
{

}

class my_config_path extends config_path
{

}

class my_config_db extends config_db
{

}

class config_url
{
	public $base;
	public $style;
	public $domain;
	public $self;
	
	public function __construct()
	{
		$this->self = $_SERVER['SCRIPT_NAME'];
		$this->base = dirname($this->self);
		$this->domain = $_SERVER['HTTP_HOST'];
		$this->style = $_SER
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
	
	public static function initiate()
	{
		self::$url = new my_config_url;
		self::$path = new my_config_path;
		self::$db = new my_config_db;
	}
}

config::initiate();

?>
