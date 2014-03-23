<?
class ControllerPage extends Controller
{
	public function __before(){
		auth::defineAll(auth::ADMIN);
	}
	
	public function index(){
		$pages = run()->manager->page->listAll();
		return view()->__path('/admin/page')->index(array(
			"pages"=>$pages
		));
	}
	
	public function add(){
		$post = vars::post();
		if($post->page){
			if(run()->manager->page->insert($post->page)){
				util::Redirect(router::URL('/'));
			}
		}
		return view()->__path('/admin/page')->edit(array(
			"post"=>(object)$post->page
		));
	}
	
	public function delete($id){
		if(run()->manager->page->delete($id)){
			util::Redirect(router::URL('/'));
		}
	}
	
	public function edit($id){
		$post = vars::post();
		if($post->page){
			$data = $post->page;
			$data["id"] = $id;
			if(run()->manager->page->update($data)){
				util::Redirect(router::URL('/'));
			}
		}
		$page = run()->manager->page->findBy(array("id"=>$id));
		return view()->__path('/admin/page')->edit(array(
			"page"=>$page,
			"post"=>(object)$post->page
		));
	}
}