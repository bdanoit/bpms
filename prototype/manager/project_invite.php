<?php
require_once(__DIR__.'/project_permission.php');
class ManagerProjectInvite extends ManagerProjectPermission{
    protected $table = 'project_invite';
    
    public final function countByUser($user_id){
        $SQL = <<<SQL
        SELECT COUNT(user_id) AS `count`
        FROM $this->table 
        WHERE user_id = $user_id 
        AND permission_id = 1
SQL;
        $result = $this->fetch_single($SQL, NULL, false);
        return $result ? $result->count : false;
    }
    
    public final function insertMany($project_id, array $users, $permissions = NULL){
        if(!$permissions) $permissions = array(1);
       $SQL = "INSERT INTO $this->table (project_id, user_id, permission_id) VALUES ";
        foreach($users as $uidx => $user_id){
            foreach($permissions as $pidx => $pid){
                if($uidx || $idx) $SQL.= ',';
                $SQL.= "($project_id, $user_id, $pid)";
            }
        }
        return $this->exec($SQL);
    }
    
    public final function accept($project_id, $user_id){
        $this->DBH->beginTransaction();
        
        try{
            $this->exec("INSERT INTO project_permission SELECT * FROM $this->table WHERE project_id = $project_id AND user_id = $user_id");
            $this->exec("DELETE FROM $this->table WHERE project_id = $project_id AND user_id = $user_id");
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
        return true;
    }
    
    public final function decline($project_id, $user_id){
        return $this->deleteBy(array("project_id"=>$project_id,"user_id"=>$user_id));
    }
    
    public final function listByProject($project_id, $prepare = true){
        $SQL = <<<SQL
        SELECT u.*, GROUP_CONCAT(i.permission_id SEPARATOR ',') AS `permissions`
        FROM $this->table i 
        LEFT JOIN user u ON i.user_id = u.id 
        WHERE i.project_id = $project_id 
        GROUP BY i.user_id
SQL;
        return $this->fetch_many($SQL, NULL, $prepare);
        
    }
    
    public final function listByUser($user_id, $prepare = true){
        $SQL = <<<SQL
        SELECT p.*, GROUP_CONCAT(i.permission_id SEPARATOR ',') AS `permissions`
        FROM $this->table i 
        LEFT JOIN project p ON i.project_id = p.id 
        WHERE i.user_id = $user_id 
        GROUP BY i.project_id
SQL;
        return $this->fetch_many($SQL, NULL, $prepare);
    }
    protected function prepare(&$row){
        $row->started_on = date('F d, Y', strtotime($row->created));
        if($row->permissions){
            $row->permissions = explode(',', $row->permissions);
        }
        $row->creator = function() use ($row){
            if(!$row->creator_id) return false;
            return run()->manager->user->findBy(array("id"=>$row->creator_id));
        };
    }
}