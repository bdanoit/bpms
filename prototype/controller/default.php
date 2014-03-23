<?
class ControllerDefault extends Controller
{
	public function __before(){
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
            $login = (object)$_POST;
        }
		if(auth::user()) return view()->welcome(array(
		    "user"=>auth::user()
		));
		else return view()->login(array(
		    "login"=>$login
		));
    }
    
    public function register(){
        _global()->title = "Register";
        if($_POST){
            if(run()->manager->user->insert($_POST)){
                if(run()->manager->user->tryLogin($_POST)){
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
        _global()->title = "My Projects";
        $projects = run()->manager->project->listBy(array(
            "creator_id"=>auth::user()->id
        ));
		return view()->projects(array(
		    "user"=>auth::user(),
            "projects"=>$projects
		));
    }
    
    public function new_project(){
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
    
    public function settings($success = false){
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
    
	public function logout(){
		auth::logout();
		util::Redirect(router::URL('/'));
	}
}
