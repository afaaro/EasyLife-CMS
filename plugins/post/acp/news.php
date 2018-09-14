<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class newsController {
	function index() {
		global $db, $User, $config;
		
		?>
	    <script type='text/javascript' charset='utf-8'>
	        function showmenu(id) {
	            $('#menu_'+id).toggle();
	            $('#status_'+id).toggle();
	        }
	        function Delete() {
	            return confirm('Are you sure deleting this item.');
	        }
	    </script>
	    <?php
		add_to_title(' News');

	    $id  = Io::GetVar("GET", "id", "int");
	    $ok  = Io::GetVar("GET", "ok", "bool", false, false);
	    $cat = Io::GetVar("GET", "cat", "int", false, "0");
        if(isset($_POST['start_date'])){ 
            $start_date = @strtotime($_POST['start_date']); if ($start_date === - 1 OR !$start_date) $start_date = ""; 
        } else { 
            $start_date = ""; 
        }

	    if (isset($_GET['ref']) && $_GET['ref'] == "delete") {
	    	if($ok) {
	    		debug($id);
	    	} else {
	    		Error::Trigger('INFO', "<div align='center'><b>Delete the news?</b><br><a href='".Url::link('post/news&ref=delete&id='.$id.'&ok=true')."' title='Yes'>Yes</a> - <a href='".Url::link('post/news')."' title='No'>No</a></div>");
	    	}
		} elseif (isset($_GET['ref']) && $_GET['ref'] == "form") {
			if(empty($id)) {
				$data = null;
				$options = null;
			} else {
				// Get from db data
				$data = $db->query("SELECT * FROM #__post WHERE id=".intval($id))->row;
				if(is_array($data['options'])){
					$options = null;
				} else {
					$options = Utils::Unserialize($data['options']);	
				}
			}

	        $photo_result = $db->query("SELECT * FROM #__post_images WHERE item='".$id."'");
	        $news_photos = array();
	        $news_photo_opts = array();
	        if ($photo_result->num_rows > 0) {
	        	foreach($photo_result->rows as $photo_data) {
	                $news_photos[$photo_data['id']] = $photo_data;
	                $news_photo_opts[$photo_data['id']] = $photo_data['image'];
	            }
	        }

	        if (isset($_POST['save']) OR isset($_POST['save_and_close'])) {
         		$input['id']     	= Io::GetVar('POST','id','int', false, 0);
				if($config['multilang_enable'] === 'on') {
					$def = $config['multilang_default'];

					$input['title'] = Io::GetVar("POST", "title", "fullhtml");
					$input['title'] = $input['title'][$def];

					$input['text'] = Io::GetVar("POST", "text", "fullhtml");
					$input['text'] = $input['text'][$def];

					$input['category'] = Io::GetVar("POST", "category", "int");
					$input['status'] = Io::GetVar("POST", "status", "int");
					$input['type'] = "news";
				} else {
					$input['title'] = Io::GetVar("POST", "title", "fullhtml");
					$input['text'] = Io::GetVar("POST", "text", "fullhtml");

					$input['category'] = Io::GetVar("POST", "category", "int");
					$input['status'] = Io::GetVar("POST", "status", "int");
					$input['type'] = "news";
				}

		        //$input['image']   	= Io::GetVar("POST", "image", "nohtml", true);
		        $input['created'] 	= $start_date != 0 ? $start_date : time();
		        $input['changed'] 	= time();
		        $input['author'] 	= $User->Uid();
		        $input['promote'] 	= Io::GetVar("POST", "promote", "int");
		        $input['status'] 	= Io::GetVar("POST", "status", "int", false, "1");
		        $input['role'] 		= Io::GetVar("POST", "role", "int", false, "0");
		        $input['fbauto'] 	= Io::GetVar("POST", "fbauto", "int", false, "1");
		        
				$errors = array();
				if(empty($input['title'])) $errors[] = "Title field is required";

				if (!sizeof($errors)) {
					$options = array();

	                $left = Io::GetVar('POST','left','int');
	                $right = Io::GetVar('POST','right','int');
	                $options['layout']['left'] = (!empty($left)) ? 1 : 0 ;
	                $options['layout']['right'] = (!empty($right)) ? 1 : 0 ;

					$input['options'] = Utils::Serialize($options);

		        	if (Utils::CheckToken() === true) {
						if(empty($id)) {
							$lastid = dbquery_insert(PREFIX.'post', $input, 'save');
							$db->query("UPDATE #__post SET name='".clean_link($input['category'])."/".$lastid."' WHERE id=".intval($lastid));
							$db->query("UPDATE #__post_images SET item='".intval($lastid)."', author='".$input['author']."' WHERE item='0'");
						} else {
							// Update
							$input['created'] = $data['created'] == 0 ? $input['created'] : $data['created'];
							$input['author'] = $data['author'];
							dbquery_insert(PREFIX.'post', $input, 'update', 'id='.intval($id));
							$db->query("UPDATE #__post SET name='".clean_link($input['category'])."/".$id."' WHERE id=".intval($id));
							$db->query("UPDATE #__post_images SET item='".intval($id)."', author='".$input['author']."' WHERE item='0'");
						}

						if($config['multilang_enable'] === 'on') {
	                        // insert param multilang
	                        unset($_POST['title'][$def]);
	                        $multilang = [];
	                        foreach ($_POST['title'] as $key => $value) {
	                        	$title = !empty($_POST['title'][$key]) ? $_POST['title'][$key] : $input['title'];
	                        	//$content = !empty($_POST['content'][$key]) ? $_POST['content'][$key] : $input['text'];

	                            if (!empty($_POST['text'][$key]) || $_POST['text'][$key] != '') {
	                                if ($_POST['text'][$key] == '<p><br></p>' || $_POST['text'][$key] == '<br>') {
	                                    $content = $input['text'];
	                                } else {
	                                    $content = $_POST['text'][$key];
	                                }
	                            } else {
	                                $content = $input['text'];
	                            }

		                        $multilang[] = array(
		                                            $key => array(
		                                                'title' => $title,
		                                                'content' => Utils::jsonFormat($content),
		                                            ),
		                                        );
	                        }
	                        $multilang = json_encode($multilang, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	                        if (!Language::existParam('multilang', $id)) {
	                            Language::addParam('multilang', $multilang, $id);
	                        } else {
	                            Language::editParam('multilang', $multilang, $id);
	                        }
						}

		                if (isset($_POST['save_and_close'])) {
		                    redirect(Url::link('post/news'));
		                } else {
		                    redirect(Url::link('post/news&amp;ref=form'));
		                }
		        	} else {
		                addNotice('danger', 'Token is wrong');    		
		        	}
				}
			} else {
				opentable("");
				
				$action = empty($id) ? Url::link('post/news&amp;ref=form') : Url::link('post/news&amp;ref=form&amp;id='.$id);
				echo Form::Open($action, array('enctype'=>'multipart/form-data'));

					echo "<div class='row'><div class='col-md-8'>";
						//echo Form::AddElement(array('element'=>'submit', 'name'=>'save', 'value'=>'Save', 'id'=>'upload_form', 'class'=>'btn btn-primary btn-xs'));
						echo Form::AddElement(array('element'=>'submit_and_reset', 
							's_name'=>'save', 
							's_value'=>'Save', 
							's_id'=>'upload_form', 
							's_class'=>'btn btn-primary btn-xs',
							'r_name'=>'save_and_close', 
							'r_value'=>'Save and Close', 
							'r_id'=>'upload_form', 
							'r_class'=>'btn btn-success btn-xs',
						));

						echo Form::Hidden('id', $data['id']);

						if($config['multilang_enable'] === 'on') {
							$def = $config['multilang_default'];
							$deflang = Language::getDefaultLang();
							$listlang = json_decode($config['multilang_country'], true);
							$deflag = strtolower($listlang[$def]['flag']);
							
							echo "<div class='nav-tabs-custom card'>";
			                    echo "<ul class='nav nav-tabs' role='tablist'>";
			                        echo "<li class='active'><a href='#lang-{$def}' role='tab' data-toggle='tab'><span class='flag-icon flag-icon-{$deflag}'></span> {$deflang['country']}</a></li>";

			                        unset($listlang[$config['multilang_default']]);
			                        foreach ($listlang as $key => $value) {
			                        	$flag = strtolower($value['flag']);
			                        	echo "<li><a href='#lang-{$key}' role='tab' data-toggle='tab'><span class='flag-icon flag-icon-{$flag}'></span> {$value['country']}</a></li>";
			                        }
			                    echo "</ul>";

			                    echo "<div class='tab-content'>";
				                    echo "<div class='tab-pane active' id='lang-{$def}'>
				                        <div class='form-group'>
				                            <label for='title'>Title ({$def}) </label>
				                            <input type='text' name='title[{$def}]' class='form-control' id='title' placeholder='Post Title' value='".$data['title']."'>
				                        </div>
				                        <div class='form-group'>";
				                            echo Form::AddElement(array('element'=>'textarea_adv', 'name'=>'text['.$def.']', 'value'=>$data['text'], 'id'=>'editor_'.$def));
				                            //echo "<textarea name='text[{$def}]' class='form-control content editor' id='content' rows=''>".$data['text']."</textarea>";
				                        echo "</div>
				                    </div>";

				                    unset($listlang[$config['multilang_default']]);
				                    foreach ($listlang as $key => $value) {
				                        // debug($key);
				                        if (!empty($id)) {
				                            $lang = Language::getLangParam($key, $id);
				                            if ($lang == '') {
				                                $lang['title'] = '';
				                                $lang['content'] = '';
				                            } else {
				                                $lang = $lang;
				                            }
				                        } else {
				                            $lang['title'] = '';
				                            $lang['content'] = '';
				                        }
					                    echo "<div class='tab-pane' id='lang-{$key}'>
					                        <div class='form-group'>
					                            <label for='title'>Title ({$key}) </label>
					                            <input type='text' name='title[{$key}]' class='form-control' id='title' placeholder='Post Title' value='{$lang['title']}'>
					                        </div>";
					                        echo Form::AddElement(array('element'=>'textarea_adv', 'name'=>'text['.$key.']', 'value'=>$lang['content'], 'id'=>'editor_'.$key));
					                    echo "</div>";
				                    }
			                    echo "</div>";
			                echo "</div>";
						} else {
			                echo "<div class='form-group'>
			                        <label for='title'>Title</label>
			                        <input type='text' name='title' class='form-control' id='title' placeholder='Post Title' value='".$data['title']."'>
			                    </div>";

			                    echo Form::AddElement(array('element'=>'textarea_adv', 'name'=>'text', 'value'=>$data['text'], 'id'=>'editor'));
						}
					echo "</div>";
					echo "<div class='col-md-4'>";
						echo Form::AddElement(array('element'=>'select',
							'label'=>'Category', 
							'name'=>'category', 
							'values'=>dropdown_categories(), 
							'selected'=>$data['category'],
							'optdisabled'=>array(0),
							'class'=>'form-control',
						));

						opentable("Photo Gallery");
							echo Form::AddElement(array('element'=>'file',
								'name'	=>'image[]',
								'value'	=>$id,
								'multiple'=>true,
								'id'	  =>'image',
								'class'	=>'form-control',
								//'extra' => 'onchange="preview_image();"',
								'suffix'	=> '<div id=\'uploaded_file\' class=\'uploaded_file\'></div>'
							));

							?>
							<style type="text/css">
							.uploaded_file {
								overflow: hidden;
								margin-top: 10px;
								margin-bottom: 10px;
							}
							.uploaded_file li {
								float: left;
								margin: 0 0 5px 5px;
								width: 100px;
								list-style-type: none;
								position: relative;
							}
							.uploaded_file li .delete_icon {
								position: absolute;
								top: 7px;
								right: 7px;
								background: #006699;
								color: white;
							}
							.thumbnail {

							}
							</style>

							<script type='text/javascript' charset='utf-8'>
						        jQuery(document).ready(function() {
						        	var storedFiles = [];

						            $('body').on('change', '#image', function(e){
						                e.preventDefault();

						                var files = this.files;
						                            
						                for (i = 0; i < files.length; i++) {
						                    var readImg = new FileReader();
						                    var file = files[i];

						                    if (file.type.match('image.*')){
						                        storedFiles.push(file);
						                        readImg.onload = (function(file) {
						                            return function(e) {
						                                $('#uploaded_file').append(
						                                "<li file = '" + file.name + "'>" +                                 
						                                    "<img class = 'thumbnail img-thumb' src = '" + e.target.result + "' />" + 
						                                    "<span class = 'delete_image delete_icon'> X </span>" + 
						                                "</li>"
						                                );      
						                            };
						                        })(file);
						                        readImg.readAsDataURL(file);
						                        
						                    } else {
						                        alert('the file '+ file.name + ' is not an image<br/>');
						                    }
						                }

						                var form_data = new FormData();
						                for(var i = 0; i < storedFiles.length; i++) {
						                    form_data.append('image[]', storedFiles[i]);
						                }

						                $.ajax({
						                    url: '<?php echo BASEDIR; ?>ajax.php?op=upload_images',
						                    type: 'POST',
						                    contentType: false,
						                    data: form_data,
						                    processData: false,
						                    cache: false,
						                    success: function(html) {
						                        $(".uploaded_files").html('');				                        
						                    },
										    
										    error: function(err){
										        console.log(err);
										    }
						                });				
						            });
						        });
							</script>
							<?php	

							if (!empty($news_photos)) {
								echo "<ul class='uploaded_file'>";
								$img_path = "";
								foreach ($news_photos as $photo_id => $photo_data) {
									$image = $photo_data['image'];
						            if (file_exists(BASEDIR."uploader/".date('Y-m')."/".$image)) {
						                $img_path = BASEDIR."uploader/".date('Y-m')."/".$image;
						            }
						            echo "<li>";
						            	echo "<img class='thumbnail img-thumb' src='{$img_path}'>";
						            	echo "<button type='submit' name='delete_photo' value='".$photo_data['id']."' id='delete_photo_".$photo_data['id']."' class='delete_image delete_icon'> X </button>";
						            echo "</li>";

						            ?>
						            <script type="text/javascript">
						            	jQuery(document).ready(function() {
						            		var id = <?php echo $photo_data['id']; ?>;
								        	$('body').on('click','#delete_photo_' + id, function(e){
								                e.preventDefault();
								                $(this).parent().remove('');        
								                
								                var files = document.getElementById("image").files;
								                var file = $(this).parent().attr('file');
								                for(var i = 0; i < files.length; i++) {
								                    if(files[i].name == file) {
								                        files.splice(i, 1);
								                        break;
								                    }
								                }
						
												$.ajax({
													url: '<?php echo BASEDIR; ?>ajax.php?op=upload_images',
													type: "POST",
													data:  {'delete_photo':id},
													success: function(data){
														$("#uploaded_file").append(data);
													},
													error: function(){} 	        
												});
								        	});
						            	});
						            </script>
						            <?php
								}
								echo "</ul>";
							}
						closetable();

						// echo Form::AddElement(array('element'=>'text',
						// 	'label'=>'Date Created',
						// 	'name'=>'created',
						// 	'value'=>$data['created'],
						// 	'class'=>'form-control',
						// ));
						//echo html_datepicker('created', $data['created']);
						opentable("Options");
						echo Form::AddElement(array('element'=>'select',
							'label'=>'Status',
							'name'=>'status',
							'values'=>array('Active'=>1, 'Inactive'=>0),
							'selected'=>$data['status'],
							'class'=>'form-control',
						));

						echo Form::AddElement(array('element'=>'checkbox',
							'label'=>'Featured',
							'name'=>'promote',
							'value'=>$data['promote']
						));
						
						echo Form::AddElement(array('element'=>'checkbox',
							'label'=>'Post to Facebook',
							'name'=>'fbauto',
							'value'=>$data['fbauto']
						));
						closetable();

						opentable("Layout");
							//Left
							echo Form::AddElement(array('element'=>'select',
								'label'=>'Left Column',
								'name'=>'left',
								'values'=>array("Hide"=>"0", "Show"=>"1"),
								'selected'=>@$options['layout']['left'],
								'class'=>'form-control',
							));

							//Right
							echo Form::AddElement(array('element'=>'select',
								'label'=>'Right Column',
								'name'=>'right',
								'values'=>array("Hide"=>"0", "Show"=>"1"),
								'selected'=>@$options['layout']['right'],
								'class'=>'form-control',
							));

						closetable();

						echo Form::AddElement(array('element'=>'submit_and_reset', 
							's_name'=>'save', 
							's_value'=>'Save', 
							's_id'=>'upload_form', 
							's_class'=>'btn btn-primary btn-xs',
							'r_name'=>'save_and_close', 
							'r_value'=>'Save and Close', 
							'r_id'=>'upload_form', 
							'r_class'=>'btn btn-success btn-xs',
						));
					echo "</div><!--- end of .row ---></div>";
					
				echo Form::Close();
				closetable();
			}
		} else {
			opentable("Post List<span class='pull-right'><a href='".Url::link('post/news&amp;ref=form')."' class='btn btn-primary btn-xs'>Create Post</a></span>");
			//echo fetch_ajax('get_post_category', '0');
			?>
			<style type="text/css">
				div.pagination {
				    font-family: "Lucida Sans", Geneva, Verdana, sans-serif;
				    padding:20px;
				    margin:7px;
				}
				div.pagination a {
				    margin: 2px;
				    padding: 0.5em 0.64em 0.43em 0.64em;
				    background-color: #ee4e4e;
				    text-decoration: none;
				    color: #fff;
				}
				div.pagination a:hover, div.pagination a:active {
				    padding: 0.5em 0.64em 0.43em 0.64em;
				    margin: 2px;
				    background-color: #de1818;
				    color: #fff;
				}
				div.pagination span.current {
				    padding: 0.5em 0.64em 0.43em 0.64em;
				    margin: 2px;
				    background-color: #f6efcc;
				    color: #6d643c;
				}
				div.pagination span.disabled {
				    display:none;
				}
			</style>
			<script type="text/javascript">
			function searchFilter(page_num) {
			    page_num = page_num ? page_num : 0;
			    var keywords = $('#keywords').val();
			    var sortBy = $('#sortBy').val();
			    $.ajax({
			        type: 'POST',
			        url: '<?php echo BASEDIR; ?>plugins/post/acp/ajax.php',
			        data: { page: page_num, keywords: keywords, sortBy: sortBy },
			        beforeSend: function () {
			            $('.loading-overlay').show();
			        },
			        success: function (html) {
			            $('#posts_content').html(html);
			            $('.loading-overlay').fadeOut("slow");
			        }
			    });
			}
			$(document).ready(function() {
				searchFilter(0);
			});
			</script>
			<?php

			echo "<div class='post-search-panel'>";
				echo "<div class='form-group pull-left'>";
			    	echo "<input type='text' class='form-control' id='keywords' placeholder='Type keywords to filter posts' onkeyup='searchFilter()'/>";
			    echo "</div>";
			    echo "<div class='form-group pull-right'>";
				    echo "<select class='form-control' id='sortBy' onchange='searchFilter()'>";
				        echo "<option value=''>Sort By</option>";
				        echo "<option value='asc'>Ascending</option>";
				        echo "<option value='desc'>Descending</option>";
				    echo "</select>";
				echo "</div>";
			echo "</div>";

			echo "<div id='posts_content'></div>";

			closetable();
		}
	}
}


function getMultiple_FILES() {
	$_FILE = array();
	foreach ($_FILES as $key => $value) {
		foreach ($value as $k => $val) {
			for ($i = 0; $i < count($val); $i++) {
				$_FILE[$i][$k] = $val[$i];
			}
		}
	}

	return $_FILE;
}

?>

