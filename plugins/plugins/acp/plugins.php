<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class pluginsController {
	function index() {
		global $db, $User, $config;

	    $id  = Io::GetVar("GET", "id", "int", false, 0);
	    $ok  = Io::GetVar("GET", "ok", "bool", false, false);

		if (isset($_GET['ref']) && $_GET['ref'] == "uninstall") {
			if ($row = $db->query("SELECT * FROM ".PREFIX."content WHERE id=".intval($id))->row) {
				$controller	= Io::Output($row['controller']);

            	$setupresult = false;
        		if (file_exists(BASEDIR."plugins/".$controller."/setup.php")) {
        			include_once(BASEDIR."plugins/".$controller."/setup.php");
            	
            		if (method_exists("Setup","Uninstall")) {
            			$setupresult = Setup::Uninstall();
            		}
            	}

            	if (!empty($setupresult)) {
					$result = $db->query("DELETE FROM ".PREFIX."menu_acp WHERE rights='".($controller ? $controller : "")."' AND url='admin.php?cont=".$controller."'");
					$result = $db->query("SELECT id, user_rights FROM ".PREFIX."user WHERE user_level<='103'");
					foreach($result->rows as $data) {
						$user_rights = explode(".", $data['user_rights']);
						if (in_array($controller, $user_rights)) {
							$key = array_search($controller, $user_rights);
							unset($user_rights[$key]);
						}
						$result2 = $db->query("UPDATE ".PREFIX."user SET user_rights='".implode(".", $user_rights)."' WHERE id='".$data['id']."'");
					}

            		$db->query("DELETE FROM ".PREFIX."content WHERE id='".intval($id)."'");
            		$db->query("DELETE FROM ".PREFIX."menu_acp WHERE uniqueid='{$controller}_main'");
            		redirect(Url::link('plugins/plugins'));
            	}	            	
			}
		} elseif (isset($_GET['ref']) && $_GET['ref'] == "form") {
			if(empty($id)) {
				$data = null;
			} else {
				$data = $db->query("SELECT * FROM ".PREFIX."content WHERE id=".intval($id))->row;
			}

			if(isset($_POST['submit'])) {
				$input['title'] = Io::GetVar('POST','title','fullhtml');
				$input['name'] = clean_url($input['title']);
				$input['controller'] = Io::GetVar('POST','controller','nohtml');
				$input['type'] = Io::GetVar('POST','type','nohtml','PLUGIN');
				$input['roles'] = Io::GetVar('POST','roles','nohtml',true,array());
				$input['acp'] = Io::GetVar('POST','acp','nohtml');
				$input['status'] = Io::GetVar('POST','status','nohtml');
				
				$errors = array();
				if(empty($input['title'])) $errors[] = "Title field is required";

				//Install plugin
				if(empty($id)) {
					$setupresult = false;
					if (!sizeof($errors)) {
						if (file_exists(BASEDIR."plugins/".$input['controller']."/setup.php")) {
							include_once(BASEDIR."plugins/".$input['controller']."/setup.php");
							
							if (method_exists("Setup","Install")) {
								$setupresult = Setup::Install();
							}
						}
					}
				}

				if (!sizeof($errors)) {
                    if (in_array("ALL",$input['roles'])) $input['roles'] = array();
                    $input['roles'] = Utils::Serialize($input['roles']);
                    
					if(empty($id)) {
						dbquery_insert(PREFIX."content", $input, "save");
					} else {
						dbquery_insert(PREFIX."content", $input, "update", "id=".intval($id));
					}
					redirect(Url::link('plugins/plugins'));
				} else {
					Error::Trigger('INFO', implode("<br />",$errors));
				}
			} else {
				echo Form::Open(FUSION_REQUEST);
					opentable("<button type='submit' class='btn btn-success' name='submit'>Save</button>");
					echo Form::AddElement(array('element'=>'text', 'label'=>'Title', 'name'=>'title', 'value'=>$data['title']));
					
					if(empty($id)) {
	                    //Controller
	                    $tdir = Utils::GetDirContent(BASEDIR."plugins/");
	                    $controllers = array();
						foreach ($tdir as $dir) {
							if (file_exists(BASEDIR."plugins/".$dir."/setup.php")) {
	                        	$controllers[MB::ucfirst($dir)] = $dir;
	                        }
	                    }

	                    echo Form::AddElement(['element'=>'select', 'label'=>'Controller', 'name'=>'controller', 'values'=>$controllers]);

	                    echo Form::AddElement(['element'=>'select', 'label'=>'Type', 'name'=>'type', 'values'=>['Plugin'=>'PLUGIN', 'Redirect'=>'REDIRECT', 'Static'=>'STATIC'], 'id'=>'type']);
					}

					echo Form::AddElement(['element'=>'select', 'label'=>'ACP', 'name'=>'acp', 'values'=>['Yes'=>'yes', 'No'=>'no'], 'id'=>'acp']);
					echo Form::AddElement(['element'=>'select', 'label'=>'Status', 'name'=>'status', 'values'=>['Active'=>'active', 'Inactive'=>'inactive', 'Acp'=>'acp'], 'id'=>'status']);
					closetable();
				echo Form::Close();
			}
		} else {
			$type = Io::GetVar("GET","type",false,true,"PLUGIN");
			//Options
			$sortby = $db->escape(Io::GetVar("GET","sortby",false,true,"title"));
			$order = $db->escape(Io::GetVar("GET","order",false,true,"ASC"));
            //Where
            $where = "WHERE ";
			$where .= "type='".$db->escape(MB::strtoupper($type))."'";
            $where .= " AND status!='ACP'";

            $result = $db->query("SELECT * FROM ".PREFIX."content {$where} ORDER BY $sortby $order");
            echo "<table class='table table-striped table-bordered table-rounded table-hover'>";
            echo "<tr><td>Plugin Title<span class='pull-right'><a href='".Url::link('plugins/plugins&amp;ref=form')."' class='btn btn-primary btn-xs'>Add Plugin</a></span></td></tr>";
            foreach($result->rows as $row) {
				$id	= Io::Output($row['id'],"int");
				$controller = Io::Output($row['controller']);
				$type = Io::Output($row['type']);
				if(file_exists(BASEDIR."plugins/".$controller."/setup.php")) {
					echo "<tr onmouseover='javascript:showmenu($id);' onmouseout='javascript:showmenu($id);'>\n";
						echo "<td>".Io::Output($row['title']);
							echo "<div id='menu_$id' style='display:none; margin-top:2px;' class='pull-right'>\n";
								echo "<span class='text-left btn btn-default btn-xs'>".Io::Output($row['status'])."</span> ";
							if($User->hasPermission('modify', 'plugins/plugins')) {
								echo "<a href='".Url::link('plugins/plugins&amp;ref=form&amp;id='.$id)."' class='btn btn-primary btn-xs'>Edit</a> ";
							}
							if($User->hasPermission('delete', 'plugins/plugins')) {
								echo "<a href='".Url::link('plugins/plugins&amp;ref=uninstall&amp;id='.$id)."' class='btn btn-danger btn-xs'>Uninstall</a> \n";
							}
							echo "</div>";
						echo "</td>";
					echo "</tr>";
				}
            }
            echo "</table>";
		}
	}
}