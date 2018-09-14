<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class menuController {
	function index() {
		global $db, $User, $config;
	
		opentable("Menu");
		    $id  = Io::GetVar("GET", "id", "int");
		    $ok  = Io::GetVar("GET", "ok", "bool", false, false);
			$ref  = Io::GetVar("GET", "ref", "nohtml");
			switch ($ref) {
				default:
	            	echo "<div class='dd'><ol class='dd-list'>";
	            		$result = $db->query("SELECT * FROM ".PREFIX."menu");
						foreach ($result->rows as $row) {
							echo "<li class='dd-item' data-id='".$row['id']."'>";
							echo "<div class='dd-handle'></div>";
							echo "<div class='dd-content'>";
								echo "<b>ID:{$row['id']}</b>&nbsp;&nbsp;";
								echo Io::Output($row['title']);
								echo "<div class='pull-right'>";
									echo "<a href='".Url::link('plugins/menu&amp;ref=form&id='.$row['id'])."'><i title='Edit' alt='Edit' class='fa fa-pencil-square-o'></i></a>&nbsp;&nbsp;";
									echo "<a href='".Url::link('plugins/menu&amp;ref=delete&id='.$row['id'])."'><i title='Delete' alt='Delete' class='fa fa-trash text-danger'></i></a>";
								echo "</div>";
							echo "</div>";
						}
	            	echo "</ol></div>";
					break;

				case 'form':
					if(empty($id)) {
						$data = null;
					} else {
						$data = $db->query("SELECT * FROM ".PREFIX."menu WHERE id=".intval($id))->row;
					}

					if($_SERVER['REQUEST_METHOD'] == 'POST') {
						$input['title'] 	= Io::GetVar('POST','title','fullhtml');
						$input['url'] 		= Io::GetVar('POST','url','nohtml');
						$input['zone'] 		= Io::GetVar('POST','zone','nohtml');
						$input['position'] 	= Io::GetVar('POST','position','int');
						$input['roles'] 	= Io::GetVar('POST','roles','nohtml',true,array());


						$errors = array();
						if (empty($input['title'])) $errors[] = "Title field is required";
						if (empty($input['url'])) $errors[] = "Url field is required";

						if (!sizeof($errors)) {
							if (in_array("ALL",$roles)) $roles = array();
							$input['roles'] = Utils::Serialize($input['roles']);

                            if ($input['position']==0) {
                                $row = $Db->GetRow("SELECT position FROM ".PREFIX."menu WHERE zone='".$db->escape($input['zone'])."' ORDER BY position DESC LIMIT 1");
                                $input['position'] = Io::Output($row['position']);
                                $input['position']++;
                            }
							if(empty($id)) {
								// Insert
								dbquery_insert(PREFIX."menu", $input, "save");
							} else {
								// Update
								dbquery_insert(PREFIX."menu", $input, "update", "id=".intval($id));
							}
						}

						redirect(Url::link('plugins/menuacp'));
					} else {
						echo Form::Open();
							echo Form::AddElement(array('element'=>'text', 'name'=>'title', 'label'=>'Title', 'value'=>$data['title']));
							echo Form::AddElement(array('element'=>'text', 'name'=>'url', 'label'=>'Url', 'value'=>$data['url']));

							echo Form::AddElement(array('element'=>'select',
								'label'=>'Zone', 
								'name'=>'zone', 
								'values'=>array('Header'=>'head', 'Navigation'=>'nav'), 
								'selected'=>$data['zone'],
								'optdisabled'=>array(0),
								'class'=>'form-control',
							));

							echo Form::AddElement(array('element'=>'submit', 'name'=>'save', 'value'=>'Save', 'class'=>'btn btn-primary'));
						echo Form::Close();
					}
					break;

				case 'delete':
					debug($id);
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
		closetable();
	}
}