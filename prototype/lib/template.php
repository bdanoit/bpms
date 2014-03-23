<?php
function template()
{
	return template::singleton();
}
class template extends content
{
	private static $obj;
	public static function singleton(){
		if(self::$obj) return self::$obj;
		return self::$obj = new template();
	}
	protected function dir(){
		return TEMPLATE_DIR;
	}
}
class TemplateException extends Exception{}