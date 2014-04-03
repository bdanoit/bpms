<?php
class ManagerMilestone extends Manager{
    protected $table = 'phase';
    
	protected final function prepare(&$row){
        $end = $row->end = strtotime($row->end);
        $now = time();
        $row->end_date = date('Y-m-d', $end);
        $row->end_time = date('H:i', $end);
        $row->end_pretty = date('F j, Y', $end);
	}
	
    public final function insert(array $data){
        $this->beforeInsert($data);
        return parent::insert($data);
    }
	
    public final function update(array $data, array $uk = array('project_id', 'id')){
        $this->beforeInsert($data);
        return parent::update($data, $uk);
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
