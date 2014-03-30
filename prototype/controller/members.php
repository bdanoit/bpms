<?
class ControllerMembers extends Controller
{
	public function __before(){
	    if(auth::user()){
	        _global()->projects = run()->manager->project->listByUser(auth::user()->id);
	    }
        $id = router::Current()->vars->id;
        _global()->project = $this->project = run()->manager->project->findBy(array(
            "id"=>$id
        ));
        $this->view = view()->members;
        auth::defineAll(auth::GRANT);
		auth::define(array(
            "index"=>auth::MEMBER
		));
	}
    
    public function index($project_id, $action){
        _global()->title = "Members";
        $members = run()->manager->user->listByProject($project_id);
        $permissions = run()->manager->permission->listAll();
        return $this->view->index(array(
		    "project"=>$this->project,
            "members"=>$members,
            "permissions"=>$permissions
		));
    }
    
    public function view($project_id, $action, $user_id){
        $user = run()->manager->user->findBy(array("id"=>$user_id));
        _global()->title = $user->name;
        return "Coming soon...";
    }
    
    public function remove($project_id, $action, $user_id){
        _global()->title = "Remove Member";
        if(run()->manager->projectPermission->deleteByProjectUser($project_id, $user_id))
            util::Redirect(router::URL("/*id/members"));
        return view()->project->error();
    }
    
    public function add($project_id, $action){
        _global()->title = "Add a member";
        if($_POST){
            $data = (object)$_POST;
            if(run()->manager->projectPermission->insert($this->project->id, $data->name, $data->permissions))
                util::Redirect(router::URL("/*id/members"));
        }
        $permissions = run()->manager->permission->listAll();
        return $this->view->add(array(
            "data"=>$data,
		    "project"=>$this->project,
            "permissions"=>$permissions
		));
    }
    
    public function modify($project_id, $action, $user_id){
        _global()->title = "Modify member";
        if($_POST){
            $data = (object)$_POST;
            if(run()->manager->projectPermission->update($this->project->id, $data->name, $data->permissions))
                util::Redirect(router::URL("/*id/members"));
        }
        $permissions = run()->manager->permission->listAll();
        $vars = run()->manager->projectPermission->findBy(array("project_id"=>$project_id,"user_id"=>$user_id,"permission_id"=>1));
        $user_permissions = array();
        foreach($vars->permissions() as $permission){
            $user_permissions[] = $permission->id;
        }
        
        return $this->view->modify(array(
            "data"=>$data,
		    "project"=>$this->project,
            "permissions"=>$permissions,
            "user_permissions"=>$user_permissions,
            "vars"=>$vars
		));
    }
    
    public function json($project_id, $action){
        $this->template = "blank";
        $query = $_GET['query'];
        return $this->view->json(array(
            "members"=>run()->manager->user->search($query)
        ));
    }
    
    public function forbidden(){
        util::Redirect(router::URL("/forbidden", "default"));
    }
}