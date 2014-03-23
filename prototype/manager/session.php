<?php
class ManagerSession extends Manager{
	protected function database(){
		return _global()->mysql->database;
	}
	protected function table(){
		return "sessions";
	}
	protected function order(){
		return array(
			"created"=>"DESC"
		);
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
