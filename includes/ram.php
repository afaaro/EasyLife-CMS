<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class Ram {
	static $data = array();
	
	static function Set($label,$value) {
		self::$data[$label] = $value;
	}
	
	static function Get($label,$default=false) {
		return (isset(self::$data[$label])) ? self::$data[$label] : $default;
		
	}
	
	static function GetAll() { //TODO //DEBUG Delete
		return self::$data;
	}
}

?>