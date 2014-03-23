<?php
function run(){
	return run::singleton();
}
class run{
	private $cache = array();
	private static $obj;
	
	public static function singleton(){
		if(self::$obj) return self::$obj;
		return self::$obj = new run();
	}
	
	private function cache($key){
		if(isset($this->cache[$key])){
			return $this->cache[$key];
		}
		return false;
	}
	
	public function __call($name, $args){
		$name = strtolower($name);
		$name[0] = strtoupper($name[0]);
		$method = string::CamelToUnder($name);
		if($class = $this->cache($method)){
			return $class;
		}
		else{
			$name = "Run".$name;
			if(!class_exists($name)){
				throw new RuntimeException("Class '$name' does not exist");
			}
			$class = new $name();
			$this->cache[$method] = $class;
			return $class;
		}
	}
	
	public function __get($name){
		return $this->__call($name, array());
	}
}
class RunManager extends RunObj{
	protected function dir(){
		return MANAGER_DIR;
	}
	protected function prefix(){
		return "Manager";
	}
}
class RunController extends RunObj{
	protected function dir(){
		return CONTROLLER_DIR;
	}
	protected function prefix(){
		return "Controller";
	}
}
abstract class RunObj{
	protected $cache = array();
	
	/*
	 * @return string directory
	 */
	abstract protected function dir();
	
	/*
	 * @return string prefix
	 */
	abstract protected function prefix();
	
	protected function cache($key){
		if(isset($this->cache[$key])){
			return $this->cache[$key];
		}
		return false;
	}
	
	public function __call($name, $args){
		$name[0] = strtoupper($name[0]);
		$filename = string::CamelToUnder($name);
		if($class = $this->cache($filename)){
			return $class;
		}
		require_once($this->dir()."/$filename.php");
		$class_name = $this->prefix().$name;
		$class = new $class_name();
		$this->cache[$filename] = $class;
		return $class;
	}
	
	public function __get($name){
		return $this->__call($name, array());
	}
}
