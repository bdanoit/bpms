<?php
if (isset($_POST["PHPSESSID"])) {
	session_id($_POST["PHPSESSID"]);
}
session_start();
/*
 * Key directories
 */
if(!defined('__DIR__')){
	define('__DIR__', dirname(__FILE__));
}
define("BASE_DIR", __DIR__.'/..');
define("LIB_DIR", BASE_DIR.'/lib');
define("VIEW_DIR", BASE_DIR.'/view');
define("TEMPLATE_DIR", BASE_DIR.'/template');
define("CONTROLLER_DIR", BASE_DIR.'/controller');
define("CONFIG_DIR", BASE_DIR.'/config');
define("MANAGER_DIR", BASE_DIR.'/manager');
define("PATH_CACHE", BASE_DIR.'/cache');
define("UPLOAD_DIR", BASE_DIR.'/upload');

/*
 * Required library files
 */
$required = array(
	"content",
	"controller",
	"form_auth",
	"global",
	"http",
	"manager.2.0",
	"router",
	"run",
	"string",
	"template",
	"time",
	"util",
	"view",
	"auth",
	"cookie"
);
foreach($required as $filename){
	require_once(LIB_DIR."/$filename.php");
}


/*
 * Look for Config
 */
$config = @parse_ini_file(BASE_DIR.'/config.ini', true);
if($config) foreach($config as $key => $settings){
	_global()->$key = (object)$settings;
}
if(!_global()->db){
	_global()->db = (object)array(
        "db"=>"mysql",
		"host"=>"localhost",
		"username"=>"root",
		"password"=>"safe1mysql2",
		"dbname"=>"bpms"
	);
}

/*
 * Connect routes
 */
require_once(CONFIG_DIR.'/routes.ini');

/*
 * Resolve and run current action
 */
$info = router::Resolve();
$name = string::Camelize($info->controller);
$ctrl = run()->controller()->$name();
$method = $info->method;
if(method_exists($ctrl, "__before")){
	$content = $ctrl->__before();
}
if(!auth::check($info->method)){
	if(method_exists($ctrl, "forbidden")){
		$content = $ctrl->forbidden();
	}
	else throw new Exception('You do not have permission to access this area');
}
else $content = call_user_func_array(array($ctrl, $method), router::Params());


/*
 * Output template
 */
$template = $ctrl->template ? $ctrl->template : $info->template;
print template()->{$template}(array(
	"content"=>$content,
    "vars"=>router::Current()->vars,
    "route"=>router::Current(),
    "user"=>auth::user(),
    "global"=>_global()
));

# close database connection
$dbh = NULL;
