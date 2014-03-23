<?php
class ManagerUser extends Manager{
	protected function database(){
		return _global()->mysql->database;
	}
	protected function table(){
		return "user";
	}
	protected function order(){
		return array(
			"email"=>"ASC"
		);
	}
	
	public final function checkPassword($user_id, $password){
		if(!$password) return false;
		$user = $this->findBy(array(
			"id"=>$user_id
		));
		if(!$user) return false;
		if(md5($password) == $user->password){
			return true;
		}
		return false;
	}
	
	public final function update($params){
		$new_password = $params["new_password"];
		if(isset($params["new_password"])){
			if(!$this->checkPassword($params["id"], $params["password"])){
				$GLOBALS["errors"][] = (object)array(
					"code"=>dechex(0),
					"message"=>"You have entered an incorrect password"
				);
				return false;
			}
            unset($params["password"]);
			if($new_password[0] != $new_password[1]){
				$GLOBALS["errors"][] = (object)array(
					"code"=>dechex(0),
					"message"=>"Your new passwords do not match"
				);
				return false;
			}
			$params["password"] = $new_password[0];
		}
		unset($params["new_password"]);
        return parent::update($params);
	}
	
	public final function findByHash($hash){
		$session = run()->manager->session->findBy(array("hash"=>$hash));
		if(!$session) return false;
		return $this->findBy(array("id"=>$session->user_id));
	}
	
	public final function insert($params){
		$password = $params["password"];
		if(!$password[0]){
			$GLOBALS["errors"][] = (object)array(
				"code"=>dechex(0),
				"message"=>"You must enter a password"
			);
			return;
		}
		$user = $this->findBy(array("email"=>$params["email"]));
		if($user){
			$GLOBALS["errors"][] = (object)array(
				"code"=>dechex(0),
				"message"=>"A user with that email already exists"
			);
			return;
		}
		if($password[0] != $password[1]){
			$GLOBALS["errors"][] = (object)array(
				"code"=>dechex(0),
				"message"=>"Your passwords do not match"
			);
			return;
		}
		$params["password"] = $password[0];//string::bhash_md5($password[0]);
		$result = parent::insert($params);
		return $result;
	}
	
	public final function tryLogin(array $data){
		$data["password"] = md5($data["password"]);
		$user = $this->findBy($data);
		if(!$user){
			$GLOBALS["errors"][] = (object)array(
				"code"=>dechex(0),
				"message"=>"Your credentials are incorrect."
			);
			return false;
		}
		$hash = string::bhash_md5($user->email.time().rand(1,1048576));
		$session = run()->manager->session->deleteBy(array(
			"hash"=>$hash
		));
		$session = run()->manager->session->insert(array(
			"hash"=>$hash,
			"user_id"=>$user->id
		));
		if(!$session){
			$GLOBALS["errors"][] = (object)array(
				"code"=>dechex(0),
				"message"=>"Session could not be created. Please ensure that cookies are enabled."
			);
			return false;
		}
		cookie()->user()
			->expires(time() + (60*60*24*31))
			->set($hash);
		return true;
	}
	
	protected final function prepare(&$row){
		//$row->full_name = "$row->first_name $row->last_name";
	}
	
	protected function validation(){
		return array(
			"first_name"=>
				formAuth::Required,
			"last_name"=>
				formAuth::Required,
			"email"=>
				formAuth::Required |
				formAuth::Email,
			"manager"=>
				formAuth::Email,
			"password"=>
				formAuth::Password
		);
	}
}
