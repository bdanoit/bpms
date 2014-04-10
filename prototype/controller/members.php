<?
class ControllerMembers extends Controller
{
	public function __before(){
        $this->view = view()->members;
        auth::defineAll(auth::GRANT);
		auth::define(array(
            "index"=>auth::MEMBER
		));
	}
    
    public function index($project_id, $action){
        _global()->title = "Members";
        $members = run()->manager->user->listByProject($project_id);
        $invitees = run()->manager->projectInvite->listByProject($project_id);
        $permissions = run()->manager->permission->listAll();
        return $this->view->index(array(
		    "project"=>$this->project,
            "members"=>$members,
            "invitees"=>$invitees,
            "permissions"=>$permissions
		));
    }
    
    public function invitees($project_id, $action){
        _global()->title = "Members";
        $invitees = run()->manager->projectInvite->listByProject($project_id);
        $permissions = run()->manager->permission->listAll();
        return $this->view->invitees(array(
		    "project"=>$this->project,
            "invitees"=>$invitees,
            "permissions"=>$permissions
		));
    }
    
    public function remove_invitee($project_id, $action, $user_id){
        _global()->title = NULL;
        if(run()->manager->projectInvite->decline($project_id, $user_id))
            util::Redirect(router::URL('/*id/members/invitees'));
        return view()->error(array("message"=>"Could not remove invitee..."));
    }
    
    public function stats($project_id, $action){
        _global()->title = "Member Statistics";
        $stats = run()->manager->user->stats($project_id, $_GET);
        return $this->view->stats(array(
		    "project"=>$this->project,
            "stats"=>$stats,
            "get"=>$_GET ? (object)$_GET : NULL
		));
    }
    
    public function view($project_id, $action, $user_id){
        $user = run()->manager->user->findByProject($project_id, $user_id);
        _global()->title = 'Member information';
        if($user){
            $tasks = run()->manager->task->listByProjectUser($project_id, $user_id);
            $tasks_complete = run()->manager->task->listByProjectUser($project_id, $user_id, 1);
        }
        return $this->view->member(array(
		    "project"=>$this->project,
            "member"=>$user,
            "tasks"=>$tasks,
            "tasks_complete"=>$tasks_complete
		));
    }
    
    public function remove($project_id, $action, $user_id){
        _global()->title = "Remove Member";
        if(run()->manager->projectPermission->deleteByProjectUser($project_id, $user_id))
            util::Redirect(router::URL("/*id/members"));
        return view()->project->error();
    }
    
    public function add($project_id, $action){
        _global()->title = "Invite a member";
        if($_POST){
            $data = (object)$_POST;
            if(run()->manager->projectInvite->insert($this->project->id, $data->name, $data->permissions))
                util::Redirect(router::URL("/*id/members/invitees"));
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