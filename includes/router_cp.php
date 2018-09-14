<?php

/**
 * Action class
 */
class Router {
	private $id;
	private $route;
	private $method = 'index';

	/**
	 * Constructor
	 *
	 * @param    string $route
	 */
	public function __construct($route) {
		$this->id = $route;

		$parts = explode('/', preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route));
		// Break apart the route
		while ($parts) {
			$file = BASEDIR.'plugins/'.current($parts).'/acp/' . end($parts) . '.php';

			if (is_file($file)) {
				$this->route = implode('/', $parts);
				break;
			} else {
				$this->method = array_pop($parts);
			}
		}
	}

	/**
	 *
	 *
	 * @return    string
	 *
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 *
	 *
	 * @param    object $registry
	 * @param    array $args
	 */
	public function Run(array $args = array()) {
		global $db;
		// Stop any magical methods being called
		if (substr($this->method, 0, 2) == '__') {
			return new \Exception('Error: Calls to magic methods are not allowed!');
		}

		$parts = explode('/', preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$this->route));
		if ($row = $db->query("SELECT id,title,name,controller,type,content,options FROM ".PREFIX."content WHERE name='".$db->escape(current($parts))."' AND acp='yes'")->row) {
			$controller = array();
			$controller['id'] = Io::Output($row['id'],"int");
			$controller['title'] = Io::Output($row['title']);
			$controller['name'] = Io::Output($row['name']);
			$controller['controller'] = Io::Output($row['controller']);
			$controller['options'] = Utils::Unserialize(Io::Output($row['options']));

			//Store controller's information in Ram
			Ram::Set('controller',$controller);

			//Site title step
			$pt = Io::GetVar("GET","cont","[^a-zA-Z0-9\-]");
			if (!empty($pt)) Handler::addToTitle($controller['title']);

			define("_PLUGIN",$controller['name']);
			define("_PLUGIN_CONTROLLER",$controller['controller']);
			define("_PLUGIN_TITLE",$controller['title']);

			$proceed = true;
		} else {
			$proceed = false;
			$plugin = "dashboard";
			$file = BASEDIR.'plugins/'.$plugin.'/acp/dashboard.php';

			$controller['controller'] = "dashboard";
			Ram::Set('controller',$controller);
			define("_PLUGIN",false);
			define("_PLUGIN_CONTROLLER",false);
			define("_PLUGIN_TITLE",false);
		}
		
		if ($proceed==true) {
			$plugin = $controller['controller'];

			$file = BASEDIR.'plugins/'.$plugin.'/acp/' . end($parts) . '.php';

			if (file_exists($file)===false) {
				$plugin = 'dashboard';
				$file = BASEDIR."plugins/".$plugin."/acp/dashboard.php";		
			}

			//Load controller file
			include($file);

			$route = explode("/", $this->route);

			$class = preg_replace('/[^a-zA-Z0-9]/', '', end($route)).'Controller';
			$controller = new $class();

			$reflection = new \ReflectionClass($class);

			if ($reflection->hasMethod($this->method) && $reflection->getMethod($this->method)->getNumberOfRequiredParameters() <= count($args)) {
				return call_user_func_array(array($controller, $this->method), $args);
			} else {
				return new \Exception('Error: Could not call ' . $this->route . '/' . $this->method . '!');
			}
		} else {
			opentable("Error Found");
			Error::Trigger("USERERROR", "Sorry");
			closetable();
		}
	}
}