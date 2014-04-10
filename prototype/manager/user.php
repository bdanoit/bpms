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
            SELECT id,name FROM
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
		$params["password"] = $password[0];
        
        //start transaction
        $this->DBH->beginTransaction();
        
        //insert new user
        try{
            $result = parent::insert($params);
        }
        catch(ManagerException $exc){
            $this->handle_exception($exc);
            $this->DBH->rollback();
            return false;
        }
        
        //insert record and email user
        try{
            $hash = string::bhash_md5($params['name'].time().rand(1,1048576));
            $user_id = $this->DBH->lastInsertId();
            run()->manager->userVerify->insert(array(
                "hash"=>$hash,
                "user_id"=>$user_id
            ));
        }
        catch(ManagerException $exc){
            $this->handle_exception($exc);
            $this->DBH->rollback();
            return false;
        }
        
        //email user activation url
        try{
            $this->send_verify_email($params['email'], $hash);
        }
        catch(ManagerException $exc){
            $this->handle_exception($exc);
            $this->DBH->rollback();
            return false;
        }
        
        $this->DBH->commit();
		return $result;
	}
	
	public final function tryLogin(array $data){
		$user = $this->findByNameOrEmailWPass($data);
		if(!$user){
			$GLOBALS["errors"][] = (object)array(
				"code"=>dechex(0),
				"message"=>"Your credentials are incorrect."
			);
			return false;
		}
        if(!$user->verified){
			$GLOBALS["errors"][] = (object)array(
				"code"=>dechex(0),
				"message"=>"You have not verified your account. Please check your email and follow the instructions provided"
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
    
    public final function findByNameOrEmailWPass(array $data, $prepare = true){
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
    
    public final function findByNameOrEmail(array $data, $prepare = true){
        $params = array(
            'id'=>$data['name'] ? $data['name'] : $data['email']
        );
        $SQL = <<<SQL
        SELECT * 
        FROM {$this->table}
        WHERE (name = :id OR email = :id)
SQL;
        return $this->fetch_single($SQL, $params);
    }
    
    /**
     * Find a user by the project they belong to
     */
    public final function findByProject($project_id, $user_id){
        $SQL = <<<SQL
SELECT 
    u.id, 
    u.name, 
    u.email, 
    a.no_tasks, 
    a.no_complete, 
    l.no_logged, 
    l.time_logged,
    a.no_tasks - a.no_complete as no_left 
        FROM user u 
        RIGHT JOIN project_permission p ON u.id = p.user_id
        LEFT JOIN (SELECT user_id, project_id, COUNT(task_id) AS no_tasks, SUM(complete) AS no_complete FROM task_assigned_to a1 LEFT JOIN (SELECT id, complete FROM task) t1 ON a1.task_id = t1.id WHERE project_id = $project_id GROUP BY user_id) a ON u.id = a.user_id 
        LEFT JOIN (SELECT user_id, project_id, COUNT(id) AS no_logged, SUM(TIMESTAMPDIFF(SECOND, start, end)) AS time_logged FROM task_log WHERE project_id = $project_id GROUP BY user_id) l ON l.project_id = a.project_id AND l.user_id = a.user_id 
        WHERE p.project_id = $project_id AND u.id = $user_id
SQL;
        return $this->fetch_single($SQL, NULL);
    }
    
    /**
     * Get statistics for every user in a given project
     */
    public final function stats($project_id, array $order = array()){
        if(!$order){
            $order = array("time_logged"=>"DESC");
        }
        $order_sql = $this->arr_to_string($order, ',', '`$k` $v');
        $SQL = <<<SQL
SELECT 
    u.id, 
    u.name, 
    u.email, 
    a.no_tasks, 
    a.no_complete, 
    l.no_logged, 
    l.time_logged,
    a.no_tasks - a.no_complete as no_left 
        FROM user u 
        RIGHT JOIN (SELECT user_id, project_id, COUNT(task_id) AS no_tasks, SUM(complete) AS no_complete FROM task_assigned_to a1 LEFT JOIN (SELECT id, complete FROM task) t1 ON a1.task_id = t1.id WHERE project_id = $project_id GROUP BY user_id) a ON u.id = a.user_id 
        LEFT JOIN (SELECT user_id, project_id, COUNT(id) AS no_logged, SUM(TIMESTAMPDIFF(SECOND, start, end)) AS time_logged FROM task_log WHERE project_id = $project_id GROUP BY user_id) l ON l.project_id = a.project_id AND l.user_id = a.user_id 
        GROUP BY u.id 
        ORDER BY $order_sql
SQL;
        return $this->fetch_many($SQL);
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
    
    /**
     * Convienience handling of manager exceptions
     */
    protected function handle_exception(ManagerException $exc){
        if($exc->getCode() == 1048){
            $message = $this->fetch_single('SELECT get_last_custom_error() AS err', NULL, false);
        }
        $message = $message ? $message->err : $exc->getMessage();
        $GLOBALS["errors"][] = (object)array(
            "code"=>$exc->getCode(),
            "message"=>$message
        );
    }
    
    public function findByVerifyHash($hash, $prepare = true){
        return $this->fetch_single("SELECT u.* FROM user u RIGHT JOIN user_verify v ON u.id = v.user_id WHERE hash = :hash", array("hash"=>$hash), $prepare);
    }
    
    public function findByResetHash($hash, $prepare = true){
        return $this->fetch_single("SELECT u.* FROM user u RIGHT JOIN user_forgot f ON u.id = f.user_id WHERE hash = :hash", array("hash"=>$hash), $prepare);
    }
    
    public function updatePassword(array $data){
        
        //check password
        $new_password = $data['new_password'];
        unset($data['new_password']);
		if($new_password[0] != $new_password[1]){
			$GLOBALS["errors"][] = (object)array(
				"code"=>dechex(0),
				"message"=>"Your new passwords do not match"
			);
			return false;
		}
		$data["password"] = $new_password[0];
        
        //begin transaction
        $this->DBH->beginTransaction();
        try{
            parent::update($data);
            run()->manager->userForgot->deleteBy(array('user_id'=>$data['id']));
        }
        catch(ManagerException $exc){
            $this->handle_exception($exc);
            $this->DBH->rollback();
            return false;
        }
        $this->DBH->commit();
        return true;
    }
    
    public function verify($id){
        $this->DBH->beginTransaction();
        
        //Set user as verified
        try{
            $this->exec("UPDATE `user` SET verified = 1 WHERE id = $id");
        }
        catch(ManagerException $exc){
            $this->handle_exception($exc);
            $this->DBH->rollback();
            return false;
        }
        
        //Delete verified column
        try{
            $this->exec("DELETE FROM `user_verify` WHERE user_id = $id");
        }
        catch(ManagerException $exc){
            $this->handle_exception($exc);
            $this->DBH->rollback();
            return false;
        }
        $this->DBH->commit();
        return true;
    }
    
    /**
     * Sends an email to user with activation link (verify)
     */
    protected function send_verify_email($email, $hash){
        return $this->send_email($email, $hash, 'verify');
    }
    
    /**
     * Sends an email to user with activation link (reset password)
     */
    protected function send_reset_email($email, $hash){
        return $this->send_email($email, $hash, 'reset');
    }
    
    /**
     * Begin reset password process
     */
    public function resetByName($name){
        $this->DBH->beginTransaction();
        
        $user = run()->manager->user->findByNameOrEmail(array("name"=>$name));
        
        if(!$user){
            $GLOBALS['errors'][] = (object)array(
                "code"=>dechex(0),
                "message"=>"That username or email was not found."
            );
            return false;
        }
        
        $hash = string::bhash_md5($user->name.time().rand(1,1048576));
        
        //Set user as verified
        try{
            //clean out old entries
            run()->manager->userForgot->deleteBy(array('user_id'=>$user->id));
            //insert new entry
            run()->manager->userForgot->insert(array('user_id'=>$user->id,'hash'=>$hash));
            $this->send_reset_email($user->email, $hash);
        }
        catch(ManagerException $exc){
            $this->handle_exception($exc);
            $this->DBH->rollback();
            return false;
        }
        
        $this->DBH->commit();
        return $user;
    }
    
    /**
     * Sends an email to user with activation link
     */
    protected function send_email($email, $hash, $type = 'reset'){
        require_once(LIB_DIR.'/class.phpmailer.php');
        $body = view()->members->{$type.'_email'}(array(
            "email"=>$email,
            "hash"=>$hash
        ));
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "tls";
        $mail->Host = "smtp.gmail.com";
        $mail->Port = 587;
        $mail->Username = "bpms@danoit.com";
        $mail->Password = "safe1belize2";

        $mail->SetFrom('bpms@danoit.com', 'BPMS');
        $mail->AddReplyTo('bpms@danoit.com', "BPMS");
        switch($type){
            case 'verify': $mail->Subject = "Verify your email - BPMS"; break;
            case 'reset': $mail->Subject = "Reset your password - BPMS"; break;
            default: $mail->Subject = "BPMS Mailer";
        }
        $mail->AltBody = "You must enable HTML emails to see this message...";
        $mail->MsgHTML($body);

        $mail->AddAddress($email);

        if(!$mail->Send()) {
            throw new ManagerException("Mail could not be delivered to recipient ($email)");
        } else {
            return true;
        }
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
