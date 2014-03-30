<?php
class ManagerMilestone extends Manager{
    protected $table = 'milestone';
    
	protected final function prepare(&$row){
	}
	
	protected function validation(){
		return array(
			"name"=>
				formAuth::Required
		);
	}
}
