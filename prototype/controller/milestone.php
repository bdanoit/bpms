<?
class ControllerMilestone extends Controller
{
	public function __before(){
        $this->view = view()->milestone;
        auth::defineAll(auth::EDIT);
		auth::define(array(
            "index"=>auth::MEMBER
		));
	}
    
    public function index($project_id, $action){
        _global()->title = "Milestones";
        $milestones = run()->manager->milestone->listBy(array("project_id"=>$project_id));
        $permissions = run()->manager->permission->listAll();
        return $this->view->index(array(
		    "project"=>$this->project,
            "milestones"=>$milestones,
            "message"=>"No milestones yet."
		));
    }
    
    public function view($project_id, $action, $id){
        $milestone = run()->manager->milestone->findBy(array("project_id"=>$project_id,"id"=>$id));
        _global()->title = $milestone->name;
        return "Coming soon...";
    }
    
    public function remove($project_id, $action, $id){
        _global()->title = "Remove milestone";
        if(run()->manager->milestone->deleteBy(array("project_id"=>$project_id,"id"=>$id)))
            util::Redirect(router::URL("/*id/milestone"));
        return view()->project->error();
    }
    
    public function add($project_id, $action){
        _global()->title = "Add a milestone";
        if($data = $_POST){
            $data['project_id'] = $project_id;
            $data['creator_id'] = auth::user()->id;
            if(run()->manager->milestone->insert($data))
                util::Redirect(router::URL("/*id/milestone"));
        }
        
        //define default times for create view
        $default = (object)array(
            "end_date" => date('Y-m-d'),
            "end_time" => '23:59'
        );
        
        return $this->view->add(array(
            "data"=>$data,
		    "project"=>$this->project,
		    "submit_name"=>"Add",
		    "default"=>$default
		));
    }
    
    public function modify($project_id, $action, $id){
        _global()->title = "Modify milestone";
        $milestone = run()->manager->milestone->findBy(array("project_id"=>$project_id,"id"=>$id));
        if(!$milestone) $this->forbidden();
        if($data = $_POST){
            $data['project_id'] = $project_id;
            $data['id'] = $milestone->id;
            if(run()->manager->milestone->update($data))
                util::Redirect(router::URL("/*id/milestone"));
        }
        return $this->view->add(array(
            "data"=>$data,
		    "project"=>$this->project,
		    "submit_name"=>"Update",
		    "milestone"=>$milestone
		));
    }
    
    public function forbidden(){
        util::Redirect(router::URL("/forbidden", "default"));
    }
}
