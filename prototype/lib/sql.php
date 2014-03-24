<?php
class SQL{
	private static $lastQuery = "";
	
	protected static $mysqli;
	
	public static function lastError(){
		return self::mysqli()->error;
	}
	
	public static function lastQuery(){
		return self::$lastQuery;
	}
	
	protected static function mysqli(){
		if(!self::$mysqli){
			$info = _global()->mysql;
			self::$mysqli = new MySQLi($info->host, $info->username, $info->password, $info->dbname, $info->port, $info->socket);
			self::$mysqli->real_query("SET NAMES 'utf8'");
		}
		return self::$mysqli;
	}
	
	protected static function escape($string){
		return self::mysqli()->real_escape_string(trim($string));
	}
	
	protected static function query_rows($sql){
		self::$lastQuery = $sql;
		if(!self::query($sql)){
			return false;
		}
		
		$mysqli = self::mysqli();
		
		$result_set = array();
		if($result = $mysqli->store_result()){
			while($row = $result->fetch_assoc()){
				$result_set[] = new SQLResultObject($row);
			}
			return $result_set;
		}
		
		return false;
	}
	
	protected static function query($sql){
		self::$lastQuery = $sql;
		$mysqli = self::mysqli();
		$mysqli->real_query($sql);
		
		if($mysqli->errno){
			return false;
		}
		
		return true;
		
	}
	
	protected static function query_single_row($sql){
		$result = self::query_rows($sql);
		return $result ? $result[0] : false;
	}
	
	protected static function query_row_exists($sql){
		$row = self::query_single_row($sql);
		return $row ? true : false;
	}
	
	protected static function insert_id(){
		return self::$mysqli ? self::mysqli()->insert_id : false;
		
	}
}

class SQLResultObject{
	private $functions = array();
	public function __construct($data){
		foreach($data as $key => $value){
			$this->$key = $value;
		}
	}
	
	public function __set($key, $value){
		if(!is_string($value) && is_callable($value)){
			$this->functions[$key] = $value;
			return;
		}
		$this->$key = $value;
	}
	
	public function __get($key){
		return $this->data[$key];
	}
	
	public function __call($key, $args){
		$func = $this->functions[$key];
		if(!is_callable($func)) return null;
		$args = array_merge(array($this), $args);
		return call_user_func_array($func, $args);
	}
}

class SQLException extends Exception{
    public final function message(){
        return $this->message;
    }
    public final function errno(){
        return $this->errno;
    }
}