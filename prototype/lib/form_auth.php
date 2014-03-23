<?php
class formAuth{
	const Required = 1;
	const Integer = 2;
	const Email = 4;
	const Decimal = 8;
	const Password = 16;
	const Time = 32;
	const Date = 64;
	const Boolean = 128;
	
	private static $constants;
	
	public static function check($sum, $value, $name = ''){
		foreach(self::get_constants() as $key => $bin){
			if($sum & $bin){
				if(!call_user_func('formAuth::is'.$key, $value)){
					return (object)array(
						"code"=>dechex($bin),
						"message"=>"$name ".self::message($bin)
					);
				}
			}
		}
	}
	
	private static function get_constants(){
		if(self::$constants) return self::$constants;
		$reflection = new ReflectionClass(get_class());
		self::$constants = $reflection->getConstants();
		return self::get_constants();
	}
	
	public static function message($key){
		switch($key)
		{
		case formAuth::Required:
			return "is required";
		case formAuth::Email:
			return "must be a valid email";
		case formAuth::Integer:
			return "must be an integer";
		case formAuth::Boolean:
			return "must be a boolean value";
		case formAuth::Decimal:
			return "must be a decimal";
		case formAuth::Password:
			return "must be at least 4 characters long, and must only contain valid characters <small>(a-z, 0-9, _, -, @)</small>";
		case formAuth::Time:
			return "must be a valid time (eg: 12:00pm)";
		case formAuth::Date:
			return "must be a valid date (eg: 10/27/2011)";
		}
	}
	
	public static function isRequired($value){
		if($value === '' || $value === null || $value === false) return false;
		return true;
	}
	public static function isInteger($value){
		if(!$value) return true;
		if(preg_match('#^[0-9]+$#', $value)) return true;
	}
	public static function isEmail($value){
		if(!$value) return true;
		if(filter_var($value, FILTER_VALIDATE_EMAIL)) return true;
	}
	public static function isDecimal($value){
		if(!$value) return true;
		if(preg_match('#^[0-9]+(?:\.[0-9]+)?$#', $value)) return true;
	}
	
	public static function isPassword($value){
		if(preg_match('#^[a-zA-Z0-9_@-]{4,32}$#', $value)) return true;
	}
	
	public static function isTime($value){
		if(!$value) return true;
		if(preg_match('#^(?:1[0-2]|0?[1-9]):[0-5][0-9](?:am|pm)$#', $value)) return true;
	}
	
	public static function isDate($value){
		if(!$value) return true;
		if(preg_match('#^(?:1[0-2]|0?[1-9])\/(?:3[0-1]|[0-2]?[0-9])\/(?:2[0-9]{3})$#', $value)) return true;
	}
	
	public static function isBoolean($value){
		if(!$value) return true;
		if(filter_var($value, FILTER_VALIDATE_BOOLEAN)) return true;
	}
}