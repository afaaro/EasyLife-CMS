<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class userPermissionController {
	function index() {
		global $db, $User, $config;

		$id = Io::GetVar("GET", "id", "int");
		$user_permission = $db->select('#__user', 'id,username,user_rights', 'id='.intval($id));

		$user_rights = json_decode($user_permission[0]['user_rights'], true);

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$input['id']	= Io::GetVar('POST', 'id', 'int', false, 0);
			$input['user_rights'] = Io::GetVar('POST', 'permission', false, array());
			$input['user_rights'] = json_encode($input['user_rights']);

			//debug($input);
			dbquery_insert('#__user', $input, 'update', 'id='.intval($id)); 
		} else {
			opentable("<i class='fa fa-list'></i> Edit [".$user_permission[0]['username']."] Permission");
				echo Form::Open((empty($id) ? Url::link('user/user_permission') : Url::link('user/user_permission&amp;id='.intval($id))), ['id'=>'form-user-group', 'class'=>'form-horizontal']);
					echo Form::Hidden('id', $user_permission[0]['id']);

                    //Required roles
					$ignore = array(
						'error/error',
						'user/functions',
					);

					$permissions = array();
					$files = array();

					// Make path into an array
					$path = array(BASEDIR.'plugins/*/acp/*');
					
					// While the path array is still populated keep looping through
					while(count($path) != 0) {
						$next = array_shift($path);

						foreach(glob($next) as $file) {
							// If directory add to path array
							if (is_dir($file)) {
								$path[] = $file . '/*';
							}


							// Add the file to the files to be deleted array
							if (is_file($file)) {
								$files[] = $file;
							}
						}
					}
					$files = str_replace('acp/', '', $files);

					// Sort the file array
					sort($files);

					foreach($files as $file) {
						$controller = substr($file, strlen(BASEDIR . 'plugins/'));

						$permission = substr($controller, 0, strrpos($controller, '.'));
						
						if (!in_array($permission, $ignore)) {
							$permissions[] = $permission;
						}
					}

					echo "<div class='form-group'>";
						echo "<label class='control-label'>Can Access</label>";
							echo "<div class='well well-sm' style='height:150px;overflow:auto;'>";
							foreach($permissions as $permission) {
								$values = !empty($user_rights['access']) ? $user_rights['access'] : array();
								echo "<div class='checkbox'><label>";
								$chkd = (array_search($permission, $values) !== false) ? 'checked="checked"' : '';
								//echo "<input type='hidden' name='permission[access][]' value='".$permission."' />";
								echo "<input type='checkbox' class='minimal' name='permission[access][]' " .$chkd. " value='".$permission."' />$permission";
								echo "</label></div>";
							}
							echo "</div>";
							echo "<button type=\"button\" onclick=\"$(this).parent().find(':checkbox').prop('checked', true);\" class=\"btn btn-link\">Select all</button> / <button type=\"button\" onclick=\"$(this).parent().find(':checkbox').prop('checked', false);\" class=\"btn btn-link\">Deselect All</button>";
					echo "</div>";

					echo "<div class='form-group'>";
						echo "<label class='control-label'>Can Modify</label>";
							echo "<div class='well well-sm' style='height:150px;overflow:auto;'>";
							foreach($permissions as $permission) {
								$values = !empty($user_rights['modify']) ? $user_rights['modify'] : array();
								echo "<div class='checkbox'><label>";
								$chkd = (array_search($permission, $values) !== false) ? 'checked="checked"' : '';
								//echo "<input type='hidden' name='permission[modify][]' value='".$permission."' />";
								echo "<input type='checkbox' class='minimal' name='permission[modify][]' " .$chkd. " value='".$permission."' />$permission";
								echo "</label></div>";
							}
							echo "</div>";
							echo "<button type=\"button\" onclick=\"$(this).parent().find(':checkbox').prop('checked', true);\" class=\"btn btn-link\">Select all</button> / <button type=\"button\" onclick=\"$(this).parent().find(':checkbox').prop('checked', false);\" class=\"btn btn-link\">Deselect All</button>";
					echo "</div>";

					echo "<div class='form-group'>";
						echo "<label control-label'>Can Delete</label>";
							echo "<div class='well well-sm' style='height:150px;overflow:auto;'>";
							foreach($permissions as $permission) {
								$values = !empty($user_rights['delete']) ? $user_rights['delete'] : array();
								echo "<div class='checkbox'><label>";
								$chkd = (array_search($permission, $values) !== false) ? 'checked="checked"' : '';
								//echo "<input type='hidden' name='permission[delete][]' value='".$permission."' />";
								echo "<input type='checkbox' class='minimal' name='permission[delete][]' " .$chkd. " value='".$permission."' />$permission";
								echo "</label></div>";
							}
							echo "</div>";
							echo "<button type=\"button\" onclick=\"$(this).parent().find(':checkbox').prop('checked', true);\" class=\"btn btn-link\">Select all</button> / <button type=\"button\" onclick=\"$(this).parent().find(':checkbox').prop('checked', false);\" class=\"btn btn-link\">Deselect All</button>";
					echo "</div>";

					echo "<div class='form-group'>";
						echo "<button type='submit' form='form-user-group' data-toggle='tooltip' class='btn btn-primary' data-original-title='Save'><i class='fa fa-save'></i></button>\n";
						echo "<a href='".Url::link('user/user')."' data-toggle='tooltip' title='' class='btn btn-default'><i class='fa fa-reply'></i></a>\n";
					echo "</div>";
				echo Form::close();
			closetable();
		}
		// $permissions = $db->query("SELECT * FROM #__user_levels_permissions WHERE user_level_id=".$role_id)->rows;

	 //    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	 //    	$permissions_post = isset($_POST['permission']) ? $_POST['permission'] : null;

	 //    	foreach($permissions_post as $table => $post) {
	 //    		$input['create_any'] = $post['create_any'];
	 //    		$input['modify_own'] = $post['modify_own'];
	 //    		$input['modify_any'] = $post['modify_any'];
	 //    		$input['delete_own'] = $post['delete_own'];
	 //    		$input['delete_any'] = $post['delete_any'];
	 //    		$input['view'] = $post['view'];

	 //    		$result = $db->query("SELECT COUNT(*) AS total FROM #__user_levels_permissions WHERE user_level_id='{$role_id}' AND controller='{$table}'");
	 //    		if($result->row['total'] == 0) {
	 //    			$input['controller'] = $table;
	 //    			$input['user_level_id'] = $role_id;
	 //    			dbquery_insert('#__user_levels_permissions', $input, 'save');
		//     		redirect(Url::link('user/user_permission&amp;role_id='.$role_id), 0);
	 //    		} else {
	 //    			dbquery_insert('#__user_levels_permissions', $input, 'update', 'user_level_id='.intval($role_id));
	 //    			redirect(Url::link('user/user_group'), 0);
	 //    		}
	 //    	}    	
	 //    } else {
		//     if(!empty($permissions)) {
		//     	$permissions = $permissions;
		//     } else {
		//         $permissions[] = array(
		//         	'user_level_permission_id'=>'',
		//         	'user_level_id'			  =>'',
		//             'create_any'	=> '0',
		//             'modify_own' 	=> '0',
		//             'modify_any' 	=> '0',
		//             'delete_own' 	=> '0',
		//             'delete_any' 	=> '0',
		//             'view' 			=> '1',
		//             'controller'	=> '',
		//         );
		//     }

		//     //for($i=0; $i < count($permissions); $i++) unset($permissions[$i]['user_level_permission_id'],$permissions[$i]['user_level_id'],$permissions[$i]['controller']);
		//     opentable("<a href='".Url::link('user/user_group')."' data-toggle='tooltip' title='' class='btn btn-default'><i class='fa fa-reply'></i></a>");
		//     echo "<form method='post' action=''><table class='table table-condensed table-striped table-bordered table-hover'>";
		//     echo "<tr>";
		//     	echo "<th class='header'>Table name</th>";
		//     	echo "<th align='center' style='text-align: center;'>Create</th>";
		//     	echo "<th align='center' style='text-align: center;'>Modify Own</th>";
		//     	echo "<th align='center' style='text-align: center;'>Modify Any</th>";
		//     	echo "<th align='center' style='text-align: center;'>Delete Own</th>";
		//     	echo "<th align='center' style='text-align: center;'>Delete Any</th>";
		//     	echo "<th align='center' style='text-align: center;'>Can View</th>";
		//     echo "</tr>";
		//     $tables = list_tables();
		//     foreach($tables as $table) {
		//     	for($i=0; $i < count($permissions) && $i < count($table); $i++) {
		//     		unset($permissions[$i]['user_level_permission_id'],$permissions[$i]['user_level_id'],$permissions[$i]['controller']);
		//     		echo "<tr>";
		//     			echo "<td class='head'>".$table."</td>";
		//     			$permission = $permissions[$i];
		//     			foreach($permission as $k => $v) {
		// 					$chkd = ($v==1) ? 'checked="checked"' : '';
		// 					echo "<td align='center' style='text-align: center;'>";
		// 					echo "<input type='hidden' name='permission[".$table."][".$k."]' value='0' />";
		// 					echo "<input type='checkbox' class='minimal' name='permission[".$table."][".$k."]' " .$chkd. " value='1' />";
		// 					echo "</td>";
		//     			}
		//     		echo "</tr>";
		//     	}
		//     }
		//    		echo "<button type=\"button\" onclick=\"$(this).parent().find(':checkbox').prop('checked', true);\" class=\"btn btn-link\">Select all</button> / <button type=\"button\" onclick=\"$(this).parent().find(':checkbox').prop('checked', false);\" class=\"btn btn-link\">Deselect All</button>";
		//     echo "<input type='submit' name='submit' value='submit'>";
		//     echo "</table></form>";
		//     closetable();
	 //    }
	}
}