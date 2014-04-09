<?php
class ManagerUserForgot extends Manager{
    protected $table = 'user_forgot';
	
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
