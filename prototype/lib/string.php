<?php
class string{
	/**
	 * Camel Case any hyphenated or underscored string
	 * 
	 * @param string $string
	 * @return string camelized string
	 */
	public static function Camelize($string){
		$string = preg_replace_callback(
				"/^[a-z]|[\s-_][a-z]/",
				create_function(
					'$matches',
					'return strtoupper($matches[0]);'
				),
				$string
		);
		$string = preg_replace("/[\s-_]/", "", $string);
		return $string;
	}
	
	/**
	 * Camel Case any hyphenated or underscored string
	 * 
	 * @param string $string
	 * @return string camelized string
	 */
	public static function keyToName($string){
		$string = preg_replace_callback(
				"/^[a-z]|[\s-_][a-z]/",
				create_function(
					'$matches',
					'return strtoupper($matches[0]);'
				),
				$string
		);
		$string = preg_replace("/[\s-_]/", " ", $string);
		return $string;
	}

	/**
	 * Turns a string into a url-friendly slug
	 *
	 * @param string $string
	 */
	public static function Slug($string){
		return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
	}
	/*public static function Slug($string){
		$slug = strtolower(trim($string));
		$slug = preg_replace('/[\s_]/', "-", $slug);
		$slug = preg_replace('/[^a-z09-]/', "", $slug);
		return $slug;
	}*/

	/**
	 * Turns a CamelCase string into an array of words
	 *
	 * @param string $string
	 * @return array of words
	 */
	public static function CamelToArray($string){
		$pattern = '#^[a-z_]+|[A-Z][a-z_]+|[A-Z]+(?=[A-Z][a-z_])|[A-Z]+#';
		return preg_match_all($pattern, $string, $match) ?
				$match[0] :
					false;
	}

	/**
	 * Turns a CamelCase string into an array of words
	 *
	 * @param string $string
	 * @return array of words
	 */
	public static function CamelToUnder($string){
		return strtolower(implode("_", self::CamelToArray($string)));
	}

	/**
	 * Strip the beginning of a url all the way up to and including www.
	 * 
	 * @param string $string
	 * @return string
	 */
	public static function StripWWW($string){
		return preg_replace('#^(:?http://)?www\.#', "", $string);
	}
	public static function Format($format, $string, $placeHolder = "#"){
		$numMatches = preg_match_all("/($placeHolder+)/", $format, $matches);
		foreach ($matches[0] as $match)
		{
			$matchLen = strlen($match);
			$format = preg_replace("/$placeHolder+/", substr($string, 0, $matchLen), $format, 1);
			$string = substr($string, $matchLen);
		}
		return $format;
	}
	

	/**
	 * Split a string into a list of words
	 * 
	 * @param string $string
	 * @return array of words
	 */
	public static function Words($string){
		$words = preg_split('#\s+#', $string);
		$return = array();
		foreach($words as $word){
			if(preg_match('#[a-z][\-a-z\p{L&}]+[a-z]#', $word, $match)){
				$return[] = $match[0];
			}
		}
		return $return;
	}
	
	/**
	 * Encode a string into UTF8
	 * 
	 * @param string $string
	 * @return string utf8 encoded string
	 */
	public static function UTF8Encode($string){
		switch(mb_detect_encoding($string, null, true)){
			case "UTF-8":
				break;
			default:
				$string = utf8_encode($string);
		}
		return $string;
	}
	
	public static function RemoveAccents($string){
		$string = self::UTF8Encode($string);
		$string = htmlentities(iconv("UTF-8", "ISO-8859-1",$string));
		$string = preg_replace("/&(.)(acute|cedil|grave|circ|ring|tilde|uml);/", "$1", $string);
		return $string;
	}
	
	public static function IsUppercase($string){
		return mb_strtoupper($string, "UTF-8") == $string;
	}
	
	
	/**
	 * Create an complex hash
	 * 
	 * @param string $string
	 * @return string md5 hash
	 */
	public static function bhash_md5($string) {
		$len = strlen($string);
		$int = $len * 255;
		$salt = self::encode($int)."Angelina Jolie";
		$result = $salt.$string.$salt;
		for($i = 0; $i < 262144; $i++){
			$result = md5($result);
		}
		return $result;
	}
	
	public static function encode($val, $base=62, $chars='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
		$str = '';
		do {
			$i = $val % $base;
			$str = $chars[$i] . $str;
			$val = ($val - $i) / $base;
		} while($val > 0);
		return $str;
	}
	
	function Random($length = 10){
		$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
		$string = '';	

		for ($p = 0; $p < $length; $p++) {
			$string .= $characters[mt_rand(0, strlen($characters))];
		}

		return $string;
	}
}
?>
