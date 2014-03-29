<?
class ControllerTasks extends Controller
{
	public function __before(){
	    if(auth::user()){
	        _global()->projects = run()->manager->project->listByUser(auth::user()->id);
	    }
        $id = router::Current()->vars->id;
        $this->project = run()->manager->project->findBy(array(
            "id"=>$id
        ));
        $this->view = view()->tasks;
        _global()->project = $this->project;
        auth::defineAll(auth::EDIT);
		auth::define(array(
			"index"=>auth::MEMBER,
			"finished"=>auth::MEMBER,
			"view"=>auth::MEMBER
		));
	}
    
    public function index($project_id, $action){
        _global()->title = "My tasks";
        return $this->view->index(array(
		    "project"=>$this->project,
            "data"=>$data,
		    "tasks"=>run()->manager->task->listByProjectUser($this->project->id, auth::user()->id),
            "message"=>"No tasks assigned to you."
		));
    }
    
    public function finished($project_id, $action){
        _global()->title = "Finished tasks";
        return $this->view->index(array(
		    "project"=>$this->project,
            "data"=>$data,
		    "tasks"=>run()->manager->task->listByProjectUser($this->project->id, auth::user()->id, 1),
            "message"=>"No finished tasks."
		));
    }
    
    public function all($project_id, $action){
        _global()->title = "All tasks";
        return $this->view->index(array(
		    "project"=>$this->project,
            "data"=>$data,
		    "tasks"=>run()->manager->task->listBy(array("project_id"=>$this->project->id)),
            "message"=>"No tasks."
		));
    }
    
    public function view($project_id, $action, $task_id){
        _global()->title = "View task";
        $task = run()->manager->task->findBy(array("id"=>$task_id));
        _global()->title = $task->name;
        _global()->breadcrumb[_global()->title] = $action;
        return $this->view->task(array(
		    "project"=>$this->project,
            "task"=>$task
		));
    }
    
    public function create($project_id, $action){
        _global()->title = "Create task";
        if($data = $_POST){
            $data['project_id'] = $project_id;
            if(run()->manager->task->insert($data)){
                util::Redirect(router::URL("/*id/tasks/all"));
            }
        }
        $now = time();
        $day = 86400;
        $default = (object)array(
            "start_date" => date('Y-m-d'),
            "start_time" => date('12:00'),
            "end_date" => date('Y-m-d', $now + $day),
            "end_time" => date('23:59')
        );
        return $this->view->create(array(
		    "project"=>$this->project,
            "members"=>run()->manager->user->listByProject($project_id),
            "task"=>(object)$_POST,
            "submit_name"=>"Create",
		"default"=>$default
		));
    }
    
    public function edit($project_id, $action, $task_id){
        $task = run()->manager->task->findBy(array("id"=>$task_id,"project_id"=>$project_id));
        if(!$task){
            _global()->title = "Task not found";
            $GLOBALS['errors'][] = (object)array("code"=>1, "message"=>'Task does not exist');
            return view()->project->error();
        }
        _global()->title = $task->name;
        if($data = $_POST){
            $data['project_id'] = $project_id;
            $data['id'] = $task_id;
            if(run()->manager->task->update($data)){
                util::Redirect(router::URL("/*id/tasks/all"));
            }
        }
        if($assigned_to = $task->assigned_to()){
            $result = array();
            foreach($assigned_to as $user){
                $result[] = $user->id;
            }
            $task->assigned_to = $result;
        }
        return $this->view->create(array(
		    "project"=>$this->project,
            "members"=>run()->manager->user->listByProject($project_id),
            "task"=>$task,
            "submit_name"=>"Update"
		));
    }
    
    public function task_complete($project_id, $action, $task_id){
        _global()->title = "Mark task as complete";
        if(run()->manager->task->complete($project_id, $task_id)) util::Redirect(util::LAST);
        return view()->project->error();
    }
    
    public function task_incomplete($project_id, $action, $task_id){
        _global()->title = "Mark task as incomplete";
        _global()->breadcrumb[_global()->title] = $action;
        if(run()->manager->task->complete($project_id, $task_id, false)) util::Redirect(util::LAST);
        return view()->project->error();
    }
    
    public function forbidden(){
        util::Redirect(router::URL("/forbidden", "default"));
    }
}
