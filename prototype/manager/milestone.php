<?php
class ManagerMilestone extends Manager{
    protected $table = 'phase';
    
	protected final function prepare(&$row){
        $end = $row->end = strtotime($row->end);
        $now = time();
        $row->end_date = date('Y-m-d', $end);
        $row->end_time = date('H:i', $end);
        $row->end_pretty = date('F j, Y', $end);
        $row->last = function() use ($row){
            return run()->manager->milestone->findLastByProject($row->project_id, $row->end);
        };
	}
	
    public final function insert(array $data){
        $this->beforeInsert($data);
        return parent::insert($data);
    }
	
    public final function update(array $data, array $uk = array('project_id', 'id')){
        $this->beforeInsert($data);
        return parent::update($data, $uk);
    }
    
    /**
     * Finds nearest milestone ending after given unix timestamp
     */
    public final function findNextByProject($project_id, $timestamp){
        $datetime = date("Y-m-d H:i:s", $timestamp);
        $SQL = <<<SQL
        SELECT *
        FROM $this->table 
        WHERE project_id = $project_id 
        AND end > '$datetime' 
        ORDER BY end ASC
        LIMIT 1
SQL;
        return $this->fetch_single($SQL);
    }
    
    /**
     * Finds nearest milestone ending before given unix timestamp
     */
    public final function findLastByProject($project_id, $timestamp){
        $datetime = date("Y-m-d H:i:s", $timestamp);
        $SQL = <<<SQL
        SELECT *
        FROM $this->table 
        WHERE project_id = $project_id 
        AND '$datetime' > end 
        ORDER BY end DESC
        LIMIT 1
SQL;
        return $this->fetch_single($SQL, NULL);
    }
    
    protected function beforeInsert(array &$data){
        $obj = (object)$data;
        unset($data["end_date"], $data["end_time"]);
        $data['end'] = date('Y-m-d H:i:s', strtotime("$obj->end_date $obj->end_time"));
    }
    
	protected function validation(){
		return array(
			"name"=>
				formAuth::Required
		);
	}
}
