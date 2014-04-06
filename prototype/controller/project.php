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
    
    public function index_old($project_id, $action, $msid){
        _global()->title = "Overview";
        $bounds = run()->manager->task->projectOverviewBounds($project_id);
        $milestones = run()->manager->milestone->listBy(array("project_id"=>$project_id));
        
        # determine which tasks to show
        if($msid == "all"){}    # show all tasks
        elseif($msid){          # show specific phase
            $current_ms = run()->manager->milestone->findBy(array("project_id"=>$project_id,"id"=>$msid));
        }
        else{                   # show current phase
            $current_ms = run()->manager->milestone->findNextByProject($project_id, time());
        }
        if($current_ms && $bounds){
            $start = $bounds->start;
            $end = $bounds->end;
            if($current_ms){
                $end = $current_ms->end;
                if($last = $current_ms->last()){
                    $start = $last->end;
                }
            }
            $tasks = run()->manager->task->listByProjectMilestone($project_id, $start, $end);
        }
        else $tasks = run()->manager->task->listBy(array("project_id"=>$project_id));
        return $this->view->index(array(
		    "project"=>$this->project,
            "tasks"=>$tasks,
            "bounds"=>$bounds,
            "milestones"=>$milestones,
            "current_ms"=>$current_ms
		));
    }
    
    public function index($project_id, $action){
        return $this->view->canvas(array("project_id"=>$project_id));
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
    
    public function index_json($project_id, $action){
        $this->template = 'blank';
        header('Content-type: application/json');
        
        $now = time();
        $post = $_POST;
        $data = (object)$post['data'];
        
        $bounds = run()->manager->task->projectOverviewBounds($project_id);
        
        $milestones = run()->manager->milestone->listBy(array("project_id"=>$project_id));
        if($milestones){
        $ids = array();
        foreach($milestones as $index => $milestone){
            $ids[] = $milestone->id;
            $milestone->start = $index ? $milestone->last()->end + 1 : $bounds->start;
            if($milestone->id == $data->id){
                $curr_ms = $milestone;
            }
            elseif(!$data->id && $milestone->start <= $now){
                $curr_ms = $milestone;
            }
        }
            $curr_ms = $curr_ms ? $curr_ms : $milestone;
        }
        
        $complete = $data->status ? 0 : NULL;
        
        if($data->id != 'all' && $curr_ms){
            $tasks = run()->manager->task->listByProjectMilestone($project_id, $curr_ms->start, $curr_ms->end, $complete);
        }
        else $tasks = run()->manager->task->listBy(array("project_id"=>$project_id,"complete"=>$complete));
        foreach($tasks as $task){
            $task->collaborators = $task->assigned_to();
            if(!$start) $start = $task->start;
            elseif($task->start < $start) $start = $task->start;
            if(!$end) $end = $task->end;
            elseif($task->end > $end) $end = $task->end;
        }
        
        $day = 86400;
        $start = strtotime(date('m/d/Y', $start));
        $end = strtotime(date('m/d/Y', $end + $day));
        $temp_ts = $start;
        $num_months = 0;
        $num_days = ceil(($end-$start) / $day);
        do{
            $num_months++;
            $temp_ts = strtotime("+1 month", strtotime(date('m/1/Y', $temp_ts)));
        } while ($temp_ts <= $end);
        $data = array(
            "start"=>$start,
            "end"=>$end,
            "current_day"=>(int)date('N', $now),
            "current_month"=>(int)date('M', $now),
            "current_year"=>(int)date('Y', $now),
            "first_day_i"=>(int)date('j', $start),
            "last_day_i"=>(int)date('j', $end),
            "first_day_s"=>(int)date('N', $start),
            "num_months"=>$num_months,
            "num_days"=>$num_days,
            "project_id"=>$project_id,
            "milestones"=>$milestones,
            "phase"=>$curr_ms,
            "data"=>$tasks
        );
        return json_encode($data);
    }
    
    public function forbidden(){
        util::Redirect(router::URL("/forbidden", "default"));
    }
}
