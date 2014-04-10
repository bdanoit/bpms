<?
class Controller{
    protected $construct = true;
    public function __construct(){
        if(!$this->construct) return false;
        
        $user = $this->user = auth::user();
        # list of users current projects
        if($user){
	        _global()->projects = run()->manager->project->listByUser(auth::user()->id);
        }
        # current defined router variables
        $vars = router::Current()->vars;
        
        # find and store current project
        if($vars->id){
            _global()->project = $this->project = run()->manager->project->findBy(array(
                "id"=>$vars->id
            ));
        }
        
        # get the number of invites
        if($user){
            _global()->invites = $this->invites = run()->manager->projectInvite->countByUser($user->id);
        }
    }
}