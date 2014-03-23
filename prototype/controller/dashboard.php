<?
class ControllerDashboard extends Controller
{
	public function __before(){
        $this->project = run()->manager->project->findBy(array(
            "id"=>$this->id
        ));
		auth::define(array(
			"index"=>auth::USER
		));
	}
    
    public function index(){
        var_dump($this->project);exit;
		return view()->index(array(
		    "login"=>false
		));
    }
}
