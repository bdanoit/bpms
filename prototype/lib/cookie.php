<?php
function cookie(){
	return cookie::singleton();
}
class cookie{
	private static $obj;
	private static $cache = array();
	private function cache($key){
		if($class = self::$cache[$key]){
			return $class;
		}
		return false;
	}
	
	public static function singleton(){
		if(self::$obj) return self::$obj;
		return self::$obj = new cookie();
	}
	
	public function __call($key, $args){
		$key = string::CamelToUnder($key);
		if($obj = self::cache($key)){
			return $obj;
		}
		self::$cache[$key] = new CookieObject($key);
		return self::$key($key);
	}
	
	public function __get($key){
		return $this->__call($key, array());
	}
}
class CookieObject{
	private $key;
	private $expires = null;
	private $value = null;
	private $path = "/";
	private $secure = null;
	private $domain = null;
	
	
	public function __construct($key){
		$this->key = $key;
		$value = $_COOKIE[$this->key] ? $_COOKIE[$this->key] : false;
		$value = $this->parse_expiry($value);
		if($json = json_decode($value, true)) $value = $json;
		$this->value = $value;
	}
	
	private function parse_expiry($value){
		$regex = '#^([0-9]+)\|#';
		preg_match($regex, $value, $match);
		$this->expires = $match[1];
		return preg_replace($regex, "", $value);
	}
	
	public function value(){
		return is_array($this->value) ? (object)$this->value : $this->value;
	}
	
	public function set($value, $expires = null){
		$expires = $expires ? $expires : ($this->expires ? $this->expires : time() + 3600);
		$this->value = $value;
		if(is_array($value)){
			$value = json_encode($value);
		}
		setcookie($this->key, $expires."|".$value, $expires, $this->path, $this->domain, $this->secure);
		return $this;
	}
	
	public function update($value){
		if(!is_array($value) || !is_array($this->value)) $this->set($value);
		$current = $this->value;
		$this->set(array_merge($current, $value));
		return $this;
	}
	
	public function delete(){
		setcookie($this->key, "", time() - 3600, $this->path);
		$this->value = null;
		return $this;
	}
	
	public function expires($value){
		$this->expires = $value;
		return $this;
	}
	
	public function path($value){
		$this->path = $value;
		return $this;
	}
	
	public function secure($value){
		$this->secure = $value;
		return $this;
	}
	
	public function domain($value){
		$this->domain = $value;
		return $this;
	}
}