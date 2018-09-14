<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

require_once BASEDIR."plugins/"._PLUGIN."/acp/functions.php";

class userGroupController {
	function index() {
		global $db, $User, $config;

		if (isset($_GET['ref']) && $_GET['ref'] == 'delete') {

		} elseif (isset($_GET['ref']) && $_GET['ref'] == 'form') {
			$id = Io::GetVar("GET", "id", "int");

			if ($id != 0) {
				$data = $db->query("SELECT DISTINCT * FROM " . PREFIX . "user_group WHERE id = '" . (int)$id . "'")->row;
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

					echo "<div class='form-group'>";
						echo "<button type='submit' form='form-user-group' data-toggle='tooltip' class='btn btn-primary' data-original-title='Save'><i class='fa fa-save'></i></button> ";
						echo "<a href='".Url::link('user/user_group')."' data-toggle='tooltip' title='' class='btn btn-default'><i class='fa fa-reply'></i></a>\n";
					echo "</div>";

				echo Form::close();
				closetable();
			}
		} else {
			$date = new DateTime('2018-04-05');
			$result = $date->format('Y-m-d H:i:s');
			//debug(strtotime($result));
			opentable("<a href='".Url::link('user/user_group&amp;ref=form')."' data-toggle='tooltip' title='Add Group' class='btn btn-default btn-xs'><i class='fa fa-plus'></i></a>");
				echo "<div class='dd'><ol class='dd-list'>";
				$result = $db->query("SELECT * FROM #__user_levels")->rows;
				foreach($result as $row) {
					echo "<li class='dd-item' data-id='".$row['user_level_id']."'>";
					echo "<div class='dd-handle'></div>";
					echo "<div class='dd-content'>";
						echo "<b>ID:{$row['user_level_id']}</b>&nbsp;&nbsp;";
						echo Io::Output($row['user_level_name']);
						echo "<div class='pull-right'>";
							echo "<a href='".Url::link('user/user_group&amp;ref=form&amp;id='.Io::Output($row['user_level_id']))."' class='btn btn-primary btn-xs'>Edit Group</a>&nbsp;&nbsp;";
							echo "<a href='".Url::link('user/user_permission&amp;role_id='.Io::Output($row['user_level_id']))."' class='btn btn-primary btn-xs'>Edit Permissions</a>&nbsp;&nbsp;";
							echo "<a href='".Url::link('user/user_group&amp;ref=delete&amp;id='.Io::Output($row['user_level_id']))."' class='btn btn-danger btn-xs'><i title='Delete' alt='Delete' class='fa fa-remove text-danger'></i></a>";
						echo "</div>";
					echo "</div>";
				}
				echo "</ol></div>";
			closetable();
		}
	}
}