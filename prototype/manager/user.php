<?php
class ManagerUser extends Manager{
    protected $table = 'user';
    
    public final function listByProject($id, $prepare = true){
        $SQL = <<<SQL
            SELECT *, $id as project_id FROM
                $this->table
            WHERE
                id IN (SELECT user_id FROM project_permission WHERE project_id = $id GROUP BY user_id);
SQL;
        return $this->fetch_many($SQL, NULL, $prepare);
    }
    
    public final function search($string, $prepare = false){
        if(strlen($string) < 3) return false;
        $SQL = <<<SQL
            SELECT name FROM
                $this->table
            WHERE
                name LIKE :query
SQL;
        $params = array(
            "query"=>"$string%"
        );
        return $this->fetch_many($SQL, $params, $prepare);
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
		if($params["new_password"][0]){
			if(!$this->checkPassword($params["id"], $params["password"])){
				$GLOBALS["errors"][] = (object)array(
					"code"=>dechex(0),
					"message"=>"Your current password is incorrect"
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
		unset($params["new_password"], $params["password"]);
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
		
		if($user = $this->findBy(array("email"=>$params["email"]))){
			$GLOBALS["errors"][] = (object)array(
				"code"=>dechex(0),
				"message"=>"A user with that email already exists"
			);
			return;
		}
		if($user = $this->findBy(array("name"=>$params["name"]))){
			$GLOBALS["errors"][] = (object)array(
				"code"=>dechex(0),
				"message"=>"A user with that name already exists"
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
		$user = $this->findByUsernameOrEmail($data);
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
    
    public final function findByUsernameOrEmail(array $data, $prepare = true){
        $params = array(
            'id'=>$data['name'] ? $data['name'] : $data['email'],
            'password'=>md5($data['password'])
        );
        $SQL = <<<SQL
        SELECT * 
        FROM {$this->table}
        WHERE (name = :id OR email = :id)
        AND password = :password
SQL;
        return $this->fetch_single($SQL, $params);
    }
	
	protected final function prepare(&$row){
		$row->alias = $row->name ? $row->name : $row->email;
        $row->permissions = function($row){
            if(!$row->project_id) return false;
            return run()->manager->permission->listByProjectMember($row->project_id, $row->id);
        };
        
        $row->has_permission = function($row, $pid){
            if(!$permissions = $row->permissions()) return false;
            foreach($permissions as $permission){
                if($permission->id == $pid) return true;
            }
            return false;
        };
	}
	
	protected function validation(){
		return array(
			"name"=>
				formAuth::Required | formAuth::Password,
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
