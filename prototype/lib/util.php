<?php
class util
{


	/**
	 * Utility to redirect to another page
	 * 
	 * @param string location
	 */
	public static function Redirect($location = null){
		if(!$location) $location = $_SERVER['REQUEST_URI'];
		header("Location: $location");
	}
	
	/**
	 * Take two arrays and merge them into an associative array
	 *
	 * @param array $keys array of keys
	 * @param array $values array of values
	 */
	public static function PairArrays($keys, $values){
		$assoc = array();
		for($i = 0; $i < count($keys); $i++){
			$key = $keys[$i];
			$value = isset($values[$i]) ? $values[$i] : null;
			$assoc[$key] = $value;
		}
		return $assoc;
	}
	
	public static function image_resize($data, $width, $height){
		$image = new Imagick();
		$image->readImageBlob($data);
		/*$image->scaleImage($width,$height,false);*/
		//$image = new Imagick("your image file");
		// get the current image dimensions
		$geo = $image->getImageGeometry();
		
		if(!$width){
			$width = round($height / $geo['height'] * $geo['width']);
		} else if(!$height) {
			$height = round($width / $geo['width'] * $geo['height']);
		}
		
		// crop the image
		if(($geo['width']/$width) < ($geo['height']/$height))
		{
			$image->cropImage($geo['width'], floor($height*$geo['width']/$width), 0, (($geo['height']-($height*$geo['width']/$width))/2));
		}
		else
		{
			$image->cropImage(ceil($width*$geo['height']/$height), $geo['height'], (($geo['width']-($width*$geo['height']/$height))/2), 0);
		}
		// thumbnail the image
		$image->ThumbnailImage($width,$height,true);
		return $image->getImageBlob();
	}
	
	public static function tinymceReplaceImageCallback($match){
		list($tag, $url) = $match;
		$url = str_replace('&amp;', '&', $url);
		if(!preg_match('#^/file/#', $url)) return $tag;
		if(!preg_match('#width="([^"]+)"#', $tag, $info)) return $tag;
		list($null,$width) = $info;
		if(!preg_match('#height="([^"]+)"#', $tag, $info)) return $tag;
		list($null,$height) = $info;
		$data = array();
		$path = parse_url($url, PHP_URL_PATH);
		$query = parse_url($url, PHP_URL_QUERY);
		if($query) parse_str($query, $data);
		$data["width"] = $width;
		$data["height"] = $height;
		return str_replace($url, $path.'?'.http_build_query($data, '', '&amp;'), $tag);
	}
	
	public static function write_ini_file($assoc_arr, $path, $has_sections=FALSE) { 
		$content = ""; 
		if ($has_sections) { 
			foreach ($assoc_arr as $key=>$elem) { 
				$content .= "[".$key."]\n"; 
				foreach ($elem as $key2=>$elem2) { 
					if(is_array($elem2)) 
					{ 
						for($i=0;$i<count($elem2);$i++) 
						{ 
							$content .= $key2."[] = \"".$elem2[$i]."\"\n"; 
						} 
					} 
					else if($elem2=="") $content .= $key2." = \n"; 
					else $content .= $key2." = \"".$elem2."\"\n"; 
				} 
			} 
		} 
		else { 
			foreach ($assoc_arr as $key=>$elem) { 
				if(is_array($elem)) 
				{ 
					for($i=0;$i<count($elem);$i++) 
					{ 
						$content .= $key."[] = \"".$elem[$i]."\"\n"; 
					} 
				} 
				else if($elem=="") $content .= $key." = \n"; 
				else $content .= $key." = \"".$elem."\"\n"; 
			} 
		} 

		if (!$handle = fopen($path, 'w')) { 
			return false; 
		} 
		if (!fwrite($handle, $content)) { 
			return false; 
		} 
		fclose($handle); 
		return true; 
	}

}