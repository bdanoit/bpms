<?php
class ManagerSession extends Manager{
    protected $table = 'sessions';
	
	protected final function prepare(&$row){
	}
	
	protected function validation(){
		return array(
			"hash"=>
				formAuth::Required,
			"user_id"=>
				formAuth::Integer |
				formAuth::Required
		);
	}
}
