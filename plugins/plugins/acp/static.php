<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class staticController {
	function index() {
		global $db, $User, $config;

		$ref  = Io::GetVar("GET", "ref", "nohtml");
		switch($ref) {
			default:
            $result = $db->query("SELECT * FROM ".PREFIX."content WHERE type='STATIC' ORDER BY title ASC");
            echo "<table class='table table-striped table-bordered table-rounded table-hover'>";
            if($result->num_rows) {
	            foreach($result->rows as $row) {
					$id	= Io::Output($row['id'],"int");
					echo "<tr onmouseover='javascript:showmenu($id);' onmouseout='javascript:showmenu($id);'>\n";
						echo "<td>".Io::Output($row['title']);
							echo "<div id='menu_$id' style='display:none; margin-top:2px;' class='pull-right'>\n";
								echo "<span class='text-left btn btn-default btn-xs'>".Io::Output($row['status'])."</span> ";
							if($User->hasPermission('modify', 'plugins/static')) {
								echo "<a href='".Url::link('plugins/static&amp;ref=form&amp;id='.$id)."' class='btn btn-primary btn-xs'>EDIT</a>\n";
							}
							if($User->hasPermission('delete', 'plugins/static')) {
								echo "<a href='".Url::link('plugins/static&amp;ref=delete&amp;id='.$id)."' class='btn btn-danger btn-xs'>Uninstall</a>\n";
							}
							echo "</div>";
						echo "</td>";
					echo "</tr>";
	            }
            } else {
            	echo "<tr><td class='text-center'>No records found.</td></tr>";
            }
            echo "</table>";
			break;

			case 'form':
			    $id  = Io::GetVar("GET", "id", "int");
			    $ok  = Io::GetVar("GET", "ok", "bool", false, false);
				if(empty($id)) {
					$data = null;
				} else {
					$data = $db->query("SELECT * FROM ".PREFIX."content WHERE type='STATIC' AND id=".intval($id))->row;
				}
				
				if($_SERVER['REQUEST_METHOD'] == 'POST') {
					$input['id'] = Io::GetVar("POST", "id", "int", false, 0);
					$input['title'] = Io::GetVar('POST','title','fullhtml');
					$input['name'] = clean_url($input['title']);
					$input['type'] = 'STATIC';
					$input['content'] = Io::GetVar("POST", "content", "fullhtml");
					$input['roles'] = Io::GetVar('POST','roles','nohtml',true,array());
					$input['acp'] = Io::GetVar('POST','acp','nohtml');
					$input['status'] = Io::GetVar('POST','status','nohtml');

					$errors = array();
					
					if (empty($input['title'])) $errors[] = "Title Field is required";
					if($db->query("SELECT id FROM ".PREFIX."content WHERE title='".$input['title']."'")->row) {
						$errors[] = "Title is already exist";
					}

					if (!sizeof($errors)) {
						if (in_array("ALL",$input['roles'])) $input['roles'] = array();
						$input['roles'] = Utils::Serialize($input['roles']);

						if(empty($id)) {
							dbquery_insert(PREFIX."content", $input, "save");
						} else {
							dbquery_insert(PREFIX."content", $input, "update", "id=".intval($id));
						}
						redirect(Url::link('plugins/static'));
					}
				} else {
					echo Form::Open();
						opentable();
						echo "<input type='submit' class='btn btn-success' name='save' value='Save'>";
						echo Form::AddElement(array('element'=>'text', 'label'=>'Title', 'name'=>'title', 'value'=>$data['title']));

						echo Form::AddElement(array('element'=>'textarea_adv', 'name'=>'content', 'value'=>$data['content'], 'id'=>'editor'));

						echo Form::AddElement(['element'=>'select', 'label'=>'ACP', 'name'=>'acp', 'values'=>['Yes'=>'yes', 'No'=>'no'], 'selected'=>$data['acp']]);
						echo Form::AddElement(array('element'=>'select', 'label'=>'Status', 'name'=>'status', 'values'=>['Active'=>'active', 'Inactive'=>'inactive', 'Acp'=>'acp'], 'selected'=>$data['status']));
						closetable();
					echo Form::Close();
				}
			break;
		}
	}
}