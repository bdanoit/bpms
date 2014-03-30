<?php
class ManagerPermission extends Manager{
    protected $table = 'permission';
    
    public final function listByProjectMember($project_id, $user_id){
        $SQL = <<<SQL
            SELECT *
            FROM $this->table
            WHERE id IN
                (SELECT permission_id FROM project_permission WHERE project_id = $project_id && user_id = $user_id)
SQL;
        return $this->fetch_many($SQL);
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
