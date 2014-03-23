<?php
class ManagerPage extends Manager{
	protected function database(){
		return _global()->mysql->database;
	}
	protected function table(){
		return "pages";
	}
	protected function order(){
		return array(
			"title"=>"ASC"
		);
	}
	
	protected final function prepare(&$row){
	}
	
	public final function insert($params){
		if(!$params["url"]){
			$params["url"] = string::Slug($params["title"]);
		}
		$params['content'] = $this->fixImageTags($params['content']);
		return parent::insert($params);
	}
	
	public final function update($params){
		if(!$params["url"]){
			$params["url"] = string::Slug($params["title"]);
		}
		$params['content'] = $this->fixImageTags($params['content']);
		return parent::update($params);
	}
	
	private function fixImageTags($string){
		return preg_replace_callback('#<img.+?src="([^"]+)".+?>#', 'util::tinymceReplaceImageCallback', $string);
	}
	
	protected function validation(){
		return array(
			"url"=>
				formAuth::Required,
			"title"=>
				formAuth::Required
		);
	}
}