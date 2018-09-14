<?php

class logoutController {
	function index() {
		global $db,$User,$config;

		$User->Logout();
		redirect($config['site_url']);
	}
}