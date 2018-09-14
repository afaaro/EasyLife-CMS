<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

/* Kses */
@require_once("kses/kses.php");
//@require_once("mb.php");

class Io {
	static function GetVar($METHOD,$varname,$filter=false,$encode=true,$default=false) {
		$METHOD = MB::strtoupper($METHOD);
		switch ($METHOD) {
			default:
			case "REQUEST":	$input = &$_REQUEST;	break;
			case "GET":		$input = &$_GET;		break;
			case "POST":	$input = &$_POST;		break;
			case "COOKIE":	$input = &$_COOKIE;		break;
			case "SESSION":	$input = &$_SESSION;	break;
			case "FILES":	$input = &$_FILES;		break;
			case "SERVER":	$input = &$_SERVER;		break;
			case "ENV":		$input = &$_ENV;		break;
		}
		return (isset($input[$varname]) && $input[$varname]!=="") ? self::Filter($input[$varname],$filter,$encode) : $default;
	}
	
	static function GetInt($METHOD,$varname,$default=0) {
		return self::GetVar($METHOD,$varname,"int",false,$default);
	}
	
	static function GetFloat($METHOD,$varname,$default=0) {
		return self::GetVar($METHOD,$varname,"float",false,$default);
	}
	
	static function GetBool($METHOD,$varname,$default=false) {
		return self::GetVar($METHOD,$varname,"bool",false,$default);
	}
	
	static function GetCookie($varname,$filter=false,$encode=true,$default=false) {
		return self::GetVar("COOKIE",$varname,$filter,$encode,$default);
	}
	
	static function SetCookie($varname,$value,$expiration=false,$filter=false,$encode=false) {
		if ($expiration==false) $expiration = time()+3600;
		$value = (!empty($value)) ? self::Filter($value,$filter,$encode) : "" ;
		setcookie($varname,$value,$expiration,_COOKIEPATH);
	}
	
	static function GetSession($varname,$filter=false,$encode=true,$default=false) {
		Session::Start();
		return self::GetVar("SESSION",$varname,$filter,$encode,$default);
	}
	static function SetSession($varname,$value) {
		Session::Start();
		return ($_SESSION[$varname]=$value) ? true : false ;
	}
	
	static function GetServer($varname,$filter=false,$encode=true,$default=false) {
		return self::GetVar("SERVER",$varname,$filter,$encode,$default);
	}
	
	static function GetEnv($varname,$filter=false,$encode=true,$default=false) {
		return self::GetVar("ENV",$varname,$filter,$encode,$default);
	}
	
	static function GetFiles($varname) {
		return self::GetVar("FILES",$varname);
	}
	
	static function GetAlnum($METHOD,$varname,$encode=true,$default=false) {
		return self::GetVar($METHOD,$varname,"[^a-zA-Z0-9]",$encode,$default);
	}
	
	static function GetWord($METHOD,$varname,$encode=true,$default=false) {
		return self::GetVar($METHOD,$varname,"[^a-zA-Z]",$encode,$default);
	}
	
	static function GetString($METHOD,$varname,$encode=true,$default=false) {
		return self::GetVar($METHOD,$varname,false,$encode,$default);
	}
	
	static function Filter($var,$filter=false,$encode=true) {
		global $config;
		if (get_magic_quotes_gpc()) {
			if (is_array($var)) {
				array_walk_recursive($var,array('self','arrayStripslashes'));
			} else {
				$var = stripslashes($var);
			}
		}
		
		switch (MB::strtolower($filter)) {
			//Simple
			case "int":
				$var = (int) $var;
				break;
			case "float":
				$var = (float) $var;
				break;
			case "bool":
				$var = (bool) $var;
				break;
			//Content
			case "fullhtml":
				$var = preg_replace('#<p[^>]*>(\s|&nbsp;|</?\s?br\s?/?>)*</?p>#', '', $var); // remove empty paragraph tags
				$var = preg_replace('/(<[^>]+) (class|id|style)=".*?"/i', '$1', $var); // remove any attribute in div
				//Advanced (full) html tags allowed
				if (is_array($var)) {
					array_walk_recursive($var,array('self','arrayFullHtmlFilter'));
				} else {
					require_once("kses/allowed.php");
					$var = kses($var,$config['allowed_tags_advanced']);
				}
				break;
			case "publichtml":
				//Public html tags allowed
				if (is_array($var)) {
					array_walk_recursive($var,array('self','arrayPublicHtmlFilter'));
				} else {
					require_once("kses/allowed.php");
					$var = kses($var,$config['allowed_tags_public']);
				}
				break;
			case "nohtml":
				//No html tags allowed
				if (is_array($var)) {
					array_walk_recursive($var,array('self','arrayNoHtmlFilter'));
				} else {
					$var = kses(strip_tags($var),array());
				}
				break;
			case "addslashes":
				$var = addslashes($var);
			break;
			//Custom or none
			default:
				$var = ($filter!==false) ? preg_replace("`".$filter."`i","",$var) : $var ;
				break;
		}
		
		if ($encode===true) {
			if (is_array($var)) {
				array_walk_recursive($var,array('self','arrayHtmlSpecialCharsOutput'));
				return $var;
			} else {
				return htmlspecialchars($var,ENT_QUOTES,'UTF-8');
			}
		} else {
			return $var;
		}
	}

	static function arrayStripslashes(&$value,$key) {
		$value = stripslashes($value);
	}
	static function arrayNoHtmlFilter(&$value,$key) {
		$value = kses(strip_tags($value),array());
	}
	static function arrayPublicHtmlFilter(&$value,$key) {
		global $config;

		require_once("kses/allowed.php");
		$value = kses(strip_tags($value),$config['allowed_tags_public']);
	}
	static function arrayFullHtmlFilter(&$value,$key) {
		global $config;
		
		require_once("kses/allowed.php");
		$value = kses(strip_tags($value),array($config['allowed_tags_advanced']));
	}
	
	static function Output($var,$encode=false,$html=1) {
		//Note: Convert data if no filter (and no encoding) has been applied on input
		if ($encode==="int") $var = intval($var);
		if ($encode===true) {
			if (is_array($var)) {
				array_walk_recursive($var,array('self','arrayHtmlSpecialCharsOutput'));
				return $var;
			} else {
				return htmlspecialchars($var,ENT_QUOTES,'UTF-8');
			}
		} else {
			return ($html==1) ? html_entity_decode($var) : stripslashes($var);
		}		
	}
	
	static function arrayHtmlSpecialCharsOutput(&$value,$key) {
		$value = htmlspecialchars($value,ENT_QUOTES,'UTF-8');
	}

	static function map_deep( $value, $callback ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $index => $item ) {
				$value[ $index ] = self::map_deep( $item, $callback );
			}
		} elseif ( is_object( $value ) ) {
			$object_vars = get_object_vars( $value );
			foreach ( $object_vars as $property_name => $property_value ) {
				$value->$property_name = self::map_deep( $property_value, $callback );
			}
		} else {
			$value = call_user_func( $callback, $value );
		}

		return $value;
	}

	static function unhtmlentities($string,$html=1) {
		$trans_tbl1 = get_html_translation_table(HTML_ENTITIES);
		foreach ($trans_tbl1 as $ascii => $htmlentitie) {
			$trans_tbl2[$ascii] = '&#'.ord($ascii).';';
		}
		$string = str_replace("&#039;","'",$string);
		$string = str_replace("&#39;","'",$string);
			
		$trans_tbl1 = array_flip($trans_tbl1);
		$trans_tbl2 = array_flip($trans_tbl2);
		
		$tagstostrtip = array('iframe','script','style');
		$string = strtr(strtr($string,$trans_tbl1),$trans_tbl2);
		if ($html==1) { $string = self::strip_selected_tags($string,$tagstostrtip); }
		return $string;
	}

	static function strip_selected_tags($text, $tags = array()) {
		$args = func_get_args();
		$text = array_shift($args);
		$tags = func_num_args() > 2 ? array_diff($args,array($text)) : (array)$tags;
		foreach ($tags as $tag){
			if(preg_match_all('/<'.$tag.'[^>]*>([^<]*)<\/'.$tag.'>/iu',$text,$found) ){
				$text = str_replace($found[0],$found[1],$text);
			}
		}
		//return preg_replace( '/(<('.join('|',$tags).')(\\n|\\r|.)*\/>)/iu', '', $text); //Has problems with PHP5
		return @$text;
	}
}

// function GetVar($method,$varname,$filter=false,$encode=true,$default=false){
// 	return Io::GetVar($method,$varname,$filter,$encode,$default);
// }

// function Output($var,$encode=false){
// 	return Io::Output($var,$encode);
// }

?>