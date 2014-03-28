<?php
class ManagerProject extends Manager{
	protected function database(){
		return _global()->mysql->database;
	}
	protected function table(){
		return "project";
	}
	protected function order(){
		return array(
			"created"=>"ASC"
		);
	}
    
    public final function listByUser($id, $prepare = true){
        $SQL = <<<SQL
SELECT * FROM
    {$this->database()}.{$this->table()}
WHERE
    id IN (SELECT project_id FROM {$this->database()}.project_permission WHERE user_id = $id GROUP BY user_id, project_id);
SQL;
        $results = self::query_rows($SQL);
        if(!$results || !$prepare) return $result;
        foreach($results as $result){
			$this->prepare($result);
		}
        return $results;
    }
    
	protected final function prepare(&$row){
		$row->started_on = date('F d, Y', strtotime($row->created));
        $row->name = strtoupper($row->name);
        $row->user = function($row){ return run()->manager->user->findBy(array("id"=>$row->creator_id)); };
	}
	
	protected function validation(){
		return array(
			"name"=>
				formAuth::Required,
			"creator_id"=>
				formAuth::Required,
			"created"=>
				formAuth::Required
		);
	}
}
