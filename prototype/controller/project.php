<?
class ControllerProject extends Controller
{
	public function __before(){
        # list of users current projects
	    if(auth::user()){
	        _global()->projects = run()->manager->project->listByUser(auth::user()->id);
	    }
        
        # current defined router variables
        $vars = router::Current()->vars;
        
        # find and store current project
        _global()->project = $this->project = run()->manager->project->findBy(array(
            "id"=>$vars->id
        ));
        
        # define the directory of views
        $this->view = view()->project;
        
        # define permissions
        auth::defineAll(array(auth::MEMBER));
	}
    
    public function index($project_id){
        _global()->title = "Overview";
        $bounds = run()->manager->task->projectOverviewBounds($project_id);
        $tasks = run()->manager->task->listBy(array("project_id"=>$project_id));
        return $this->view->index(array(
		    "project"=>$this->project,
            "tasks"=>$tasks,
            "bounds"=>$bounds
		));
    }
    
    public function add_milestone($project_id, $action){
        _global()->title = "Add a milestone";
        if($_POST){
            $data = (object)$_POST;
            if(run()->manager->projectPermission->insert($this->project->id, $data->email, $data->permissions))
                util::Redirect(router::URL("/$project_id/members"));
        }
        return $this->view->add_milestone(array(
            "data"=>$data,
		    "project"=>$this->project
		));
    }
    
    public function forbidden(){
        util::Redirect(router::URL("/forbidden", "default"));
    }
}
