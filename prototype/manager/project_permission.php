<?php
class ManagerProjectPermission extends Manager{
	protected function database(){
		return _global()->mysql->database;
	}
	protected function table(){
		return "project_permission";
	}
	protected function order(){
		return array(
			"project_id"=>"ASC"
		);
	}
    
    public final function insert($project_id, $user_email, array $permissions){
        return self::update($project_id, $user_email, $permissions, true);
    }
    
    public final function update($project_id, $user_email, array $permissions, $insert = false){
        $user = run()->manager->user->findBy(array("email"=>$user_email));
        $project = run()->manager->project->findBy(array("id"=>$project_id));
        if($insert && $this->findBy(array("user_id"=>$user->id,"project_id"=>$project_id))){
			$GLOBALS["errors"][] = (object)array(
				"code"=>dechex(0),
				"message"=>"User ($user_email) is already a member of this project."
			);
            return false;
        }
        if(!$user){
			$GLOBALS["errors"][] = (object)array(
				"code"=>dechex(0),
				"message"=>"User ($user_email) does not exist."
			);
            return false;
        }
        foreach($permissions as $id => $insert){
            $data = array(
                "project_id"=>$project_id,
                "user_id"=>$user->id,
                "permission_id"=>$id
            );
            if((bool)$insert){
                try{
                    parent::insert($data);
                }
                catch(SQLException $exc){
                    if(strstr($exc->message(), 'Duplicate entry')){
                    }
                    else{
                        $GLOBALS["errors"][] = (object)array(
                            "code"=>dechex(0),
                            "message"=>$exc->message()
                        );
                        return false;
                    }
                }
            }
            else{
                if($project->creator_id == $user->id){
                    $GLOBALS["errors"][] = (object)array(
                        "code"=>dechex(0),
                        "message"=>"DELETE canceled, project owner permissions cannot be revoked."
                    );
                    return false;
                }
                parent::deleteBy($data);
            }
        }
        return true;
    }
    
	protected final function prepare(&$row){
        $row->user = function($row){ return run()->manager->user->findBy(array("id"=>$row->user_id)); };
        $row->permissions = function($row){ return run()->manager->permission->listByProjectMember($row->project_id, $row->user_id); };
	}
	
	protected function validation(){
		return array(
			"name"=>
				formAuth::Required
		);
	}
}
