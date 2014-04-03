<?php
class ManagerTask extends Manager{
    const day = 86400;
    
    protected $orderBy = array(
        "end"=>"ASC",
        "start"=>"ASC"
    );
    
    protected $table = 'task';
    
    public final function listByProjectUser($project_id, $user_id, $complete = 0, $prepare = true){
        $SQL = <<<SQL
        SELECT * FROM
            $this->table
        WHERE
            complete = $complete
        AND
            id IN (SELECT task_id FROM task_assigned_to WHERE project_id = $project_id AND user_id = $user_id)
        ORDER BY end ASC, start DESC
SQL;
        return $this->fetch_many($SQL);
    }
    
    /**
    * Returns 0 if task not inserted, -1 if users were not added properly, 1 on success
    **/
    public final function insert(array $data){
        $obj = (object)$data;
        unset($data["assigned_to"], $data["start_date"], $data["start_time"], $data["end_date"], $data["end_time"]);
        $data['start'] = date('Y-m-d H:i:s', strtotime("$obj->start_date $obj->start_time"));
        $data['end'] = date('Y-m-d H:i:s', strtotime("$obj->end_date $obj->end_time"));
        $status = 0;
        if(parent::insert($data)){
            $project_id = $obj->project_id;
            $task_id = $this->DBH->lastInsertId();
            foreach($obj->assigned_to as $user_id){
                if($this->exec("INSERT INTO {$this->table}_assigned_to (project_id, task_id, user_id) VALUES ($project_id, $task_id, $user_id);")){
                    $status--;
                }
            }
            if($status == 0) $status = 1;
        }
        return $status;
    }
    
    /**
    * Returns 0 if task not inserted, -1 if users were not added properly, 1 on success
    **/
    public final function update(array $data){
        $obj = (object)$data;
        unset($data["assigned_to"], $data["start_date"], $data["start_time"], $data["end_date"], $data["end_time"]);
        $data['start'] = date('Y-m-d H:i:s', strtotime("$obj->start_date $obj->start_time"));
        $data['end'] = date('Y-m-d H:i:s', strtotime("$obj->end_date $obj->end_time"));
        $status = 0;
        if(parent::update($data)){
            $project_id = $obj->project_id;
            $task_id = $data['id'];
            foreach($obj->assigned_to as $user_id){
                try{
                    $this->exec("INSERT INTO task_assigned_to (project_id, task_id, user_id) VALUES ($project_id, $task_id, $user_id);");
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
            if($obj->assigned_to){
                $ids = implode(',', $obj->assigned_to);
                $this->exec("DELETE FROM task_assigned_to WHERE project_id = $project_id AND task_id = $task_id AND user_id NOT IN ($ids)");
            }
            $status = 1;
        }
        return $status;
    }
    
    /**
    * mark task as complete
    **/
    public final function complete($project_id, $task_id, $complete = true){
        $result = parent::update(array(
            "project_id"=>$project_id,
            "id"=>$task_id,
            "complete"=>(int)$complete
        ));
        var_dump($result);
        return $result;
    }
    
    public final function assignedTo($project_id, $task_id, $prepare = true){
        $SQL = <<<SQL
        SELECT u.id, u.name
        FROM task_assigned_to a
        LEFT JOIN user u
        ON
            a.user_id = u.id
        WHERE
            a.project_id = $project_id
        AND
            a.task_id = $task_id
SQL;
        return $this->fetch_many($SQL, NULL, $prepare);
    }
    
    public final function projectOverviewBounds($project_id, $prepare = false){
        $SQL = <<<SQL
        SELECT
            MIN(start) AS start,
            MAX(end) AS end,
            AVG(UNIX_TIMESTAMP(start)) as start_avg,
            AVG(UNIX_TIMESTAMP(end)) as end_avg
        FROM task
        WHERE project_id = $project_id
SQL;
        $row = $this->fetch_single($SQL, NULL, false);
        if(!$row) return false;
        $row->start = strtotime($row->start);
        $row->end = strtotime($row->end);
        return $row;
    }
    
	protected final function prepare(&$row){
        $row->assigned_to = function($row){ return run()->manager->task->assignedTo($row->project_id, $row->id); };
        $row->assigned_to_me = function($row){
            if($row->assigned_to()){
                foreach($row->assigned_to() as $user){
                    if($user->id == auth::user()->id) return true;
                }
            }
        };
        $start = $row->start = strtotime($row->start);
        $end = $row->end = strtotime($row->end);
        $now = time();
        $row->start_date = date('Y-m-d', $start);
        $row->end_date = date('Y-m-d', $end);
        $row->start_time = date('H:i', $start);
        $row->end_time = date('H:i', $end);
        $row->start_pretty = $this->date_pretty($start);
        $row->end_pretty = $this->date_pretty($end);
        if(!$row->complete){
            if($now > $end) $row->is_late = true;
            $day = self::day;
            if(($end/$day - $now/$day) < 3) $row->is_due_soon = true;
        }
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
				formAuth::Required,
			"assigned_to"=>
				formAuth::Required
		);
	}
    
    private function date_pretty($ts){
        $format = 'F dS, Y';
        $front = array();
        $back = array();
        if(date('Y') == date('Y', $ts)){
            $format = 'F dS';
            if((int)($ts/self::day) - (int)(time()/self::day >= 7)){
                $format = 'F d \a\t g:ia';
                $cd = date('d');
                $td = date('d', $ts);
            }
            if(date('m') == date('m', $ts)){
                $format = 'F d\<\s\u\p\>S\</\\s\u\p\> \a\t g:ia';
                if(date('W') != date('W', $ts)){
                    //$front[] = "Last ";
                }
                else{
                    $format = 'l \a\t g:ia';
                }
                if(date('d') == date('d', $ts)){
                    $format = '\T\o\d\a\y \a\t g:ia';
                }
            }
        }
        return implode(' ', $front).date($format, $ts).implode(' ', $back);
    }
}
