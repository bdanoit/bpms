<?php
class ManagerTask extends Manager{
    const day = 86400;
	protected function database(){
		return _global()->mysql->database;
	}
	protected function table(){
		return "task";
	}
	protected function order(){
		return array(
			"end"=>"ASC",
            "start"=>"DESC"
		);
	}
    
    public final function listByProjectUser($project_id, $user_id, $prepare = true){
        $SQL = <<<SQL
SELECT * FROM
    {$this->database()}.{$this->table()}
WHERE
    complete = 0
AND
    id IN (SELECT task_id FROM {$this->database()}.task_assigned_to WHERE project_id = $project_id AND user_id = $user_id)
{$this->createOrderBy()}
SQL;
        $results = self::query_rows($SQL);
        if(!$results || !$prepare) return $result;
        foreach($results as $result){
			$this->prepare($result);
		}
        return $results;
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
            $task_id = self::insert_id();
            foreach($obj->assigned_to as $user_id){
                if(!self::query("INSERT INTO {$this->database()}.{$this->table()}_assigned_to (project_id, task_id, user_id) VALUES ($project_id, $task_id, $user_id);")){
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
                self::query("INSERT INTO {$this->database()}.{$this->table()}_assigned_to (project_id, task_id, user_id) VALUES ($project_id, $task_id, $user_id);");
            }
            if($obj->assigned_to){
                $ids = implode(',', $obj->assigned_to);
                self::query("DELETE FROM {$this->database()}.{$this->table()}_assigned_to WHERE project_id = $project_id AND task_id = $task_id AND user_id NOT IN ($ids)");
            }
            $status = 1;
        }
        return $status;
    }
    
    /**
    * mark task as complete
    **/
    public final function complete($project_id, $task_id){
        $result = parent::update(array(
            "project_id"=>$project_id,
            "id"=>$task_id,
            "complete"=>1
        ));
        var_dump($result);
        return $result;
    }
    
    public final function assigned_to($project_id, $task_id){
        $SQL = <<<SQL
        SELECT u.id, u.name
        FROM {$this->database()}.{$this->table()}_assigned_to a
        LEFT JOIN {$this->database()}.user u
        ON
            a.user_id = u.id
        WHERE
            a.project_id = $project_id
        AND
            a.task_id = $task_id
SQL;
        if($result = self::query_rows($SQL)){
        }
        return $result;
    }
    
	protected final function prepare(&$row){
        $row->assigned_to = function($row){ return run()->manager->task->assigned_to($row->project_id, $row->id); };
        $start = $row->start = strtotime($row->start);
        $end = $row->end = strtotime($row->end);
        $now = time();
        $row->start_date = date('Y-m-d', $start);
        $row->end_date = date('Y-m-d', $end);
        $row->start_time = date('H:i', $start);
        $row->end_time = date('H:i', $end);
        $row->start_pretty = $this->date_pretty($start);
        $row->end_pretty = $this->date_pretty($end);
        if($now > $end) $row->is_late = true;
        $day = self::day;
        if(($end/$day - $now/$day) < 3) $row->is_due_soon = true;
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
