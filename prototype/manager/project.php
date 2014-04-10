<?php
class ManagerProject extends Manager{
    protected $table = 'project';
    
    public final function listByUser($id, $prepare = true){
        $SQL = <<<SQL
SELECT * FROM
    $this->table
WHERE
    id IN (SELECT project_id FROM project_permission WHERE user_id = $id GROUP BY user_id, project_id);
SQL;
        return $this->fetch_many($SQL);
    }
    
    public final function insert(array $data, $prepare = true){
        $obj = (object)$data;
        unset($data['members']);
        
        $this->DBH->beginTransaction();
        try{
            parent::insert($data);
            $id = $this->DBH->lastInsertId();
            if($obj->members){
                $members = explode(',', $obj->members);
                run()->manager->projectInvite->insertMany($id, $members);
            }
        }
        catch(ManagerException $exc){
            $GLOBALS["errors"][] = (object)array(
                "code"=>$exc->getCode(),
                "message"=>$exc->getMessage()
            );
            $this->DBH->rollback();
            return false;
        }
        
        $this->DBH->commit();
        return $id;
    }
    
	protected final function prepare(&$row){
		$row->started_on = date('F d, Y', strtotime($row->created));
        $row->name = strtoupper($row->name);
        $row->user = function() use ($row) { return run()->manager->user->findBy(array("id"=>$row->creator_id)); };
        call_user_func(array( $row, 'user' ));
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
