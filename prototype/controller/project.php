<?
class ControllerProject extends Controller
{
	public function __before(){
	    if(auth::user()){
	        _global()->projects = run()->manager->project->listByUser(auth::user()->id);
	    }
        $id = router::Current()->vars->id;
        $this->project = run()->manager->project->findBy(array(
            "id"=>$id
        ));
        $this->view = view()->project;
        _global()->project = $this->project;
        _global()->breadcrumb = array(
            "{$this->project->name}"=>NULL
        );
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
        _global()->breadcrumb[_global()->title] = $action;
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
