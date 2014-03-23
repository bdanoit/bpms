<?php
abstract class Manager extends SQL{
	abstract protected function database();
	abstract protected function table();
	abstract protected function order();
	abstract protected function validation();
	
	protected $page_info;
	protected $no_limit;
	protected $limit;
	
	protected function validate($data, $insert = true){
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
	
	protected function prepare($row){}
	
	protected function prepareMany($many){
		if(!$many) return false;
		foreach($many as &$row){
			$this->prepare($row);
		}
		return $many;
	}
	
	public function createQuery($sql){
		$results = self::query_rows($sql);
		return $this->prepareMany($results);
	}
	
	public function insert(array $data, $validate = true){
		if($validate && !$this->validate($data)) return false;
		$keys = array();
		$types = '';
		$handles = array();
		$values = array();
		$questions = array();
		$num = 0;
		foreach($data as $key => $value){
			if($value === '' || $value === null || $value === false) continue;
			switch(true)
			{
			case is_resource($value):
				$types.= 'b';
					$handles[$num] = $value;
					$values[] = NULL;
				break;
			case is_numeric($value):
				if(preg_match('#^[0-9]+$#', $value)){
					$types.= 'i';
					$values[] = (int)$value;
				}
				else{
					$types.= 'd';
					$values[] = (float)$value;
				}
				break;
			case is_string($value):
				$types.= 's';
				$values[] = $value;
				break;
			default:
				continue 2;
			}
			$keys[] = "`$key`";
			$questions[] = '?';
			$num++;
		}
		if(!$values) return false;
		$keys = implode(",", $keys);
		$questions = implode(",", $questions);
		$this->journal("update", $this->class_name()." has been added.", $id);
		$stmt = self::mysqli()->prepare("INSERT INTO ".$this->database().".".$this->table()." ($keys) VALUES ($questions);");
		array_unshift($values, $types);
		$tmp = array();
        foreach($values as $key => $value) $tmp[$key] = &$values[$key];
		$this->call_user_func_array(array($stmt, 'bind_param'), $tmp);
		foreach($handles as $i => $handle){
			while (!feof($handle)) {
				$stmt->send_long_data($i, fread($handle, 8192));
			}
			fclose($handle);
		}
		$result = $stmt->execute();
		if($stmt->error)
			throw new SQLException($stmt->error);
		return $result;
	}
	
	private function call_user_func_array(array $info, array $params){
		list($obj, $method) = $info;
		$string = 'return $obj->{$method}(';
		foreach($params as $i => $param){
			if($i) $string.= ',';
			$string.= '$params['.$i.']';
		}
		$string.= ');';
		eval($string);
	}
	
	public function update(array $data, $validate = true){
		if($validate && !$this->validate($data)) return false;
		$set = "";
		$id = $data["id"];
		unset($data["id"]);
		foreach($data as $key => $value){
			if($value === null || $value === false){
				$value = 'null';
			}
			else {
				$value = is_numeric($value) ? $value : "'".self::escape($value)."'";
			}
			$set.= $set ? "," : "";
			$set.= "`$key`=$value";
		}
		if(!$set) return true;
		$this->journal("update", $this->class_name()." $id has been updated.", $id);
		return self::query("UPDATE ".$this->database().".".$this->table()." SET $set WHERE id = $id");
	}
	
	public function delete($id){
		return $this->deleteBy(array("id"=>$id));
	}
	
	public function deleteBy(array $params){
		$results = self::query("DELETE FROM ".$this->database().".".$this->table()." WHERE ".$this->createWhere($params));
		return $results;
	}
	
	public function findBy(array $params, $prepare = true){
		$columns = $this->columns();
		$columns = $columns ? implode(',', $columns) : '*';
		$result = self::query_single_row("SELECT $columns FROM ".$this->database().".".$this->table()." ".$this->join_sql()." WHERE ".$this->createWhere($params)." LIMIT 1");
		if(!$result) return false;
		if($prepare) $this->prepare($result);
		return $result;
	}
	
	public function listBy(array $params, $prepare = true){
		$columns = $this->columns();
		$columns = $columns ? implode(',', $columns) : '*';
		$results = self::query_rows("SELECT $columns FROM ".$this->database().".".$this->table()." ".$this->join_sql()." WHERE ".$this->createWhere($params)." ".$this->createOrderBy()." ".$this->createLimit());
		if(!$results) return false;
		if($prepare) foreach($results as $result){
			$this->prepare($result);
		}
		return $results;
	}
	
	public function listAll($prepare = true){
		$columns = $this->columns();
		$columns = $columns ? implode(',', $columns) : '*';
		$results = self::query_rows("SELECT $columns FROM ".$this->database().".".$this->table()." ".$this->join_sql()." ".$this->createOrderBy()." ".$this->createLimit());
		if(!$results) return false;
		if($prepare) foreach($results as $result){
			$this->prepare($result);
		}
		return $results;
	}
	
	protected function createOrderBy(){
		$info = $this->order();
		if(!$info) return "";
		$string = "";
		foreach($info as $column => $order){
			if($string) $string.= ", ";
			$string.= "$column $order";
		}
		return " ORDER BY $string";
	}
	
	public function countBy(array $params){
		$results = self::query_single_row("SELECT count(id) AS count FROM ".$this->database().".".$this->table()." WHERE ".$this->createWhere($params));
		return ($results) ? $results->count : null;
	}
	
	public function limit($limit){
		if(!is_integer($limit)) return $this;
		$this->limit = $limit;
		return $this;
	}
	
	protected function createLimit(){
		if($this->no_limit) return "";
		if($limit = $this->limit){
			$this->limit = null;
			return "LIMIT $limit";
		}
		$info = $this->page_info();
		if(!$info) return "";
		$start = $info->current_page*$info->results_per_page-$info->results_per_page;
		$end = $info->results_per_page;
		return "LIMIT $start, $end";
	}
	
	/**
	 * Override this function and return an associative
	 * array containing:
	 * 
	 * @return array containing results_per_page, current_page
	 */
	protected function page_info(){
		return null;
	}
	
	protected function journal($subtype, $entry, $ref_id = null){
		//journalling off!
		return false;
	}
	
	protected function columns(){
		return false;
	}
	
	protected function createWhere($params, $prefix = ''){
		if($prefix) $prefix.= '.';
		$where = "";
		foreach($params as $key => $value){
			$where.= $where ? " AND " : "";
			switch(true){
				case is_null($value):
					$where.= "$prefix`$key` is null";
					break;
				case is_numeric($value):
					$where.= "$prefix`$key`=$value";
					break;
				case is_array($value):
					$tmp = array();
					foreach($value as $item){
						$tmp[] = is_numeric($item) ? $item : "'".self::escape($item)."'";
					}
					$where.= "$prefix`$key` IN (".implode(',', $tmp).")";
					break;
				default:
					$where.= "$prefix`$key`='".self::escape($value)."'";
					break;
			}
			
		}
		return $where;
	}
	
	protected function class_name(){
		return preg_match('#[a-zA-Z0-9]+$#', get_class($this), $match) ? $match[0] : "";
	}
	
	protected function join_sql(){
		return "";
	}
}