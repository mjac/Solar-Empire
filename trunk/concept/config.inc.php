<?php

class config
{
	public static $url;
	public static $path;
	public static $db;
	public static $security;

	public function initiate()
	{
		self::$url = configUrl;
	}
}


class configUrl
{

}

class configPath
{

}

class configDb
{

}

class configLib
{
	public static $Savant3;
	public static $SDA;
}

class configSecurity
{

}

config::initiate();

?>