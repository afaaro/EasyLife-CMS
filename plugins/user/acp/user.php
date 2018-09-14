<?php

class userController {
	function index() {
		global $db, $User, $config;

		$id = Io::GetVar("GET", "id", "int", false, 0);
		if (isset($_GET['ref']) && $_GET['ref'] == "form") {
			if ($id != 0) {
				$data = getUser($id);
				$data['password'] = '';
				$data['cpassword'] = '';
			} else {
				$data = null;
			}
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$input['username'] = Io::GetVar('POST', 'username');
				$input['password'] = Io::GetVar('POST', 'password');
				$input['cpassword'] = Io::GetVar('POST', 'cpassword');
				$input['email'] = Io::GetVar('POST', 'email');
				$input['real_name'] = Io::GetVar('POST', 'real_name');
				$input['user_level'] = Io::GetVar('POST', 'user_level', 'int');
				$input['status'] = Io::GetVar('POST', 'status');

				$errors = [];
				if (empty($input['username'])) $errors[] = 'Username field is required';
				if (empty($input['password'])) $errors[] = 'Password field is required';
				if (empty($input['cpassword'])) $errors[] = 'Confirm Password field is required';
				if ($input['password'] != $input['cpassword']) $errors[] = 'Password don\'t match';

				if(!sizeof($errors)) {
					$lastid = ($id) ? dbquery_insert(PREFIX.'user', $input, 'save') : dbquery_insert(PREFIX.'user', $input, 'update'); 
				} else {
					echo "<div class='page-header'>\n";
					echo "<div class='panel panel-default'>\n<div class='panel-body'>\n<div class='text-center'>\n";
					echo implode("<br>", $errors);
					echo "</div>\n</div>\n</div>\n";
					echo "</div>\n";
				}
			} else {
				// User Form
			}
		} else {
			// List User
		}
	}
}