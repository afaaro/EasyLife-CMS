<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class Session {
	static function Started() {
		return isset($_SESSION) ? true : false ;
	}
	
	static function Start() {
		if (!self::Started()) @session_start();
	}
	
	static function Write($index,$value) {
		if (!self::Started()) @session_start();
		$_SESSION[$index] = $value;
	}

	static function Read($index,$filter=false,$encode=true,$default=false) {
		if (!self::Started()) @session_start();
		return Io::GetSession($index,$filter,$encode,$default);
	}
	
	static function Wipe() {
		if (self::Started()) {
			session_unset();
		}
		$_SESSION = array();
	}
	
	static function Destroy() {
		if (self::Started()) {
			session_unset();
			session_destroy();
		}
		$_SESSION = array();
	}
	
	static function Regenerate() {
		if (self::Started()) {
			session_regenerate_id();
			return session_id();
		} else {
			return false;
		}
	}

    static function Close() {
        session_write_close();
    }

    static function setOptions(array $options = []) {
        foreach ($options as $key => $value) {
            ini_set(sprintf('session.%s', $key), $value);
        }
    }

    static function Erase($key) {
        return Arr::erase($_SESSION, $key);
    }

    static function Flash($data = null) {
        if (is_null($data)) {
            return static::Get('_out', []);
        }

        static::Put('_in', $data);

        return null;
    }

    static function Get($key, $default = null) {
        return Arr::get($_SESSION, $key, $default);
    }

    static function Put($key, $value) {
        return Arr::set($_SESSION, $key, $value);
    }
}

?>