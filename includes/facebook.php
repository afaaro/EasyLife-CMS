<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

function facebook_autopost($id) {
	global $config, $aidlink;
	

	require_once INFUSIONS."media/inc/assets/facebook/facebook.php";
	$config['site_seo_urls'] 			= 1;
	$config['facebook_api_id']          = "152336491589381";
	$config['facebook_api_key']         = "b4065ba18c8fccbf5cb342debce3de1b";
	$config['facebook_app_page_id']     = "133856583339708";
	$config['facebook_app_page_token']  = "EAACKjJp3rwUBAPTH1N7HjB425jH14ZBa9sCO4L5R4oMOiKWZCZAZATdujjGjQLc8dZAsk4ehX30YziMnZCHqT6RfJ3sfg0WNxYXrrwmZCAZAAZBPUQDxVB50jSGJYikwL2C0ZBZCggY0NHrahCMF9OMRPnQ9sih7pV7FvUc2eo69mvm5QdgNcC9fJK2K1CdqlU56NQZD";
	$page_url = $config['site_url']."fb-callback.php";
	$code = isset($_GET['code']) ? stripinput($_GET['code']) : null;
	
    add_to_head("<meta property='fb:admins' content='124530158268967'>");
    add_to_head("<meta property='fb:pages' content='".$config['facebook_app_page_id']."'>");
	
	$facebook = new Facebook(array(
	    'appId'  => $config['facebook_api_id'],
	    'secret' => $config['facebook_api_key']
	));
	
	$config['permissions'] = "email, user_location, user_birthday, public_actions, publish_pages, manage_pages, public_profile";
	// Get User ID
	$user = $facebook->getUser();
	// login or logout url will be needed depending on current user state.
	if ($user) {
	  	$logoutUrl = $facebook->getLogoutUrl();
	  	echo "<a href='".$logoutUrl."'>Logout</a>";
	} else {
	    $loginUrl = $facebook->getLoginUrl(array('scope' => $config['permissions'])); 
	    echo " <a href='".$loginUrl."'>Sign in with Facebook</a>";
	}
	
	if(!isset($_GET['code'])) {
		$dialog_url = "https://www.facebook.com/dialog/oauth?client_id=".$config['facebook_api_id']."&redirect_uri=".urlencode($page_url)."&scope=".$config['permissions'];
	
		echo " - <a href='{$dialog_url}'>Login Token</a>";
	} else {
		//$token_url = "https://graph.facebook.com/oauth/access_token?".http_build_query(array('type'=>'client_cred','client_id'=>$config['facebook_api_id'],'redirect_uri'=>urlencode($page_url),'client_secret'=>$config['facebook_api_key'],'code'=> $code));
		$token_url = "https://graph.facebook.com/oauth/access_token?client_id=".$config['facebook_api_id']."&redirect_uri=" . urlencode($page_url) . "&client_secret=".$config['facebook_api_key']."&code=".$code;
		$facebook_info = json_decode(file_get_contents($token_url), TRUE);
	
		if(isset($facebook_info['access_token'])) {
	        $array = array('access_token' => $facebook_info['access_token']);
	        $page_info = $facebook->api("/me/accounts", 'GET', $array);
	        if (isset($page_info['data'])) {
	            foreach ($page_info['data'] as $account) {
	                if ($config['facebook_app_page_id'] == $account['id']) {
	                    $access_token = $account['access_token'];
	
	                    debug($access_token);
	                    break;
	                }
	            }
	        }
		}
	}
	
	$share_topics = array();
	$result_query = dbquery("SELECT * FROM ".DB_PREFIX."media_node WHERE id='".intval($id)."' AND fbauto=0 AND status=1");
	while($row = dbarray($result_query)){
	    $post = array(
	        'id'    	=> $row['id'],
	        'title'  	=> $row['title'],
	        'path'		=> $row['path'],
	        'catid'  	=> $row['catid'],
	        'fbauto'    => $row['fbauto'],
	        'image'     => $row['image']
	        );
	    array_push($share_topics, $post);
	}
	
	foreach($share_topics as $share_topic) {
		if($share_topic['fbauto'] == 0) {
		    $post_link = root_path()."media.php?"._NODE."=topics&amp;cat=".$share_topic['path'];
		
		    list($width,$height) = getimagesize(BASEDIR."infusions/media/uploads/inline-images/".$share_topic['image']);
		
		    if($config['site_seo_urls']==1){
		        $link_post = RewriteUrl($post_link);
		    }else{
		        $link_post = $post_link;
		    }
		
		    $params['access_token'] = $config['facebook_app_page_token'];

			$params['link'] = $link_post;
			
		    if(!empty($share_topic['title'])){
		        $params['message'] = strip_tags(Io::Output($share_topic['title']));
		    }
		
		   	// check if topic successfully posted to Facebook
			try {
			    $ret = $facebook->api('/'.$config['facebook_app_page_id'].'/feed', 'POST', $params); // configure appropriately
		
		        // mark topic as posted (ensure that it will be posted only once)
		        $sql = "UPDATE ".DB_PREFIX."media_node SET fbauto = 1 WHERE id = " . $share_topic['id'];
		        if(dbquery($sql) === false) {
		            trigger_error('Wrong SQL: ' . $sql, E_USER_ERROR);
		        }
		        $result = $share_topic['id'] . ' ' . $share_topic['title'] . ' successfully posted to Facebook! \n';
		
			} catch(Exception $e) {
			    $result = $share_topic['id'] . ' ' . $share_topic['title'] . ' FAILED... (' . $e->getMessage() . ') \n';
			}
		    if (iSUPERADMIN) {
		        echo $result;
		    }
		}
	}
}