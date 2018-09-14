<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');

require_once "../../../../../maincore.php";
//include(INFUSIONS."media/includes/helpers/call.php");

$whitelist	  = array('.jpg', '.gif', '.png', '.jpeg');
$allowed_type = array('application/octet-stream');
$upload_errors = array(
        1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
        2 => "The uploaded file exceeds the MAX_FILE_SIZE directive",
        3 => "The uploaded file was only partially uploaded",
        4 => "No file was uploaded",
        6 => "Missing a temporary folder"
	);

$pathname = INFUSIONS."media/uploads/" . date('Y') . "-" . date('m')."/";
  
if (!is_dir($pathname)) {
    mkdir($pathname, 0777, true);
}

// Optional: instance name (might be used to adjust the server folders for example)
$CKEditor = $_GET['CKEditor'] ;

// Required: Function number as indicated by CKEditor.
$funcNum = $_GET['CKEditorFuncNum'] ;

// Optional: To provide localized messages
$langCode = $_GET['langCode'] ;

$url = '' ;

if (isset($_POST['upload']) && is_array($_FILES['upload'])) {
	//require_once('img.resize.php');

	$file = $_FILES['upload'];
	
	$tmp_parts = explode('.', $file['name']);
	$ext = array_pop($tmp_parts);
	$ext = strtolower($ext);
	$ext = '.'. $ext;
	
	if (in_array($ext, $whitelist) && in_array($file['type'], $allowed_type)) {
		if ($file['error'] == 0) {
			if ($file['size'] > 0) {
                $new_name = $file['name'];
				$uploadFile = $pathname . $new_name;
				
				$move = @copy($file['tmp_name'], $uploadFile);
		        if (@copy($file['tmp_name'], $uploadFile)){
		            chmod($uploadFile, 0644);
		                // Build the url that should be used for this file   
    				$url = $uploadFile;
	                $thumb_name = str_replace($ext, "_thumb".$ext, $uploadFile);
	                //$image_result = call("create_thumbnail", $uploadFile, $thumb_name);

					$img = getimagesize($uploadFile); // 0 = width, 1 = height, 2 = tyoe, 3 = attr					
					$width = $img[0];
					$height = $img[1];
					
					if ($img[0] > 500){
						$width = 500;
						$ratio = (500 * 100) / $img[0];
						$height = round(($img[1] * $ratio) / 100);
					}

		            echo "<img src='uploads/" . date('Y') . "-" . date('m') . "/" . $new_name ."' width='$width' height='$height' style='vertical-align: middle; display: block; margin-left: auto; margin-right: auto;' id='gallery' rel='gallery' />";
		        } else {
		            $error = 'The uploaded file could not be moved.';
		        }
			} else {
				$error = 'File is empty. This error could also be caused by uploads being disabled in your php.ini.';
			}
		} else {
			$error = $upload_errors[$file['error']];
		}
	} else {
		$error = 'Invalid file type.';
	}
} else {
	$error = 'Select a file first.';
}

if (strlen($error) > 0) {
	echo '<div class="alert alert-error" id="_error_">'. $error .'</div>';
}

echo "<script type='text/javascript'> window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$error')</script>";
exit();


?>