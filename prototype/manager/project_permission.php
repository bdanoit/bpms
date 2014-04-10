<?php
class ManagerProjectPermission extends Manager{
    protected $table = 'project_permission';
    
    public final function insert($project_id, $user_name, array $permissions){
        return self::update($project_id, $user_name, $permissions, true);
    }
    
    public final function update($project_id, $user_name, array $permissions, $insert = false){
        $user = run()->manager->user->findBy(array("name"=>$user_name));
        $project = run()->manager->project->findBy(array("id"=>$project_id));
        
        //if this is an insert, and user is already a member
        if($insert && run()->manager->projectPermission->findBy(array("user_id"=>$user->id,"project_id"=>$project_id))){
			$GLOBALS["errors"][] = (object)array(
				"code"=>dechex(0),
				"message"=>"User ($user_name) is already a member of this project."
			);
            return false;
        }
        
        //if this is an insert, and user is already invited
        if($insert && run()->manager->projectInvite->findBy(array("user_id"=>$user->id,"project_id"=>$project_id))){
			$GLOBALS["errors"][] = (object)array(
				"code"=>dechex(0),
				"message"=>"User ($user_name) is already invited."
			);
            return false;
        }
        
        //If the user does not exist
        if(!$user){
			$GLOBALS["errors"][] = (object)array(
				"code"=>dechex(0),
				"message"=>"User ($user_name) does not exist."
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
                catch(ManagerException $exc){
                    //Ignore duplicate entry exception
                    if($exc->getCode() == 1062){}
                    else{
                        $GLOBALS["errors"][] = (object)array(
                            "code"=>$exc->getCode(),
                            "message"=>$exc->getMessage()
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
                try{
                    parent::deleteBy($data);
                }
                catch(ManagerException $exc){
                    $GLOBALS["errors"][] = (object)array(
                        "code"=>$exc->getCode(),
                        "message"=>$exc->getMessage()
                    );
                    return false;
                }
            }
        }
        return true;
    }
    
    public final function deleteByProjectUser($project_id, $user_id){
        $project = run()->manager->project->findBy(array("id"=>$project_id));
        if($project->creator_id == $user_id){
            $GLOBALS["errors"][] = (object)array(
                "code"=>dechex(0),
                "message"=>"DELETE canceled, project owner permissions cannot be revoked."
            );
            return false;
        }
        return parent::deleteBy(array(
            "project_id"=>$project_id,
            "user_id"=>$user_id
        ));
    }
    
	protected function prepare(&$row){
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
