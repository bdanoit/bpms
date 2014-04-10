<?
class ControllerTaskLog extends Controller
{
	public function __before(){
        //current defined router variables
        $vars = router::Current()->vars;
        
        //find and store current task
        $this->task = run()->manager->task->findBy(array("id"=>$vars->task_id,"project_id"=>$vars->id));
        
        //check if this task is assigned to user
        if(!$this->task->assigned_to_me()) $this->forbidden();
        
        //define the directory of views
        $this->view = view()->tasks;
        
        //define permissions
        auth::defineAll(auth::MEMBER);
	}
    
    public function add($project_id, $task_id, $action, $log_id = false){
        _global()->title = "Log time";
        $task = $this->task;
        
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
            "log"=>(object)$data,
            "tasks"=>$tasks,
            "submit_name"=>"Add",
            "default"=>$default
		));
    }
    
    public function remove($project_id, $task_id, $action, $log_id = false){
        _global()->title = "Remove log";
        $task = $this->task;
        $log = run()->manager->taskLog->findBy(array(
            "project_id"=>$project_id,
            "task_id"=>$task_id,
            "user_id"=>auth::user()->id,
            "id"=>$log_id
        ));
        
        if(!$log) $this->forbidden();
        
        if(run()->manager->taskLog->deleteBy(array(
            "project_id"=>$project_id,
            "task_id"=>$task_id,
            "user_id"=>auth::user()->id,
            "id"=>$log_id
        ))) util::Redirect(router::URL("/*id/tasks/view/$task->id"));
        
        return view()->project->error();
    }
    
    public function edit($project_id, $task_id, $action, $log_id = false){
        _global()->title = "Modify log";
        $task = $this->task;
        $log = run()->manager->taskLog->findBy(array(
            "project_id"=>$project_id,
            "task_id"=>$task_id,
            "user_id"=>auth::user()->id,
            "id"=>$log_id
        ));
        
        if(!$log) $this->forbidden();
        
        if($data = $_POST){
            if($task) $data['task_id'] = $task->id;
            $data['project_id'] = $project_id;
            $data['user_id'] = auth::user()->id;
            $data['id'] = $log_id;
            if(run()->manager->taskLog->update($data)){
                util::Redirect(router::URL("/*id/tasks/view/$task->id"));
            }
        }
        
        return $this->view->log(array(
            "log"=>$log,
		    "project"=>$this->project,
            "members"=>run()->manager->user->listByProject($project_id),
            "task"=>$task,
            "tasks"=>$tasks,
            "submit_name"=>"Update",
            "default"=>$default
		));
    }
    
    public function forbidden(){
        util::Redirect(router::URL("/forbidden", "default"));
    }
}
