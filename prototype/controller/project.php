<?
class ControllerProject extends Controller
{
	public function __before(){
        $id = router::Current()->vars->id;
        $this->project = run()->manager->project->findBy(array(
            "id"=>$id
        ));
        _global()->project = $this->project;
        _global()->breadcrumb = array(
            "{$this->project->name}"=>NULL
        );
        auth::defineAll(array(auth::MEMBER));
		auth::define(array(
			"add_member"=>auth::EDIT,
			"modify_member"=>auth::EDIT,
			"create_task"=>auth::EDIT,
			"remove_member"=>auth::DELETE
		));
	}
    
    public function index(){
        _global()->title = "Overview";
        return view()->project_index(array(
		    "project"=>$this->project
		));
    }
    
    public function members($project_id, $action){
        _global()->title = "Members";
        _global()->breadcrumb[_global()->title] = $action;
        $members = run()->manager->user->listByProject($project_id);
        return view()->project_members(array(
		    "project"=>$this->project,
            "members"=>$members
		));
    }
    
    public function create_task($project_id, $action){
        _global()->title = "Create task";
        _global()->breadcrumb[_global()->title] = $action;
        if($_POST){
        }
        return view()->project_create_task(array(
		    "project"=>$this->project,
            "members"=>run()->manager->user->listByProject($project_id)
		));
    }
    
    public function add_milestone($project_id, $action){
        _global()->title = "Add a milestone";
        _global()->breadcrumb[_global()->title] = $action;
        if($_POST){
            $data = (object)$_POST;
            if(run()->manager->projectPermission->insert($this->project->id, $data->email, $data->permissions))
                util::Redirect(router::URL("/$project_id/members"));
        }
        return view()->project_add_milestone(array(
            "data"=>$data,
		    "project"=>$this->project
		));
    }
    
    public function add_member($project_id, $action){
        _global()->title = "Add a member";
        _global()->breadcrumb[_global()->title] = $action;
        if($_POST){
            $data = (object)$_POST;
            if(run()->manager->projectPermission->insert($this->project->id, $data->email, $data->permissions))
                util::Redirect(router::URL("/$project_id/members"));
        }
        $permissions = run()->manager->permission->listAll();
        return view()->project_add_member(array(
            "data"=>$data,
		    "project"=>$this->project,
            "permissions"=>$permissions
		));
    }
    
    public function modify_member($project_id, $action, $user_id){
        _global()->title = "Modify member";
        _global()->breadcrumb[_global()->title] = $action;
        if($_POST){
            $data = (object)$_POST;
            if(run()->manager->projectPermission->update($this->project->id, $data->email, $data->permissions))
                util::Redirect(router::URL("/$project_id/members"));
        }
        $permissions = run()->manager->permission->listAll();
        $vars = run()->manager->projectPermission->findBy(array("project_id"=>$project_id,"user_id"=>$user_id,"permission_id"=>1));
        $user_permissions = array();
        foreach($vars->permissions() as $permission){
            $user_permissions[] = $permission->id;
        }
        
        return view()->project_modify_member(array(
            "data"=>$data,
		    "project"=>$this->project,
            "permissions"=>$permissions,
            "user_permissions"=>$user_permissions,
            "vars"=>$vars
		));
    }
    
    public function forbidden(){
        util::Redirect(router::URL("/forbidden", "default"));
    }
    
    public function remove_member($project_id, $action, $user_id){
        _global()->title = "Remove Member";
        _global()->breadcrumb[_global()->title] = $action;
        if(run()->manager->projectPermission->deleteBy(array("project_id"=>$project_id,"user_id"=>$user_id)))
            util::Redirect(router::URL("/$project_id/members"));
        return view()->project_error();
    }
}
