<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class userLevelPermissionController {
	function index() {
		global $db, $User, $config;

		$role_id = Io::GetVar("GET", "role_id", "int");

		$permissions = $db->query("SELECT * FROM #__user_levels_permissions WHERE user_level_id=".$role_id)->rows;

	    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	    	$permissions_post = isset($_POST['permission']) ? $_POST['permission'] : null;

	    	foreach($permissions_post as $table => $post) {
	    		$input['create_any'] = $post['create_any'];
	    		$input['modify_own'] = $post['modify_own'];
	    		$input['modify_any'] = $post['modify_any'];
	    		$input['delete_own'] = $post['delete_own'];
	    		$input['delete_any'] = $post['delete_any'];
	    		$input['view'] = $post['view'];

	    		$result = $db->query("SELECT COUNT(*) AS total FROM #__user_levels_permissions WHERE user_level_id='{$role_id}' AND controller='{$table}'");
	    		if($result->row['total'] == 0) {
	    			$input['controller'] = $table;
	    			$input['user_level_id'] = $role_id;
	    			dbquery_insert('#__user_levels_permissions', $input, 'save');
		    		redirect(Url::link('user/user_permission&amp;role_id='.$role_id), 0);
	    		} else {
	    			dbquery_insert('#__user_levels_permissions', $input, 'update', 'user_level_id='.intval($role_id));
	    			redirect(Url::link('user/user_group'), 0);
	    		}
	    	}    	
	    } else {
		    if(!empty($permissions)) {
		    	$permissions = $permissions;
		    } else {
		        $permissions[] = array(
		        	'user_level_permission_id'=>'',
		        	'user_level_id'			  =>'',
		            'create_any'	=> '0',
		            'modify_own' 	=> '0',
		            'modify_any' 	=> '0',
		            'delete_own' 	=> '0',
		            'delete_any' 	=> '0',
		            'view' 			=> '1',
		            'controller'	=> '',
		        );
		    }

		    //for($i=0; $i < count($permissions); $i++) unset($permissions[$i]['user_level_permission_id'],$permissions[$i]['user_level_id'],$permissions[$i]['controller']);
		    opentable("<a href='".Url::link('user/user_group')."' data-toggle='tooltip' title='' class='btn btn-default'><i class='fa fa-reply'></i></a>");
		    echo "<form method='post' action=''><table class='table table-condensed table-striped table-bordered table-hover'>";
		    echo "<tr>";
		    	echo "<th class='header'>Table name</th>";
		    	echo "<th align='center' style='text-align: center;'>Create</th>";
		    	echo "<th align='center' style='text-align: center;'>Modify Own</th>";
		    	echo "<th align='center' style='text-align: center;'>Modify Any</th>";
		    	echo "<th align='center' style='text-align: center;'>Delete Own</th>";
		    	echo "<th align='center' style='text-align: center;'>Delete Any</th>";
		    	echo "<th align='center' style='text-align: center;'>Can View</th>";
		    echo "</tr>";
		    $tables = list_tables();
		    foreach($tables as $table) {
		    	for($i=0; $i < count($permissions) && $i < count($table); $i++) {
		    		unset($permissions[$i]['user_level_permission_id'],$permissions[$i]['user_level_id'],$permissions[$i]['controller']);
		    		echo "<tr>";
		    			echo "<td class='head'>".$table."</td>";
		    			$permission = $permissions[$i];
		    			foreach($permission as $k => $v) {
							$chkd = ($v==1) ? 'checked="checked"' : '';
							echo "<td align='center' style='text-align: center;'>";
							echo "<input type='hidden' name='permission[".$table."][".$k."]' value='0' />";
							echo "<input type='checkbox' class='minimal' name='permission[".$table."][".$k."]' " .$chkd. " value='1' />";
							echo "</td>";
		    			}
		    		echo "</tr>";
		    	}
		    }
		   		echo "<button type=\"button\" onclick=\"$(this).parent().find(':checkbox').prop('checked', true);\" class=\"btn btn-link\">Select all</button> / <button type=\"button\" onclick=\"$(this).parent().find(':checkbox').prop('checked', false);\" class=\"btn btn-link\">Deselect All</button>";
		    echo "<input type='submit' name='submit' value='submit'>";
		    echo "</table></form>";
		    closetable();
	    }
	}
}