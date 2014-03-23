<?php
abstract class content{
	private $filename;
	private $path = '';
	private $params = array();
	
	abstract protected function dir();
	
	public function __toString(){
        $errors = $GLOBALS["errors"];
		if($this->params) foreach($this->params as $__key => $__null){
			$$__key=$this->params[$__key];
		}
		ob_start();
		include($this->dir()."$this->path/$this->filename.phtml");
		$source = ob_get_contents();
		ob_end_clean();
		return $source;
	}
	
	public function __call($key, $args){
		if($args){
			list($params) = $args;
			if(is_array($params)) $this->params = $params;
		}
		$filename = string::CamelToUnder($key);
		if(!file_exists($this->dir().$this->path."/$filename.phtml")){
			$class = get_class($this);
			$class[0] = strtoupper($class[0]);
			$ExceptionClass = $class.'Exception';
			throw new $ExceptionClass("Could not find $class: $filename");
		}
		$this->filename = $filename;
		return $this;
	}
	
	public function __get($key){
		return $this->__call($key, array());
	}
	
	public function __path($path){
		if(is_string($path)) $this->path = $path;
		return $this;
	}
}
class ContentException{}