<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class optionController {
	function index() {
		global $db, $User, $action;

		opentable();
			$controller = Io::GetVar("GET","controller","#[^a-zA-Z0-9\-]#i");
		    $id  = Io::GetVar("GET", "id", "int");
		    $ok  = Io::GetVar("GET", "ok", "bool", false, false);
			$ref  = Io::GetVar("GET", "ref", "nohtml");

			switch ($ref) {
				default:
					if ($row = $db->query("SELECT title,name,options FROM ".PREFIX."content WHERE controller='".$db->escape($controller)."'")->row) {
						$title		= Io::Output($row['title']);
						$name		= Io::Output($row['name']);
						$options	= Utils::Unserialize(Io::Output($row['options']));

						opentable($title);
						if (sizeof($options)) {
							unset($options['layout']);
							echo "<div class='dd'><ol class='dd-list'>";
							foreach ($options as $key => $value) {
								echo "<li class='dd-item' data-id='".$key."'>";
									echo "<div class='dd-handle'></div>";
									echo "<div class='dd-content'>";
										echo "<b>{$key}</b>&nbsp;&nbsp;";
										//echo $value;
										echo "<div class='pull-right'>";
											echo "<a href='".Url::link('plugins/option&amp;ref=form&amp;controller='.$controller.'&amp;label='.$key)."'><i title='Edit' alt='Edit' class='fa fa-pencil-square-o'></i></a>&nbsp;&nbsp;";
											echo "<a href='".Url::link('plugins/option&amp;ref=delete&amp;controller='.$controller.'&amp;label='.$key)."'><i title='Delete' alt='Delete' class='fa fa-trash text-danger'></i></a>";
										echo "</div>";
									echo "</div>";
								echo "</li>";
							}
							echo "</ol></div>";
						}
						closetable();
					}
					break;

				case 'form':
					debug($action);
					$label = Io::GetVar("GET","label");
					if(empty($label)) {
						$label = "";
						$value = "";
					} else {
						$data = $db->query("SELECT * FROM ".PREFIX."content WHERE controller=".$db->escape($controller))->row;
						$opt = Utils::Unserialize(Io::Output($data['options']));
						$value = (isset($opt[$label])) ? $opt[$label] : "" ;
					}

					if (isset($_POST['save'])) {
						//Get POST data
						$label = Io::GetVar('POST','label');
						$value = Io::GetVar('POST','value');
						
						$errors = array();
						if (empty($label)) $errors[] = "Label field is required";
						if ($value=="") $errors[] = "Value field is required";

						if (!sizeof($errors)) {
							debug($_POST);
							//SetOption($label,$value,$controller);
						} else {
							Error::Trigger("USERERROR",implode("<br />",$errors));
						}
					} else {
						echo Form::Open();
						//Title
						echo Form::AddElement(array("element"	=>"text",
												"label"		=>"Label",
												"width"		=>"300px",
												"value"		=>$label,
												"name"		=>"label",
												"info"		=>"Required"));
												
						//Name
						echo Form::AddElement(array("element"	=>"text",
												"label"		=>"Link Name",
												"name"		=>"value",
												"value"		=>$value,
												"width"		=>"300px",
												"info"		=>"Required"));

						echo Form::AddElement(array('element'=>'submit', 'name'=>'save', 'value'=>'Save', 'class'=>'btn btn-primary'));
						echo Form::Close();
					}

					break;
			}
		closetable();

    	?>
		<script type='text/javascript'>
			$(document).ready(function(){
				$(".dd").nestable({
				  maxDepth: 500,
				  collapsedClass:'dd-collapsed',
				});
				$('.dd').nestable('collapseAll');
				
				$('.dd-handle a').on('mousedown', function(e){
					e.stopPropagation();
				});

				$('.dd-handle a').on('touchstart', function(e){
					e.stopPropagation();
				});
			});
		</script>
    	<?php
	}
}


function GetOption($key,$default=false) {
	$controller = Ram::Get("controller");
	return (isset($controller['options'][$key])) ? $controller['options'][$key] : $default ;
}

function SetOption($key,$value,$controller=false) {
	global $db;

	if ($controller) {
		if ($row = $db->query("SELECT id,options FROM ".PREFIX."content WHERE controller='".$db->escape($controller)."'")->row) {
			$opt = Utils::Unserialize(Io::Output($row['options']));
			$opt[$key] = $value;
			if ($db->Query("UPDATE ".PREFIX."content SET options='".$db->escape(Utils::Serialize($opt))."' WHERE id='".intval(Io::Output($row['id']))."'")) {
				return true;
			}
		}
	} else {
		$controller = Ram::Get("controller");
		$controller['options'][$key] = $value;
		Ram::Set("controller",$controller);
		if ($db->query("UPDATE ".PREFIX."content SET options='".$db->escape(Utils::Serialize($controller['options']))."' WHERE id='".intval($controller['id'])."'")) {
			return true;
		}
	}
	return false;
}

function DeleteOption($key,$controller=false) {
	global $db;

	if ($controller) {
		if ($row = $db->GetRow("SELECT id,options FROM ".PREFIX."content WHERE controller='".$db->escape($controller)."'")->row) {
			$opt = Utils::Unserialize(Io::Output($row['options']));
			unset($opt[$key]);
			if ($db->query("UPDATE ".PREFIX."content SET options='".$db->escape(Utils::Serialize($opt))."' WHERE id='".intval(Io::Output($row['id']))."'")) {
				return true;
			}
		}
	} else {
		$controller = Ram::Get("controller");
		if (isset($controller['options'][$key])) {
			unset($controller['options'][$key]);
			Ram::Set("controller",$controller);
			$db->query("UPDATE ".PREFIX."content SET options='".$db->escape(Utils::Serialize($controller['options']))."' WHERE id='".intval($controller['id'])."'");
			return true;
		}			
	}
	return false;
}