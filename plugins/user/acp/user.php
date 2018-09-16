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
			opentable("<a href='".Url::link('user/user_group&amp;ref=form')."' data-toggle='tooltip' title='Add Group' class='btn btn-default btn-xs'><i class='fa fa-plus'></i></a>");
			echo "<div class='dd'><ol class='dd-list'>";
			foreach($db->select('#__user') as $row) {			
				echo "<li class='dd-item' data-id='".$row['id']."'>";
				echo "<div class='dd-handle'></div>";
				echo "<div class='dd-content'>";
					echo "<b>ID:{$row['id']}</b>&nbsp;&nbsp;";
					echo Io::Output($row['username']);
					echo "<div class='pull-right'>";
						echo "<a href='".Url::link('user/user_group&amp;ref=form&amp;id='.Io::Output($row['id']))."' class='btn btn-primary btn-xs'>Edit Group</a>&nbsp;&nbsp;";
						echo "<a href='".Url::link('user/user_permission&amp;id='.Io::Output($row['id']))."' class='btn btn-primary btn-xs'>Edit Permissions</a>&nbsp;&nbsp;";
						echo "<a href='".Url::link('user/user_group&amp;ref=delete&amp;id='.Io::Output($row['id']))."' class='btn btn-danger btn-xs'><i title='Delete' alt='Delete' class='fa fa-remove text-danger'></i></a>";
					echo "</div>";
				echo "</div>";
			}
			echo "</ol></div>";
			closetable();
		}
	}
}