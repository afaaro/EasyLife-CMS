<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

//if (!defined('_COOKIEPATH')) define('_COOKIEPATH', preg_replace('|https?://[^/]+|i', '', $config['site_url'] . '/'));

class User {
	protected $userdata = array();
	protected $error = "";
	protected $permission = array();
	var $cookie_name = "user";

	function __construct() {
		global $db, $config;

		$success = false;
		$cookie_data = Io::GetCookie($this->cookie_name,"[^a-zA-Z0-9_]",false,false);

		if ($cookie_data!==false) {
			$cookie_piece = explode("_",$cookie_data);
			$uid = (isset($cookie_piece[0])) ? $cookie_piece[0] : 0 ;
			$row = $db->query("SELECT * FROM #__user WHERE id=".intval($uid)." AND status='active'")->row;
			
			if ($cookie_data==Io::Output($row['id'])."_".sha1(Io::Output($row['username']).sha1(Io::Output($row['password'])).Io::Output($row['cookiesalt']))) {
				$this->userdata = $row;
				$this->userdata['user_rights'] = json_decode($row['user_rights'], true);

				unset($this->userdata['password'], $this->userdata['code'], $this->userdata['status'], $this->userdata['cookiesalt']);

				$db->query("UPDATE #__user SET lastseen=NOW(),lastip='".$db->escape(Utils::Ip2num(Utils::GetIp()))."' WHERE id=".intval($this->userdata['id']));

				//$user_group_query = $db->query("SELECT permission FROM " . PREFIX . "user_group WHERE id = '" . (int)$this->userdata['user_level'] . "'");

				//$permissions = json_decode($this->userdata['user_rights'], true);

				if (is_array($this->userdata['user_rights'])) {
					foreach ($this->userdata['user_rights'] as $key => $value) {
						$this->permission[$key] = $value;
					}
				}

				$success = true;
			} else $this->Logout();
		}
		if ($success==false) $this->Logout();
	}

	function Ip2num($ip) {
	    return @inet_pton($ip);
	}

	function Authenticate() {
		global $db, $config;

		// //Check token
		// if (!Utils::CheckToken()) {
		// 	$this->error = _t("INVALID_TOKEN");
		// 	return false;
		// }

	    $username = Io::GetVar("POST", "username");
	    $password = Io::GetVar("POST", "password");

		//Check access
		if (empty($username)) {
			$this->error = "Username field is required";
			return false;
		}

		if (empty($password)) {
			$this->error = "Password field is required";
			return false;
		}

		if (!$row = $db->query("SELECT * FROM #__user WHERE username='".$db->escape($username)."' AND status='active' LIMIT 1")->row) {
			$this->error = "Username and Password are not valid.";
			return false;
		}

		if(Hash::check($password, $row['password'])) {
			//Authentication successful

			//Get user data from db
			$this->userdata['id'] = Io::Output($row['id']);
			$this->userdata['user_level'] = Io::Output($row['user_level']);
			$this->userdata['user_rights'] = json_decode(Io::Output($row['user_rights']), true);
			$this->userdata['username'] = Io::Output($row['username']);
			$this->userdata['password'] = Io::Output($row['password']);
			$this->userdata['real_name'] = strip_tags(Io::Output($row['real_name']));
			$this->userdata['email'] = Io::Output($row['email']);
			$this->userdata['regdate'] = Io::Output($row['regdate']);
			$this->userdata['lastseen'] = Io::Output($row['lastseen']);
			//$this->userdata['lastip'] = Utils::Num2Ip(Io::Output($row['lastip']));

			$salt = random_str(50);
			$db->query("UPDATE #__user SET lastseen=NOW(),lastip='".$db->escape(Utils::Ip2num(Utils::GetIp()))."',cookiesalt='".$salt."' WHERE id=".intval($this->userdata['id']));
			//Create cookie
			$cookie_expire = (intval(Io::GetVar("POST","remember","int"))==1) ? time() + (86400 * $config['login_cookie_expire']) : 0 ;
			$cookie_value = $this->userdata['id']."_".sha1($username.sha1($this->userdata['password']).$salt);
			setcookie($this->cookie_name,$cookie_value,$cookie_expire,_COOKIEPATH);

			// $user_group_query = $db->query("SELECT permission FROM " . PREFIX . "user_group WHERE id = '" . (int)$this->userdata['user_level'] . "'");

			// $permissions = json_decode($user_group_query->row['permission'], true);

			if (is_array($this->userdata['user_rights'])) {
				foreach ($this->userdata['user_rights'] as $key => $value) {
					$this->permission[$key] = $value;
				}
			}

			return true;
		}

		$this->error = "Username and Password are not valid.";
		return false;
	}

	function GetError() {
		return addNotice('warning', $this->error);
	}

	function Logout() {
		setcookie($this->cookie_name,"",time()-31536000,_COOKIEPATH);

		$this->userdata['id'] = 0;
		$this->userdata['username'] = false;
		$this->userdata['real_name'] = "Guest";
		$this->userdata['email'] = false;
		$this->userdata['regdate'] = false;
		$this->userdata['lastseen'] = false;
		$this->userdata['lastip'] = false;
		$this->userdata['user_level'] = 0;
		Session::Regenerate();
		
		return true;
	}

	
	function IsUser($uid=false) {
		global $db;
		
		if ($uid===false) return ($this->userdata['id']>0) ? true : false ;
		else return ($db->query("SELECT id FROM #__user WHERE id='".intval($uid)."' AND status='active' LIMIT 1")->num_rows > 0) ? true : false ;
	}

	function IsAdmin($uid=false) {
		global $db;
		if ($uid===false) return ($this->userdata['id']>0) ? true : false ;
		else return ($db->query("SELECT id, user_level FROM #__user WHERE id='".intval($uid)."' AND user_level='103' LIMIT 1")->num_rows > 0) ? true : false ;
	}

	function Uid() {
		return (isset($this->userdata['id'])) ? $this->userdata['id'] : 0 ;
	}

	function GetInfo($key,$default=false) {
		return (isset($this->userdata[$key])) ? $this->userdata[$key] : $default ;
	}

	//User information
	function Ip() {
		return Utils::GetIp();
	}

	function Data() {
		return $this->userdata;
	}

	public function hasPermission($key, $value) {
		if (isset($this->userdata['user_rights'][$key])) {
			return in_array($value, $this->userdata['user_rights'][$key]);
		} else {
			return false;
		}

	}

	// return a role object with associated permissions
	public function hasAccess($key) {
		global $db;
		$user_levels = $db->query("SELECT ul.*,up.* FROM #__user_levels_permissions as up JOIN #__user_levels as ul ON up.user_level_id=ul.user_level_id WHERE ul.user_level_id='".$this->userdata['user_level']."'")->rows;

		foreach($user_levels as $user_level) {
			return $user_level[$key] == 1 ? TRUE : FALSE;
		}
		return false;
	}

}

//Users management
$User = new User();
//debug($User);
if (isset($_GET['logout'])) {
    $User->Logout();
    redirect($config['site_url']);
}

$guest = ($User->IsUser()) ? 0 : 1 ;
$db->query("REPLACE INTO #__user_online (ip,uid,guest,date) VALUES ('".$db->escape(Utils::Ip2num($User->Ip()))."','".$db->escape($User->Uid())."','".intval($guest)."',NOW())");
$db->query("DELETE FROM #__user_online WHERE (date + INTERVAL 5 MINUTE) < NOW()");
