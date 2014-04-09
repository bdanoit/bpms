<?
class ControllerDefault extends Controller
{
	public function __before(){
	    if(auth::user()){
	        _global()->projects = run()->manager->project->listByUser(auth::user()->id);
	    }
		auth::define(array(
			"projects"=>auth::USER,
			"settings"=>auth::USER,
			"logout"=>auth::USER,
			"new_project"=>auth::USER
		));
	}
    
    public function login($action, $success = false){
        if($_POST){
            if(run()->manager->user->tryLogin($_POST)){
                util::Redirect(router::URL('/projects'));
            }
            $user = (object)$_POST;
        }
		if(auth::user()) return view()->welcome(array(
		    "user"=>auth::user()
		));
		else return view()->login(array(
		    "user"=>$user,
            "success"=>$success
		));
    }
    
    public function register($action, $success = false){
        _global()->title = "Register";
        if($_POST){
            $data = (object)$_POST;
            if(run()->manager->user->insert($_POST)){
                util::Redirect(router::URL('/register/success'));
            }
            $user = (object)$_POST;
        }
        if($success) return view()->register_success();
		if(auth::user()){
            util::Redirect(router::URL('/'));
        }
		else return view()->register(array(
		    "user"=>$user
		));
    }
    
    public function projects(){
        _global()->title = "Projects";
        $projects = run()->manager->project->listByUser(auth::user()->id);
		return view()->projects(array(
		    "user"=>auth::user(),
            "projects"=>$projects
		));
    }
    
    public function new_project($action){
        _global()->title = "New project";
        if($_POST){
            $data = $_POST;
            $data['creator_id'] = auth::user()->id;
            $data['created'] = date('Y-m-d H:i:s');
            if(run()->manager->project->insert($data)){
                util::Redirect(router::URL('/projects'));
            }
        }
		return view()->new_project(array(
		    "user"=>auth::user()
		));
    }
    
    public function contact(){
        _global()->title = "Contact";
        require_once(LIB_DIR.'/class.phpmailer.php');
        $user = auth::user();
        if($_POST){
            $post = (object)$_POST;
            if($user) $post->email = $user->email;
            $recipient = 'bpms@danoit.com';
            $body = view()->mail_contact(array(
                "email"=>$post->email,
                "message"=>$post->message,
                "user"=>$user
            ));
            if($body){
                $mail = new PHPMailer();
                $mail->IsSMTP();
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = "tls";
                $mail->Host = "smtp.gmail.com";
                $mail->Port = 587;
                $mail->Username = "bpms@danoit.com";
                $mail->Password = "safe1belize2";

                $mail->SetFrom('bpms@danoit.com', 'BPMS');
                $mail->AddReplyTo($post->email);
                $mail->Subject = "Contact form - BPMS";
                $mail->AltBody = "You must enable HTML emails to see this message...";
                $mail->MsgHTML($body);

                $mail->AddAddress($recipient);

                if(!$mail->Send()) {
                    $GLOBALS['error'] = array("code"=>0,"message"=>"Email could not be delivered");
                } else {
                    util::Redirect(router::URL('/success'));
                    exit;
                }
            }
        }
        return view()->contact(array(
            "post"=>$post,
            "user"=>$user
        ));
    }
    
    public function success(){
        _global()->title = "Success!";
        return view()->success();
    }
    
    public function about(){
        _global()->title = "About";
        return view()->about();
    }
    
    public function verify($action, $hash){
        $user = run()->manager->user->findByVerifyHash($hash);
        if(!$user){
            util::Redirect(router::URL('/forbidden'));
        }
        run()->manager->user->verify($user->id);
        return view()->verify_success(array(
            "user"=>$user
        ));
    }
    
    public function reset_password($action, $success = false){
        if($_POST){
            $post = (object)$_POST;
            $user = run()->manager->user->resetByName($post->name);
            if($user){
                util::Redirect(router::URL('/reset-password/success'));
            }
        }
        if($success) return view()->reset_success();
        return view()->reset_password(array(
            "user"=>$user,
            "post"=>$post,
            "success"=>$success
        ));
    }
    
    public function new_password($action, $hash = false){
        $user = run()->manager->user->findByResetHash($hash);
        if(!$user){
            util::Redirect(router::URL('/forbidden'));
        }
        if($_POST){
            $data = $_POST;
            $data['id'] = $user->id;
            if(run()->manager->user->updatePassword($data)){
                if(run()->manager->user->tryLogin(array(
                    "name"=>$user->name,
                    "password"=>$data['new_password'][0]
                ))){
                    util::Redirect(router::URL('/projects'));
                }
            }
        }
        return view()->new_password(array(
            "user"=>$user
        ));
    }
    
    public function settings($action, $success = false){
        _global()->title = "Settings";
        $user = auth::user();
        if($_POST){
            $data = $_POST;
            $data['id'] = $user->id;
            if(run()->manager->user->update($data)){
                util::Redirect(router::URL('/settings/success'));
            }
        }
        return view()->settings(array(
            "user"=>$user,
            "success"=>$success
        ));
    }
    
	public function logout($action){
		auth::logout();
		util::Redirect(router::URL('/'));
	}
    
    public function forbidden(){
        _global()->title = "403 Forbidden";
        return view()->forbidden();
    }
}
