<?php
class http{
	public static function path(){
		return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	}
	
	public static function pathArray(){
		return explode('/', self::path());
	}
	
	public static function uri(){
		return self::path().vars::get();
	}
}
class vars{
	private static $obj;
	public static function get(){
		return new GETObj();
	}
	public static function post(){
		return new POSTObj();
	}
}

class GETObj extends VarsObj{
	public function __construct(){
		$this->params = $_GET;
	}
}

class POSTObj extends VarsObj{
	public function __construct(){
		$this->params = $_POST;
	}
}

class VarsObj{
	protected $params = array();
	public function __toString(){
		return '?'.http_build_query($this->params);
	}
	public function __set($key, $value){
		$this->params[$key] = $value;
	}
	public function __get($key){
		return $this->params[$key];
	}
	public function update(array $params){
		foreach($params as $key => $value){
			if($value === null){
				unset($this->params[$key]);
				continue;
			}
			$this->params[$key] = $value;
		}
		return $this;
	}
	public function set(array $params){
		$this->params = $params;
		return $this;
	}
}