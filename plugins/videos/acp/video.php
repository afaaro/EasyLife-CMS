<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class videoController {
	function index() {
		global $db;


	    $id  = Io::GetVar("GET", "id", "int");
	    $ok  = Io::GetVar("GET", "ok", "bool", false, false);
	    $cat = Io::GetVar("GET", "cat", "int", false, "0");

		if (isset($_GET['ref']) && $_GET['ref'] == "delete") {

		} elseif (isset($_GET['ref']) && $_GET['ref'] == "form") {
			if(empty($id)) {
				$data = null;
			} else {
				$data = $db->query("SELECT * FROM ".PREFIX."videos WHERE id=".intval($id))->row;
			}

			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				$input['title'] = Io::GetVar("POST", "title", "fullhtml");

				$errors  = array();
				//$basedir = _PLUGIN;
				$basedir = BASEDIR;
				$config['BASE_DIR'] 	= $basedir;
				$config['LOG_DIR'] 		= $basedir._PLUGIN.'/tmp/logs';
				$config['VDO_DIR'] 		= $basedir.'uploads/videos/';

				$config['phppath'] 		= 'C:\AppServ\php7/php.exe';
				$config['mplayer'] 		= '/usr/local/bin/mplayer';
				$config['mencoder'] 	= '/usr/local/bin/mencoder';
				$config['ffmpeg'] 		= $basedir.'assets/ffmpeg';

				$config['video_max_size'] = '50000';
				$config['video_allowed_extensions'] = ['avi','mpg','mov','asf','mpeg','xvid','divx','3gp','mkv','3gpp','mp4','rmvb','rm','dat','wmv','flv','ogg'];

				$upload_id          = mt_rand(). '_' .time();
				$upload_max_size    = $config['video_max_size']*1024*1024;
				$video              = array('title' => '', 'category' => 0);
			
				$maxUpload      	= (int)(ini_get('upload_max_filesize'));
				$maxPost        	= (int)(ini_get('post_max_size'));

				if (!empty($_FILES['video_file']['name'])) {
					$upload = $_FILES['video_file'];
				    $fileName = $_FILES['video_file']['name'];
				    $fileSize = $_FILES['video_file']['size'];
				    $fileTmpName  = $_FILES['video_file']['tmp_name'];
				    $fileType = $_FILES['video_file']['type'];
				    $fileExtension = explode('.', implode('.', $fileName));
				    $fileExtension = strtolower(end($fileExtension));

			        if (!in_array($fileExtension, $config['video_allowed_extensions'])) {
			            $errors[]       = 'Invalid video extension. Allowed extensions: '.implode('|', $config['video_allowed_extensions']).'!';
			        }
				}


				if($input['title'] == '') $errors[] = "Title field is empty";

				if(!sizeof($errors)) {
					$result = $db->query("INSERT INTO #__videos SET title='".$input['title']."'");
					$video_id = $db->getLastId();

					$video_key     = substr(md5($video_id),11,20);
					$db->query("UPDATE #__videos SET uniq_id = '" .$video_key. "' WHERE id = " .intval($video_id). " LIMIT 1");
					
			        $vdoname    = $video_id. '.' .$fileExtension;
			        $flvdoname  = $video_id. '.flv';
			        
			        $vdo_path   = $config['VDO_DIR'].$vdoname;

			        if (move_uploaded_file($upload['tmp_name'][0], $vdo_path)) {

						// ---------------------------------------------------------------------------
						// ---------------------------------------------------------------------------
						function run_in_background($Command, $Priority = 0){
							if($Priority) $PID = shell_exec("nohup nice -n $Priority $Command 2> /dev/null & echo $!");
						    else $PID = shell_exec("nohup $Command 2> /dev/null & echo $!");
						    return($PID);
						}
							
						exec($config['mplayer']. ' -vo null -ao null -frames 0 -identify "' .$vdo_path. '"', $p);

						// Create our FFMPEG-PHP class  
						$ffmpegObj = new ffmpeg_movie($vdo_path);
						debug($ffmpegObj);
						// while(list($k,$v)=each($p)){
						// 	if (preg_match("/^ID_.+\=.+/", $v)){
						// 		$lx = explode("=", $v);
						// 		$vidinfo[$lx[0]] = $lx[1];
						// 	}
						// }
						// $duration = $vidinfo['ID_LENGTH'];
						// $height = $vidinfo['ID_VIDEO_HEIGHT'];
						// $width = $vidinfo['ID_VIDEO_WIDTH'];
						// $fps = $vidinfo['ID_VIDEO_FPS'];
						// $id_video_format = $vidinfo['ID_VIDEO_FORMAT'];
						// $cgi = ( strpos(php_sapi_name(), 'cgi') ) ? 'env -i ' : NULL;

						// // Proc
			   //          $cmd = $cgi.$config['phppath']
					 //    	." ".$config['BASE_DIR']."/scripts/convert_videos.php"
					 //    	." ".$vdoname
					 //    	." ".$video_id
					 //    	." ".$vdo_path
					 //    ."";
			   //          log_conversion($config['LOG_DIR']. '/' .$video_id. '.log', $cmd);
			   //          $lg = $config['LOG_DIR']. '/' .$video_id. '.log2';
			   //          run_in_background($cmd.' > '.$lg);
			        } else {
			        	$errors[] = 'Failed to move uploaded file!';
			        }
				} else {
					echo implode('<br>', $errors);
				}
			} else {
				$action = empty($id) ? Url::link('videos/video&amp;ref=form') : Url::link('videos/video&amp;ref=form&amp;id='.$id);
				echo Form::Open($action, array('enctype'=>'multipart/form-data'));

					echo Form::AddElement(array('element'=>'text', 'label'=>'Title', 'name'=>'title'));
					echo Form::AddElement(array('element'=>'file', 'label'=>'Video File', 'name'=>'video_file[]'));

					echo Form::AddElement(array('element'=>'submit', 'value'=>'Upload'));
				echo Form::Close();
			}
		} else {
			debug(_PLUGIN);
		}
	}
}