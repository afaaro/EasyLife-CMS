<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class Router {
	protected $controller;
	protected $action;
	protected $file;
	var $breadcrumbs = array();
	
	function Run() {
		global $db,$config,$User;

		$querystring = $_SERVER['QUERY_STRING'];
		if ($querystring==false && !isset($_SERVER['QUERY_STRING']) && !isset($_SERVER['argv'])) trigger_error("Request can not proceed ".$_SERVER['REQUEST_URI'], E_USER_ERROR);

		if (!preg_match("#index.php#i",$_SERVER['REQUEST_URI']) && !empty($querystring)) {
			//If last part == suffix, delete it
			$sulen = strlen($config['seo_urls_suffix']);
			$querystring = ($config['seo_urls_suffix']==MB::substr($querystring,-($sulen),$sulen)) ? MB::substr($querystring,0,strlen($querystring)-$sulen) : $querystring ;

			//Rebuild controller
			$parts = explode($config['seo_urls_separator'],$querystring);
			$plugname = preg_replace("#[^a-zA-Z0-9\-]#i","",str_replace($config['node']."=","",$parts[0]));
			$_GET[$config['node']] = $plugname;
			
			if ($plugrow = $db->query("SELECT id,title,name,controller,type,content,options,roles FROM ".PREFIX."content WHERE name='".$db->escape($plugname)."' AND status='active'")->row) {
				define("_PLUGOK",true);
				$controller = Io::Output($plugrow['controller']);	

				//Rebuild query map
				if (file_exists(BASEDIR."plugins/".$controller."/map.php")) {
					include_once(BASEDIR."plugins/".$controller."/map.php");

					if (sizeof($parts)>1 && isset($map)) {
						$op = (isset($map[$parts[1]])) ? $parts[1] : "index" ;
						if (isset($map[$op])) {
							$limit = ((sizeof($parts)-1)>sizeof($map[$op])) ? sizeof($map[$op]) : (sizeof($parts)-1) ;
							for ($i=0;$i<$limit;$i++) $_GET[$map[$op][$i]] = preg_replace("#[^a-zA-Z0-9\-]#i","",$parts[$i+1]);
						}
					}
				}			
			}
		}
		
		$this->plugname = Io::GetVar("GET",$config['node'],"nohtml",true,$config['default_home']);
		$parts = explode($config['seo_urls_separator'], preg_replace('/[^a-zA-Z0-9_\/-]/', '', (string)$this->plugname));
		$this->action = Io::GetVar("GET","op","[^a-zA-Z0-9\-]",true,"index");
		define("_ISHOME",(isset($_GET[$config['node']])) ? 0 : 1);

		if (defined("_PLUGOK")) {
			$row = $plugrow;
		} else {
			$row = $db->query("SELECT id,title,name,controller,type,content,options,roles FROM ".PREFIX."content WHERE name='".$db->escape(current($parts))."' AND status='active'")->row;
		}

		if ($row) {
			$controller = array();
			$controller['id'] = Io::Output($row['id'],"int");
			$controller['title'] = Io::Output($row['title']);
			$controller['name'] = Io::Output($row['name']);
			$controller['controller'] = Io::Output($row['controller']);
			$controller['type'] = Io::Output($row['type']);
			$controller['content'] = Io::Output($row['content']);
			$controller['options'] = unserialize(Io::Output($row['options']));
			$controller['roles'] = Utils::Unserialize(Io::Output($row['roles']));

			//Store controller's information in Ram
			Ram::Set('controller',$controller);

			//Site title step
			$pt = Io::GetVar("GET",$config['node'],"[^a-zA-Z0-9\-]");
			//if (!empty($pt)) add_to_title(current($parts));
			
			//Required role check
			$proceed = true;
			//if ($user->CheckRole($controller['roles']) || $User->IsAdmin()) {

			define("_PLUGIN",$controller['name']);
			define("_PLUGIN_CONTROLLER",$controller['controller']);
			define("_PLUGIN_TITLE",$controller['title']);
			
			if ($controller['type']=="REDIRECT") {
				if (!Utils::ValidUrl($controller['content'])) {
					$proceed = false;
					$message = "Address not valid"; //TODO: Translate and build something nice?
				}
			}

			// if($User->IsAdmin()) {
			// 	define("_PLUGIN",$controller['name']);
			// 	define("_PLUGIN_CONTROLLER",$controller['controller']);
			// 	define("_PLUGIN_TITLE",$controller['title']);
				
			// 	if ($controller['type']=="REDIRECT") {
			// 		if (!Utils::ValidUrl($controller['content'])) {
			// 			$proceed = false;
			// 			$message = "Address not valid"; //TODO: Translate and build something nice?
			// 		}
			// 	}
			// } else {
			// 	define("_PLUGIN",false);
			// 	define("_PLUGIN_CONTROLLER",false);
			// 	define("_PLUGIN_TITLE",false);
				
			// 	$proceed = false;
			// 	$message = "You are not authorized to access this page"; //TODO: Build something nice?
			// }
		} else {
			$proceed = false;
			$message = "This page you are looking does not exist."; //TODO: Build something nice?
		}

		if ($proceed==true) {
			switch (MB::strtoupper($controller['type'])) {
				case "INTERNAL":
					define("_INTERNAL",true);
				default:
				case "PLUGIN":
					$this->controller = $controller['controller'];
					$this->file = BASEDIR."plugins/".$this->controller."/".end($parts).".php";

					if (file_exists($this->file)==false) {
						$this->file = BASEDIR."plugins/".$this->controller."/".current($parts).".php";
					}

					//Breadcrumbs BASEDIR
					$this->breadcrumbs[] = "<span class='sys_breadcrumb'><a href='index.php' title='Home'>Home</a></span>";
					if (_PLUGIN_TITLE && isset($_GET[_NODE]))
						$this->breadcrumbs[] = "<span class='sys_breadcrumb'><a href='index.php?"._NODE."="._PLUGIN."' title='"._PLUGIN_TITLE."'>"._PLUGIN_TITLE."</a></span>";
										
					//Load controller file
					include($this->file);

					$route = explode("/", implode('/', $parts));					
					
					//Load controller class
					$class = preg_replace('/[^a-zA-Z0-9]/', '', end($parts))."Controller";
					$controller = new $class();

					$action = (is_callable(array($controller,$this->action))==false) ? "index" : $this->action ;
					$controller->$action();

					break;

				case "STATIC":
					//Breadcrumbs BASEDIR
					$this->breadcrumbs[] = "<span class='sys_breadcrumb'><a href='index.php' title='Home'>Home</a></span>";
					if (_PLUGIN_TITLE && isset($_GET[_NODE]))
						$this->breadcrumbs[] = "<span class='sys_breadcrumb'><a href='index.php?"._NODE."="._PLUGIN."' title='"._PLUGIN_TITLE."'>"._PLUGIN_TITLE."</a></span>";
					
					echo "<div class='breadcrumbs' style='margin:10px 0'>".implode(_BREADCRUMB_SEPARATOR,$this->breadcrumbs)."</div>";
					
					opentable(MB::ucfirst($controller['title']));
						echo Io::Output($controller['content']);
					closetable();
					break;

				case "REDIRECT":
					redirect($controller['content']);
					break;
			}
		} else {
			//Error message
			echo $message;
		}
	}
	
	function breadcrumbs() {
		return $this->breadcrumbs;
	}
}