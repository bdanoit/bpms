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
			"logout"=>auth::USER
		));
	}
    
    public function index(){
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
		    "user"=>$user
		));
    }
    
    public function register(){
        _global()->title = "Register";
        if($_POST){
            $data = (object)$_POST;
            if(run()->manager->user->insert($_POST)){
                if(run()->manager->user->tryLogin(array("email"=>$data->email,"password"=>$data->password[0]))){
                    util::Redirect(router::URL('/projects'));
                }
            }
            $user = (object)$_POST;
        }
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
