<?php
require_once BASEDIR."plugins/"._PLUGIN."/acp/functions.php";

class userGroupController {
	function index() {
		global $db, $User, $config;

		if (isset($_GET['ref']) && $_GET['ref'] == 'delete') {
			$items = Io::GetVar("GET","id",false,true);

			if(in_array($items, array(103,102,101)) !== FALSE) {
				echo "Sorry you can not delete this role";
			} else {
				$result = $db->query("DELETE FROM ".PREFIX."user_group WHERE user_level=".$db->escape($items)) ? 1 : 0 ;
				$total = $db->countAffected();
				debug($total);
			}
		} elseif (isset($_GET['ref']) && $_GET['ref'] == 'form') {
			$id = Io::GetVar("GET", "id", "int");

			if ($id != 0) {
				$data = getUserGroup($id);
			} else {
				$data = null;
			}

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$input['id']	= Io::GetVar('POST', 'id', 'int', false, 0);
				$input['label'] = Io::GetVar('POST', 'label');
				$input['title'] = Io::GetVar('POST', 'title');
				$input['user_level'] = Io::GetVar('POST', 'user_level', 'int');
				$input['permission'] = Io::GetVar('POST', 'permission', false, array());
				$input['permission'] = json_encode($input['permission']);

				$errors = [];
				if (empty($input['label'])) $errors[] = 'Label field is required';
				if (empty($input['user_level'])) $errors[] = 'Level ID field is required';
				if (empty($input['title'])) $errors[] = 'Title field is required';

				if(!sizeof($errors)) {
					$lastid = ($input['id']==0) ? dbquery_insert(PREFIX.'user_group', $input, 'save') : dbquery_insert(PREFIX.'user_group', $input, 'update', 'user_level='.intval($id)); 
					redirect(Url::link('user/user_group'));
				} else {
					echo "<div class='page-header'>\n";
					echo "<div class='panel panel-default'>\n<div class='panel-body'>\n<div class='text-center'>\n";
					echo implode("<br>", $errors);
					echo "</div>\n</div>\n</div>\n";
					echo "</div>\n";
				}
			} else {
				opentable("<i class='fa fa-list'></i> ".(empty($id) ? 'Add User Group' : 'Edit User Group'));
				echo Form::Open((empty($id) ? Url::link('user/user_group&amp;ref=form') : Url::link('user/user_group&amp;ref=form&amp;id='.intval($id))), ['id'=>'form-user-group', 'class'=>'form-horizontal']);
					echo Form::Hidden('id', $data['id']);
					echo Form::AddElement(['element'=>'text', 'label'=>'Title', 'name'=>'title', 'value'=>$data['title']]);

					echo Form::AddElement(['element'=>'text', 'label'=>'Label', 'name'=>'label', 'value'=>$data['label']]);

					echo Form::AddElement(['element'=>'text', 'label'=>'User Level', 'name'=>'user_level', 'value'=>$data['user_level']]);

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
								$values = !empty($data['permission']['access']) ? $data['permission']['access'] : array();
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
								$values = !empty($data['permission']['modify']) ? $data['permission']['modify'] : array();
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
								$values = !empty($data['permission']['delete']) ? $data['permission']['delete'] : array();
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
						echo "<a href='".Url::link('user/user_group')."' data-toggle='tooltip' title='' class='btn btn-default'><i class='fa fa-reply'></i></a>\n";
					echo "</div>";
				echo Form::close();
				closetable();
			}
		} else {
			opentable("<a href='".Url::link('user/user_group&amp;ref=form')."' data-toggle='tooltip' title='Add Group' class='btn btn-default btn-xs'><i class='fa fa-plus'></i></a>");
					
	            	echo "<div class='dd'><ol class='dd-list'>";
	            		$result = $db->query("SELECT * FROM ".PREFIX."user_group")->rows;
						foreach ($result as $row) {
							echo "<li class='dd-item' data-id='".$row['id']."'>";
							echo "<div class='dd-handle'></div>";
							echo "<div class='dd-content'>";
								echo "<b>ID:{$row['id']}</b>&nbsp;&nbsp;";
								echo Io::Output($row['title']);
								echo "<div class='pull-right'>";
									echo "<a href='".Url::link('user/user_group&amp;ref=form&amp;id='.Io::Output($row['user_level']))."'><i title='Edit' alt='Edit' class='fa fa-pencil-square-o'></i></a>&nbsp;&nbsp;";
									echo "<a href='".Url::link('user/user_group&amp;ref=delete&amp;id='.Io::Output($row['user_level']))."'><i title='Delete' alt='Delete' class='fa fa-remove text-danger'></i></a>";
								echo "</div>";
							echo "</div>";
						}
	            	echo "</ol></div>";

			closetable();
		}

		if(isset($_GET['permission'])) {

		}
	}

	function permission() {
		$rid = Io::GetVar("GET", "role_id", "int");

		debug($rid);
	}
}