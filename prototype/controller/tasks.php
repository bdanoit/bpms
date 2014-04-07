<?
class ControllerTasks extends Controller
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
        $this->view = view()->tasks;
        
        //define permissions
        auth::defineAll(auth::EDIT);
		auth::define(array(
			"index"=>auth::MEMBER,
			"finished"=>auth::MEMBER,
			"view"=>auth::MEMBER,
            "remove"=>auth::DELETE
		));
	}
    
    public function index($project_id, $action){
        _global()->title = "My tasks";
        return $this->view->index(array(
		    "project"=>$this->project,
            "data"=>$data,
		    "tasks"=>run()->manager->task->listByProjectUser($this->project->id, auth::user()->id, 0, $_GET),
            "message"=>"No tasks assigned to you.",
            "get"=>$_GET ? (object)$_GET : NULL
		));
    }
    
    public function finished($project_id, $action){
        _global()->title = "Finished tasks";
        return $this->view->index(array(
		    "project"=>$this->project,
            "data"=>$data,
		    "tasks"=>run()->manager->task->listByProjectUser($this->project->id, auth::user()->id, 1, $_GET),
            "message"=>"No finished tasks.",
            "get"=>$_GET ? (object)$_GET : NULL
		));
    }
    
    public function all($project_id, $action){
        _global()->title = "All tasks";
        return $this->view->index(array(
		    "project"=>$this->project,
            "data"=>$data,
		    "tasks"=>run()->manager->task->listByProject($this->project->id, NULL, $_GET),
            "message"=>"No tasks.",
            "get"=>$_GET ? (object)$_GET : NULL
		));
    }
    
    public function create($project_id, $action){
        _global()->title = "Create task";
        if($data = $_POST){
            $data['project_id'] = $project_id;
            $data['creator_id'] = auth::user()->id;
            if($id = run()->manager->task->insert($data)){
                util::Redirect(router::URL("/*id/tasks/view/$id"));
            }
        }
        
        //define default times for create view
        $now = time();
        $day = 86400;
        $default = (object)array(
            "start_date" => date('Y-m-d'),
            "start_time" => '12:00',
            "end_date" => date('Y-m-d', $now + $day),
            "end_time" => '23:59'
        );
        
        return $this->view->create(array(
		    "project"=>$this->project,
            "members"=>run()->manager->user->listByProject($project_id),
            "task"=>(object)$_POST,
            "submit_name"=>"Create",
            "default"=>$default
		));
    }
    
    public function view($project_id, $action, $task_id){
        $task = run()->manager->task->findBy(array("id"=>$task_id,"project_id"=>$project_id));
        $log = run()->manager->taskLog->listBy(array("project_id"=>$project_id,"task_id"=>$task_id,"user_id"=>auth::user()->id));
        
        _global()->title = $task->name;
        _global()->breadcrumb[_global()->title] = $action;
        return $this->view->task(array(
		    "project"=>$this->project,
            "task"=>$task,
            "log"=>$log
		));
    }
    
    public function edit($project_id, $action, $task_id){
        $task = run()->manager->task->findBy(array("id"=>$task_id,"project_id"=>$project_id));
        //if task cannot be found, throw an error
        if(!$task){
            _global()->title = "Task not found";
            $GLOBALS['errors'][] = (object)array("code"=>1, "message"=>'Task does not exist');
            return view()->project->error();
        }
        _global()->title = $task->name;
        
        //if user submitted data, process it
        if($data = $_POST){
            $data['project_id'] = $project_id;
            $data['id'] = $task_id;
            if(run()->manager->task->update($data)){
                util::Redirect(router::URL("/*id/tasks/view/$task_id"));
            }
        }
        
        //format data for entry into view
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
    
    public function add_time($project_id, $action, $task_id){
        _global()->title = "Log time";
        $task = run()->manager->task->findBy(array("id"=>$task_id,"project_id"=>$project_id));
        if(!$task){
            $tasks = run()->manager->task->listByProjectUser($this->project->id, auth::user()->id);
        }
        
        if($data = $_POST){
            if($task) $data['task_id'] = $task->id;
            $data['project_id'] = $project_id;
            $data['user_id'] = auth::user()->id;
            if(run()->manager->taskLog->insert($data)){
                util::Redirect(router::URL("/*id/tasks/view/$task->id"));
            }
        }
        
        
        //define default times for add_time view
        $default = (object)array(
            "date" => date('Y-m-d'),
            "time" => date('H:i')
        );
        
        return $this->view->log(array(
		    "project"=>$this->project,
            "members"=>run()->manager->user->listByProject($project_id),
            "task"=>$task,
            "tasks"=>$tasks,
            "submit_name"=>"Add",
            "default"=>$default
		));
    }
    
    public function remove($project_id, $action, $task_id){
        _global()->title = "Remove task";
        if(run()->manager->task->deleteBy(array("project_id"=>$project_id,"id"=>$task_id))) util::Redirect(util::LAST);
        return view()->project->error();
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
