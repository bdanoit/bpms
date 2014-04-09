<?php
class ManagerUserVerify extends Manager{
    protected $table = 'user_verify';
	
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
