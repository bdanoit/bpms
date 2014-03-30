<?
class ControllerProject extends Controller
{
	public function __before(){
        //list of users current projects
	    if(auth::user()){
	        _global()->projects = run()->manager->project->listByUser(auth::user()->id);
	    }
        
        //current defined router variables
        $vars = router::Current()->vars;
        
        //find and store current project
        _global()->project = $this->project = run()->manager->project->findBy(array(
            "id"=>$vars->id
        ));
        
        //define the directory of views
        $this->view = view()->project;
        
        //define permissions
        auth::defineAll(array(auth::MEMBER));
	}
    
    public function index(){
        _global()->title = "Overview";
        return $this->view->index(array(
		    "project"=>$this->project
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
}
