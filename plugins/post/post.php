<?php

class postController {
	function index() {
		global $db, $User, $config;
		
		
		$name = Io::GetVar("GET", "name");
		$id = Io::GetVar("GET", "id", "int");	

		if (empty($name)) {
			//echo "<div class='row'>";

				echo "<div class='col-md-12'>";
					echo fetch_ajax('get_post_category', 0);


				echo "</div>";

				// echo "<div class='col-md-4'>";
				// 	echo '<div class="m-b-10" style="text-align:center;min-width:100%"><iframe src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2Funiversalsomalitv%2F&tabs&width=340&height=130&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=false&appId=152336491589381" width="340" height="130" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe></div>';

				// 	echo Popular_post(5);
					
				// echo "</div>";
			//echo "</div>";
		} else {
			$name = ltrim($_SERVER['QUERY_STRING'], 'cont=post');

			if($config['seo_urls']==1) {
				$query_string = explode($config['seo_urls_separator'], $_SERVER['QUERY_STRING']);
				$name = ltrim(implode($config['seo_urls_separator'], $query_string), "cont=post");
				$name = ltrim($name, '/');
			} else {
				$name = $name;
			}

			if ($data = $db->query("SELECT * FROM ".PREFIX."post WHERE (name='{$name}' OR id='{$id}')")->row) {
				$this->_viewPost($data);
			} elseif($data = $db->query("SELECT id,title FROM #__post_categories WHERE name='{$name}'")->row) {
				opentable($data['title']);
					$result = $db->query("SELECT * FROM #__post_categories WHERE parent=".Io::Output($data['id']));
					if($result->num_rows) {
						$counter = 0; $i=0;
						echo "<table class='table table-bordered cat_list' cellpadding='4' cellspacing='0' width='100%' border='0'>";
						foreach($result->rows as $row) {
							if ($row['parent'] == $data['id']) {
								$post = $db->query("SELECT COUNT(id) AS total FROM #__post WHERE category IN('".categories_parent($row['id'])."')")->row;
								$class = bgclass();
			                    if (($counter%2)==0) { echo "<tr class='$class'>"; }
			                    echo "<td class='table-bordered'>";
			                        echo "<span class='fa fa-folder-open'>&nbsp;</span><a href='".Url::link('post/name='.clean_link($row['id']))."'>".$row['title']."</a>";
			                    echo "</td>";
			                    echo "<td style='width:1%;text-align:center;' nowrap><span class='stat-info'>".$post['total']."</span></td>";
			                    if (($counter++%2)!=0) { echo "</tr>\n"; }
							}
							$i++;
						}
						echo "</table>";
					}
				closetable();
			}
		}		
	}

	function _viewPost($row){
		global $db, $config;


		$id = Io::Output($row['id'], "int");
		if (!isset($_COOKIE["post_{$id}"])) {
			setcookie("post_{$id}",time(),time()+86400, _COOKIEPATH); //1 hour 3600 - 24 hours 86400
			$db->query("UPDATE ".PREFIX."post SET views=views+1 WHERE id='".intval($id)."'");
		}

		add_to_title($row['title']);
		echo "<div class='col-md-8'>";
			echo "<article class='node node--id-$id node--view-mode-full'>";
				echo "<header class='node__header--has-meta node__header'>";
					echo "<h1 class='node__title'><span class='field field-name-title field-formatter-string field-type-string field-label-hidden quickedit-field'>".Io::Output($row['title'])."</span></h1>";
					echo "<div class='node__meta clear'>";
						echo "<div class='date'>".date('Y-m-d H:i:s',$row['created'])."</div>";
						echo "<div class='text'>Fusion</div>";
					echo "</div>";

					echo "<div class='social_meta clear'>";
		                add_to_head("<script type='text/javascript' src='//platform-api.sharethis.com/js/sharethis.js#property=5b130c9a8e56ee0011c80054&product=inline-share-buttons' async='async'></script>");
		                echo "<div class='share'><div class='sharethis-inline-share-buttons'></div></div>";
					echo "</div>";
				echo "</header>";

				echo "<div class='node__content text-dark m-t-20 m-b-20 overflow-hide'>";
					echo "<div class='clearfix text-formatted field field-node--body field-name-body field-label-hidden'>";
						$gallery = self::get_NewsGalleryData($id);
						if (!empty($gallery)) {
							//echo "<div class='post-gallery'>";
							echo "<div id='gallery' style='display:none;'>";
								foreach ($gallery as $image_id => $image) {
									$source = BASEDIR."uploader/".date('Y-m');
									echo gallery($source."/".$image['image'], '', 'gallery');
								}
							echo "</div>";
						}

					echo "</div>";
				echo "</div>";
			echo "</article>";

		echo "</div>";
		echo "<div class='col-md-4'>";
			?>
			<script type="text/javascript">
			    google_ad_client = "ca-pub-2020213978766982";
			    google_ad_slot = "7878115216";
			    google_ad_width = 336;
			    google_ad_height = 280;
			</script>
			<!-- Posts Right Banner -->
			<script type="text/javascript"
			src="//pagead2.googlesyndication.com/pagead/show_ads.js">
			</script>
			<?php

			$category = Io::Output($row['category'], "int");
			echo Popular_post(5, $category, $category);
		echo "</div>";
	}
	
	function updatecategory() {
		global $db;

		set_time_limit(0);
		ini_set('max_execution_time', 3000); //300 seconds = 5 minutes
		ini_set('memory_limit', '1024M'); // or you could use 1G

		$result = $db->query("SELECT * FROM #__post_categories")->rows;
		foreach($result as $row) {
			$category = $this->get_url($row['id']);
			$db->query("UPDATE #__post_categories SET name='".$category."' WHERE id=".intval($row['id']));
			// if ($row['name'] != $category) {
			// 	$db->query("UPDATE #__post_categories SET name='".$category."' WHERE id=".intval($row['id']));
			// }
		}
	}

	function updatepost() {
		global $db;

		set_time_limit(0);
		ini_set('max_execution_time', 3000); //300 seconds = 5 minutes
		ini_set('memory_limit', '1024M'); // or you could use 1G

		$result = $db->query("SELECT * FROM #__post")->rows;
		foreach($result as $post) {
			$category = $this->get_url($post['category'])."/".$post['id'];

			// $date_added = date('Y m d H:i:s', $post['created']);
			// $post_item = Io::Output($post['id'], 'int');
			// $post_image = Io::Output($post['image']);

			// $target_path = BASEDIR."uploader/".date('Y-m', $post['created'])."/";
			// $old_path = BASEDIR."uploads/inline-images/".$post_image;
			// chmod($old_path, 0666);

		 //    if(!is_dir($target_path)){
		 //        mkdir($target_path, 0755);		// Create directory if it does not exist
		 //    }

		 //    if(!$db->query("SELECT id FROM #__post_images WHERE item=".$post_item)->num_rows) {
		 //    	$db->query("INSERT INTO #__post_images SET item='{$post_item}', image='{$post_image}', date='{$date_added}'");

		 //    	if(!file_exists($target_path.$post_image)) {
		 //    		rename(BASEDIR."uploads/inline-images/".$post_image, $target_path.$post_image);
		 //    	}					    	
		 //    }

			if ($post['name'] != $category) {
				$db->query("UPDATE #__post SET name='".$category."' WHERE id=".intval($post['id']));
			}			
		}

	}

	function get_url($id) {
	    global $db;

	    if( ! $id ) return;

	    $full_path = "";
	    while ($id > 0) {
	        $result = $db->query("SELECT title,parent FROM #__post_categories WHERE id='$id'");
		    $data = $result->row;
	        if ($full_path) {
	            $full_path = "/".$full_path;
	        }	        
            $full_path = clean_url($data['title']).$full_path;
            $id = $data['parent'];
	    }
	    return $full_path;
	}

    static function get_NewsGalleryData($id) {
    	global $db;

        $row = array();
        $result = $db->query("SELECT * FROM #__post_images WHERE item='".$id."'");
        if ($result->num_rows > 0) {
        	foreach($result->rows as $gData) {
                $row[$gData['id']] = $gData;
            }
        }

        return (array)$row;
    }
}