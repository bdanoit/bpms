<?php
class ManagerProject extends Manager{
	protected function database(){
		return _global()->mysql->database;
	}
	protected function table(){
		return "project";
	}
	protected function order(){
		return array(
			"created"=>"ASC"
		);
	}
    
	protected final function prepare(&$row){
		$row->started_on = date('F d, Y', strtotime($row->created));
	}
	
	protected function validation(){
		return array(
			"name"=>
				formAuth::Required,
			"creator_id"=>
				formAuth::Required,
			"created"=>
				formAuth::Required
		);
	}
}
