<?php
/**
 * Base Manager class version 2.0
 *
 * Using PDO and prepared statements to prevent SQL injection
 * and allow for greater database portability
 */
abstract class Manager{
    protected $table;
    protected $DBH;
    protected static $dbh;
    
    public function __construct(){
        if(!self::$dbh){
            $info = _global()->db;
            self::$dbh = new PDO("$info->db:host=$info->host;dbname=$info->dbname", $info->username, $info->password);
        }
        $this->DBH = self::$dbh;
    }
	
    /**
     * Validate form data
     */
	protected function validate($data){
		$info = (object)$this->validation();
		$assoc = array();
		foreach($data as $key => $value){
			$name = string::keyToName($key);
			$result = formAuth::check($info->$key, $value, $name);
			if($result){
				$assoc[] = $result;
			}
		}
		$GLOBALS["errors"] = $assoc;
		return $assoc ? false : true;
	}
	
    /**
     * This method is called for each row in a result set
     */
	abstract protected function prepare(&$row);
	
    /**
     * Convenience method to prepare an array of results
     */
	protected function prepareMany($many){
		if(!$many) return false;
		foreach($many as &$row){
			$this->prepare($row);
		}
		return $many;
	}
    
    /**
     * Prepare attributes, placeholders, and values
     * for a prepared statement
     */
    protected function prepare_data(array $data, array &$attributes = array(), array &$values = array()){
        if(!$data) return true; # return false on empty array
		foreach($data as $attr => $value){
            if(!preg_match('#[a-z_]+#', $attr)){
                throw new ManagerException("Invalid SQL attribute ($key)");
            }
            $attributes[] = $attr;
            $values[] = $value;
		}
        return true;
    }
    
    /**
     * Takes an array and turns it into a formatted string
     *
     * string format    replaces $k and $v with the array
     *                  key and value respectively
     */
    protected function arr_to_string(array $data, $delimiter = false, $format = false){
        $return = '';
        $count = 0;
        foreach($data as $key => $value){
            # add delimiter to string
            if($delimiter && $count){
                $return.= $delimiter;
            }
            if($format){
                $tmp = preg_replace('#\$k#', $key, $format);
                $tmp = preg_replace('#\$v#', $value, $tmp);
                $return.= $tmp;
            }
            else $return.= $value;
            $count++;
        }
        return $return;
    }
    
    /**
     * Removes and update keys from a data set
     * and stores them in a new array
     */
    protected function prepare_uk(array &$data, $pkeys, array &$assoc = array()){
        foreach($pkeys as $key){
            if(!$data[$key]){
                throw new ManagerException("SQL data does not contain key ($key)");
            }
            $assoc[$key] = $data[$key];
            unset($data[$key]);
        }
    }
	
	public function insert(array $data, $validate = true){
		# validate data
        if($validate && !$this->validate($data)) return false;
        
        # prepare data
        $attributes = $values = array();
        if(!$this->prepare_data($data, $attributes, $values)){
            throw new ManagerException("SQL insert requires attributes");
        }
        
        # turn arrays into formatted strings
		$attr = $this->arr_to_string($attributes, ',', '`$v`');
		$placeholders = $this->arr_to_string($attributes, ',', ':$v');
        
        # execute statement
        return $this->exec("INSERT INTO $this->table ($attr) VALUE ($placeholders)", $data);
	}
	
	public function update(array $data, array $uk = array('id'), $validate = true){
        if(!$uk){
            throw new ManagerException("SQL update requires key(s) to update on");
        }
        
		# validate data
		if($validate && !$this->validate($data)) return false;
        
        # remove update keys from data
        $data_without_uk = $data;
        $ukeys = array();
        $this->prepare_uk($data_without_uk, $uk, $ukeys);
        
		# prepare data
        $attributes = $values = array();
        if(!$this->prepare_data($data_without_uk, $attributes, $values)){
            throw new ManagerException("SQL insert requires attributes");
        }
        
        # turn arrays into formatted strings for sql statement
        $set = $this->arr_to_string($attributes, ',', '`$v`=:$v');
        $where = $this->arr_to_string($uk, ' AND ', '`$v`=:$v');
        
        # execute statement
        return $this->exec("UPDATE $this->table SET $set WHERE $where", $data);
	}
	
	public function deleteBy(array $data){
		# validate data
		if($validate && !$this->validate($data)) return false;
        
		# prepare data
        $attributes = $values = array();
        if(!$this->prepare_data($data, $attributes, $values)){
            throw new ManagerException("SQL insert requires attributes");
        }
        
        # turn arrays into formatted strings for sql statement
        $where = $this->arr_to_string($attributes, ' AND ', '`$v`=:$v');
        
        # execute statement
        return $this->exec("DELETE FROM $this->table WHERE $where", $data);
	}
	
	public function findBy(array $data, $prepare = true){
		# prepare data
        $attributes = $values = array();
        if(!$this->prepare_data($data, $attributes, $values)){
            throw new ManagerException("SQL insert requires attributes");
        }
        
        # turn arrays into formatted strings for sql statement
        $where = $this->arr_to_string($attributes, ' AND ', '`$v`=:$v');
        
        # execute statement
        return $this->fetch_single("SELECT * FROM $this->table WHERE $where", $data, $prepare);
	}
	
	public function listBy(array $data, $prepare = true){
		# prepare data
        $attributes = $values = array();
        if(!$this->prepare_data($data, $attributes, $values)){
            throw new ManagerException("SQL insert requires attributes");
        }
        
        # turn arrays into formatted strings for sql statement
        $where = $this->arr_to_string($attributes, ' AND ', '`$v`=:$v');
        
        #check if order is specified
        if($this->orderBy){
            $order = ' ORDER BY ' . $this->arr_to_string($this->orderBy, ',', '`$k` $v');
        }
        
        # execute statement
        return $this->fetch_many("SELECT * FROM $this->table WHERE $where $order", $data, $prepare);
	}
	
	public function listAll($prepare = true){
        #check if order is specified
        if($this->orderBy){
            $order = ' ORDER BY ' . $this->arr_to_string($this->orderBy, ',', '`$k` $v');
        }
        
        # execute statement
        return $this->fetch_many("SELECT * FROM $this->table $order", NULL, $prepare);
	}
    
    protected function fetch_single($SQL, $data = NULL, $prepare = true){
        # execute statement
        $stmt = $this->DBH->prepare($SQL);
        if(!$stmt->execute($data)) return false;
        
        # set the fetch mode
        $stmt->setFetchMode(PDO::FETCH_OBJ);
         
        # prepare and return result
        if(!$row = $stmt->fetch()) return false;
        
        # insert row into result object
        $row = new ManagerResultObject($row);
        
        if($prepare){
            $this->prepare($row);
        }
        return $row;
    }
    
    protected function fetch_many($SQL, $data = NULL, $prepare = true){
        # execute statement
        if(!$stmt = $this->exec($SQL, $data)) return false;
        
        # set the fetch mode
        $stmt->setFetchMode(PDO::FETCH_OBJ);
         
        # prepare and return result set
        $results = array();
        while($row = $stmt->fetch()) {
            # insert row into result object
            $row = new ManagerResultObject($row);
            
            if($prepare){
                $this->prepare($row);
            }
            $results[] = $row;
        }
        return $results;
    }
    
    /**
     * Executes a SQL statement
     *
     * Returns PDO stmt Object
     */
    protected function exec($SQL, $data = NULL){
        $stmt = $this->DBH->prepare($SQL);
        if(!$stmt->execute($data)){
            $info = $stmt->errorInfo();
            throw new ManagerException($info[2], $info[1]);
        }
        return $stmt;
    }
    
    
}

class ManagerResultObject{
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

class ManagerException extends Exception{
}
