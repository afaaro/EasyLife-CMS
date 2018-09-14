<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

function upload_image_video($input){
    // Copy Image
    $video_thumbnail = new Media($input);

    $file = md5_file($video_thumbnail->get_thumb("large")) . time() . '.jpg';
	$target_path = INFUSIONS."media/uploader/".date('Y-m')."/";

    if(!is_dir($target_path)){
        mkdir($target_path, 0705);		// Create directory if it does not exist
    }

    $old_path = INFUSIONS."media/uploads/inline-images/";
    if(file_exists($old_path.$file)) {
        unlink($old_path.$file);
    }

    if(file_exists($target_path.$file)) {
        unlink($target_path.$file);
    }
                        
    if(copy($video_thumbnail->get_thumb("large"), $target_path.$file) && copy($video_thumbnail->get_thumb("large"), INFUSIONS."media/uploads/inline-images/".$file)){
        return $file;
    } else {
        return false;
    }
}

function create_thumbnail($source_image, $destination_path, $destination_width, $destination_height="", $type = 0) { 
	// Create image from file

	$img_info = @getimagesize($source_image);
	$valid_types = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP);
    switch ($img_info['mime']) {
        case "image/gif":
            $image = @imagecreatefromgif($source_image);
        break;
        case "image/png":
            $image = @imagecreatefrompng($source_image);
        break;
        case "image/bmp":
            $image = @imagecreatefrombmp($source_image);
        break;
        default:
        case "image/jpeg":
        case "image/pjpeg":
            $image = @imagecreatefromjpeg($source_image);
        break;
    }

    // $type (1=crop to fit, 2=letterbox) 
    $source_width = imagesx($image); 
    $source_height = imagesy($image);
	// Calculate the scaling we need to do to fit the image inside our frame
	$scale = min($destination_width/$source_width, $destination_height/$source_height);
	
    $source_ratio = $source_width / $source_height; 
    $destination_ratio = $destination_width / $destination_height; 
    if ($type == 1) { 
        // crop to fit 
        if ($source_ratio > $destination_ratio) { 
            // source has a wider ratio 
            $temp_width = (int)($source_height * $destination_ratio); 
            $temp_height = $source_height; 
            $source_x = (int)(($source_width - $temp_width) / 2); 
            $source_y = 0; 
        } else { 
            // source has a taller ratio 
            $temp_width = $source_width; 
            $temp_height = (int)($source_width / $destination_ratio); 
            $source_x = 0; 
            $source_y = (int)(($source_height - $temp_height) / 2); 
        } 
        $destination_x = 0; 
        $destination_y = 0; 
        $source_width = $temp_width; 
        $source_height = $temp_height; 
        $new_destination_width = $destination_width; 
        $new_destination_height = $destination_height; 
    } else { 
        // letterbox 
        if ($source_ratio < $destination_ratio) { 
            // source has a taller ratio 
            $temp_width = (int)($destination_height * $source_ratio); 
            $temp_height = $destination_height; 
            $destination_x = (int)(($destination_width - $temp_width) / 2); 
            $destination_y = 0; 
        } else { 
            // source has a wider ratio 
            $temp_width = $destination_width; 
            $temp_height = (int)($destination_width / $source_ratio); 
            $destination_x = 0; 
            $destination_y = (int)(($destination_height - $temp_height) / 2); 
        } 
        $source_x = 0; 
        $source_y = 0; 
        $new_destination_width = $temp_width; 
        $new_destination_height = $temp_height; 
    } 
    $destination_image = imagecreatetruecolor($destination_width, $destination_height);
    if ($type > 1) {
		imagealphablending($destination_image, true);
		imagesavealpha($destination_image, true);
		imagefilledrectangle($imagefilledrectangle,0,0, $destination_width, $destination_height, imagecolorallocate($destination_image, 0,0,0));
        //imagefill($destination_image, 0, 0, imagecolorallocate($destination_image, 0,255,0));
    }

    imagecopyresampled($destination_image, $image, $destination_x, $destination_y, $source_x, $source_y, $new_destination_width, $new_destination_height, $source_width, $source_height); 

	/* Save image */
	switch($img_info['mime'])	{
	    case 'image/jpeg':
	        imagejpeg($destination_image, $destination_path, 80);
	        break;
	    case 'image/png':
	        imagepng($destination_image, $destination_path, 0);
	        break;
	    case 'image/gif':
	        imagegif($destination_image, $destination_path);
	        break;
	}
    return true;
	/* cleanup memory */
	imagedestroy($image);
	imagedestroy($destination_image);
}

function get_blockpost($block__title, $catid, $limit=2) {
	global $db, $config;
	
	echo "<div class='programmes-home m-b-20'>";
		if ($block__title != "") echo "<div class='block__title'><span>$block__title</span></div>";
		
	    $result = $db->query("SELECT * FROM #__media_node WHERE catid IN(".get_parents($catid).") ORDER BY created desc LIMIT $limit");
	    if($result->num_rows > 0) {
		    $a = 0;
	        foreach($result->rows as $row) {
				$category = Io::Output($row['catid'], "int");
				$id = Io::Output($row['id'], "int");
				$title = Io::Output($row['title']);
				$post_image = Io::Output($row['image']);
				
				if($config['seo_urls']==1) {
					$link = RewriteUrl(Url::page("topics&amp;name=".clean_link($category)."&amp;id=".$id));
				} else {
					$link = Url::page("topics&amp;name=".clean_link($category)."&amp;id=".$id);
				}

			    echo "<div class='group-item'>";
			    	echo "<article class='item".(($a++%$limit) == 0 ? " large" : " small")."' onMouseOut=\"this.style.border = '1px solid #DBDBDB'\" onMouseOver=\"this.style.border = '1px solid #999999'\">";
			            echo "<div class='poster'>\n";
			                echo "<div class='image-header __media'>\n<div class='thumb'>\n";
			                    echo (file_exists(INFUSIONS."media/uploads/inline-images/".$post_image) && $post_image != '') ? "<img src='".asset("media/uploads/inline-images/".$post_image)."'>" : "";
			                    //echo "<span class='catname'>".$row['name']."</span>";
			                echo "</div>\n</div>\n";
			            echo "</div>\n";
			
			            echo "<div class='text'>\n";
			                echo ($row['promote']) ? "<i class='pull-right fa fa-warning icon-sm'></i>\n" : "";
			                echo "<h3 class='post-title panel-title'><a class='strong text-dark' href='$link'>".Io::Output($row['title'])."</a></h3>\n";
			                echo "<p class='date'>".date('Y-m-d',$row['created'])."</p>";
			
			            echo "</div>\n";
			            echo "<a href='{$link}'><div class='overlay'></div></a>";
			        echo "</article>\n";
			    echo "</div>";
	        }
	    }
	echo "</div>";
}

function get_gridpost($block__title, $catid, $limit=2) {
	global $db, $config;
	
	echo "<div class='programmes-home m-b-20'>";
		if ($block__title != "") echo "<div class='block__title'><span>$block__title</span></div>";
		
	    $result = $db->query("SELECT * FROM #__media_node WHERE catid IN(".get_parents($catid).") ORDER BY created desc LIMIT $limit");
	    if($result->num_rows > 0) {
		    $a = 0;
	        foreach($result->rows as $row) {
				$category = Io::Output($row['catid'], "int");
				$id = Io::Output($row['id'], "int");
				$title = Io::Output($row['title']);
				$post_image = Io::Output($row['image']);
				
				if($config['seo_urls']==1) {
					$link = RewriteUrl(Url::page("topics&amp;name=".clean_link($category)."&amp;id=".$id));
				} else {
					$link = Url::page("topics&amp;name=".clean_link($category)."&amp;id=".$id);
				}

				$mobiledetect = new Mobile_Detect();
				if($mobiledetect->isMobile() === true) {
					$item = 2;
					$span = 'col-md-12';
				} elseif($mobiledetect->isTablet() === true) {
					$item = 2;
					$span = 'col-md-6';
				} else {
					$item = 3;
					$span = 'col-md-4';
				}
				if ($a % $item == 0) echo "</list><list class='post-group clearfix'>"; $a++;
				
		    	echo "<article class='{$span} grid'>";
		            echo "<div class='overflow-hide'>\n";
		                echo "<div class='image-header __media'>\n<div class='thumb'>\n";
		                    echo (file_exists(INFUSIONS."media/uploads/inline-images/".$post_image) && $post_image != '') ? "<img src='".asset("media/uploads/inline-images/".$post_image)."'>" : "";
		                    //echo "<span class='catname'>".$row['name']."</span>";
		                echo "</div>\n</div>\n";
		            echo "</div>\n";
		
		            echo "<div class='text'>\n";
		                echo ($row['promote']) ? "<i class='pull-right fa fa-warning icon-sm'></i>\n" : "";
		                echo "<h3 class='post-title panel-title'><a class='strong text-dark' href='$link'>".Io::Output($row['title'])."</a></h3>\n";
		                echo "<p class='date'>".date('Y-m-d',$row['created'])."</p>";
		
		            echo "</div>\n";
		            echo "<a href='{$link}'><div class='overlay'></div></a>";
		        echo "</article>\n";
	        }
	    }
	echo "</div>";
}

function Popular_post($limit = 5, $catid=false, $exclude=false) {
    global $db, $config;

    $categories = load_categories();
    $catid = ($catid !== false) ? $categories[$catid]['parent'] : get_parents($catid);
    $catid = get_parents($catid);

    echo "<div class='block' style='padding:0;'>\n<div class='block__inner'>";
    echo "<h1 class='block__title'><span>Most Popular</span></h1>";
    $number = 0; $b = 0;
    echo "<ul class='list'>";
    $post_list = post_list($catid, '', 'views DESC', 0, $limit, $exclude);
    foreach($post_list as $row) {
        $number = $number + 1;
        if($number < 10) {
            $number = "0".$number;
        }

        $category = Io::Output($row['catid'], "int");
        $id = Io::Output($row['id'], "int");
        $title = Io::Output($row['title']);

        if($config['seo_urls']==1) {
            $link = RewriteUrl(Url::page("topics&amp;name=".clean_link($category)."&amp;id=".$id));
        } else {
            $link = Url::page("topics&amp;name=".clean_link($category)."&amp;id=".$id);
        }
        echo "<li class='item".(($b++%$limit) == 0 ? " large-item" : " small-item")."' onMouseOut=\"this.style.border = '1px solid #F0F0F0'\" onMouseOver=\"this.style.border = '1px solid #999999'\" style='margin:0;border-radius:0;'>";
            echo "<div class='overflow-hide'><a href='{$link}'>";
                echo "<span style='margin-right: 13px;float: left;line-height: 2;color: #DC0000;font-size: 2.2em;text-indent: 5px;'>{$number}</span>\n";
                echo "<h3 style='color: #333333;margin-bottom:0;'>".trimlink($row['title'], 60)."</h3><span style='font-size:9px;'>".date('d F', $row['created'])."</span>";
            echo "</a></div>";
            echo "<a href='{$link}'><div class='overlay'></div></a>";
        echo "</li>";
    }
    echo "</div>";
    echo "</div>\n</div>";
}


function post_list($catid='', $post_ids='', $sort='', $offset=0, $limit=5, $exclude='') {
    global $db;

    $output = $where = array();

    $where[] = 'p.status=1';

    if(is_array($post_ids) && count($post_ids) > 0) {
        $where[] = "p.id IN (".implode(',', $post_ids).")";
    }

    if ($catid != 0) $where[] = "p.catid IN ($catid)";

    if ($exclude > 0) $where[] = "p.catid NOT IN (".$exclude.")";

    //Build query
    $where = (sizeof($where)>0) ? " WHERE ".implode(" AND ",$where) : "";

    $orderby = (!empty($sort)) ? "$sort, " : "created DESC  ";
    $orderby = substr($orderby, 0, -2);

    $result = $db->query("SELECT p.* FROM #__media_node AS p {$where} ORDER BY $orderby LIMIT ".$offset.", ".$limit);
    if($result->num_rows > 0) {
        foreach($result->rows as $row) {
            $output[$row['id']] = $row;
        }
    }

    return $output;
}

if (!function_exists('gallery')) {
    function gallery($img_path, $img_title, $identifier = 'gallery') {
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/jquery-2.1.1.min.js'></script>");
        add_to_head("<link rel='stylesheet' href='".INFUSIONS."media/inc/assets/jquery/unitegallery/css/unite-gallery.css' type='text/css' media='screen' />");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/themes/default/ug-theme-default.js'></script>");
        add_to_head("<link rel='stylesheet' href='".INFUSIONS."media/inc/assets/jquery/unitegallery/themes/default/ug-theme-default.css' type='text/css' media='screen' />");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/themes/video/ug-theme-video.js'></script>");

        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-common-libraries.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-functions.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-thumbsgeneral.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-thumbsstrip.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-touchthumbs.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-panelsbase.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-strippanel.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-gridpanel.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-thumbsgrid.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-tiles.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-tiledesign.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-avia.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-slider.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-sliderassets.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-touchslider.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-zoomslider.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-video.js'></script>");        
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-gallery.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-lightbox.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-carousel.js'></script>");
        add_to_head("<script type='text/javascript' src='".INFUSIONS."media/inc/assets/jquery/unitegallery/js/ug-api.js'></script>");

        add_to_footer("<script type='text/javascript'>jQuery(document).ready(function(){ jQuery('#".$identifier."').unitegallery(); });</script>");
        
        echo "<img alt='$img_title' src='$img_path' data-image='$img_path' data-description='$img_title'>";
    }
}