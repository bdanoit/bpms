<?php
function view()
{
	return view::singleton();
}
class view extends content
{
	private static $obj;
	public static function singleton(){
		//if(self::$obj) return self::$obj;
		return self::$obj = new view();
	}
	protected function dir(){
		return VIEW_DIR;
	}
}
class ViewException extends Exception{}