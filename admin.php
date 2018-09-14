<?php 
error_reporting(-1);
ini_set("display_errors",1);

date_default_timezone_set('Europe/London'); 

define("_LOAD", TRUE);

// Locate config.php and set the basedir path
$folder_level = ""; $i = 0;
while (!file_exists($folder_level."system/storage/config/config.php")) {
    $folder_level .= "../"; $i++;
    if ($i == 7) { die("config.php file not found"); }
}
define("BASEDIR", $folder_level);

//require_once __DIR__.'/maincore.php';
require_once BASEDIR."inc/classes/database.php";
require_once BASEDIR."system/storage/config/dbconfig.php";
$db = new Database(DBHOST, DBUSER, DBPASS, DBNAME);
require_once BASEDIR."inc/inc_helper.php";
require_once BASEDIR."inc/inc_database.php";
require_once BASEDIR."system/functions/crud.php";


echo "<table>";
$result = db_select("SELECT * FROM #__user_levels");
foreach($result as $row) {
	echo "<tr>";
		echo "<td>".$row['user_level_id']."</td>";
		echo "<td>".$row['user_level_name']."</td>";
		echo "<td><a class='btn btn-sm btn-primary' href='admin.php?role_edit&role_id=".$row['user_level_id']."' title='Edit Role'><i style='color:#fff' class='fa fa-edit'></i> Edit permissions</a>  </td>";
	echo "</tr>";
}
echo "</table>";

if(isset($_GET['role_edit'])) {
	$rid = (int) $_GET['role_id'];

    $permissions = db_select("SELECT * FROM #__user_levels_permissions WHERE user_level_id=".$rid);
    if(isset($_POST['submit'])) {
    	$permissions_post = isset($_POST['permission']) ? $_POST['permission'] : null;

    	//debug($permissions_post);
    	foreach($permissions_post as $table => $post) {
    		$result = $db->query("SELECT COUNT(*) AS total FROM #__user_levels_permissions WHERE user_level_id='{$rid}' AND controller='{$table}'");
    		if($result->row['total'] == 0) {
	    		$db->query("INSERT INTO #__user_levels_permissions SET create_any='".$post['create_any']."', modify_own='".$post['modify_own']."', modify_any='".$post['modify_any']."', delete_own='".$post['delete_own']."', delete_any='".$post['delete_any']."', view='".$post['view']."', controller='".$table."', user_level_id='{$rid}'");
    		} else {
	    		$db->query("UPDATE #__user_levels_permissions SET create_any='".$post['create_any']."', modify_own='".$post['modify_own']."', modify_any='".$post['modify_any']."', delete_own='".$post['delete_own']."', delete_any='".$post['delete_any']."', view='".$post['view']."' WHERE user_level_id='{$rid}' AND controller='".$table."'");
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
	    
	    echo "<form method='post' action=''><table>";
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
    }
}


?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>