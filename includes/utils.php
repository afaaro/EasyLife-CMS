<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class Utils {

	static function GetMicrotime() {
		list($usec,$sec) = explode(" ",microtime());
		return ((float)$usec+(float)$sec);
	}

	static function StartBuffering($function=false) {
		if ($function!==false) {
			ob_start($function);
		} else {
			ob_start();
		}
	}

	static function GetBufferContent($operation=false) {
		$content = ob_get_contents();
		switch ($operation) {
			case "clean":
				ob_end_clean();
				break;
			case "flush":
				ob_end_flush();
				break;
		}
		return $content;
	}
	static function Redirect($url,$sec=false) {
		global $config_sys;

		if (MB::strpos($url,"index.php")===0) $url = get_option('site_url')."/".$url;
		
		if (!headers_sent() && $sec===false) {
			header("Location: ".$url);
		} else {
			echo '<meta http-equiv="refresh" content="'.$sec.';url='.$url.'" />';
		}
	}
	static function Serialize($array) {
		return serialize(is_array($array) ? $array : array());
	}

	static function Unserialize($string) {
		$array = @unserialize($string);
		return ($array!==false) ? $array : array();
	}

	static function GenerateRandomString($size=false,$chars=false) {
		if ($size===false) $size = 10;
		if ($chars!==false) {
			$string = "";
			for($i=0;$i<$size;$i++) $string .= $chars{mt_rand(0,strlen($chars)-1)};			
		} else {
			$string = md5(uniqid(mt_rand(0,time()),true));
		}
		return ($size) ? MB::substr($string,0,$size) : $string;
	}

	static function GenerateToken() {
		$token = self::GenerateRandomString();
		return $token.":".md5($token.md5(mt_rand(1,9999)));
	}

	static function CheckToken() {
		if (iADMIN) return true;

		$ctok = Io::GetVar("POST","ctok","[^a-zA-Z0-9]");
		$ftok = Io::GetVar("POST","ftok","[^a-zA-Z0-9]");
		return (empty($ctok) || empty($ftok) && md5($ctok.md5(mt_rand(1,9999)))!=$ftok) ? false : true ;
	}
	
	static function CutStr($str,$chars=5) {
		return MB::substr($str,0,$chars);
	}

	static function ValidUrl($url) {
		return (preg_match("#^(?:(?:http|https)://)+([\w\d]+)+(?:[\w\d~\._\-\&]+)\.(?:[a-zA-Z]{2,5})+(:\d+)?/*[\w\d\?\.\+\(\)\&\/\=\#%\~_\-]*$#is",$url)) ? true : false ;
	}

	static function ValidEmail($email) {
		return (preg_match("`^(?:[\w\d~\._-]{2,})(@localhost)|(?:@{1}[\w\d~\._-]{1,})(?:\.{1}[a-zA-Z]{2,5})$`is",$email)) ? true : false ;
	}

	static function ValidIp($ip) {
		return (preg_match("`^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$`is",$ip) && @inet_pton($ip)) ? true : false ;
	}

	static function Ip2num($ip) {
		return @inet_pton($ip);
	}

	static function Num2ip($ip) {
		return @inet_ntop($ip);
	}
	
	//Ip address
	static function GetIp() {  
	    $ip = Io::GetServer("HTTP_CLIENT_IP","nohtml");
	    if (empty($ip)) {
	        $ip = Io::GetServer("HTTP_X_FORWARDED_FOR","nohtml");
	        if (empty($ip)) {
	            $ip = Io::GetServer("REMOTE_ADDR","nohtml");
	        }
	    }
	    $ip = str_replace("::1","127.0.0.1",$ip);
	    if (MB::stripos($ip,",")) {
	        $ip = explode(",",$ip);
	        $ip = (isset($ip[0])) ? $ip[0] : false ;
	    }
	    return (static::ValidIp($ip)) ? $ip : "0.0.0.0" ;
	}

	static function GetXmlFile($file=false) {
		if (!is_readable($file)) return false;
		
		$xmlobj = @simplexml_load_file($file,'SimpleXMLElement',LIBXML_NOCDATA);
		return $xmlobj;
	}
	
	static function GetDirContent($path,$exclude=array()) {
		$filearray = array();
		$handle = opendir($path);
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && $file!="index.html" && !in_array($file,$exclude)) $filearray[] = $file;
		}
		closedir($handle);
		if (sizeof($filearray)) {
			sort($filearray);
			reset($filearray);
		}
		return $filearray;
	}
	
	static function Bytes2str($bytes) {
		if ($bytes<1024) {
			return "$bytes byte";
		} else {
			$kb = $bytes / 1024;
			if ($kb<1024) {
				return sprintf("%01.2f", $kb)." Kb";
			} else {
				$mb = $kb / 1024;
				if ($mb<1024) {
					return sprintf("%01.2f", $mb)." Mb";
				} else {
					$gb = $mb / 1024;
						return sprintf("%01.2f", $gb)." Gb";
				}
			}
		}
	}

    public static function jsonFormat($var) {
        $var = str_replace("\r\n", "\n", $var);
        $var = str_replace("\r", "\n", $var);

        // // // JSON requires new line characters be escaped
        $var = str_replace("\n", '\\n', $var);
        $var = str_replace("'", '\\u0027', $var);
        $var = preg_replace_callback(
            '/<([^<>]+)>/',
            function ($matches) {
                return str_replace('"', '\"', $matches[0]);
            },
            $var
        );
        // $var = preg_replace_callback(
        //     '/([^<>]+)/',
        //     function ($matches) {
        //         return str_replace("'", '&apos;', $matches[0]);
        //     },
        //     $var
        // );

        $var = str_replace('/>', ' />', $var);
        $var = str_replace('</', '<\/', $var);

        $var = Io::Output($var);
        $var = str_replace('\&', '&', $var);

        return $var;
    }
}