<?php
class router{
	private static $routes = array();
	private static $current = null;
	private static $current_route;

	/**
	 * Add a route
	 * 
	 * @param string $pattern route pattern
	 * @param string $controller controller used for route
	 * @param string $defaultAction default action for route
	 * @return boolean true on success, false on failure
	 */
	public static function Connect($pattern, $controller, $defaultMethod, $template){
		$name = preg_replace('#^.*/#', "", $controller);
		$path = preg_replace('#[^/]*$#', "", $controller);
		if(isset(self::$routes[$pattern])){
			throw new RouterException("Duplicate pattern for: <strong>$pattern</strong>");
			return false;
		}
		self::$routes[$pattern] = array(
			"pattern"=>$pattern,
			"controller"=>$name,
			"path"=>$path,
			"default"=>$defaultMethod,
			"template"=>$template
		);
		return true;
	}
	
	public static function URI(){
		return preg_replace('#(?:&|\?).*$#', "", $_SERVER['REQUEST_URI']);
	}
	
	public static function URL($path, $controller = null){
		if($controller){
			$base = self::findBase($controller);
			if($base === false){
				throw new RouterException("Could not find route for controller ($controller)");
			}
		}
		else $base = self::Current()->base;
        $vars = router::Current()->vars;
        $path = preg_replace_callback(
            '#\*([^/]+)#',
            function($matches) use ($vars){
                $match = $matches[1];
                if(!$value = $vars->$match){
                    throw new RouterException("Could not find variable ($match) in controller ($controller)");
                }
                return $vars->$match;
            },
            $path
        );
		if(!$path) return $base;
		else return $base.$path;
	}
	
	/*public static function Redirect($location = null){
		if($location) $location = self::Current()->base.$location;
		\Util::Redirect($location);
	}*/

	public static function Resolve(){
		$uri = self::URI();
		foreach(array_reverse(self::$routes) as $pattern => $info){
			$var_regex = '#/\$([^/]+)#';
			preg_match_all($var_regex, $pattern, $keys);
			$new_pattern = preg_replace($var_regex, '(?:/([^/]+))?', $pattern);
			if(preg_match("#^$new_pattern(?:/[^/]*)*?$#", $uri, $match)){
				$base = preg_replace('#/\$.+$#', "", $pattern);
				break;
			}
		}
		if(!$match){
			throw new RouterException("Matching route could not be found");
		}
		self::$current = $pattern;
		
		// Take the first regex match out
		array_shift($match);
		// Combine $keys and $match into associative array
		$assoc = util::PairArrays($keys[1], $match);
		// Clearn up method
		$method = $assoc["action"] ?
			self::CleanMethod($assoc["action"]) : $info["default"];
		// Take action out of base
		if($method)
			$base = preg_replace('#(?<=.)/'.$method.'$#', "", $base);
		self::$current_route = array(
			"controller"=>$info["controller"],
			"path"=>$info["path"],
			"base"=>$base,
			"method"=>$method,
			"action"=>$assoc["action"],
			"template"=>$info["template"],
            "vars"=>(object)$assoc
		);
		return self::Current();
	}
	
	public static function Current(){
		return (object)self::$current_route;
	}
	
	public static function findBase($key){
		foreach(array_reverse(self::$routes) as $route){
			if($route["controller"] == $key){
				$base = preg_replace('#/\$[^/]+#', '', $route["pattern"]);
				return $base;
			}
		}
		return false;
	}

	private static function CleanMethod($method){
		return preg_replace('[^a-zA-Z_]', "", str_replace("-", "_", $method));
	}
	
	public static function Params(){
        if(!self::Current()->vars) return array();
        $arr = array();
        foreach(self::Current()->vars as $key => $value){
            $arr[] = $value;
        }
        return $arr;
	}
}
class RouterException extends Exception{
	
}
?>
