<?php
class ManagerTaskEmailAlerts extends Manager{
    protected $table = 'task_email_alerts';
    
	protected final function prepare(&$row){
        $end = strtotime($row->end);
        $when = strtotime($when);
        
        # pretty end date 
        $row->end_pretty = date('F j, Y \a\t h:ia', $end);
    }
    
    /**
     * Finds email alerts what are older than NOW (Query 4a)
     */
    public final function listOlder($timestamp = NULL){
        if(!$timestamp) $timestamp = time();
        $time = date('Y-m-d H:i:s', $timestamp);
        $SQL = <<<SQL
        SELECT 
            a.project_id,
            a.task_id,
            a.`when`,
            a.`type`,
            t.end,
            t.name as task_name,
            u.name as user_name,
            u.email,
            p.name as project_name
        FROM {$this->table} a
        LEFT JOIN task t
            ON a.project_id = t.project_id AND a.task_id = t.id
        LEFT JOIN task_assigned_to w
            ON w.project_id = t.project_id AND w.task_id = t.id
        LEFT JOIN user u
            ON w.user_id = u.id
        LEFT JOIN project p
            ON a.project_id = p.id
        WHERE '$time' >= `when`
SQL;
        return $this->fetch_many($SQL, NULL);
    }
	
	protected function validation(){
		return array();
	}
}
