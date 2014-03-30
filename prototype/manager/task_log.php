<?php
class ManagerTaskLog extends Manager{
    protected $table = 'task_log';
    protected $orderBy = array("start"=>"DESC");
    
    protected function sanitize(&$data){
        $vars = (object)$data;
        unset(
            $data['start_date'],
            $data['start_time'],
            $data['end_date'],
            $data['end_time']
        );
        $data['start'] = date('Y-m-d H:i:s', strtotime("$vars->start_date $vars->start_time"));
        $data['end'] = date('Y-m-d H:i:s', strtotime("$vars->end_date $vars->end_time"));
    }
    
    public final function insert($data){
        $this->sanitize($data);
        return parent::insert($data);
    }
    
    public final function update($data){
        $this->sanitize($data);
        return parent::update($data);
    }
    
	protected final function prepare(&$row){
        $row->user = function($row){
            return run()->manager->user->findBy(array("id"=>$row->user_id));
        };
        
        # get start and end date timestamps
        $start = strtotime($row->start);
        $end = strtotime($row->end);
        
        # format for form data
        $row->start_date = date('Y-m-d', $start);
        $row->start_time = date('H:i', $start);
        $row->end_date = date('Y-m-d', $end);
        $row->end_time = date('H:i', $end);
        
        # pretty start date 
        $row->pretty_date = date('m/d/Y h:ia', $start);
        
        # time
        $row->seconds = $end - $start;
        $row->minutes = ceil($row->seconds/60);
        if($row->minutes > 60){
            $row->hours = floor($row->minutes/60);
            $row->minutes = $row->minutes%60;
        }
        $row->note = htmlentities($row->note);
	}
	
	protected function validation(){
		return array(
			"project_id"=>
				formAuth::Required,
			"task_id"=>
				formAuth::Required,
			"user_id"=>
				formAuth::Required,
			"start"=>
				formAuth::Required,
			"end"=>
				formAuth::Required
		);
	}
}
