<?php
class ManagerTask extends Manager{
	protected function database(){
		return _global()->mysql->database;
	}
	protected function table(){
		return "task";
	}
	protected function order(){
		return array(
			"end"=>"DESC",
            "start"=>"DESC"
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
	}
	
	protected function validation(){
		return array(
			"project_id"=>
				formAuth::Required,
			"phase_id"=>
				formAuth::Required,
			"name"=>
				formAuth::Required,
			"start"=>
				formAuth::Required,
			"end"=>
				formAuth::Required
		);
	}
}
