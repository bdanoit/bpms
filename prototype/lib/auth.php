<?php
class auth{
	const USER = 0;
	const MEMBER = 1;
	const EDIT = 2;
	const DELETE = 4;
	const GRANT = 8;
	
	private static $require;
	private static $requireAll;
	
	private static $user;
	
	public static function user(){
		if(self::$user !== null) return self::$user;
		self::$user = run()->manager->user->findByHash(cookie()->user->value());
        #begin hack
        $route = router::Current();
        $areas = array(
            "project",
            "tasks",
            "members",
            "milestones"
        );
        if(in_array($route->controller, $areas) && $route->vars->id){
            $permissions = run()->manager->permission->listByProjectMember($route->vars->id, self::$user->id);
            if($permissions) foreach($permissions as $permission){
                self::$user->auth_level = self::$user->auth_level | pow(2,($permission->id-1));
            }
        }
        #end hack
		return self::user();
	}
	
	public static function define(array $actions){
		self::$require = $actions;
	}
	
	public static function defineAll($type){
		self::$requireAll = $type;
	}
	
	public static function logout(){
		return cookie()->user->delete();
	}
	
	public static function check($action){
		$level = isset(self::$require[$action]) ? self::$require[$action] : self::$requireAll;
		if(!$level) return true;
		$user = self::user();
		if(!$user) return false;
		if((int)$user->auth_level & (int)$level) return true;
		return false;
	}
	
}
