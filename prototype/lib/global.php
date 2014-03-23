<?php
function _global()
{
	return GlobalObj::singleton();
}
class GlobalObj
{
	private static $obj;
	public static function singleton(){
		if(self::$obj) return self::$obj;
		return self::$obj = new GlobalObj();
	}
	
	public function __set($key, $value){
		$this->$key = $value;
	}
	
	public function __get($key){
		return $this->$key;
	}
}