<?php
class ManagerPermission extends Manager{
	protected function database(){
		return _global()->mysql->database;
	}
	protected function table(){
		return "permission";
	}
	protected function order(){
		return array(
			"id"=>"ASC"
		);
	}
    
    public final function listByProjectMember($project_id, $user_id){
		$columns = $this->columns();
		$columns = $columns ? implode(',', $columns) : '*';
        $WHERE = "id IN (SELECT permission_id FROM ".$this->database().".project_permission WHERE project_id = $project_id && user_id = $user_id)";
        $SQL = "SELECT $columns FROM ".$this->database().".".$this->table()." ".$this->join_sql()." WHERE $WHERE ".$this->createOrderBy()." ".$this->createLimit();
        
		$results = self::query_rows($SQL);
		if(!$results) return false;
		if($prepare) foreach($results as $result){
			$this->prepare($result);
		}
		return $results;
    }
    
	protected final function prepare(&$row){
	}
	
	protected function validation(){
		return array(
			"name"=>
				formAuth::Required
		);
	}
}
