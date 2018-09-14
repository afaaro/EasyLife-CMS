<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class menuacpController {
	function index() {
		global $db, $User, $config;

		opentable("Menu Acp <div class='pull-right'><a href='".Url::link('plugins/menuacp&amp;ref=form')."' class='btn btn-primary btn-xs'><i class='fa fa-save' style='color:white'></i>&nbsp;&nbsp;Add</a></div>");
		    $id  = Io::GetVar("GET", "id", "int");
		    $ok  = Io::GetVar("GET", "ok", "bool", false, false);
			$ref  = Io::GetVar("GET", "ref", "nohtml");
			switch ($ref) {
				default:
	            	echo "<div class='dd'><ol class='dd-list'>";
	            		echo DisplayMenu(0, false);
	            	echo "</ol></div>";
					break;

				case 'form':
					if(empty($id)) {
						$data = null;
					} else {
						$data = $db->query("SELECT * FROM ".PREFIX."menu_acp WHERE id=".intval($id))->row;
					}

					if($_SERVER['REQUEST_METHOD'] == 'POST') {
						$input['title'] 	= Io::GetVar("POST", "title");
						$input['url'] 		= Io::GetVar("POST", "url");
						$input['icon'] 		= Io::GetVar("POST", "icon");
						$input['parent'] 	= Io::GetVar("POST", "parent", "int", false, 0);
						
						if(empty($id)) {
							// Insert
							dbquery_insert(PREFIX."menu_acp", $input, "save");
						} else {
							// Update
							dbquery_insert(PREFIX."menu_acp", $input, "update", "id=".intval($id));
						}
						redirect(Url::link('plugins/menuacp'));
					} else {
						echo Form::Open();
							echo Form::AddElement(array('element'=>'text', 'name'=>'title', 'label'=>'Title', 'value'=>$data['title']));
							echo Form::AddElement(array('element'=>'text', 'name'=>'url', 'label'=>'Url', 'value'=>$data['url']));
							echo Form::AddElement(array('element'=>'text', 'name'=>'icon', 'label'=>'Icon', 'value'=>$data['icon']));

							echo Form::AddElement(array('element'=>'select',
								'label'=>'Parent', 
								'name'=>'parent', 
								'values'=>DisplayOption(), 
								'selected'=>$data['parent'],
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
	}
}

function DisplayOption($id=0, $level=0) {
    global $db;

    /* Child patern */
    $indent = str_repeat("&#8212;", $level);

    $result = $db->query("SELECT * FROM ".PREFIX."menu_acp WHERE parent = '".intval($id)."' ORDER BY title ASC");
    $select["-ROOT-"] = 0;
    foreach($result->rows as $row) {
        $catid = Io::Output($row['id'], "int");
        $select[$indent." ".Io::Output($row['title'])] = Io::Output($row['id'], "int");
        $select = array_merge($select, DisplayOption($catid, $level + 1));
    }
    return $select;
}

function DisplayMenu($parentid = 0, $sublevelmarker = false) {
	global $db, $User, $config;	

    $cat_info = load_menu_acp($parentid);
    $root_category = array();
    
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
						$cat_item .= "<a href='".Url::link('plugins/menuacp&amp;ref=form&id='.$cat_info[$id]['id'])."'><i title='Edit' alt='Edit' class='fa fa-pencil-square-o'></i></a>&nbsp;&nbsp;";
						$cat_item .= "<a href='".Url::link('plugins/menuacp&amp;ref=delete&id='.$cat_info[$id]['id'])."'><i title='Delete' alt='Delete' class='fa fa-trash text-danger'></i></a>";
					$cat_item .= "</div>";
				$cat_item .= "</div>";				
				$cat_item .= DisplayMenu( $id, true );
				$cat_item .= "</li>";
			}

			if( $sublevelmarker ) return "<ol class=\"dd-list\">".$cat_item."</ol>"; else return $cat_item;
		}
	}

	?>
	<script>

		// $(document).ready(function() {

		// 	$('.dd').nestable({
		// 		maxDepth: 500,
		// 		collapsedClass:'dd-collapsed',
		// 	});

		// 	$('.dd').nestable('collapseAll');
			
		// 	$('.dd-handle a').on('mousedown', function(e){
		// 		e.stopPropagation();
		// 	});

		// 	$('.dd-handle a').on('touchstart', function(e){
		// 		e.stopPropagation();
		// 	});

		// 	$('.nestable-action').on('click', function(e)
		// 	{
		// 		var target = $(e.target),
		// 			action = target.data('action');
		// 		if (action === 'expand-all') {
		// 			$('.dd').nestable('expandAll');
		// 		}
		// 		if (action === 'collapse-all') {
		// 			$('.dd').nestable('collapseAll');
		// 		}
		// 	});
			
		// 	$('#catsort').click(function(){		
		// 		var url = "action=catsort&list="+window.JSON.stringify($('.dd').nestable('serialize'));
		// 		ShowLoading('');
		// 		$.post('engine/ajax/adminfunction.php', url, function(data){
		
		// 			HideLoading('');
		
		// 			if (data == 'ok') {

		// 				DLEalert('{$lang['cat_sort_ok']}', '{$lang['p_info']}');

		// 			} else {

		// 				DLEalert('{$lang['cat_sort_fail']}', '{$lang['p_info']}');

		// 			}
		
		// 		});

		// 	});


		// });
	</script>
	<?php
}

function load_menu_acp($parentid) {
    global $db;

    $result = $db->query("SELECT * FROM #__menu_acp WHERE parent=".$parentid);
    $categories = array();
    if($result->num_rows > 0){
	    foreach($result->rows as $row) {
            $categories[$row['id']] = $row;
        } 
    }
    return $categories;
}


