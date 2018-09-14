<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class categoryController {
	function index() {
		global $db, $User, $config;

	    $id  = Io::GetVar("GET", "id", "int");
	    $ok  = Io::GetVar("GET", "ok", "bool", false, false);
	    $cat = Io::GetVar("GET", "cat", "int", false, "0");

	    if (isset($_GET['ref']) && $_GET['ref'] == "delete") {
	    	if($ok) {
	    		debug($id);
	    	} else {
	    		Error::Trigger('INFO', "<div align='center'><b>Delete the category?</b><br><a href='".Url::link('post/category&ref=delete&id='.$id.'&ok=true')."' title='Yes'>Yes</a> - <a href='".Url::link('post/category')."' title='No'>No</a></div>");
	    	}
		} elseif (isset($_GET['ref']) && $_GET['ref'] == "form") {
			if(empty($id)) {
				$data = null;
			} else {
				$data = $db->query("SELECT * FROM ".PREFIX."post_categories WHERE id=".intval($id))->row;
			}

			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				$input['id']		= Io::GetVar("POST", "id", "int", false, 0);
				$input['title'] 	= Io::GetVar("POST", "title", "nohtml", false);
				$input['parent'] 	= Io::GetVar("POST", "parent", "int");
				$input['name'] 		= clean_link($input['id']);

				$errors = array();
				if(empty($input['title'])) $errors[] = "Title field is empty";

				if(!sizeof($errors)) {
					if(empty($id)) {
						$lastid = dbquery_insert(PREFIX.'post_categories', $input, 'save');
						$db->query("UPDATE #__post_categories SET name='".clean_link($lastid)."' WHERE id=".intval($lastid));
					} else {
						dbquery_insert(PREFIX.'post_categories', $input, 'update', 'id='.intval($id));
						$db->query("UPDATE #__post_categories SET name='".clean_link($id)."' WHERE id=".intval($id));
					}
					redirect(Url::link('post/category'));
				} else {
					opentable();
					echo implode("<br>", $errors);
					closetable();
				}
			} else {
				opentable("Form");
					echo Form::Open(FUSION_REQUEST);
						echo Form::Hidden('id', $id);
						echo Form::AddElement(array('element'=>'text', 'label'=>'Title', 'name'=>'title', 'value'=>$data['title']));

						echo Form::AddElement(array('element'=>'select',
							'label'=>'Parent', 
							'name'=>'parent', 
							'values'=>dropdown_categories(), 
							'selected'=>$data['parent'],
							'optdisabled'=>array(0),
							'class'=>'form-control',
						));

						echo Form::AddElement(array('element'=>'submit', 'name'=>'save', 'value'=>'Save', 'class'=>'btn btn-primary'));
					echo Form::Close();
				closetable();
			}
		} else {
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
			opentable("Categories<span class='pull-right'><a href='".Url::link('post/category&amp;ref=form')."' class='btn btn-primary btn-xs'>Create Category</a></span>");
        	echo "<div class='dd'><ol class='dd-list'>";
        		echo DisplayCategories();
        	echo "</ol></div>";
			closetable();
		}
	}
}

function DisplayCategories($parentid = 0, $sublevelmarker = false) {
	global $db, $User, $config;	

    $cat_info = load_categories(); $root_category = array();

	if(count($cat_info)) {
    	foreach($cat_info as $cats) {
    		if( $cats['parent'] == $parentid ) $root_category[] = $cats['id'];
    	}
		$cat_item = "";
		if( count($root_category)) {
			foreach ($root_category as $id) {
				$cat_item .= "<li class='dd-item' data-id='".$cat_info[$id]['id']."'>";
				$cat_item .= "<div class='dd-handle'></div>";
				$cat_item .= "<div class='dd-content'>";
					$cat_item .= "<b>ID:{$cat_info[$id]['id']}</b>&nbsp;&nbsp;";
					$cat_item .= Io::Output($cat_info[$id]['title']);
					$cat_item .= "<div class='pull-right'>";
						$cat_item .= "<a href='".Url::link('post/category&amp;ref=form&id='.$cat_info[$id]['id'])."'><i title='Edit' alt='Edit' class='fa fa-pencil-square-o'></i></a>&nbsp;&nbsp;";
						$cat_item .= "<a href='".Url::link('post/category&amp;ref=delete&id='.$cat_info[$id]['id'])."'><i title='Delete' alt='Delete' class='fa fa-trash text-danger'></i></a>";
					$cat_item .= "</div>";
				$cat_item .= "</div>";				
				$cat_item .= DisplayCategories( $id, true );
				$cat_item .= "</li>";
			}

			if( $sublevelmarker ) return "<ol class=\"dd-list\">".$cat_item."</ol>"; else return $cat_item;
		}
	}
}